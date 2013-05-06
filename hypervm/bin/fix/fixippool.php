<?php 

include_once "htmllib/lib/include.php"; 

initProgram('admin');

$list = $login->getList('ippool');

$sq = new Sqlite(null, 'tmpipassign');
$res = $sq->getTable();
foreach($res as $r) {
	if (!ippool::checkIfAlreadyAssigned('vps', $r['nname'])) {
		$sq->rawQuery("delete from tmpipassign where nname = '{$r['nname']}';");
		continue;
	}

	if ((time() - $r['ddate']) > 40) {
		$sq->rawQuery("delete from tmpipassign where nname = '{$r['nname']}';");
	}
}

foreach($list as $l) {

	$l->freeflag = 'on';

	$fip = $l->getFreeIp(10000, 'any');


	if ($fip) { 
		$l->freeflag = 'on';
	} else { 
		$l->freeflag = 'dull';
	}

	$l->setUpdateSubaction();
	$l->write();
}

