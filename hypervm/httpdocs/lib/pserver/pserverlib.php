<?php 
class pserver extends pservercore {

static $__desc_used_vpslist_f = array("", "", "vms On this Server", '');
static $__desc_importvps = array("", "", "confirm_import", '');
static $__desc_vpsdriver = array("", "", "driver");
static $__desc_reversednsonly = array("f", "", "reverse_dns_only");
static $__acdesc_update_vpslist = array("", "", "vms On This Server");
static $__acdesc_update_importvps = array("", "", "Import Raw VPS");
static $__acdesc_update_savevpsdata = array("", "", "save_VPS_data");
static $__acdesc_update_importhypervmvps = array("", "", "Import_HyperVM_VPS");
static $__desc_datacenter = array("", "", "data_center");
static $__desc_max_vps_num = array("", "", "maximum_number_of_vpses");
static $__desc_centralbackupserver = array("", "", "central_backup_server");

static $__desc_vps_l = array("v", "", "");
static $__desc_centralbackupconfig_o = array("", "", "");
static $__desc_dirlocation_o = array("d", "", "");
static $__desc_button_showused_f = array("b", "",  "", 'a=list&c=vps');
static $__desc_button_graph_f = array("b", "",  "", 'a=graph&sa=vpsbase');

static $__acdesc_graph_vpsbase	 = array("", "",  "vps_graphs");
static $__acdesc_graph_vpstraffic	 = array("", "",  "vps_traffic");
static $__acdesc_graph_vpsv6traffic	 = array("", "",  "vps_v6traffic");
static $__acdesc_graph_vpscpuusage	 = array("", "",  "vps_cpu");
static $__acdesc_graph_vpsmemoryusage	 = array("", "",  "vps_memory");
static $__acdesc_update_centralbackupconfig	 = array("", "",  "central_backup_config");

function display($var)
{
	global $gbl, $sgbl, $login, $ghtml; 
	if ($var === 'used_f') {
		return $this->createUsed();
	}

	if ($var === 'vpsdriver') {
		$driverapp = $gbl->getSyncClass('localhost', $this->nname, 'vps');
		return $driverapp;
	}

	return parent::display($var);

}

function createUsed()
{
	if (isset($this->used_f)) {
		return $this->used_f;
	}
	$res = $this->getUsed();
	if ($res) {
		$this->used_f = 'on';
	} else {
		$this->used_f = 'dull';
	}

	return $this->used_f;
}


static function createListSlist($parent)
{

	$list = get_namelist_from_objectlist($parent->getList('datacenter'));

	$clist[] = '--any--';

	$clist = lx_merge_good($clist, $list);

	$nlist['nname'] = null;
	$nlist['datacenter'] = array('s', $clist);
	return $nlist;
}

function createShowPropertyList(&$alist)
{
	//$alist['property'][] = "o=sp_specialplay&a=updateForm&sa=skin";
	$alist['property'][] = 'a=show';
	$alist['property'][] = "a=updateform&sa=information";
	$alist['property'][] = "a=updateform&sa=password";
	$alist['property'][] = "a=graph&sa=vpsbase";
	$alist['property'][] = "a=list&c=psrole_a";
}

function createGraphList()
{
	global $gbl, $sgbl, $login, $ghtml; 
	$alist[] = "a=graph&sa=vpstraffic";
	$alist[] = "a=graph&sa=vpsv6traffic";
	$driverapp = $gbl->getSyncClass(null, $this->nname, 'vps');
	if ($driverapp === 'xen') {
	} else {
		$alist[] = "a=graph&sa=vpsmemoryusage";
	}

	$alist[] = "a=graph&sa=vpscpuusage";
	return $alist;
}

static function  createListNlist($parent, $class = NULL)
{

	$nlist['ostype'] = '3%';
	$nlist['used_f'] = '3%';
	$nlist['nname'] = '100%';
	$nlist['ddate'] = '10%';
	$nlist['osversion'] = '3%';
	$nlist['loadavg'] = '3%';
	$nlist['vpsdriver'] = '3%';
	$nlist['datacenter'] = '3%';
	$nlist['button_graph_f'] = '5%';
	$nlist['abutton_list_s_process'] = '5%';
	$nlist['abutton_list_s_ipaddress'] = '5%';
	$nlist['abutton_list_s_service'] = '5%';
	$nlist['abutton_list_s_diskusage'] = '5%';
	
	return $nlist;
}

function getUsed()
{
	$vlist = array("vps" => "vps");
	$ret = null;
	foreach($vlist as $k => $v) {
		if (!is_array($v)) {
			$db = $v;
			$vname = "syncserver";
		} else {
			$db = $v[0];
			$vname = $v[1];
		}

		$db = new Sqlite($this->__masterserver, $db);
		$str = "$vname = '$this->nname'";
		$res = $db->getRowsWhere($str, array('nname'));
		if ($res) {
			$tmp = null;
			foreach($res as $r) {
				$tmp[] = $r['nname'];
			}
			$ret[$k] = implode(", ", $tmp);
		}
	}

	return $ret;
}



function createShowAlist(&$alist, $subaction = null)
{

	//$alist[] = "a=show";
	
	global $gbl, $sgbl, $login, $ghtml; 

	$alist['__title_security'] = "Security";
	$alist[] = "a=show&o=sshconfig";
	$alist[] = "a=show&o=lxguard";
	$alist[] = "a=list&c=hostdeny"; 
	$alist[] = "a=list&c=sshauthorizedkey"; 

	$alist['__title_main'] = $this->getTitleWithSync();
	//$alist['property'][] = "a=updateForm&sa=update";
	//$this->getCPToggleUrl($alist);
	//$alist[] = "a=list&c=vps";
	$alist[] = "a=show&o=dirlocation";
	$alist[] = "a=updateform&sa=savevpsdata";
	$alist[] = "a=updateform&sa=importhypervmvps";
	$alist[] = "a=updateform&sa=commandcenter";
	//$alist[] = "a=updateform&sa=backupconfig";

	//$alist[] = "a=list&c=component";
	

	if ($sgbl->isDebug()) {
		$alist[] = 'a=updateform&sa=ssl_key';
		$alist[] = "a=show&o=kloxo";
	}
	$alist[] = "a=updateform&sa=mysqlpasswordreset";
	$alist[] = 'a=list&c=ipaddress';
	$alist[] = 'a=updateform&sa=centralbackupconfig';
	$alist['__title_next'] = get_plural(get_description('service'));

	$cnl = array('service',  'cron', 'process');
	foreach($cnl as $cn) {
		$alist = $this->getListActions($alist, $cn);
	}

	//$this->driverApp->createShowAlist($alist);



	$alist[] = "a=show&o=sshclient";
	$alist[] = "a=show&o=llog";
	//$alist[] = "a=updateform&sa=phpsmtp";
	$alist[] = "a=show&l[class]=ffile&l[nname]=";

	//$alist[] = "a=list&c=firewall";
	//$alist[] = "a=show&o=proxy";
	//$alist[] = "a=updateform&sa=update&c=serverspam";
	$alist['__title_nnn'] = 'Machine';
	$alist[] = "a=updateform&sa=importvps";
	$alist[] = "a=show&o=driver";
	//$alist[] = "a=update&sa=loaddriverinfo";
	$alist[] = "a=updateForm&sa=reboot";

	//$alist[] = "a=updateForm&sa=poweroff";

	return $alist;
}

function updatesaveVPSData($param)
{
	$sq = new Sqlite(null, "vps");
	$list = $sq->getRowsWhere("syncserver = '$this->nname'", array("nname"));
	$list = get_namelist_from_arraylist($list);
	foreach($list as $l) {
		$vps = new VPS(null, $this->nname, $l);
		$vps->get();
		$data[$l] = $vps;
	}
	$this->__var_vpsdata = $data;
	$param['some_f'] = 'a';
	return $param;
}


function updateImporthypervmVPS($param)
{
	global $gbl, $sgbl, $login, $ghtml; 

	$this->setUpdateSubaction('importhypervmvps');
	$res = rl_exec_set($this->__masterserver, $this->syncserver, $this);
	$this->dbaction = 'clean';

	if (!$res) {
		throw new lxexception('hypervm_import_failed', '');
	}


	foreach($res as $k => $r) {
		$r->__parent_o = $login;
		if ($r->isXen()) {
			if ($this->checkXenVpsName($r)) {
				unset($res[$k]);
			}
		} else {
			if ($this->checkOpenvzVpsid($r)) {
				unset($res[$k]);
			}
		}
	}

	if ($res) foreach($res as $k => $r) {
		$r->metadbaction = 'writeonly';
		$r->dbaction = 'add';
		$r->write();
	}
	exec_justdb_collectquota();
	$gbl->__this_redirect = $ghtml->getFullUrl('a=list&c=vps') . "&frm_smessage=import_succeeded";

}


function updateImportVPs($param)
{
	global $gbl, $sgbl, $login, $ghtml; 

	$gen = $login->getObject('general');
	$sq = new Sqlite(null, 'vps');
	$driverapp = $gbl->getSyncClass('localhost', $this->nname, 'vps');

	if ($driverapp === 'xen') {
		if (!$gen->generalmisc_b->xenimportdriver) {
			throw new lxexception('need_to_set_xenimportdriver', '');
		}
		$this->__var_xenimportdriver = $gen->generalmisc_b->xenimportdriver;
	}

	$this->__var_vps_driver = $driverapp;

	$this->setUpdateSubaction('importvps');
	$res = rl_exec_set($this->__masterserver, $this->syncserver, $this);
	$this->dbaction = 'clean';

	if (!$res) {
		throw new lxexception('could_not_find_any_vms_to_import', '');
	}

	foreach($res as $k => $r) {
		$r->__parent_o = $login;
		if ($driverapp === 'openvz') {
			if ($this->checkOpenvzVpsid($r)) {
				unset($res[$k]);
			}
		} else {
			if ($this->checkXenVpsName($r)) {
				unset($res[$k]);
			}
		}
	}

	if ($res) foreach($res as $k => $r) {
		$r->metadbaction = 'writeonly';
		$r->dbaction = 'add';
		$r->write();
	}

	if ($driverapp === 'xen') {
		foreach($res as $k => $r) {
			$r->makeSureVifExists();
			$r->makeSureTheMacAddressExists();
			$r->createSyncClass();
			$r->createExtraVariables();
			$r->dbaction = 'update';
			$r->metadbaction = 'all';
			//if ($this->__var_xenimportdriver !== 'hypervm') {
				$r->setUpdateSubaction('createconfig');
			//}
			$r->was();
		}
	}

	exec_justdb_collectquota();

	$gbl->__this_redirect = $ghtml->getFullUrl('a=list&c=vps') . "&frm_smessage=import_succeeded";

}

function checkXenVpsName($r)
{

	$sq = new Sqlite(null, 'vps');
	$v = $sq->rawQuery("select * from vps where nname = '$r->nname'");

	if (!$v) {
		return false;
	}

	if ($v[0]['syncserver'] === $r->syncserver) {
		dprint("$r->nname already exists on the same server $r->syncserver <br> ");
		return true;
	} else {
		dprint("$r->nname already exists on the another server {$v[0]['syncserver']} <br> ");
		throw new lxexception('vpsid_already_exists_on_another_server', '', "$r->nname : {$v[0]['syncserver']}");
	}

}
function checkOpenvzVpsid($r)
{
	$sq = new Sqlite(null, 'vps');
	$v = $sq->rawQuery("select * from vps where vpsid = '$r->vpsid'");

	if (!$v) {
		return false;
	}

	if ($v[0]['syncserver'] === $r->syncserver) {
		dprint("$r->vpsid already exists on the same server $r->syncserver <br> ");
		return true;
	} else {
		dprint("$r->vpsid already exists on the another server {$v[0]['syncserver']} <br> ");
		throw new lxexception('vpsid_already_exists_on_another_server', '', "$r->vpsid : {$v[0]['syncserver']}");
	}
}

function getQuotaServer_traffic_last_usage()
{
	global $gbl, $sgbl, $login, $ghtml; 
	if (isset($sgbl->__var_server_traffic_last_usage)) {
		return $sgbl->__var_server_traffic_last_usage[$this->nname];
	} else {
		return $this->used->server_traffic_last_usage;
	}

}


function getQuotaServer_traffic_usage()
{
	global $gbl, $sgbl, $login, $ghtml; 
	if (isset($sgbl->__var_server_traffic_usage)) {
		return $sgbl->__var_server_traffic_usage[$this->nname];
	} else {
		return $this->used->server_traffic_usage;
	}
}



}

