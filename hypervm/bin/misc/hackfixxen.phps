<?php 

include_once "htmllib/lib/include.php"; 

initProgram('admin');

$login->loadAllObjects('vps');

$list = $login->getList('vps');

foreach($list as $l) {
	if (!$l->isXen()) {
		continue;
	}
	$l->setUpdateSubaction('createconfig');
	$l->was();
}
