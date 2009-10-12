<?PHP
//
//    HyperVM, Server Virtualization GUI for OpenVZ and Xen
//
//    Copyright (C) 2000-2009     LxLabs
//    Copyright (C) 2009          LxCenter
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
?>

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

