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
include_once "htmllib/lib/lxserverlib.php";

kill_and_save_pid('hypervm.php');
debug_for_backend();

$global_dontlogshell = true;
execSisinfoc();

system("iptables -t nat -L");

vpstraffic__openvz::iptables_delete();
vpstraffic__openvz::iptables_create();

if($argv[1] === 'master'){
	start_portmonitor();
}
dprint("Starting Server\n");
system("echo 16536 > /proc/sys/net/ipv4/tcp_max_tw_buckets_ve");
system("echo 256 > /proc/sys/net/ipv4/tcp_max_tw_kmem_fraction");

if (is_openvz()) {
	//system("sysctl net.ipv4.conf.all.proxy_arp=1");
}


lxshell_php("../bin/fix/fixippool.php");

$global_dontlogshell = false;
lxserver_main();






function timed_execution()
{
	global $global_dontlogshell;
	$global_dontlogshell = true;
	timed_exec(2,  "checkRestart");
	timed_exec(2 * 5, "execSisinfoc");
	$global_dontlogshell = false;
}

function execSisinfoc()
{
	dprint("execing sisinfoc\n");
	lxshell_background("__path_php_path", "../bin/sisinfoc.php");
}

function start_portmonitor()
{
	dprint("Starting portmonitor\n");
	system("pkill -f lxportmonitor.php");
	lxshell_background("__path_php_path", "../bin/common/lxportmonitor.php", "--data-server=localhost");
}
