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

$slave = $argv[1];

$sq = new Sqlite(null, 'pserver');
if (!$sq->getRowsWhere("nname = '$slave'")) {
	print("No slave by $slave\n");
	exit;
}
$driverapp = $gbl->getSyncClass(null, $slave, 'vps');
if ($driverapp !== 'openvz') {
	print("driver for $slave not openvz\n");
	exit;
}

$shift = $argv[2];
if (!$shift) { $shift = 1000; }

$sq = new Sqlite(null, 'vps');
$res = $sq->getRowsWhere("syncserver = '$slave'", array('nname'));
$list = get_namelist_from_arraylist($res);
foreach($list as $l) {
	$o = new Vps(null, $slave, $l);
	$o->get();
	$param['vpsid'] = $o->vpsid + $shift;
	print("Moving $o->nname from $o->vpsid to {$param['vpsid']}\n");
	$o->updatechangeVPSid($param);
	$o->vpsid = $param['vpsid'];
	$o->setUpdateSubaction('changevpsid');
	$o->was();
}
