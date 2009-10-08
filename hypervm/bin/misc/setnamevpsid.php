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

if (!isset($argv[1])) {
	print("Need file name\n");
	exit;
}

$file = $argv[1];

$opt = parse_opt($argv);

$slave = null;
if (isset($opt["slave-id"])) {
	$slave = $opt["slave-id"];
}


$sq = new Sqlite(null, "vps");

$res = $sq->getTable();

$nlist = lfile_get_unserialize($file);

if (!$nlist) {
	print("Could not read file $file\n");
	exit;
}

if (!$nlist) {
	print("Cannot read db file..... Exiting..\n");
	exit;
}
//dprintr($nlist);

foreach($res as $r) {
	if ($slave) {
		if ($r['syncserver'] !== $slave) {
			continue;
		}
	} else {
		if (!csa($r['nname'], "openvz")) {
			print("Not imported ... Skipping\n");
			continue;
		}
	}

	print("Fixing {$r['nname']} ..\n");
	print_r($nlist[$r['vpsid']]);
	print("\n");

	if (!isset($nlist[$r['vpsid']])) {
		print("No Data entry for {$r['vpsid']} {$r['nname']}\n");
		continue;
	}

	$l = $nlist[$r['vpsid']];
	$newname = $l['nname'];
	$contact = $l['contactemail'];
	$password = $l['password'];
	$rootpassword = $l['rootpassword'];


	$sq->rawQuery("update vps set nname = '$newname' where nname = '{$r['nname']}'");
	$sq->rawQuery("update vps set contactemail = '$contact' where nname = '{$r['nname']}'");
	$sq->rawQuery("update vps set password = '$password' where nname = '{$r['nname']}'");
	$sq->rawQuery("update vps set rootpassword = '$rootpassword' where nname = '{$r['nname']}'");
}

