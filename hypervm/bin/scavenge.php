<?php 
include_once "htmllib/lib/include.php";


$global_dontlogshell = true;

exit_if_another_instance_running();

passthru("$sgbl->__path_php_path ../bin/common/mebackup.php");
passthru("$sgbl->__path_php_path ../bin/gettraffic.php");
passthru("$sgbl->__path_php_path ../bin/collectquota.php");
passthru("$sgbl->__path_php_path ../bin/common/schedulebackup.php");
passthru("$sgbl->__path_php_path ../bin/fix/fixippool.php");

passthru("$sgbl->__path_php_path ../bin/common/clearsession.php");


initProgram('admin');
checkClusterDiskQuota();

auto_update();
passthru("$sgbl->__path_php_path ../bin/common/fixlogdir.php");
