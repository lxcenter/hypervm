<?PHP
//
//    HyperVM, Server Virtualization GUI for OpenVZ and Xen
//
//    Copyright (C) 2000-2009     LxLabs
//    Copyright (C) 2009-2014     LxCenter
//
//    This program is free software: you can redistribute it and/or modify
//    it under the terms of the GNU Affero General Public License as
//    published by the Free Software Foundation, either version 3 of the
//    License, or (at your option) any later version.
//
//    This program is distributed in the hope that it will be useful,
//    but WITHOUT ANY WARRANTY; without even the implied warranty of
//    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
//    GNU Affero General Public License for more details.
//
//    You should have received a copy of the GNU Affero General Public License
//    along with this program.  If not, see <http://www.gnu.org/licenses/>.
//
// PHp4, without the lxlabs infrastructure... The code is very bad, primarily because it falls outside the lxlabs
// framework, and has to work on php4 too, which is something we have no experience in. We need to move as much of the
// activities to code after the installation of the lxlabs framework.

function lxins_main()
{
    global $argv;

    $opt = parse_opt($argv);


    $installtype = $opt['install-type'];

    $highmem = false;
    if (isset($opt['has-highmem'])) {
        $highmem = true;
    }

    if (!isset($opt['virtualization-type'])) {
        print("Need virtualization type --virtualization-type=xen/openvz/NONE\n");
        exit;
    } else {
        $virtualization = $opt['virtualization-type'];
    }

    $skipostemplate = false;
    if (isset($opt['skip-ostemplate'])) {
        $skipostemplate = true;
    }

    if (array_search($virtualization, array("xen", "openvz", "NONE")) === false) {
        print("Only xen/openvz/NONE are curently supported\n");
        exit;
    }

    $dbroot = isset($opt['db-rootuser']) ? $opt['db-rootuser'] : "root";
    $dbpass = isset($opt['db-rootpassword']) ? $opt['db-rootpassword'] : "";

    $osversion = find_os_version();

    if (file_exists("/usr/local/lxlabs/hypervm")) {
        print("HyperVM is installed do you wish to continue?(No/Yes):\n");
        flush();
        $stdin = fopen('php://stdin', 'r');
        $argq = fread($stdin, 5);
        $arg = trim($argq);
        if (!($arg == 'y' || $arg == 'yes' || $arg == 'Yes' || $arg == 'Y' || $arg == 'YES')) {
            print("Exiting.....\n");
            exit;
        }
    }


    if ($virtualization === 'xen') {
        if (!char_search_beg($osversion, "centos-6") && !char_search_beg($osversion, "centos-5") && !char_search_beg($osversion, "rhel-5") && !char_search_beg($osversion, "rhel-6")) {
            print("Xen is only supported on CentOS 5 and CentOS 6 distributions with HyperVM as management system\n");
            exit;
        }
    }


    if ($virtualization === 'openvz') {
        if (!char_search_beg($osversion, "centos-6") && !char_search_beg($osversion, "centos-5") && !char_search_beg($osversion, "rhel-5") && !char_search_beg($osversion, "rhel-6")) {
            print("OpenVZ is only supported on CentOS 5 and CentOS 6 distributions with HyperVM as management system\n");
            exit;
        }
    }

    //install_rhn_sources($osversion);
    install_yum_repo($osversion);

    exec("groupadd lxlabs");
    exec("useradd lxlabs -g lxlabs -s '/sbin/nologin'");

    // New since HyperVM 2.1.0 hypervm-core-php yum-plugin-replace
    $list = array("which", "lxlighttpd", "zip", "unzip", "hypervm-core-php", "curl","yum-plugin-replace");

    if ($installtype !== 'slave') {
        $mysql = array("mysql", "mysql-server");
        $list = array_merge($list, $mysql);
    }

    // When installing development version, don't loop yum (.git found)
    if (!file_exists('/usr/local/lxlabs/.git')) {

        while (true) {
            run_package_installer($list);
            if (file_exists("/usr/local/lxlabs/ext/php/php")) {
                break;
            } else {
                // This can be a endless loop, needs another check!
                print("Yum Gave Error... Trying Again...\n");
            }

        }

    } else {
        run_package_installer($list);
    }

    if ($installtype !== 'slave') {
        check_default_mysql($dbroot, $dbpass);
    }


    $xenfailed = false;

//  why is that?
//	exec("killall wget");

    system("mkdir -p /usr/local/lxlabs/hypervm");
    chdir("/usr/local/lxlabs/hypervm");
    system("mkdir -p /usr/local/lxlabs/hypervm/log");

    // Prevents deleting the development package
    if (!file_exists('/usr/local/lxlabs/.git')) {
        @ unlink("hypervm-current.zip");
    }

    if (file_exists('/usr/local/lxlabs/.git')) {
        echo 'Development GIT version found. Skipping download from LxCenter.';
    } else {
        system("wget http://download.lxcenter.org/download/hypervm/production/hypervm/hypervm-current.zip");
    }

    system("unzip -oq hypervm-current.zip", $return);

    if ($return) {
        print("\nUnzipping the core Failed.. Most likely it is corrupted. Please contact the support personnel\n");
        exit;
    }
    unlink("hypervm-current.zip");
    system("chown -R lxlabs:lxlabs /usr/local/lxlabs/");
    $dir_name = dirname(__FILE__);

    fix_network_forwarding();

    system("mkdir -p /usr/local/lxlabs/hypervm/etc/");
    @ unlink("/usr/local/lxlabs/hypervm/etc/install_xen");
    @ unlink("/usr/local/lxlabs/hypervm/etc/install_openvz");
    touch("/usr/local/lxlabs/hypervm/etc/install_$virtualization");
    chdir("/usr/local/lxlabs/hypervm/httpdocs/");
    system("/bin/cp /usr/local/lxlabs/hypervm/httpdocs/htmllib/filecore/php.ini /usr/local/lxlabs/ext/php/etc/php.ini");
    system("/usr/local/lxlabs/ext/php/php ../bin/install/create.php --install-type=$installtype --db-rootuser=$dbroot --db-rootpassword=$dbpass");

    system("chmod 755 /etc/init.d/hypervm");
    system("/sbin/chkconfig hypervm on");
    system("/sbin/chkconfig iptables off");

    $skiparg = null;
    if ($skipostemplate) {
        $skiparg = "--skipostemplate=true";
    }

    if ($virtualization === "NONE") {
        print("No Virtualization has been chosen. It is assumed that it is an existing installation\n");
    } else {
        print("Virtualization is $virtualization. Installing $virtualization Components\n");
    }

//
// call script to install base OS templates and OpenVZ repo
//
    passthru("/usr/local/lxlabs/ext/php/php ../bin/install/virt-install.php --install-type=$installtype --virtualization-type=$virtualization $skiparg");


    echo smart_wordwrap("\n\n\nCongratulations!.\nHyperVM has been installed successfully on your server as $installtype \n");

    if ($installtype === 'master') {
        echo smart_wordwrap("\nYou can browse to the administration interface at:\n");
        echo smart_wordwrap("Secure - https://<ip-address>:8887\n");
        echo smart_wordwrap("Normal -  http://<ip-address>:8888\n\n");
        echo smart_wordwrap("The login and password are 'admin' 'admin'. After Logging in, you will have to change your password to something more secure.\n");
        echo smart_wordwrap("Thanks for choosing HyperVM to manage your Server, and allowing us to be of service.\n");
    } else {
        echo smart_wordwrap("You should open the port 8889 on this server, since this is used for the communication between master and slave.\n");
        echo smart_wordwrap("To access this slave, go admin->slaves->add slave, give the ip/machine name of this server. The password is 'admin'. The slave will appear in the list of slaves, and you can access it just like you access localhost.\n");
    }

    if ($virtualization === 'openvz') {
        echo smart_wordwrap("\n***There is one more step you have to do to make this complete. Open /etc/grub.conf, and change the 'default=1' line to 'default=0', and reboot this machine. You will be rebooted into the OpenVZ kernel and will able to manage VPSes from the HyperVM interface.\n");
    } else if ($virtualization === 'xen') {
        echo smart_wordwrap("\n**** You will have to reboot for the XEN kernel to take effect. Once rebooted, you will able to manage XEN virtual machines using the HyperVM interface.\n");
    }

    echo smart_wordwrap("\n\nExtra note:\n");
    echo smart_wordwrap("To install extra XEN and/or OpenVZ OS templates please run:\n\n");
    echo smart_wordwrap("sh /script/install-extra-ostemplates\n");
    echo smart_wordwrap("\nThese templates are left out the install process to speed up the HyperVM installation. By default only CentOS 5 and HostInBox(Kloxo) OS templates are installed.\n\n");
    echo smart_wordwrap("\n#!# Reboot your system to boot into the right kernel #!#\n\n");

    if (file_exists('/usr/local/lxlabs/.git')) {
        echo smart_wordwrap("Remember, you installed a Development version. Do not use it on production servers!\n\n");
    }


}

function fix_network_forwarding()
{
    $list = file("/etc/sysctl.conf");

    foreach ($list as $__l) {
        if (strstr($__l, "net.ipv4.ip_forward") !== false) {
            $newlist[] = "net.ipv4.ip_forward = 1\n";
        } else {
            $newlist[] = $__l;
        }
    }

    if (!empty($newlist)) {
        our_file_put_contents("/etc/sysctl.conf", implode("", $newlist));
    }
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

include_once "../install_common.php";
lxins_main();
