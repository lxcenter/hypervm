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

