<?php 

include_once "htmllib/lib/include.php"; 

initProgram('admin');

$login->loadAllObjects('vps');
$list = $login->getList('vps');

foreach($list as $c) {
	$dnslist = $c->getList('reversedns');
	foreach($dnslist as $dns) {
		$dns->setUpdateSubaction('full_update');
		$dns->was();
	}
}

$dnslist = $login->getList('reversedns');
foreach($dnslist as $dns) {
	$dns->setUpdateSubaction('full_update');
	$dns->was();
}
