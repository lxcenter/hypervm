<?PHP
//
//    HyperVM, Server Virtualization GUI for OpenVZ and Xen
//
//    Copyright (C) 2000-2009     LxLabs
//    Copyright (C) 2009          LxCenter
//
//    This program is free software: you can redistribute it and/or modify
//    it under the terms of the GNU Affero General Public License as
//    published by the Free Software Foundation, either version 3 of the
//    License, or (at your option) any later version.
//
//    This program is distributed in the hope that it will be useful,
//    but WITHOUT ANY WARRANTY; without even the implied warranty of
//    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
//    GNU Affero General Public License for more details.
//
//    You should have received a copy of the GNU Affero General Public License
//    along with this program.  If not, see <http://www.gnu.org/licenses/>.
?>

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




