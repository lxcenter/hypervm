<?php
include_once "../install_common.php";
lxins_main();

function lxins_main()
{
	if (hypervm_exists_continue()) {
		print "Exiting.....";
	};
	global $argv;
	$opt = parse_opt($argv);
	$installtype = isset($opt['install-type']) ? $opt['install-type'] : "master";
	$skipostemplate = isset($opt['skip-ostemplate']) ? true : false;
	$skiparg = $skipostemplate ? "--skipostemplate=true" : null;
	$virtualization = isset($opt['virtualization-type']) ? $opt['virtualization-type'] : "";

	if (array_search($virtualization, array("xen", "openvz", "NONE")) === false) {
		print "Only xen/openvz/NONE are curently supported\n";
		print "Need virtualization type --virtualization-type=xen/openvz/NONE\n";
		exit;
	}
	
	$dbroot = isset($opt['db-rootuser']) ? $opt['db-rootuser']: "root";
	$dbpass = isset($opt['db-rootpassword']) ? $opt['db-rootpassword']: "";

	$osversion = "";#find_os_version();

	if (!supported_virtualization($virtualization,$osversion)) exit();

	install_hypervm($installtype,$dbroot,$dbpass,$virtualization);

	if ( $virtualization != "NONE" ) {
		if ( install_virtualization($virtualization,$installtype,$skiparg) ) print "$virtualization has been successfully installed";
		else print "An error occurred while installing $virtualization.";
	}
	else print "No virtualization has been chosen.  It is assumed that it is or will be installed manually";
	print success_message($installtype,$virtualization);
}

function fix_network_forwarding()
{
	$list = file("/etc/sysctl.conf");

	foreach($list as $__l) {
		if (strstr($__l, "net.ipv4.ip_forward") !== false) {
			$newlist[] = "net.ipv4.ip_forward = 1\n";
		} else {
			$newlist[] = $__l;
		}
	}

	our_file_put_contents("/etc/sysctl.conf", implode("", $newlist));
	shell_exec("sysctl -p");
}

function run_package_installer($list)
{
	$package = implode(" ", $list);
	print("Installing packages $package...\n");
	if (file_exists("/usr/bin/yum")) {
		system("yum -y install $package", $return_value);
	} else {
		system("PATH=\$PATH:/usr/sbin up2date --nosig $package", $return_value);
	}
}

function hypervm_exists_continue()
{
	if(file_exists("/usr/local/lxlabs/hypervm")) {
		print "HyperVM is installed do you wish to continue?(No/Yes):\n";
		flush();
		$stdin = fopen('php://stdin','r');
		$argq = fread($stdin, 5);
		$arg = trim($argq);
		if( strtolower(substr($arg,0,1)) == "y" ){
			return true;
		}
	}
	return false;
}

function supported_virtualization($virt,$os)
{
        if ($virt == 'xen') {
                if (!char_search_beg($os, "fedora-9") && !char_search_beg($os, "centos-5") && !char_search_beg($os, "rhel-5")) {
                        print("Xen is only supported on Fedora-9 or Centos-5\n");
                        return false;
                }
        }

        if ($virt == 'openvz') {
                if (!char_search_beg($os, "centos") && !char_search_beg($os, "rhel")) {
                        print("Openvz is only supported on centos, rhel 4/5\n");
                        return false;
                }
        }
	return true;
}

function install_virtualization($virtualization,$installtype,$skiparg)
{
	print "Installing $virtualization virtualization components.\n";
	passthru("php ../bin/install/virt-install.php --install-type=$installtype --virtualization-type=$virtualization $skiparg");
}

function success_message($installtype,$virtualization)
{
        print"Congratuations. hyperVM has been installed succesfully on your server as $installtype \n";

        if ($installtype == 'master') {
                print "You can connect to the server at https://<ip-address>:8887 or http://<ip-address>:8888\n";
                print "Please note that first is secure ssl connection, while the second is normal one.\n";
                print "The login and password are 'admin' 'admin'. After Logging in, you will have to change your password to something more secure\n";
                print "Thanks for choosing hyperVM to manage your Server, and allowing us to be of service\n";
        }

	else {
                print "You should open the port 8889 on this server, since thisis used for the communication between master and slave\n";
                print "To access this slave, go admin->slaves->add slave, give the ip/machine name of this server. The password is 'admin'. The slave will appear in the list of slaves, and you can access it just like you access localhost\n";
	}
        if ($virtualization == 'openvz') {
                print "\n***There is one more step you have to do to make this complete. Open /etc/grub.conf, and change the 'default=1' line to 'default=0', and reboot this machine. You will be rebooted into the openvz kernel and will able to manage vpses from the hyperVM interface\n";
        }
		else if ($virtualization == 'xen'){
                print "\n**** You will have to reboot for the xen kernel to take effect. Once rebooted, you will able to manage xen virtual machines using the hyperVM interface\n";
        }
}

function install_hypervm($installtype,$dbroot,$dbpass,$virtualization)
{
        exec("groupadd lxlabs");
        exec("useradd -g lxlabs -s '/sbin/nologin' lxlabs");

        if ($installtype !== 'slave') check_default_mysql($dbroot, $dbpass);

        system("mkdir -p /usr/local/lxlabs/hypervm");
        chdir("/usr/local/lxlabs/hypervm");
        system("mkdir -p /usr/local/lxlabs/hypervm/log");
        @ unlink("hypervm-current.zip");
        //WHLsystem("wget http://download.lxlabs.com/download/hypervm/production/hypervm/hypervm-current.zip");
	//WHLsystem("unzip -oq hypervm-current.zip", $return);

	if ($return) {
            print "Unzipping the core Failed.. Most likely it is corrupted. Please contact the support personnel\n";
            exit;
        }
        unlink("hypervm-current.zip");
        system("chown -R lxlabs:www /usr/local/lxlabs/");
        $dir_name=dirname(__FILE__);

        #fix_network_forwarding();

        system("mkdir -p /usr/local/lxlabs/hypervm/etc/");
        @ unlink("/usr/local/lxlabs/hypervm/etc/install_xen");
        @ unlink("/usr/local/lxlabs/hypervm/etc/install_openvz");
        touch("/usr/local/lxlabs/hypervm/etc/install_$virtualization");
        chdir("/usr/local/lxlabs/hypervm/httpdocs/");
        system("/bin/cp /usr/local/lxlabs/hypervm/httpdocs/htmllib/filecore/php.ini /usr/local/lxlabs/ext/php/etc/php.ini");
        system("php ../bin/install/create.php --install-type=$installtype --db-rootuser=$dbroot --db-rootpassword=$dbpass");

        system("chmod 755 /etc/init.d/hypervm");
        system("/sbin/chkconfig hypervm on");
        system("/sbin/chkconfig iptables off");
}
