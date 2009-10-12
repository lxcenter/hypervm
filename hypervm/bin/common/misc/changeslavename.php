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

initProgram('admin');

if (!$sgbl->isHyperVM()) {
	print("Only implemented for hyperVM\n");
	exit;
}

$oslave = $argv[1];
$nslave = $argv[2];

if (!$nslave) {
	print("Usage: change-slave-name old-slave-id new-slave-id\n");
	exit;
}

$sq = new Sqlite(null, "pserver");

$r = $sq->getRowsWhere("nname = '$oslave'");
if (!$r) {
	print("No slave as $oslave\n");
	exit;
}

$r = $sq->getRowsWhere("nname = '$nslave'");
if ($r) {
	print("slave $nslave already exists\n");
	exit;
}
$sq->rawQuery("update driver set nname = '$nslave' where nname = '$oslave'"); 
$sq->rawQuery("update vps set syncserver = '$nslave' where syncserver = '$oslave'");
$sq->rawQuery("update pserver set syncserver = '$nslave' where nname = '$oslave'");
$sq->rawQuery("update pserver set nname = '$nslave' where nname = '$oslave'");
$sq->rawQuery("update dirlocation set nname = '$nslave' where nname = '$oslave'");
$sq->rawQuery("update ipaddress set syncserver = '$nslave' where syncserver = '$oslave'");



