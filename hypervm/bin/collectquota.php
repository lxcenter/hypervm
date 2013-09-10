<?php 

include_once "htmllib/lib/include.php";

initprogram('admin');
$sgbl->__var_collectquota_run = true;

exit_if_another_instance_running();

$global_dontlogshell = true;

$cmd = parse_opt($argv);
if (!isset($cmd['just-db'])) {
	$sgbl->__var_just_db = false;
	try {
		storeinGblvariables();
	} catch (Exception $e) {
		print($e->getMessage());
		print("\n");
	}
} else {
	$sgbl->__var_just_db = true;
}

// We need to blank it, since all the vpses were loaded once.

$login = null;
initProgram('admin');

$login->collectQuota();
$login->was();
findServerTraffic();


function storeinGblvariables()
{
	return null;
}

