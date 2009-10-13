<?php 

include_once "htmllib/lib/include.php";

initProgram('admin');
$list = parse_opt($argv);

if (isset($list['oldmaster'])) {
	$oldmaster = $list['oldmaster'];
} else {
	print("Usage: $argv[0] --oldmaster=old-master-slave-id\n");
	exit;
}

$sq = new Sqlite(null, 'vps');

$res = $sq->rawQuery("select * from pserver where nname = '$oldmaster'");

if (!$res) {
	print("Old Master is not present as a slave\n");
	exit;
}

$sq->rawQuery("update vps set syncserver = '$oldmaster' where syncserver = 'localhost'");
print("Converted all the vpses from localhost to $oldmaster\n");

