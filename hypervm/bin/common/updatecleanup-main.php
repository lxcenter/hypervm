<?php 

// This file starts the upcp/cleanup process

include_once "htmllib/lib/include.php";
include_once "htmllib/lib/updatelib.php";

// Check if we already are running
exit_if_another_instance_running();
// Check for debug mode (commands.php / backend.php)
debug_for_backend();
// Start main process
updatecleanup_main();
