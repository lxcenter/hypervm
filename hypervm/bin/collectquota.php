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
	global $gbl, $sgbl, $login, $ghtml;
	return;

	$firstofmonth  = @ mktime(00, 01, 00, @ date("n"), 1, @ date("Y"));
	$today = time() + 2 * 24 * 60 * 60;

	$vpslist = $login->loadAllVps();

	$vpslist = $login->getList('vps');

	if ($vpslist) foreach($vpslist as $vps) {
		$vpst  = $vps->getList("vpstraffic");
		$list = get_namelist_from_objectlist($vpst);
		$total[$vps->getClName()] = trafficGetIndividualObjectTotal($vpst, $firstofmonth, $today, $vps->nname);

		list($month, $year) = get_last_month_and_year();
		$last_traffic = VpstrafficHistory::getMonthTotal($vpst, $month, $year, null);
		if (!isset($sgbl->__var_traffic_last_usage)) {
			$sgbl->__var_traffic_last_usage = null;
		}
		$sgbl->__var_traffic_last_usage[$vps->getClName()] = $last_traffic['traffic_usage'];

		/*
		 try {
			$sgbl->__var_backupdisk_usage[$vps->getClName()] = rl_exec_get(null, $vps->syncserver, array("vps", "getBackupDiskSize"), array($vps->nname));
			} catch (Exception $e) {
			$sgbl->__var_backupdisk_usage[$vps->getClName()] = null;
			}
			*/
	}
	//dprintr($sgbl->__var_backupdisk_usage);
	$sgbl->__var_traffic_usage = $total;
	dprintr($sgbl->__var_traffic_last_usage);

}

