<?php 

include_once "htmllib/lib/include.php"; 

initProgram('admin');

if (!$sgbl->isHyperVM()) {
	print("Only implemented for hyperVM\n");
	exit;
}

$oslave = $argv[1];
$nslave = $argv[2];

if (!$nslave) {
	print("Usage: change-slave-name old-slave-id new-slave-id\n");
	exit;
}

$sq = new Sqlite(null, "pserver");

$r = $sq->getRowsWhere("nname = '$oslave'");
if (!$r) {
	print("No slave as $oslave\n");
	exit;
}

$r = $sq->getRowsWhere("nname = '$nslave'");
if ($r) {
	print("slave $nslave already exists\n");
	exit;
}
$sq->rawQuery("update driver set nname = '$nslave' where nname = '$oslave'"); 
$sq->rawQuery("update vps set syncserver = '$nslave' where syncserver = '$oslave'");
$sq->rawQuery("update pserver set syncserver = '$nslave' where nname = '$oslave'");
$sq->rawQuery("update pserver set nname = '$nslave' where nname = '$oslave'");
$sq->rawQuery("update dirlocation set nname = '$nslave' where nname = '$oslave'");
$sq->rawQuery("update ipaddress set syncserver = '$nslave' where syncserver = '$oslave'");



