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

$plist = parse_opt($argv);

$class = $argv[1];
if (!isset($argv[2])) {
	$driverapp = $gbl->getSyncClass(null, 'localhost', $class);
	print("Driver for $class is $driverapp\n");
	exit;
}

$pgm = $argv[2];



$server = $login->getFromList('pserver', 'localhost');

$os = $server->ostype;
include "../file/driver/$os.inc";


$dr = $server->getObject('driver');

if (!array_search_bool($pgm, $driver[$class])) {
	$str = implode(" ", $driver[$class]);
	print("The driver name isn't correct: Available drivers for $class: $str\n");
	exit;
}


$v = "pg_$class";
$dr->driver_b->$v = $pgm;

$dr->setUpdateSubaction();

$dr->write();

print("Successfully changed Driver for $class to $pgm\n");






