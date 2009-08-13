<?php 

include_once "htmllib/lib/include.php";

$global_dontlogshell = true;

exit_if_another_instance_running();
collectdata_main();

//log_load();
monitor_load();
lxguard_main();

check_smtp_port();

run_mail_to_ticket();

function collectdata_main()
{
	if (lxfile_exists("/proc/xen") && lxfile_exists("/usr/sbin/xm")) {
		vps__xen::find_traffic();
		vps__xen::find_cpuusage();
	}

	if (lxfile_exists("/proc/vz")) {
		vps__openvz::find_traffic();
		vps__openvz::find_cpuusage();
		vps__openvz::find_memoryusage();
	}
}




