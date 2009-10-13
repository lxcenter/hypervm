<?php 
include_once "htmllib/lib/include.php"; 

initProgram('admin');
$slave = $argv[1];
$oldtime = $argv[2];
$newtime = $argv[3];
$sgbl->__var_collectquota_run = true;

trafficperslave($slave, $oldtime, $newtime);

function trafficperslave($slave, $oldtime, $newtime)
{

	global $gbl, $sgbl, $login, $ghtml; 
	$sq = new Sqlite(null, 'vps');

	$res = $sq->getRowswhere("syncserver = '$slave'", array('nname'));
	if (!$res) { return; }
	foreach($res as $r) {
		$vps = new Vps(null, null, $r['nname']);
		$vps->get();
		$vpslist[] = $vps;
	}


	$list = null;
	foreach($vpslist as $d) {
		$rt = new Remote();
		if ($d->isXen()) {
			$rt->viflist = $d->getViflist();
		} else {
			$rt->vpsid = $d->vpsid;
		}
		$rt->nname = $d->nname;
		$list[$d->nname] = $rt;
	}

	$driverapp = $gbl->getSyncClass(null, $slave, 'vps');
	try {
		$vps_usage = rl_exec_get(null, $slave, array("vpstraffic__$driverapp", 'findTotaltrafficUsage'), array($list, $oldtime, $newtime)); 
	} catch (exception $e) {
		exit;
	}

	dprintr($vps_usage);
	$res="";
	foreach($vpslist as $d) {
		$res['nname'] = "$d->nname:$oldtime:$newtime";
		$domt = new Vpstraffic(null, null, $res['nname']);
		$res['timestamp'] =    @ strftime("%c", $newtime);
		$res['oldtimestamp'] = @ strftime("%c", $oldtime);
		$res['ddate'] = time();
		$res['comment'] = null;
		$res['parent_list'] = null;
		$res['parent_clname'] = $d->getClName();
		$res['traffic_usage'] = $vps_usage[$d->nname]['total'];
		$res['incoming_usage'] = $vps_usage[$d->nname]['incoming'];
		$res['outgoing_usage'] = $vps_usage[$d->nname]['outgoing'];
		//		print_r($res);
		$domt->create($res);
		$domt->was();
	}


	$firstofmonth  = @ mktime(00, 01, 00, @ date("n"), 1, @ date("Y"));
	$today = time() + 2 * 24 * 60 * 60;

	if ($vpslist) foreach($vpslist as $vps) {
		$vpst  = $vps->getList("vpstraffic");
		$list = get_namelist_from_objectlist($vpst);
		$tu = trafficGetIndividualObjectTotal($vpst, $firstofmonth, $today, $vps->nname);
		$sq->rawQuery("update vps set used_q_traffic_usage = '$tu' where nname = '$vps->nname'");

		list($month, $year) = get_last_month_and_year();
		$tlu =  VpstrafficHistory::getMonthTotal($vpst, $month, $year, null);
		$tlu = $tlu['traffic_usage'];
		$sq->rawQuery("update vps set used_q_traffic_last_usage = '$tlu' where nname = '$vps->nname'");

		/*
		try {
			$sgbl->__var_backupdisk_usage[$vps->getClName()] = rl_exec_get(null, $vps->syncserver, array("vps", "getBackupDiskSize"), array($vps->nname));
		} catch (Exception $e) {
			$sgbl->__var_backupdisk_usage[$vps->getClName()] = null;
		}
	*/
	}

}

