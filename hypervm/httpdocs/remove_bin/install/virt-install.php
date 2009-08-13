<?php 
include_once "htmllib/lib/include.php"; 

virt_install_main();

function virt_install_main()
{

	global $argv;

	$opt = parse_opt($argv);

	$virtualization = $opt['virtualization-type'];
	$installtype = $opt['install-type'];
	$skipostemplate = false;
	if (isset($opt['skipostemplate'])) { $skipostemplate = true; }


	if ($virtualization === 'openvz') {
		openvz_install($installtype);
	} else if ($virtualization === 'xen') {
		xen_install($installtype);
	}

	if ($installtype !== 'slave' && !$skipostemplate) {
		installOstemplates();
	}

	print("Executing Update Cleanup... Will take a long time to finish....\n");
	lxshell_return("__path_php_path", "../bin/common/updatecleanup.php", "--type=$installtype");

}

function openvz_install($installtype)
{
	$arch = `arch`;
	$arch = trim($arch);

	if ($arch === 'x86_64') {
		$list = array("vzctl.x86_64", "vzquota.x86_64", "ovzkernel.x86_64");
	} else {
		$list = array("vzctl", "vzquota", "ovzkernel-PAE");
	}

	lxfile_cp("../file/openvz.repo", "/etc/yum.repos.d/openvz.repo");

	run_package_installer($list);

}

function installOstemplates()
{
	system("mkdir -p /home/hypervm/xen/template/ ; cd /home/hypervm/xen/template/ ; wget -nd -np -c -r  download.lxlabs.com/download/vmtemplate/;");
	system("mkdir -p /vz/template/cache ; cd /vz/template/cache/ ; wget -nd -np -c -r  download.lxlabs.com/download/vpstemplate/;");
}

function xen_install($installtype)
{


	$list = array("kernel-xen", "xen", "virt-manager");
	run_package_installer($list);
	if (file_exists("/boot/vmlinuz-2.6-xen") && !file_exists("/boot/hypervm-xen-vmlinuz")) {
		system("cd /boot ; ln -s vmlinuz-2.6-xen hypervm-xen-vmlinuz; ln -s initrd-2.6-xen.img hypervm-xen-initrd.img");
	}
	system("chkconfig xendomains on");
	system("chkconfig libvirtd off");
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


