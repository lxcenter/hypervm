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
