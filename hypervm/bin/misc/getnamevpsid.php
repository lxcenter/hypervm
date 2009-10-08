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

$sq = new Sqlite(null, "vps");
$list = $sq->getTable(array("vpsid", "nname", "contactemail", "password", "rootpassword"));
foreach($list as $l) {
	$nlist[$l['vpsid']] = $l;
}

lfile_put_serialize($argv[1], $nlist);
dprintr($nlist);

