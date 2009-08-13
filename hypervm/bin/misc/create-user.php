<?php 

include_once "htmllib/lib/include.php"; 

initProgram('admin');

$login->loadAllObjects('vps');

$list = $login->getList('vps');

foreach($list as $l) {
	if ($l->syncserver !== $argv[1]) {
		continue;
	}

	$l->setUpdateSubaction('createuser');
	$l->was();
}
