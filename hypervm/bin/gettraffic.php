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

debug_for_backend();
print_time("gettraffic");
gettraffic_main();
$val = print_time("gettraffic", "Get Traffic ");
log_log("get_traffic", $val);

function gettraffic_main()
{
	global $argc, $argv;
	$list = parse_opt($argv);
	if (isset($list['delete-table']) && $list['delete-table'] === 'yes') {
		print("clearing Traffic Table\n");
		clearTrafficTable();
		filltraffictable();
	} else {
		filltraffictable();
	}
}

//testFunc();

function clearTrafficTable()
{
	$sql = new Sqlite(null, "vpstraffic");
	$sql->rawquery("delete from vpstraffic;");
}

function filltraffictable() 
{
	global $gbl, $login, $ghtml; 
	initProgram('admin');
	$t="";
 // Fake domain to store the time the last stats finding was done.

	$laccessdom = new Vps(null, null, '__last_access_domain_');
	try {
		$laccess = $laccessdom->getFromList('vpstraffic', '__last_access_domain_');
	} catch (exception $e) {
		dprint("not getting\n");
		$laccess = null;
	}


	if (!$laccess) {
        $laccess = new Vpstraffic(null, null, '__last_access_domain_');
        $oldtime = 0;
		$laccess->parent_clname = 'vps-__last_access_domain_';
        $laccess->dbaction = 'add';
    } else {
        $oldtime = $laccess->timestamp;
    }

	if ($oldtime && ((time() - $oldtime) > 5 * 3600 * 24) ) {
		$oldtime = time() - 5 * 3600 * 24;
		$laccess->timestamp = $oldtime;
		$laccess->setUpdateSubaction();
		$laccess->write();
	}



	$flag = 0;
	if($oldtime == 0) {
		// 8 days back
		$oldtime  =  @ mktime(00, 01, 00, date("n"), date("j") - 2, date("Y"));
		// Start of Jan
		//$oldtime  =  mktime( 00 , 01, 00 , 1 ,1, date("Y"));
		$flag = 1;
	}
	
	// $newtime =   mktime( 00 , 01, 00 , date("n")  , date("j")  ,date("Y"));

    $newtime = time();

	$old = $oldtime;
	$new = $newtime;

	if(($newtime - $oldtime) <= (19 * 60 * 60)) {
		dprint("Less than a day:");
		dprint("\n\n\n\n");
		return;
	}


	$j = 0;
	for($i = $newtime; $i >= $oldtime ; $i -= (24 *  60 * 60)) {
		if($j > 0) { 
			$timearray[]  = $new . "-" . $i ;
		}
		$new = $i;
		$j++;
	}

	if($flag != 1) {
		$timearray[] =  $new   .  "-" .  $oldtime;
	}
	$timearray = array_reverse($timearray);

	foreach($timearray as $t1) {
		$t = explode("-" , $t1);
		$newtime = $t[0];
		$oldtime = $t[1];
		if ($newtime - $oldtime < 4 * 60 * 60) { continue; }
		$o = @ strftime("%c" , $oldtime);
		$n = @ strftime("%c" , $newtime);
		print("\n\n$o  to ... $n\n\n"); 
		findtraffic($oldtime , $newtime);
		// Write every time, otherwise, the traffic calculation breaks off in the middle, it will be left inconsistent.
		$laccess->timestamp = $newtime;
		$laccess->setUpdateSubaction();
		$laccess->write();
	}

	// This is the time at which this was run last time.
	$laccess->timestamp = time();
	$laccess->setUpdateSubaction();
	$laccess->write();

} 
 

function findtraffic($oldtime,  $newtime)
{
	global $global_dontlogshell;
	$global_dontlogshell = true;

	$list = get_all_pserver();
	foreach($list as $l) {
		lxshell_return("__path_php_path", "../bin/trafficperslave.php", $l, $oldtime, $newtime);
	}

} 

