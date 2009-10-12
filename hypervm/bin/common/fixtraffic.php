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

$day = 1;

$list = parse_opt($argv);

if (isset($list['class'])) {
	$class = $list['class'];
} else {
	print("Need --class=\n");
	exit;
}

if (isset($list['day'])) {
	$day = $list['day'];
} else {
	print("Day not set... Defaulting to $day\n");
}


$oldtime = time() - $day * 24 * 3600;

$sq = new Sqlite(null, "{$class}traffic");

$res = $sq->getTable();

foreach($res as $r) {

	if (!csa($r['nname'], ":")) {
		continue;
	}

	$t = explode(":", $r['nname']);

	$ot = $t[1];
	if ($ot > $oldtime) {
		print("deleting $oldtime {$r['nname']}\n");
		$sq->rawQuery("delete from {$class}traffic where nname = '{$r['nname']}'");
	} else {
		//print("not deleting $oldtime {$r['nname']}\n");
	}
}


$c = "{$class}traffic";
$laccess = new $c(null, null, '__last_access_domain_');
$laccess->get();

if ($laccess->timestamp > $oldtime) {
	$laccess->timestamp = $oldtime;
	$laccess->setUpdateSubaction();
	$laccess->write();
}

system("lphp.exe ../bin/gettraffic.php");
system("lphp.exe ../bin/collectquota.php");




