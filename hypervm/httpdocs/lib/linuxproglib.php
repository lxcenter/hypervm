<?php 

function os_update_server()
{
	$list = array("rrdtool", "lxlighttpd", "lxphp", "lxzend");
	//$list = array("rrdtool");
	$package = implode(" ", $list);
	system("yum -y install $package > /dev/null 2>&1 &");
}


function os_update_openvz($highmem = false)
{
	$list = array("unzip", "vzctl", "vzctl-lib", "rrdtool", "vzquota");

	if ($highmem) {
		$list[] = 'ovzkernel-enterprise';
	} else {
		$list[] = 'ovzkernel';
	}

	$package = implode(" ", $list);
	system("PATH=\$PATH:/usr/sbin up2date --nosig --install $package", $return_value);
	system("mkdir -p /vz/template/cache ; cd /vz/template/cache/ ; wget -nd -np -c -r  download.lxcenter.org/download/vpstemplate/;");
}

