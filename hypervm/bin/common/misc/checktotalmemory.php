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
include_once "htmllib/lib/include.php";

if ($sgbl->isHyperVm()) {
	exit(10);
}


if (lxfile_exists("/proc/user_beancounters")) {
	$list = lfile("/proc/user_beancounters");
	foreach($list as $l) {
		$l = trimSpaces($l);
		if (!csb($l, "privvmpages")) {
			continue;
		}

		$d = explode(" ", $l);

		$mem = $d[3]/ 256;
	}
	exit(11);
} else if (lxfile_exists("/proc/xen")) {
	exit (11);
} else {
	$mem = pserver__linux::getTotalMemory();
	$mem += 200;
}

if ($mem < 180) {
	exit(15);
}

exit(11);
