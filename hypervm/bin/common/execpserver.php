<?php 

include_once "htmllib/lib/include.php"; 

if (!os_isSelfSystemUser()) {
	print("Not enough privileges\n");
	exit;
}

//initProgram('admin');

$list = get_all_pserver();

foreach($list as $l) {
	try {
		$res = rl_exec_get(null, $l, "exec_with_all_closed_output", $argv[1]);
		print("Got this from server $l\n");
		print_r($res);
		print("\n-----------------\n");
	} catch (Exception $e) {
		print("Got error from $l $e->__full_message\n");
	}
}


