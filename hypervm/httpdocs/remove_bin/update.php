<?php 
include_once "htmllib/lib/include.php";
include_once "lib/updatelib.php";
include_once "htmllib/lib/updatelib.php";

if (lxfile_exists("CVS")) {
	print("CVS Exists... Development Version... Exiting..\n");
	exit;
}

exit_if_another_instance_running();

update_main();


