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


class component__windows extends lxDriverClass {


static function getListVersion($syncserver, $list)
{

	$list[]['componentname'] = 'mysql';
	$list[]['componentname'] = 'perl';
	$list[]['componentname'] = 'php';
	$list[]['componentname'] = 'IIS';
	$list[]['componentname'] = 'Photoshop';
	$list[]['componentname'] = 'InternetExplorer';

	try {
		$obj = new COM("Winmgmts://./root/cimv2");
	} catch (exception $e) {
		throw new lxException("com_failed", 'disk');
	}


	$nlist = $obj->execQuery("select * from Win32_Product");

	foreach($nlist as $k => $l) {
		$name = $l->Name;
		$sing['nname'] = $name . "___" . $syncserver;
		$sing['componentname'] = $name;
		$sing['status'] = "off";
		$sing['version'] = "Not Installed";

		$sing['version'] = $l->Version;
		$sing['status'] = "on";
		/*
		if (isOn($sing['status'])) {
			$sing['full_version'] = `rpm -qi $name`; 
		} else {
			$sing['full_version'] = $sing['version'];
		}
	*/
		$ret[] = $sing;
	}
	return $ret;
}

}
