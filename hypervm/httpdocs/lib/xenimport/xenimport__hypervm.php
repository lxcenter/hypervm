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
//include_once "lib/include.php";

//__xenimport_get_data();

function __xenimport_get_data()
{
	lxfile_mkdir("/home/oldxenconfig-hypervm");
	$list = lscandir_without_dot("/home/xen");
	foreach($list as $l) {
		if (!cse($l, ".vm")) {
			continue;
		}
		$vm[] = __xenimport_parse_config("/home/xen/$l/$l.cfg");
		//lxfile_mv("/etc/xen/$l", "/home/oldxenconfig-hypervm");
	}
	dprintr($vm);
	return $vm;
}


function __xenimport_parse_config($file)
{
	$list = lfile_trim($file);

	foreach($list as $l) {
		if (!csa($l, "=")) {
			continue;
		}
		list($var, $val) = explode("=", $l);

		$var = trim($var);
		$val = trim($val);

		$val = trim($val, "'");

		switch($var) {
			case "memory":
				$ret['memory'] = $val;
				break;

			case "name":
				$ret['name'] = strtolower(strtil($val, '.vm'));
				break;


			case "disk":
				__xenimport_parsedisk($ret, $val);
				break;

		}
	}
	return $ret;


}

function __xenimport_parsedisk(&$ret, $val)
{
	preg_match("/\['phy:([^']*)'.*'phy:([^']*)'\]/i", $val, $matches);
	$ret['type'] = 'lvm';

	if (!isset($matches[0])) {
		$ret['type'] = 'file';
		preg_match("/\['file:([^']*)'.*'file:([^']*)'\]/i", $val, $matches);
	}

	if (!isset($matches[0])) {
		throw new lxException("could_not_parse_disk_string");
	}
	$diskstring = $matches[1];
	$disk = explode(",", $matches[1]);

	if (csb($disk[0], "/dev/")) { $disk[0] = strfrom($disk[0], "/dev/"); }

	if ($ret['type'] === 'file') {
		$location = "/home/xen";
		$mdn = explode("/", $disk[0]);
		$maindiskname = array_pop($mdn);
	} else {
		list($location, $maindiskname) = explode("/", $disk[0]);
	}

	$ret['location'] = $location;
	$ret['maindiskname'] = $maindiskname;

	$swap = explode(",", $matches[2]);
	if (csb($swap[0], "/dev/")) { $swap[0] = strfrom($swap[0], "/dev/"); }

	if ($ret['type'] === 'file') {
		$location = "/home/xen";
		$swp = explode("/", $swap[0]);
		$swapdiskname = array_pop($swp);
	} else {
		list($location, $swapdiskname) = explode("/", $swap[0]);
	}

	if ($location !== $ret['location']) {
		throw new lxException("swap_disk_location_not_same", 'nname', "{$ret['name']}: {$ret['location']}");
	}

	$ret['swapdiskname'] = $swapdiskname;

}


