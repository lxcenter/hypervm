<?php 
// 
// This file is part of the HyperVM installer
// dterweij 17aug09
// Installing OS Templates, OpenVZ yum/up2date repo
// 
//
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
		installOstemplates($virtualization);
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

//
// This function is changed.
// It downloads only two base ostemplates
// It only download ostemplates for virtualisation type
// added /base/ and /extra/ to download server to split base and extra os templates
// dterweij 17aug09
//
function installOstemplates($virtualization)
{
	if ($virtualization === 'xen') {
	system("mkdir -p /home/hypervm/xen/template/ ; cd /home/hypervm/xen/template/ ; wget -nd -np -c -r  download.lxcenter.org/download/xentemplates/base/;");
	}
	if ($virtualization === 'openvz') {
	system("mkdir -p /vz/template/cache ; cd /vz/template/cache/ ; wget -nd -np -c -r  download.lxcenter.org/download/openvztemplates/base/;");
	}
}

function xen_install($installtype)
{

        // newest OpenVZ kernels comes provides kernel-xen (!?) which breaks RHEL5 instalations
        // If openvz.repo file exist remove it imediately before install kernel-xen
        if (file_exists("/etc/yum.repos.d/openvz.repo")) {
            unlink("/etc/yum.repos.d/openvz.repo");
        }
	$list = array("kernel-xen", "xen", "virt-manager");
	run_package_installer($list);
	if (file_exists("/boot/vmlinuz-2.6-xen") && !file_exists("/boot/hypervm-xen-vmlinuz")) {
		system("cd /boot ; ln -s vmlinuz-2.6-xen hypervm-xen-vmlinuz; ln -s initrd-2.6-xen.img hypervm-xen-initrd.img");
	}
        if (file_exists("/etc/init.d/libvirtd")) {
            system("chkconfig libvirtd off");
        }
	system("chkconfig xendomains on");
        if (is_centossix()) {
        	system("../bin/grub-bootxen.sh");
                lxfile_cp("../file/sysfile/xen/xend-config.sxp", "/etc/xen/xend-config.sxp");
        }
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


