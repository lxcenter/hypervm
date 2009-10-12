<?php 
include_once "htmllib/lib/include.php";
		$global_dontlogshell = true;
	exit_if_another_instance_running();
// Selfbackup
	passthru("$sgbl->__path_php_path ../bin/common/mebackup.php");
	passthru("$sgbl->__path_php_path ../bin/gettraffic.php");
	passthru("$sgbl->__path_php_path ../bin/collectquota.php");
	passthru("$sgbl->__path_php_path ../bin/common/schedulebackup.php");
	passthru("$sgbl->__path_php_path ../bin/fix/fixippool.php");
	passthru("$sgbl->__path_php_path ../bin/common/clearsession.php");
	initProgram('admin');
	checkClusterDiskQuota();
// If auto-update is on check for new HyperVM Version when
// the update day is reached
	auto_update();
// Rotate HyperVM logs
	passthru("$sgbl->__path_php_path ../bin/common/fixlogdir.php");
