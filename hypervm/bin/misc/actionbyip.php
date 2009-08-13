<?php 

include_once "htmllib/lib/displayinclude.php";

initProgram('admin');

$sq = new Sqlite(null, 'vps');

$list = parse_opt($argv);

$ip = $list['ipaddress'];

$res = $sq->getRowsWhere("coma_vmipaddress_a LIKE '%$ip%'");

if ($res) {
	foreach($res as $r) {
		print($r['nname']);
		print("\n");
	}
} else {
}

