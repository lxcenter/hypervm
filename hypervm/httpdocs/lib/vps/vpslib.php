<?php

class vmipaddress_a extends LxaClass {

static $__desc = array("n", "",  "ipaddress");
static $__desc_ip_num = array("n", "",  "number_of_ip_from_pool");
static $__desc_type = array("e", "",  "ipaddress");
static $__desc_type_v_ippool = array("e", "",  "from_ippool");
static $__desc_type_v_npool = array("e", "",  "number_from_ippool");
static $__desc_type_v_normal = array("e", "",  "directly");
static $__desc_nname	 = array("n", "",  "ipaddress");

static function createListAddForm($parent, $class) { return false;}

static function createListAlist($parent, $class = NULL)
{

	$alist[] = "a=list&c=$class";
	$alist['__v_dialog_ippo'] = "a=addform&c=$class&dta[var]=type&dta[val]=ippool";
	$alist['__v_dialog_norm'] = "a=addform&c=$class&dta[var]=type&dta[val]=npool";
	$alist['__v_dialog_npoo'] = "a=addform&c=$class&dta[var]=type&dta[val]=normal";
	return $alist;
}

function postadd()
{
	ippool::addToTmpIpAssign($this->nname);
}


static function addform($parent, $class, $typetd = null)
{
	if ($parent->isXen()) {
		$vlist['__m_message_pre'] = 'xen_restart_message';
	}

	if ($typetd['val'] === 'ippool') {
		$snco = new Pserver(null, $parent->syncserver, $parent->syncserver);
		$list = $snco->getIpPool(100000000);

		if ($list['ip']) {
			$vlist['nname'] = array('s', $list['ip']);
		} else {
			$vlist['nname'] = array('M', 'no_address');
		}
	} else if ($typetd['val'] === 'npool') {
		$vlist['ip_num'] = array('s', range(1,30));
	} else {
		$vlist['nname'] = null;
	}
	$ret['action'] = 'add';
	$ret['variable'] = $vlist;
	return $ret;
}

static function add($parent, $class, $param)
{

	if ($param['type'] === 'npool') {
		$snco = new Pserver(null, $parent->syncserver, $parent->syncserver);
		$list = $snco->getIpPool($param['ip_num']);
		$list = $list['ip'];
		$first = array_shift($list);
		foreach($list as $lp) {
			full_validate_ipaddress($lp, 'nname');
			$vmip = new vmipaddress_a(null, null, $lp);
			$parent->addToList('vmipaddress_a', $vmip);
			//ippool::addToTmpIpAssign($lp);
			//$parent->__t_new_vmipaddress_a_list[$vmip->nname] = $vmip;
		}
		$param['nname'] = $first;

	} else {
		if (csa($param['nname'], ",")) {
			$list = explode(",", $param['nname']);
			$first = array_shift($list);
			foreach($list as $lp) {
				$lp = trim($lp);
				full_validate_ipaddress($lp, 'nname');
				$vmip = new vmipaddress_a(null, null, $lp);
				$parent->addToList('vmipaddress_a', $vmip);
				//$parent->__t_new_vmipaddress_a_list[$vmip->nname] = $vmip;
			}
			$param['nname'] = $first;
		}
	}
	full_validate_ipaddress($param['nname'], 'nname');
	return $param;
}

}

class vpsipaddress_a extends LxaClass {
}

class Vps extends vpsBase {
// Core

static $__desc = array("", "",  "virtual_machine");
static $__table = "vps";
// Mysql
static $__desc_nname	 = array("n", "",  "VM_name", URL_SHOW);
static $__desc_contactemail = array("", "",  "contact_email");
static $__desc_coma_vmipaddress_a = array("", "",  "ipaddress");
static $__desc_parent_name_f = array("", "",  "owner");
static $__desc_one_ipaddress_f = array("", "",  "Ip_Address_(optional)");
static $__desc_num_ipaddress_f = array("", "",  "number_of_ips(from_pool)");
static $__desc_syncserver = array("", "",  "Server");

/// Fake Variables
static $__desc_send_welcome_f    = array("f","",  "send_welcome_message"); 
static $__desc_use_resourceplan_f = array("f", "",  "use_template");
static $__desc_resourceplan_f = array("s", "",  "plan_name");
static $__desc_resourceplan_used_f    = array("n","",  "Plan"); 
static $__desc_information_f  = array("b", "",  "", 'a=updateform&sa=information'); 
static $__desc_ipaddress_f  = array("b", "",  "", 'a=list&c=vmipaddress_a'); 
static $__desc_portmon_f  = array("b", "",  "", 'a=list&c=monitorserver'); 
static $__desc_limit_f  = array("b", "",  "", 'a=updateform&sa=limit'); 
static $__desc_openvzostemplate_list = array("", "",  "openvz_template_list");
static $__desc_xenostemplate_list = array("", "",  "xen_template_list");

//static $__desc_kernelmem_usage	 = array("qh", "",  "Kernel_Memory_(KB)");
static $__desc_hostname	 = array("", "",  "hostname");
static $__desc_parent_clname	 = array("", "",  "owner");
static $__desc_lmemoryusage_f	 = array("S", "",  "mem");
static $__desc_ldiskusage_f	 = array("S", "",  "disk");
static $__desc___v_priv_used_traffic_usage    = array("S","",  "traffic"); 
static $__desc_traffic_usage_per_f	 = array("pS", "",  "traffic");
static $__desc_dipaddress_f	 = array("S", "",  "ipaddress");
static $__desc_rebuild_backup_f	 = array("f", "",  "take_a_backup_of_current_vps._will_be_available_in_the_filemanager_under_backup");
static $__desc_macaddress	 = array("", "",  "mac_address_base");

static $__desc_ttype = array("e", "", "type");
static $__desc_ttype_v_xen = array("", "", "xen");
static $__desc_ttype_v_openvz = array("", "", "openvz");

static $__desc_nosaveconfig_flag = array("f", "",  "dont_save_config");
static $__desc_disk_usage	 = array("qh", "",  "disk:disk_quota_(MB)");
static $__desc_backup_num = array("q", "",  "backup:number_of_backups");
static $__desc_memory_usage	 = array("qh", "",  "burst_mem:burstable_memory_(MB)(openvz_only)");
static $__desc_cpu_usage	 = array("qh", "",  "cpu:cpu_usage_(%) 100/CPU");
static $__desc_managedns_flag = array("q", "",  "can_manage_dns");
static $__desc_managereversedns_flag = array("q", "",  "can_manage_reverse_dns");
static $__desc_rebuildvps_flag = array("q", "",  "can_rebuild_vps");
static $__desc_centralbackup_flag = array("q", "",  "enable_central_backup");

static $__desc_cpuunit_usage	 = array("qh", "",  "cpuUNIT:CPU_UNITS_(default_1000)");
static $__desc_ncpu_usage	 = array("qh", "",  "cpuNum:number_of_CPUS");
static $__desc_ioprio_usage	 = array("qh", "",  "ioprio:IO_priority_(0-7)");

//static $__desc_monitorserver_num =   array("q","",  "number_of_monitored_servers");
//static $__desc_monitorport_num =   array("q","",  "number_of_monitored_ports");
static $__desc_uplink_usage = array("qh", "",  "uplink_traffic(KB/s)");
//static $__desc_kernelmem_usage	 = array("q", "",  "Kernel_Memory_(KB)");

static $__desc_process_usage	 = array("qh", "",  "process:number_of_processes(openvz_only)");
static $__desc_guarmem_usage	 = array("qh", "",  "guaranteed:guaranteed_memory_(MB)(openvz_only)");
static $__desc_realmem_usage	 = array("qh", "",  "realmem:real_memory_usage_(MB)(xen_only)");
static $__desc_swap_usage	 = array("qh", "",  "swap:swap_(MB)");
static $__desc_traffic_usage_q	 = array("", "",  "Traffic");
static $__desc_timezone	 = array("", "",  "timezone");
static $__desc_confirm_f	 = array("", "",  "confirm");
static $__desc_reversedns_l = array("d", "", "", "");
static $__desc_ndskshortcut_l = array("d", "", "", "");

static $__acdesc_update_ostemplatelist  =  array("","",  "ostemplate_list"); 
static $__acdesc_update_timezone	 = array("", "",  "set_timezone");
static $__acdesc_update_boot	 = array("", "",  "boot");
static $__acdesc_graph_base	 = array("", "",  "graphs");
static $__acdesc_graph_traffic	 = array("", "",  "traffic");
static $__acdesc_graph_cpuusage	 = array("", "",  "Cpuusage");
static $__acdesc_graph_memoryusage	 = array("", "",  "Memory");
static $__acdesc_update_livemigrate	 = array("", "",  "live_migrate");
static $__acdesc_update_macaddress	 = array("", "",  "mac_address");
static $__acdesc_update_clonevps = array("", "",  "clone_vps");



static $__desc_traffic_usage_per	 = array("pS", "",  "Traffic");
static $__desc_disk_usage_per	 = array("pS", "",  "Disk");
static $__desc_mainipaddress	 = array("", "",  "main_ipaddress");
static $__acdesc_update_mainipaddress	 = array("", "",  "main_ipaddress");

// Objects
//static $__desc_vpsipaddress_l = array('qd', '', '', '');
static $__desc_vpstraffic_l = array('d', '', '', '');
static $__desc_vpstraffichistory_l = array('d', '', '', '');
static $__desc_vmipaddress_a = array('', '', '', '');
static $__desc_openvzqos_l = array('', '', '', '');
static $__desc_dns_l = array('d', '', '', '');
static $__desc_traceroute_l = array('', '', '', '');
static $__desc_diskusage_l = array('', '', '', '');
static $__acdesc_update_changelocation = array('n', "", "change_location", "");
static $__acdesc_update_changeosomagename = array('n', "", "set_distro_name", "");
static $__acdesc_update_hardpoweroff = array('n', "", "hard_poweroff", "");
static $__acdesc_show = array('', "", "VM_home", "");

static $__desc_monitorserver_l = array("qdtLbB", "",  "");
static $__desc_emailalert_l = array("d", "", "", "");
static $__desc_kloxo_o = array("", "", "", "");

// Lists
//static $__desc_domain_l = array("qvd", "",  "virtual_object");


function getShowInfo()
{
	global $gbl, $sgbl, $login, $ghtml; 

	$ip = implode(", ", get_namelist_from_objectlist($this->vmipaddress_a));

	if (strlen($ip) > 19) {
		$ip = substr($ip, 0, 19) . ".....";
	}

	$extrastr = null;
	if (!$this->isXen()) {
		$extrastr = "vpsid: {$this->vpsid}; ";
	} else {
		$extrastr = "mac: {$this->macaddress};";
	}

	$consoleuser = null;
	if (!$this->isWindows()) {
		$consoleuser = "Console User: $this->username@{$ghtml->fix_lt_gt($this->getSyncserverIP())}";
	} 
	return $consoleuser;
	//return "Location: $this->corerootdir $extrastr Ipaddress: $ip";
}


function updateOstemplateList($param)
{
	
	$param['xenostemplate_list'] = lxclass::fixlistvariable($param['xenostemplate_list']);
	$param['openvzostemplate_list'] = lxclass::fixlistvariable($param['openvzostemplate_list']);
	return $param;
}


function isRealChild($c)
{
	return true;
}

static function isHardRefresh() { return true; }

function isXen()
{
	return $this->ttype === 'xen';
}

function isRealQuotaVariable($var)
{
	$list["traffic_usage"] = 'a'; 
	$list["traffic_last_usage"] = 'a'; 
	return isset($list[$var]);
}

static function findVpsGraph($server, $type)
{
	global $gbl, $sgbl, $login, $ghtml; 
	$driverapp = $gbl->getSyncClass(null, $server, 'vps');

	$sq = new Sqlite(null, 'vps');
	$list = $sq->getRowsWhere("syncserver = '$server'", array("nname", "vifname", "vpsid"));

	switch($type) {
		case "vpstraffic":
			if ($driverapp === 'xen') {
				foreach($list as $l) {
					$ret[$l['nname']] = "xen-{$l['vifname']}";
				}
			} else {
				foreach($list as $l) {
					$ret[$l['nname']] = "openvz-{$l['vpsid']}";
				}
			}
			break;

		default:
			if ($driverapp === 'xen') {
				foreach($list as $l) {
					$ret[$l['nname']] = "{$l['nname']}";
				}
			} else {
				foreach($list as $l) {
					$ret[$l['nname']] = "openvz-{$l['vpsid']}";
				}
			}
			break;
	}

	return $ret;
}

function getOneIP()
{
	$list = $this->vmipaddress_a;

	$first = getFirstFromList($list);
	if ($first) {
		return $first->nname;
	}
	return null;
}


function isSync()
{
	global $gbl, $sgbl, $login, $ghtml;
	// Don't do anything if it is syncadd or if it is restore... When restoring, installapp is handled by the     |database, and then the web backup.


	if (isset($gbl->__restore_flag) && $gbl->__restore_flag) {
		return false;
	}

	if ($this->subaction === 'changeosimagename' || $this->subaction === 'changesyncserver') {
		return false;
	}

	if ($this->subaction === 'ostemplatelist') {
		return false;
	}

	return parent::isSync();
}

function isXenVT()
{
	return false;
}

function updatechangeVPSid($param)
{
	$sq = new Sqlite(null, 'vps');

	if (!is_numeric($param['vpsid'])) {
		throw new lxexception("vpsid_should_be_numeric", '', "");
	}

	if ($sq->getRowsWhere("vpsid = '{$param['vpsid']}'")) {
		throw new lxexception("new_vpsid_exists_in_database", '', "");
	}

	if ($this->vpsid == $param['vpsid']) {
		throw new lxexception("new_vpsid_same_as_old", '', "");
	}
	$this->__var_oldvpsid = $this->vpsid;

	return $param;
}

function updateLimit($param)
{
	global $gbl, $sgbl, $login, $ghtml; 
	if (!$login->priv->isOn('vps_limit_flag')) {
		throw new lxexception("you_dont_have_limit_change_permission", '');
	}

	parent::updateLimit($param);
	return $param;
}

function isSelect()
{
	global $gbl, $sgbl, $login, $ghtml; 

	if (if_demo()) {
		if ($this->nname === 'example.com') {
			return false;
		}
		if ($this->nname === 'lxlabs.com') {
			return false;
		}
		if ($this->nname === 'example.vm') {
			return false;
		}
		if ($this->nname === 'xen.vm') {
			return false;
		}
		if ($this->nname === 'openvz.vm') {
			return false;
		}
	}

	if (!$login->priv->isOn('vps_add_flag')) {
		return false;
	}

	return ($this->getParentO()->get__table() === 'client') ;
}

function isTreeSelect() { return true; }

function fillWelcomeMessage($txt)
{
	$list = get_namelist_from_objectlist($this->vmipaddress_a);
	$allip = implode(" ", $list);
	$ip = getFirstFromList($list);
	$txt = str_replace("%all_ipaddress%", $allip, $txt);
	$txt = str_replace("%vmipaddress_a%", $ip, $txt);
	$txt = str_replace("%password%", $this->realpass, $txt);
	$txt = str_replace("%rootpassword%", $this->rootpassword, $txt);

	$list = explode("\n", $txt);
	$insideost = false;
	foreach($list as $l) {
		$l = trim($l);
		if (char_search_beg($l, "<%ostemplate:kloxo") || char_search_beg($l, "<%ostemplate:lxadmin")) {
			$insideost = true;
			continue;
		}

		if (char_search_beg($l, "<%/ostemplate%>")) {
			$insideost = false;
			continue;
		}
		if ($insideost) {
			if (csa($this->ostemplate, "kloxo") || csa($this->ostemplate, "hostinabox")) {
				$total[] = $l;
			}
			continue;
		}
		$total[] = $l;
	}

	return implode("\n", $total);

}
function getViflist()
{
	if (!$this->isWindows()) {
		$list[] = $this->vifname;
		return $list;
	}
	$name = $this->vifname;
	$count = count($this->vmipaddress_a);
	for ($i = 1; $i <= $count; $i++) {
		$hex = get_double_hex($i);
		$h = base_convert($i, 10, 36);
		$list[] = "$name$h";
	}
	return $list;
}

function getStatusForThis()
{
	global $gbl, $sgbl, $login, $ghtml; 
	$driverapp = $gbl->getSyncClass($this->__masterserver, $this->syncserver, 'vps');

	if ($this->isXen()) {
		$rootdir = '/home/xen';
	} else {
		$rootdir = $this->corerootdir;
	}
	try {
		$status = rl_exec_get(null, $this->syncserver, array("vps__$driverapp", "getStatus"), array($this->getIid(), $rootdir));
	} catch (Exception $e) {
		dprint("Could Not Get Status {$e->getMessage()}\n");
		$status = $this->status;
	}
	return $status;
}

static function getExtraParameters($parent, $list)
{
	global $gbl, $sgbl, $login, $ghtml; 

	$gen = $login->getObject('general')->generalmisc_b;

	foreach($list as $l) {
		if ($l->isXen()) {
			$result[$l->syncserver][] = array('nname' => $l->nname, 'diskname' => $l->getXenMaindiskName(), 'ttype' => $l->ttype, 'corerootdir' => $l->corerootdir, 'winflag' => $l->isWindows());
		} else {
			$result[$l->syncserver][] = array('nname' => $l->nname, 'vpsid' => $l->vpsid, 'corerootdir' => $l->corerootdir, 'ttype' => $l->ttype);
		}
	}

		
	$failedlist = null;
	foreach($result as $ks => $vs) {
		$driverapp = $gbl->getSyncClass(null, $ks, 'vps');
		try {
			$res = rl_exec_get(null, $ks, array("vps__$driverapp", "getCompleteStatus"), array($vs));
		} catch (Exception $e) {
			$res = null;
			$failedlist[$ks] = $e->getMessage();
		}


		if ($res) foreach($res as $kk => $vv) {
			$st_statuslist[$kk] = $vv;
		}
	}

	if ($failedlist) {
		$emessage = null;
		foreach($failedlist as $k => $m) {
			$emessage .= "&nbsp; Failed to get Status from $k. Server said: $m  ";
		}
		$ghtml->__http_vars['frm_emessage'] = $emessage;
		$ghtml->__http_vars['frm_m_emessage_data'] = null;
		$ghtml->print_message();
	}
	foreach($list as $object) {

		if (!isset($st_statuslist[$object->nname])) {
			continue;
		}
		$status = $st_statuslist[$object->nname]['status'];

		if (csa($status, ":")) {
			$st = explode(":", $status);
			$status = array_shift($st);
		}

		if ($status !== $object->status) {
			$object->status = $status;
			$object->setUpdateSubaction();
		} 

		if (isset($st_statuslist[$object->nname]['lmemoryusage_f'])) {
			$object->lmemoryusage_f = "{$st_statuslist[$object->nname]['lmemoryusage_f']}/{$object->priv->memory_usage}";
		}
		$object->ldiskusage_f = "{$st_statuslist[$object->nname]['ldiskusage_f']}/{$object->priv->disk_usage}";
	}

}

function display($var)
{

	global $gbl, $sgbl, $login, $ghtml; 


	if ($var === 'vpsid') {
		if (!$this->vpsid) {
			return '-';
		}
	}

	if ($var === 'ostemplate') {
		$v = strtilfirst($this->ostemplate, "-");
		return "_lxspan:$v:$this->ostemplate:";
	}

	if ($var === 'coma_vmipaddress_a') {
		$data = getFirstFromList($this->vmipaddress_a);
		if(isset($data->nname))
		{
			return $data->nname;
		}
		else
		{
			return NULL;
		}
	}

	if ($var === 'lmemoryusage_f') {
		if (!isset($this->lmemoryusage_f)) {
			return '-';
		}
		return $this->lmemoryusage_f;
	}

	if ($var === 'resourceplan_used_f') {
		return strtil($this->resourceplan_used, "___");
	}

	if ($var === 'ldiskusage_f') {
		if (!isset($this->ldiskusage_f)) {
			return '-';
		}
		return $this->ldiskusage_f;
	}

	if ($var === 'state') {
		if (!$this->state || $this->state === 'alright') {
			return 'ok';
		}
		return $this->state;
	}

	return parent::display($var);

}


function deleteSpecific()
{
	global $gbl, $sgbl, $login, $ghtml; 
	$status = $this->getStatusForThis();
	if ($status === 'create') {
		throw new lxException ("cant_delete_while_creating", '');
	}
	//$this->notifyObjects('delete');
	$sq = new Sqlite(null, 'tmpipassign');
	foreach($this->vmipaddress_a as $io) {
		$sq->rawQuery("delete from tmpipassign where nname = '$io->nname'");
	}

}

function perDisplay($var)
{
	$realname = strtil($var, "_per");
	switch($var) {
		case "disk_usage_per":
			return array($this->priv->$realname, $this->used->$realname, "MB");

		case "traffic_usage_per":
			return array($this->priv->$realname, $this->used->$realname, "MB/Month");
	}
}

static function createListAlist($parent, $class = NULL)
{
	global $gbl, $sgbl, $login, $ghtml; 
	$alist[] = "a=list&c=vps";
	if ($parent->get__table() === 'client') {
		if ($login->priv->isOn('vps_add_flag')) {
			$alist[] = "a=addform&c=vps&dta[var]=ttype&dta[val]=openvz";
			$alist[] = "a=addform&c=vps&dta[var]=ttype&dta[val]=xen";
		}
	}
	if ($parent->isAdmin()) {
		$alist[] = "a=list&c=all_vps";
		$alist[] = "a=updateform&sa=deleteorphanedvps";
	}
	return $alist;


}



function getFullPath()
{
 	$path = $this->nname;
	return   $this->root .  $path;
}

function isAction($var)
{
	global $gbl, $sgbl, $login;

	return true;
}

function generateMacAddr()
{
	
	$base = strtil($this->nname, ".vm");

	$sq = new Sqlite(null, 'vps');
	$i = 0;
	while (true) {
		$name = "$base$i";
		$name = md5($name);
		$name = preg_replace("/^(..)(..)(..).*$/", "$1:$2:$3", $name);
		$mac = "aa:00:$name";
		$res = $sq->getRowsWhere("macaddress = '$mac:%'");
		if (!$res) {
			break;
		}
		$i++;
	}
	$this->macaddress = $mac;
	$this->setUpdateSubaction();
} 

static function createListSlist($parent)
{


	$nlist['nname'] = null;
	$nlist['ostemplate'] = null;
	$nlist['contactemail'] = null;
	$nlist['coma_vmipaddress_a'] = null;
	$nlist['vpsid'] = null;
	$nlist['hostname'] = null;
	$nlist['resourceplan_used'] = null;
	$nlist['parent_clname'] = null;
	$nlist['ttype'] = array('s', array('--any--', 'xen', 'openvz'));
	$nlist['status'] = array('s', array('--any--', 'on', 'off', 'deleted'));
	$nlist['traffic_usage_q'] = array('s', array('--any--', 'overquota', 'underquota'));
	if ($parent->get__table() === 'client') {
		$res = $parent->getServerList('vps');
		$rs = $res;
		//foreach($rs as $k => $v) { if (!$v) { $rs[$k] = $k ; } }
		$rs = lx_merge_good(array("--any--"), $rs);
		$nlist['syncserver'] = array('s', $rs);
	}
	return $nlist;
}

function hasBackupFtp() { return true; }



static function hasViews() { return true ; }
static function createListNlist($parent, $view)
{
	global $gbl, $sgbl, $login, $ghtml; 

	$name_list["cpstatus"] = "3%";
	//$name_list["state"] = "3%";
	$name_list["ttype"] = "3%";
	$name_list["status"] = "3%";


	$name_list["nname"] = "100%";
	$name_list["ostemplate"] = "100%";
	$name_list["coma_vmipaddress_a"] = "10%";
	$name_list["vpsid"] = "10%";
	if ($parent->get__table() === 'client') {
		$name_list["syncserver"] = "20%";
	} else {
		$name_list["parent_name_f"] = "20%";
	}


	//$name_list["domain_num"] = "3%";

	if ($view === 'quota') {
		$name_list["traffic_usage"] = "5%";
		$name_list["__v_priv_used_traffic_usage"] = "5%";
		$name_list["traffic_usage_per_f"] = "5%";
		$name_list["lmemoryusage_f"] = "10%";
		$name_list["ldiskusage_f"] = "10%";
	} else { 
		$name_list["ddate"] = "20%";
		$name_list["resourceplan_used_f"] = "100%";
		$name_list["hostname"] = "100%";
		$name_list["traffic_usage"] = "5%";
		$name_list["abutton_updateform_s_information"] = "3%";
		$name_list["abutton_list_s_vmipaddress_a"] = "3%";
		$name_list["abutton_updateform_s_limit"] = "3%";
	}
	//$name_list["portmon_f"] = "3%";
	return $name_list;
}


function getQuotatraffic_usage()
{
	global $gbl, $sgbl, $login, $ghtml; 
	if (isset($sgbl->__var_traffic_usage)) {
		return $sgbl->__var_traffic_usage[$this->nname];
	} else {
		return $this->used->traffic_usage;
	}
}

function getQuotabackup_num()
{
	global $gbl, $sgbl, $login, $ghtml; 
	if (isset($sgbl->__var_backup_num)) {
		return $sgbl->__var_backup_num[$this->nname];
	} else {
		return $this->used->number_of_backups;
	}
}

function getQuotatraffic_last_usage()
{
	global $gbl, $sgbl, $login, $ghtml; 
	if (isset($sgbl->__var_traffic_last_usage)) {
		return $sgbl->__var_traffic_last_usage[$this->nname];
	} else {
		return $this->used->traffic_last_usage;
	}

}

function getQuotaDisk_Usage()
{
	return $this->priv->disk_usage;
}

function getQuotaKernelMem_Usage()
{
	return $this->priv->kernelmem_usage;
}

function getQuotaGuarMem_usage()
{
	return $this->priv->guarmem_usage;
}

function getQuotaMemory_usage()
{
	return $this->priv->memory_usage;
}

function getQuotaProcess_usage()
{
	return $this->priv->memory_usage;
}


function getQuotarealmem_usage()
{
	return $this->priv->realmem_usage;
}

function getQuotacpu_usage()
{
	return $this->priv->memory_usage;
}

static function getBackupDiskSize($vpsname)
{
	return lxfile_dirsize("__path_program_home/vps/$vpsname/__backup");
}

function commandUpdate($subaction, $param)
{
	switch($subaction) {
		case "change_plan":
			{
				if (isset($param['template-name'])) {
					$param['newresourceplan'] = $param['template-name'];
				} else if(isset($param['template_name'])) {
					$param['newresourceplan'] = $param['template_name'];
				}
				break;
			}
	}

	return $param;
}

static function createListIlist()
{
}


function postUpdate()
{

	// The lxclient postupdate which checks for change of skin...


	if ($this->subaction === 'createtemplate') {

		if ($this->isXen()) {
			$stem = explode("-", $this->ostemplate);

			if ($this->isWindows()) {
				$name = "{$stem[0]}-";
			} else {
				$name = "{$stem[0]}-{$stem[1]}-{$stem[2]}-";
			}

			$templatename = "$name{$this->newostemplate_name_f}";
			if ($this->isWindows()) {
				$tempfpath = "__path_program_home/xen/template/$templatename.img";
			} else {
				$tempfpath = "__path_program_home/xen/template/$templatename.tar.gz";
			}

			if (lxfile_exists($tempfpath)) {
				throw new lxException("template_already_exists");
			}

		} else {
			$stem = explode("-", $this->ostemplate);
			$name = "{$stem[0]}-{$stem[1]}-{$stem[2]}-";
			$templatename = "$name{$this->newostemplate_name_f}";
			if (lxfile_exists("/vz/template/cache/$templatename.tar.gz")) {
				throw new lxException("template_already_exists");
			}
		}

	}

	if ($this->subaction === 'rebuild' || $this->subaction === 'installkloxo') {
		if_demo_throw_exception();
		$this->setUpOsTemplateDownloadParam();
	}

	parent::postUpdate();

}

function getMultiUpload($var)
{
	if ($var === 'limit') {
		//return array('limit_s', 'change_plan');
		return "limit";
	}
	return $var;

}

function updateHeirarchySpecific()
{
	global $gbl, $sgbl, $login, $ghtml; 
	$driverapp = $gbl->getSyncClass($this->__masterserver, $this->syncserver, 'vps');
	$this->ttype = $driverapp;
}


function getQuickClass()
{
	return 'self';
}

function makeSureTheUserExists()
{
	global $gbl, $sgbl, $login, $ghtml; 
	$class = $this->get__table();
	$driverapp = $gbl->getSyncClass($this->__masterserver, $this->syncserver, $class);
	if ($this->username) {
		return true;
	}

	$username = str_replace(".", "", $this->nname);
	if ($this->ttype === 'xen') {
		$arglist = array($username, $this->password, $this->getIid(), "/usr/bin/lxxen");
	} else {
		$arglist = array($username, $this->password, $this->getIid(), "/usr/bin/lxopenvz");
	}

	$res = rl_exec_get($this->__masterserver, $this->syncserver,  array("vps", 'create_user'), $arglist);
	$this->username = $res;
	$this->setUpdateSubaction();
}

function syncCreateUser()
{
	if ($this->ttype === 'xen') {
		$shell = "/usr/bin/lxxen";
	} else {
		$shell = "/usr/bin/lxopenvz";
	}

	$username = vps::create_user($this->username, $this->password, $this->getIid(), $shell);

	$ret = array("__syncv_username" => $username);
	return $ret;

}

static function create_user($name, $passwd, $id, $shell)
{
	return os_create_system_user($name, $passwd, $id, $shell, "/home/$name");
}


function makeSureVifExists()
{
	if ($this->vifname) {
		return;
	}
	if (!$this->isXen()) {
		return;
	}
	$this->getVifName();
	$this->setUpdateSubaction();
}

function makeSureTheMacAddressExists()
{
	if ($this->macaddress) {
		return;
	}
	if (!$this->isXen()) {
		return;
	}
	$this->generateMacAddr();

}

function doDriverSpecific()
{
	global $gbl, $sgbl, $login, $ghtml; 
	$driverapp = $gbl->getSyncClass($this->__masterserver, $this->syncserver, 'vps');
	$this->ttype = $driverapp;
	$func = "postadd_$driverapp";
	$this->$func();
}

function doServerSpecific()
{
	$this->getBestLocation();
}


function isXenLvm()
{
	return char_search_beg($this->corerootdir, "lvm:");
}

function getLocationlist()
{
	$sq = new Sqlite(null, 'dirlocation');
	$res = $sq->getRowsWhere("nname = '$this->syncserver'", array("ser_common_location_a", "ser_{$this->ttype}_location_a"));

	$object = new dirlocation(null, null, 'hello');
	$location = unserialize(base64_decode($res[0]["ser_{$this->ttype}_location_a"]));
	return $location;
}

function getBestLocation()
{

	$location = $this->getLocationlist();
	$llist = get_namelist_from_objectlist($location);

	if ($this->isXen()) {
		$extra = array('/home/xen');
	} else {
		$extra = array('/vz/private');
	}

	if ($llist) {
		$list = $llist;
	} else {
		$list = $extra;
	}

	$xenlvm = false;

	foreach($list as $l) {
		if (char_search_beg($l, "lvm:")) {
			$xenlvm = true;
		}
		$nlist[] = $l;
	}

	if ($this->isXen()) {
		if (!$xenlvm) {
			if (db_get_value('client', 'admin', 'ddate') > 1203102323) {
				throw new lxException ("XEN needs a empty LVM partition.", '');
			}
		}
	}

	if ($this->isWindows()) {
		if (!$xenlvm) {
			throw new lxException ("windows_needs_lvm", '');
		}
	}

	$array = getBestLocationFromServer($this->syncserver, $nlist);
	$this->corerootdir = $array['location'];

	$name = strtil($this->nname, ".vm");
	if ($this->isXenLvm()) {
		$this->maindiskname = "{$name}_rootimg";
		$this->swapdiskname = "{$name}_vmswap";
	} else {
		$this->maindiskname = "root.img";
		$this->swapdiskname = "vm.swap";
	}
	dprintr($array);
}

function getFullPathOstemplate()
{
	if ($this->isXen()) {
		if ($this->isWindows()) {
			return "/home/hypervm/xen/template/{$this->ostemplate}";
		} else {
			return "/home/hypervm/xen/template/{$this->ostemplate}.tar.gz";
		}
	} else {
		return "/vz/template/cache/{$this->ostemplate}.tar.gz";
	}

}

function getOsTemplateFromMaster($file)
{
	if (!isset($this->__var_ost_md5)) {
		dprint("md5 not set.. That means, we are in master\n");
		return;
	}
	$md5 = lmd5_file($file);
	dprint("md5 $file $md5 {$this->__var_ost_md5}\n"); 
	if ($md5 === $this->__var_ost_md5) {
		dprint("Md5 of ostemplate is same as that of master.\n");
		return;
	}

	dprint("Md5 doesnt match.. downloading...\n");
	$filepass = $this->__var_ostemplatefileserv;
	getFromFileserv($this->__var_masterip, $filepass, $file);
}


function postAdd()
{

	global $gbl, $sgbl, $login, $ghtml; 


	$parent = $this->getParentO();
	$this->cpstatus = 'on';
	$this->status = 'create';
	$this->state = 'ok';

	$this->kloxo_flag = 'on';



	$this->rootpassword = $this->realpass;

	$this->username = str_replace(".", "", $this->nname);

	if ($this->isOn('use_resourceplan_f')) {
		$template = getFromAny(array($login, $parent), 'resourceplan', $this->resourceplan_f);
		if (!$template) {
			throw new lxexception("the_plan_doesnt_exist", 'resourceplan_f', $this->resourceplan_f);
		}
		$this->resourceplan_used = $this->resourceplan_f;
		$this->priv = clone $template->priv;
		$this->fixPrivUnset();
		$this->changePlanSpecific($template);
	}


	if ($this->one_ipaddress_f) {
		full_validate_ipaddress($this->one_ipaddress_f);
	}

	$this->rootpassword_changed = 'on';

	if ($this->one_ipaddress_f) {
		$ipadd = new vmipaddress_a(null, $this->syncserver, $this->one_ipaddress_f);

		$this->vmipaddress_a[$ipadd->nname] = $ipadd;
	}

	$syncs = new Pserver(null, $this->syncserver, $this->syncserver);
	$syncs->get();

	if (is_unlimited($this->priv->vmipaddress_a_num)) {
		$this->priv->vmipaddress_a_num = 2;
	}


	if ($this->num_ipaddress_f) {
		$netinfo = $syncs->getIpPool($this->num_ipaddress_f);

		$totallist = $netinfo['ip'];

		if (!$this->nameserver) {
			$this->nameserver = $netinfo['nameserver'];
		}

		$this->networkgateway = $netinfo['networkgateway'];
		$this->networknetmask = $netinfo['networknetmask'];
	

		if ($totallist) foreach($totallist as $ip) {
			$ipadd = new vmipaddress_a(null, $this->syncserver, $ip);
			$this->vmipaddress_a[$ipadd->nname] = $ipadd;
			ippool::addToTmpIpAssign($ip);

		}
	}


	$this->used->vmipaddress_a_num = count($this->vmipaddress_a);

	if (!$this->hostname) {
		$this->hostname = strtil($this->nname, ".vm");
	}

	$this->createSyncClass();

	$this->createPublicPrivate($sslvar);

	// hack hack convert listpriv into a differnet object.


	$driverapp = $gbl->getSyncClass($this->__masterserver, $this->syncserver, 'vps');

	$this->ttype = $driverapp;

	$this->getBestLocation();
	$func = "postadd_$driverapp";
	$this->$func();

	// $this->distributeChildQuota();


	////////////////////

	/*
	if (exists_in_db($this->__masterserver, "uuser", $uuser->nname)) {
		throw new lxexception('user_exists_in_db', 'uuser');
	}
*/

	
	$this->setUpOsTemplateDownloadParam();
	$backup = new LxBackup($this->__masterserver, $this->__readserver, $this->getClName());
	$backup->initThisDef();
	$this->AddObject('lxbackup', $backup);


	$this->lxclientpostAdd();
}


function setUpOsTemplateDownloadParam()
{
	if ($this->isLocalhost()) {
		return;
	}
	if (!lxfile_real($this->getFullPathOstemplate())) {
		return;
	}
	$this->__var_ost_md5 =  lmd5_file($this->getFullPathOstemplate());
	$this->__var_ostemplatefileserv = cp_fileserv($this->getFullPathOstemplate());
	$this->__var_masterip = getOneIPForLocalhost($this->syncserver);
}

/**
* Check the existance of a VPS lock running.
* 
* Throws a exception if someone else is using a VPS.
*
* @author Anonymous <anonymous@lxcenter.org>
* @author Ángel Guzmán Maeso <angel.guzman@lxcenter.org>
*
* @throws lxException
* @return void
*/
function checkVPSLock($vpsid = NULL)
{
	$file = 'vpslock_' . $vpsid . '.pid';

	// @todo this seems a harmful way to check a lock file with sleep. Research this
	$i = 0;
	while (TRUE) {
		if (lx_core_lock($file)) {
			sleep(3);
			$i++;
			if ($i >= 8) {
				throw new lxexception('vps_is_locked_by_another_user', '', $vpsid);
			}
		} else {
			break;
		}
	}
}

function postadd_xen()
{

	if ($this->vncdisplay) { return; }

	$sq = new Sqlite(null, "vps");
	$res = $sq->rawQuery("select vncdisplay from vps order by (vncdisplay + 0) DESC limit 1");

	$this->vncdisplay = $res[0]['vncdisplay'] + 1;

	$this->getVifName();
	$this->iid = $this->nname;
	$this->generateMacAddr();
	return;
}

function postadd_openvz()
{

	global $gbl, $sgbl, $login, $ghtml; 

	if ($this->vpsid) { return; }

	$gen = $login->getObject('general');

	$sq = new Sqlite(null, "vps");
	$res = $sq->rawQuery("select vpsid from vps order by (vpsid + 0) DESC limit 1");



	if ($gen->generalmisc_b->openvzincrement > 0) {
		$increment = $gen->generalmisc_b->openvzincrement;
	} else {
		$increment = 10;
	}

	if (!$res) {
		$this->vpsid = 110;
	} else {
		$this->vpsid = $res[0]['vpsid'] + $increment;
	}

	if ($this->vpsid == '10') {
		$this->vpsid = 110;
	}

	$this->iid = $this->vpsid;
	return;
}


function checkIfOffensive()
{
	$list = array("graph_", "getBeancounter", "hardpoweroff");
	if (is_array($this->subaction)) {
		dprint("Subaction is array\n");
		return true;
	}

	if (char_search_beg($this->subaction, "top_level")) {
		return false;
	}

	foreach($list as $l) {
		if (char_search_beg($this->subaction, $l)) {
			return false;
		}
	}
	return true;
}



function createShowMainImageList()
{
	$vlist['status'] = 1;
	$vlist['ttype'] = 1;
	return $vlist;
}

function getZiptype()
{
	return "tgz";
}

function postSync()
{
	if ($this->dbaction === 'update' && $this->subaction === 'createtemplate') {
		$this->getOstemplateFromSlave();
	}

	if ($this->dbaction === 'add') {
		$this->notifyObjects('add');
	}
}

function getOstemplateFromSlave()
{

	if ($this->isLocalhost()) { return; }

	if ($this->isXen()) {
		$file = "/home/hypervm/xen/template/{$this->__ostemplate_filename}";
	} else {
		$file = "/vz/template/cache/{$this->__ostemplate_filename}";
	}

	rl_exec_get(null, 'localhost', 'getFromFileserv', array($this->syncserver, $this->__ostemplate_filepass, $file));
	rl_exec_get(null, 'localhost', 'lxfile_generic_chmod', array($file, "0755"));
}


function getVifName()
{
	$vifbase = str_replace(".", "", $this->nname);
	$vifbase = substr($vifbase, 0, 4);
	$n = 0;
	$sq = new Sqlite(null, 'vps');
	while (true) {
		$vifname = "$vifbase$n";
		$res = $sq->getRowsWhere("vifname = '$vifname'");
		if (!$res) {
			break;
		}
		$n++;
	}

	$this->vifname = $vifname;
}

static function add($parent, $class, $param)
{
	global $gbl, $sgbl, $login, $ghtml; 

	if (if_demo()) { throw new lxException ("demo", $v); }

	lxclient::fixpserver_list($param);

	$param['nname'] = strtolower($param['nname']);

	if (!cse($param['nname'], ".vm")) {
		$param['nname'] .= ".vm";
	}

	if (csa($param['nname'], '-')) {
		throw new lxexception('name_cannot_contain_dash', 'nname', '');
	}

	if (csa($param['nname'], ' ')) {
		throw new lxexception('name_cannot_contain_space', 'nname', '');
	}

	 // the uuser is two steps removed from the main object (domain), and thus the automatic nname creation doesn't seem to work. So we have to do it here.


	$param['realpass'] = $param['password'];
	$param['password'] = crypt($param['password']);

	$total = db_get_value("pserver", $param['syncserver'], "max_vps_num");
	if ($total) {
		$sq = new Sqlite(null, 'vps');
		$countres = $sq->rawQuery("select count(*) from vps where syncserver = '{$param['syncserver']}'");
		$countres = $countres[0]['count(*)'];
		if ($countres >= $total) {
			throw new lxexception('vps_per_server_exceeded', 'syncserver', "$countres > $total");
		}
	}
	

	return $param;
}

function getFfileFromVirtualList($name)
{


	if ($this->isXen() && $this->isOn('status')) {
		throw new lxexception('to_use_filemanager_shut_down_xen_vm_first');
	} 
	
	if (!$this->isXen() && !$this->vpsid) {
		throw new lxexception('something_wrong_there_is_no_vpsid');
	}

	$name = coreFfile::getRealpath($name);
	$name = '/' . $name;
	if ($this->isXen()) {
		$root = "__path_home_dir/xen/$this->nname/mnt";
	} else {
		if ($this->isOn('status')) {
			$root = "/vz/root/{$this->vpsid}";
		} else {
			$root = "{$this->corerootdir}/{$this->vpsid}/";
		}
	}

	$ffile= new Ffile($this->__masterserver, $this->syncserver, $root, $name, "root");
	$ffile->__parent_o = $this;
	$ffile->get();
	return $ffile;
}

function getBackupServer()
{
	$use_slaves = "--use-slaves-backup-server--";
	if (!$this->centralbackup_server || ($this->centralbackup_server === $use_slaves)) {
		$bserver = db_get_value("pserver", $this->syncserver, "centralbackupserver");
	} else {
		$bserver = $this->centralbackup_server;
	}
	return $bserver;
}

static function continueFormFinish($parent, $class, $param, $continueaction)
{
	//$vlist['__m_message_pre'] = 'make_sure_ipaddress_template';

	global $gbl, $sgbl, $login, $ghtml; 
	// For IE.. too many variables won't work in get mode.


	$sgbl->method = "post";

	$driverapp = $gbl->getSyncClass('localhost', $param['listpriv_s_vpspserver_sing'], 'vps');
	$ostlist = rl_exec_get(null, $param['listpriv_s_vpspserver_sing'], array("vps__$driverapp", "getOsTemplatelist"));

	$vlist['ostemplate'] = array('A', $ostlist);

	Lxclient::fixpserver_list($param);

	//dprintr($param);
	if (!isOn($param['use_resourceplan_f'])) {
		$vlist['__c_subtitle_quota'] = 'Quota';
		$qvlist = getQuotaListForClass('vps');
		$vlist = lx_merge_good($vlist, $qvlist);
	}
	//$vlist['dbtype_list'] = null;

	$ret['param'] = $param;
	$ret['variable'] = $vlist;
	$ret['action'] = "Add";
	return $ret;
}


static function continueForm($parent, $class, $param, $continueaction)
{

	global $gbl, $sgbl, $login, $ghtml; 

	/*
	if(!eregi("^[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,5})$", $param['nname'])) { 
		throw new lxException('invalid_domain_name', 'nname');
	}
*/

	if (!cse($param['nname'], ".vm")) {
		$param['nname'] .= ".vm";
	}

	$param['nname'] = strtolower($param['nname']);


	if ($param['one_ipaddress_f']) {
		full_validate_ipaddress($param['one_ipaddress_f']);
	}

	/*
	if (!preg_match("/[a-zA-Z0-9\-]+\.[a-zA-Z0-9\-\.]+]/i", $param['nname'])) {
		throw new lxException('domain_name_invalid', 'nname');
	}
*/



	if (isOn($param['send_welcome_f'])) {
		if (!$param['contactemail']) {
			throw new lxexception("sending_welcome_needs_contactemail", array('contactemail', 'send_welcome_f'), '');
		}
		if (!validate_email($param['contactemail'])) {
			throw new lxexception("contactemail_is_not_valid_email_address", 'contactemail', '');
		}
	}

	if ($param['resourceplan_f'] === 'continue_without_plan') {
		$vlist['__c_subtitle_quota'] = 'Quota';
		$qvlist = getQuotaListForClass('vps');
		$vlist = lx_merge_good($vlist, $qvlist);
		$ret['param'] = $param;
		$ret['variable'] = $vlist;
		$ret['action'] = "Add";
	} else {
		$template = getFromAny(array($login, $parent), 'resourceplan', $param['resourceplan_f']);
		$param['use_resourceplan_f'] = 'on';
		if (!$template) {
			throw new lxexception("the_template_doesnt_exist", 'resourceplan_f', $param['resourceplan_f']);
		}

		$ret['action'] = 'addnow';

		$ret['param'] = $param;
		return $ret;
	}

	return $ret;
}



static function addCommand($parent, $class, $p)
{

	checkIfVariablesSet($p, array('name', 'v-password', 'v-num_ipaddress_f', 'v-contactemail', 'v-syncserver', 'v-ostemplate'));

	checkIfVariablesSetOr($p, $param, 'resourceplan_f', array('v-plan_name'));
	checkIfVariablesSetOr($p, $param, 'ttype', array('v-type'));

	/*
	if (!cse($p['name'], ".vm")) {
		throw new lxexception("name_should_end_in_.vm", '', '');
	}
*/

	$param['nname'] = $p['name'];
	$param['nname'] = strtolower($param['nname']);
	$dname = $param['nname'];
	$param['rootpassword'] = $p['v-password'];
	$param['use_resourceplan_f'] = 'on';
	return $param;
}

static function addform($parent, $class, $typetd = null)
{

	global $gbl, $sgbl, $login, $ghtml; 


	$vlist['nname'] = array('m', array("posttext" => ".vm"));

	$nclist = $parent->getResourcePlanList('resourceplan');

	$vlist['__v_button'] = $login->getKeywordUc('add');
	$vlist['password'] = null;
	$vlist['num_ipaddress_f'] = array('s', range(0, 8));
	$vlist['one_ipaddress_f'] = null;
	$vlist['contactemail'] = "";
	$vlist['send_welcome_f'] = "";

	$vlist['__c_subtitle_info'] = "Info";
	$vlist['hostname'] = "";

	if ($typetd['val'] === 'xen') {
		$vlist['networkgateway'] = null;
	}
	$vlist['nameserver'] = "";
	$vlist['resourceplan_f'] = array('A', $nclist);

	$vlist['__c_subtitle_server'] = "Server";
	//var_dump($typetd['val']);
	
	// $typetd['val'] openvz or xen in clientlib.php
	$serverlist = $parent->getVpsServers($typetd['val']);
	if (!$serverlist) {
		throw new lxexception('Server no configured for driver '. $typetd['val'] . '. You can use setdriver.php for configure a driver.
		 For example:
		cd /usr/local/lxlabs/hypervm/httpdocs;
		lphp.exe ../bin/common/setdriver.php --server=localhost --class=vps --driver='. $typetd['val'] . '', '', '');
	}

	$sinfo = pserver::createServerInfo($serverlist, "vps");
	$sinfo = get_warning_for_server_info($parent, $sinfo);

	$vlist['server_detail_f'] = array('M', $sinfo);
	$vlist['syncserver'] = array('s', $serverlist);
	$vlist['ostemplate'] = array('A', vps::getVpsOsimage($parent, $typetd['val']));


	$ret['variable'] = $vlist;
	$ret['action'] = "continue";
	$ret['continueaction'] = "server";

	return $ret;

}

function changePlanSpecific($plan)
{
	$this->xenostemplate_list = $plan->xenostemplate_list;
	$this->openvzostemplate_list = $plan->openvzostemplate_list;
	$this->disable_per = $plan->disable_per;
	//$this->centralbackup_flag = $plan->centralbackup_flag;
}

function getOstemplatePath()
{
	if ($this->isXen()) {
		return "/home/hypervm/xen/template/";
	} else {
		return "/vz/template/cache/";
	}
}

static function getVpsOsimage($parent, $driver, $type = "add")
{
	global $gbl, $sgbl, $login, $ghtml; 
	$class = "vps__$driver";

	$var = "{$driver}_ostl_a";

	$list = exec_class_method($class, "getOsTemplatelist", $type);

	/*
	$obj = new Ostemplatelist(null, null, 'admin');
	$obj->get();
	$ostlist = get_namelist_from_objectlist($obj->$var);
	// Filter only if not admin.
	if (count($ostlist) > 0 && !$login->isAdmin()) {
		foreach($list as $k => $v) {
			if (!array_search_bool($k, $ostlist)) {
				unset($list[$k]);
			}
		}
	}
*/


	$ostlist = "{$driver}ostemplate_list";

	if (!$login->isAdmin() && count($login->$ostlist) > 0) {
		foreach($list as $k => $v) {
			if (!array_search_bool($k, $login->$ostlist)) {
				unset($list[$k]);
			}
		}
	}

	return $list;

}

function createGraphList()
{
	$alist[] = "a=graph&sa=traffic";
	if ($this->isXen()) {
	} else {
		$alist[] = "a=graph&sa=memoryusage";
	}

	$alist[] = "a=graph&sa=cpuusage";
	return $alist;
}

function createShowIlist()
{

	$ilist[] = "ddate";
	return $ilist;
}

function getIid()
{
	if ($this->isXen()) {
		return $this->nname;
	} else {
		return $this->vpsid;
	}
}

function setiid()
{
	if ($this->isXen()) {
		$this->iid = $this->nname;
	} else {
		$this->iid = $this->vpsid;
	}
}

function updatePoweroff($param)
{
	$param['status'] = 'off';
	return $param;
}


function updateRebuild($param)
{
	global $gbl, $sgbl, $login, $ghtml; 

	$gen = $login->getObject('general')->generalmisc_b;

	$time = $gen->rebuild_limit_time * 60;

	if ($time <= 0) { $time = 10 * 60; }

	if (!$login->isAdmin()) {
		if ((time() - $this->last_rebuild_time) < $time) {
			throw new lxexception("rebuild_time_limit", 'ostemplate', $time/60);
		}
	}

	$this->last_rebuild_time = time();
	$this->write();
	$gbl->__ajax_refresh = true;
	return $param;
}


function doKloxoInit($mountpoint)
{
	if (lxfile_exists("$mountpoint/usr/local/lxlabs/kloxo/")) { 

		lfile_put_contents("$mountpoint/usr/local/lxlabs/kloxo/etc/authorized_keys", $this->text_public_key);

		if ($this->isOn('__var_kloxo_remote_i_flag')) {
			lfile_put_contents("$mountpoint/usr/local/lxlabs/kloxo/etc/remote_installapp", $this->__var_kloxo_iapp_url);
		} else {
			lxfile_rm("$mountpoint/usr/local/lxlabs/kloxo/etc/remote_installapp");
		}

	}

	if (lxfile_exists("$mountpoint/usr/local/lxlabs/lxadmin/")) {

		lfile_put_contents("$mountpoint/usr/local/lxlabs/lxadmin/etc/authorized_keys", $this->text_public_key);

		if ($this->isOn('__var_kloxo_remote_i_flag')) {
			lfile_put_contents("$mountpoint/usr/local/lxlabs/lxadmin/etc/remote_installapp", $this->__var_kloxo_iapp_url);
		} else {
			lxfile_rm("$mountpoint/usr/local/lxlabs/lxadmin/etc/remote_installapp");
		}
	}
}


function isNotWindows()
{
	return !$this->isWindows();
}

function isWindows()
{
	return char_search_beg($this->ostemplate, "windows");
}
function isBlankWindows()
{
	return char_search_beg($this->ostemplate, "windows-lxblank");
}

function createShowActionList(&$alist) 
{ 
	$this->getToggleUrl($alist);
	$this->getCPToggleUrl($alist);
}

function createShowNote() { return !$this->isLogin(); }

function createShowAlist(&$alist, $subaction = null)
{

	global $gbl, $sgbl, $login, $ghtml; 

	$revc = $login->getObject('general')->reversedns_b;

	$hackbutton = $login->getObject('general')->hackbuttonconfig_b;


	static $status;
	static $reason;

	if (isUpdating()) {
		$ghtml->print_redirect_back('system_is_updating_itself', '');
	}

	if ($ghtml->frm_action === 'show') {
	} else {
		$status = $this->status;
	}


	$this->setIId();
	if (!$status) {
		try {
			$status = $this->getStatusForThis();
			dprint($status);
		} catch (Exception $e) {
			if (if_demo()) {
				$status = 'on';
			} else {
				print("Could not get Status: Reason: {$e->getMessage()}");
				exit;
			}
		}

	}

	if (csa($status, ":")) {
		$st = explode(":", $status);
		$status = array_shift($st);
		$reason = implode(" ", $st);
		$reason = str_replace("\n", " ", $reason);
	}

	if ($status !== $this->status) {
		dprint("Status in Db not the same as Status of the system, resetting db");
		$this->status = $status;
		$this->setUpdateSubaction();
	}

	if ($this->status === 'create') {
		$alist['__v_message'] = "<b> VM is being Created... Please Wait.. </b> \n";
		$alist['__v_refresh'] = true;
		return;
	}


	if ($this->status === 'createfailed') {
		$alist['__v_message'] = "<b> VM creation has failed. Reason: $reason </b>\n";
		$alist['__v_refresh'] = true;
		return;
	}

	if ($this->checkIfLockedForAction('restore')) {
		$alist['__v_message'] = 'The VM is getting restored, please wait...';
		$alist['__v_refresh'] = true;
		return $alist;
	}

	if ($this->checkIfLockedForAction('rebuild')) {
		$alist['__v_message'] = 'The VM is getting rebuilt, please wait...';
		$alist['__v_refresh'] = true;
		return $alist;
	}

	if ($this->checkIfLockedForAction('switchserver')) {
		$alist['__v_message'] = 'The VM is being migrated, please wait...';
		$alist['__v_refresh'] = true;
		return $alist;
	}

	if ($this->checkIfLockedForAction('livemigrate')) {
		$alist['__v_message'] = 'The VPS is being Migrated Live. The CP is inactive, but your VPS is running Normally.';
		$alist['__v_refresh'] = true;
		return $alist;
	}

	if ($this->status === 'deleted') {
		$alist['__v_message'] = "The VM $this->getIid() Doesn't Exist on $this->syncserver. If you have migrated the vps, you can manually change the server in the database using the below, Or restore from backup. </b>";
		$alist['__v_refresh'] = true;
		$alist['__title_main'] = $login->getKeywordUc('resource');
		$alist['__v_dialog_csync'] = "a=updateform&sa=changesyncserver";
		$alist['__var_backup_flag'] = "a=show&o=lxbackup";
		$alist[] = "a=updateform&sa=rebuild";
		return null;
	}
	/*
	if ($this->getObject('lxbackup')->backupstage === 'doing') { 
		$alist['__v_message'] = 'The VM is getting backed up, please wait...';
		$alist['__v_refresh'] = true;
		return $alist;
	}
*/


	$this->makeSureTheUserExists();
	$this->makeSureTheMacAddressExists();
	$this->makeSureVifExists();


	if ($subaction === 'config') {
		return $this->createShowAlistConfig($alist);
	}

	if ($subaction === 'graph') {
		$alist['__title_graph'] = $login->getKeywordUc('graph');
		return $alist;
	}

		
	$alist['__title_administer'] = $login->getKeywordUc('administration');

	if ($this->getParentO()->isClass('client')) {
		if (!$this->isLogin()) {
			if ($login->priv->isOn('vps_limit_flag')) {
				$alist['__v_dialog_limit'] = "a=updateForm&sa=limit";
				$alist['__v_dialog_plan'] = "a=updateForm&sa=change_plan";
			}
		}
	}
	$alist['__v_dialog_info'] = "a=updateForm&sa=information";
	$alist['__v_dialog_pass'] = "a=updateform&sa=password";
	if (!$this->isWindows()) {
	//$alist[] = "a=show&n=browsebackup&l[class]=ffile&l[nname]=/";


		$bslave = $this->getBackupServer();

		if (!is_disabled_or_null($bslave) && $this->priv->isOn('centralbackup_flag')) {
			$alist[] = "a=show&n=browsebackup";
		}
	}

	if (!$this->isLogin()) {
		$alist[] = "a=update&sa=dologin";
	}



	$alist['__title_console'] = $login->getKeywordUc('console');
	//$alist[] = "a=show&o=sshclient";
	if ($this->isWindows()) {
		$alist[] = "a=show&o=vncviewer";
	} else {
		$alist[] = "a=show&o=consolessh";
		$alist[] = "a=list&c=sshauthorizedkey";
	}
	if (!$this->isXen()) {
		$alist[] = "a=updateform&sa=commandcenter";
	}
	$alist[] = "a=list&c=blockedip";
	$alist['__v_dialog_netw'] = "a=updateform&sa=network";
	$alist[] = "a=list&c=traceroute";
	if (!$this->isWindows()) {
		$alist['__v_dialog_main'] = "a=updateform&sa=mainipaddress";
	}
	if (!$this->isXen()) {
		$alist['__v_dialog_fixdev'] = "a=updateform&sa=fixdev";
	}


	if ($hackbutton && $hackbutton->isOn('nobackup') && !$this->isWindows()) {
		vps::$__desc_backup_num = array("", "",  "backup:number_of_backups", "number_of_backups");
	} else {
		if ($this->priv->isOn('backup_flag')) {
			$alist[] = "a=show&o=lxbackup";
		}
	}

	if ($login->isAdmin()) {
		$alist['__title_action'] = $login->getKeywordUc('action');
	}

	if ($login->priv->isOn("rebuildvps_flag")) {
		$alist['__v_dialog_rebuild'] = "a=updateform&sa=rebuild";
	}

	if (!$this->isWindows()) {
		$alist[] = "a=updateform&sa=recovervps";
	}
		

	if (!$this->isWindows()) {
		if ($login->isAdmin()) {
			$alist['__v_dialog_sw'] = "a=updateform&sa=switchserver";
		}

		if ($login->isAdmin() && !$this->isXen()) {
			$alist['__v_dialog_live'] = "a=updateform&sa=livemigrate";
		}

	}

	if ($login->isAdmin()) {
		$alist['__v_dialog_tem'] = "a=updateform&sa=createtemplate";
		$alist['__v_dialog_clone'] = "a=updateform&sa=clonevps";
	}

	if (!$this->isWindows()) {
		$alist['__title_resource'] = $login->getKeywordUc('resource');
	}

	if (!$this->isLogin() && $login->priv->isOn('ip_manage_flag')) {
		$alist[] = "a=list&c=vmipaddress_a";
	}

	if (!$this->isWindows()) {
		$alist['__v_dialog_root'] = "a=updateform&sa=rootpassword";
		$alist[] = "a=show&l[class]=ffile&l[nname]=/";
		$alist['__v_dialog_time'] = "a=updateform&sa=timezone";
		if ($this->isOn('kloxo_flag') && $login->priv->isOn('rebuildvps_flag')) {
			$alist['__v_dialog_inst'] = "a=updateform&sa=installkloxo";
		}
	}




	$alist['__title_power'] = $login->getKeywordUc('power');


	$alist[] = "a=update&sa=boot";
	$alist[] = "a=update&sa=poweroff";
	$alist[] = "a=update&sa=reboot";
	if ($this->isXen()) {
		$alist[] = "a=updateform&sa=mount";
	}

	if ($this->isXen()) {
		$alist[] = "a=updateform&sa=hardpoweroff";
	}

	$alist[] = "a=list&c=emailalert";

	if (!$this->isXen()) {
		$alist[] = "a=list&c=openvzqos";
	}
	//$this->getLxclientActions($alist);

	/*
	if ($this->priv->isOn('backup_flag')) {
		$alist[] = "a=show&o=lxbackup";
}
	*/

	$alist['__title_misc'] = "Extra";
	$this->getListActions($alist, 'vpstraffichistory');
	$this->getListActions($alist, 'utmp');
	$this->getTicketMessageUrl($alist);

	if ($hackbutton && $hackbutton->isOn('nomonitor')) {
	} else {
		if (is_unlimited($this->priv->monitorport_num) || $this->priv->monitorport_num) {
			$alist[] = "a=list&c=monitorserver";
		}
	}

	if ($this->priv->isOn('managereversedns_flag')) {
		if ($revc && $revc->isOn('enableflag')) {
			$alist[] = "a=list&c=reversedns";
		}
	}

	if ($this->priv->isOn('managedns_flag')) {
		if ($revc && $revc->isOn('forwardenableflag')) {
			$alist[] = "a=list&c=dns";
		}
	}


	$alist['__title_advanced'] = "Advanced";
	if (!$this->isLogin()) {
		$alist[] = "a=updateform&sa=disable_per";
	}


	if ($login->isAdmin() && !$this->isXen()) {
		$alist[] = "a=updateForm&sa=changenname";
	}

	if (!$this->isLogin()) {
		$alist[] = "a=updateform&sa=resendwelcome";
	}

	if ($login->isAdmin()) {
		$alist[] = "a=updateForm&sa=changelocation";
		$alist[] = "a=updateform&sa=createuser";
	}

	if ($this->isXen() && !$this->isLogin()) {
		$alist[] = "a=updateform&sa=append_xen_config";
	}

	$alist[] = "a=updateform&sa=append_inittab";
	if ($login->isAdmin()) {
		$alist[] = "a=updateform&sa=ostemplatelist";
	}

	//if ($this->isXen() && $this->ostemplate === 'unknown') {
	if ($this->isXen() && $login->isAdmin()) {
		$alist[] = "a=updateForm&sa=changeosimagename";
	}

	if (!$this->isLogin()) {
		if ($login->isNotCustomer()) {
			// Disabling change owner for the present.
			$alist[] = "a=updateForm&sa=changeowner";
		}
	}

	if ($this->isXen()) {
		$alist[] = "a=updateform&sa=macaddress";
	}

	$alist[] = "a=show&o=notification";
	$alist[] = "a=updateform&sa=miscinfo";
	if ($this->isLogin()) {
		$alist[] = "o=sp_specialplay&a=updateform&sa=login_options";
	}


	$this->getCustomButton($alist);
	return $alist;



}

		
static function get_full_alist()
{
	global $gbl, $sgbl, $login, $ghtml; 
	$alist['__title_main'] = $login->getKeywordUc('resource');

	//$alist[] = "a=show&o=sshclient";
	$alist[] = "a=show&o=vncviewer";
	$alist[] = "a=show&o=consolessh";

	$alist[] = "a=updateform&sa=commandcenter";
	$alist[] = "a=list&c=blockedip";
	$alist[] = "a=updateform&sa=network";
	$alist[] = "a=updateform&sa=mainipaddress";
	$alist[] = "a=updateForm&sa=information";
	$alist[] = "a=updateform&sa=password";
	$alist[] = "a=list&c=emailalert";

	$alist['__var_backup_flag'] = "a=show&o=lxbackup";

	$alist[] = "a=updateForm&sa=limit";
	$alist[] = "a=updateForm&sa=change_plan";

	$alist['__title_resource'] = $login->getKeywordUc('resource');

	$alist[] = "a=list&c=vmipaddress_a";
	$alist[] = "a=updateform&sa=rebuild";
	$alist[] = "a=updateform&sa=rootpassword";
	$alist[] = "a=show&l[class]=ffile&l[nname]=/";
	$alist[] = "a=updateform&sa=timezone";
	$alist[] = "a=updateform&sa=installkloxo";

	$alist[] = "a=updateform&sa=switchserver";

	$alist[] = "a=updateform&sa=livemigrate";

	$alist[] = "a=updateform&sa=createtemplate";



	$alist[] = "a=update&sa=reboot";
	$alist[] = "a=update&sa=poweroff";

	$alist[] = "a=list&c=openvzqos";
	//$this->getLxclientActions($alist);

	/*
	if ($this->priv->isOn('backup_flag')) {
		$alist[] = "a=show&o=lxbackup";
}
	*/

	$alist['__title_help'] = "Help";
	$alist[] = "a=list&vpstraffichistory";
	$alist[] = "a=utmp";
	$alist[] = "a=list&c=ticket";

	$alist[] = "a=list&c=monitorserver";
	$alist[] = "a=list&c=smessage";

	$alist[] = "a=list&c=reversedns";
	return $alist;
}


function isCoreBackup() { return true; }
function extraBackup() {return true;}



function updateInstallKloxo($param)
{
	global $gbl, $sgbl, $login, $ghtml; 
	$ver = getHIBversion();
	$param['ostemplate'] = 'centos-5-i386-hostinabox' . $ver;
	return $param;
}

function updateboot($param)
{
	$param['value'] = 'something';
	return $param;
}

function updatehardpoweroff($param)
{
	$param['value'] = 'something';
	return $param;
}

function updatereboot($param)
{
	global $gbl, $sgbl, $login, $ghtml; 

	$this->createPublicPrivate();

	$param['value'] = 'something';
	return $param;
}

function createShowPropertyList(&$alist)
{
	global $gbl, $sgbl, $login, $ghtml; 
	$alist['property'][] = "a=show";
	$alist['property'][] = "o=sp_specialplay&a=updateform&sa=skin";
	$alist['property'][] = "a=graph&sa=base";

	if ($this->isOn('kloxo_flag') && !$this->isWindows()) {
		$alist['property'][] = "a=show&o=kloxo";
	}

}

function hasFunctions() { return true; }
function getHardProperty()
{
	global $gbl, $sgbl, $login, $ghtml; 
	
	$master_server = $this->__masterserver;
	$slave_server = $this->syncserver;
	$driverapp = $gbl->getSyncClass('localhost', $slave_server, 'vps');
	
	if ($this->isXen()) {
		// Build the params
		$maindisk   = $this->getXenMaindiskName();
		$is_windows = $this->isWindows();
		$root_path  = $this->corerootdir;
		
		$parameters = array($maindisk, $is_windows, $root_path);
		
		$disk = rl_exec_get($master_server, $slave_server,  array("vps__$driverapp", "getDiskUsage"), $parameters);
		$this->used->disk_usage = $disk['used'];
	} else {
		$l = rl_exec_get($master_server, $slave_server,  array("vps__$driverapp", "vpsInfo"), array($this->getIid(), $this->corerootdir));
		$this->used->disk_usage = $l['used_s_disk'];
		$this->used->disk_inode = $l['used_s_inode'];
		$this->used->memory_usage = $l['used_s_memory'];
	}

	$this->status = $this->getStatusForThis();

	$this->coma_vmipaddress_a = implode(",", get_namelist_from_objectlist($this->vmipaddress_a));
}

function createShowAlistConfig(&$alist, $subaction = null)
{
	global $gbl, $sgbl, $login, $ghtml; 
	$alist['__title_main'] = $login->getKeywordUc('resource');

	if (!$this->isLogin()) {
		$alist[] = "a=updateform&sa=disable_per";
	}


	if ($login->isAdmin() && !$this->isXen()) {
		$alist[] = "a=updateForm&sa=changenname";
	}

	if (!$this->isLogin()) {
		$alist[] = "a=updateform&sa=resendwelcome";
	}

	if ($login->isAdmin()) {
		$alist[] = "a=updateForm&sa=changelocation";
		$alist[] = "a=updateform&sa=createuser";
	}

	if ($this->isXen() && !$this->isLogin()) {
		$alist[] = "a=updateform&sa=append_xen_config";
	}

	$alist[] = "a=updateform&sa=append_inittab";
	if ($login->isAdmin()) {
		$alist[] = "a=updateform&sa=ostemplatelist";
	}

	//if ($this->isXen() && $this->ostemplate === 'unknown') {
	if ($this->isXen()) {
		$alist[] = "a=updateForm&sa=changeosimagename";
	}

	if (!$this->isLogin()) {
		if ($login->isNotCustomer()) {
			// Disabling change owner for the present.
			$alist[] = "a=updateForm&sa=changeowner";
		}
	}

	if ($this->isXen()) {
		$alist[] = "a=updateform&sa=macaddress";
	}

	$alist[] = "a=show&o=notification";
	$alist[] = "a=updateform&sa=miscinfo";
	if ($this->isLogin()) {
		$alist[] = "o=sp_specialplay&a=updateform&sa=login_options";
	}

	return $alist;
}

function coreRecoverVps($mountpoint)
{
	if ($this->isXen()) {
		$path_ost = "__path_program_home/xen/template";
	} else {
		$path_ost = "/vz/template/cache/";
	}

	$ret = lxshell_return("tar", "-C", $mountpoint, "-xzf", "$path_ost/{$this->ostemplate}.tar.gz", "sbin", "lib",  "bin", "usr/sbin", "usr/lib", "usr/bin");

	if ($ret) {
		lxshell_return("tar", "-C", $mountpoint, "-xzf", "$path_ost/{$this->ostemplate}.tar.gz", "./sbin", "./lib",  "./bin", "./usr/sbin", "./usr/lib", "./usr/bin");
	}

}


function createShowInfoList($subaction)
{
	global $gbl, $sgbl, $login, $ghtml; 

	$gen = $login->getObject('general')->generalmisc_b;
	if ($subaction) {
		return;
	}

	if (!$this->isXen()) {
		$ilist['vpsid'] = $this->vpsid;
	} else {
		$ilist['mac'] = $this->macaddress;
	}
	$ilist['Location'] = $this->corerootdir;
	$ilist['ostemplate'] = $this->ostemplate;
	$r = explode("___", $this->resourceplan_used);
	if (!$this->isLogin()) {
		$ilist['Resource Plan'] = "_lxinurl:a=updateform&sa=change_plan:{$r[0]}:";
	}

	$server = $this->syncserver;
	$sshport = db_get_value("sshconfig", $server, "ssh_port");
	if (!$sshport) { $sshport = "22"; }

	if (!$gen->isOn('no_console_user')) {
		if (!$this->isWindows()) {
			$ilist['console'] = implode(" @ ", $this->getLogin() ) . ":$sshport";
		} else {
			$ilist['console'] = implode(" : ", $this->getLogin());
		}
	}

	$name_list = get_namelist_from_objectlist($this->vmipaddress_a);
	
	if(!empty($name_list)) {
		$ilist['IP'] = substr(implode(",", $name_list), 0, 17);
	}
	else {
		$ilist['IP'] = NULL;
	}
	
	$this->getLastLogin($ilist);
	return $ilist;
}


function getResourceChildList()
{
	global $gbl, $sgbl, $login, $ghtml; 
	$list = $this->getChildListFilter('R');
	return $list;
}


function createShowClist($subaction)
{
	return null;
}


static function createListBlist($parent, $class)
{
	global $gbl, $sgbl, $login, $ghtml; 
	if ($login->isAuxiliary()) {
		if ($login->__auxiliary_object->isOn('delete_flag')) {
			$blist[] = array("a=delete&c=$class");
		}
	} else {
		$blist[] = array("a=delete&c=$class");
	}
	$blist[] = array("a=update&sa=vpssendmessage");
	return $blist;
}

function createExtraVariables()
{
	global $gbl, $sgbl, $login, $ghtml; 
	$gen = $login->getObject('general');
	$kloxo = $gen->kloxoconfig_b;
	$gen = $gen->generalmisc_b;


	if ($gen->xeninitrd_flag === 'off') {
		$this->__var_xeninitrd_flag = 'off';
	} else {
		$this->__var_xeninitrd_flag = 'on';
	}

	if ($this->dbaction === 'add' || ($this->subaction === 'reboot' || $this->subaction === 'boot' || $this->subaction === 'rebuild')) {
		if ($this->isOn('rootpassword_changed')) {
			$this->rootpassword_changed = 'off';
			$this->__var_rootpassword_changed = 'on';
		}
	}


	$this->__var_kloxo_remote_i_flag = $kloxo->remoteinstall_flag;
	$this->__var_kloxo_iapp_url = $kloxo->installapp_url;

	$this->__var_tmp_base_dir = db_get_value("pserver", $this->syncserver, "tmpdir");

	if (!$this->__var_tmp_base_dir) { $this->__var_tmp_base_dir = "/tmp"; }

	$sq = new Sqlite(null, 'vps');

	$winip = null;
	if ($this->isWindows()) {
		$res = $sq->getRowsWhere("syncserver = '{$this->syncserver}' AND ostemplate LIKE 'windows%'", array('nname', 'hostname', 'coma_vmipaddress_a', 'macaddress', "networkgateway", "networknetmask", "nameserver"));
		if ($res) foreach($res as $r) {
			foreach($r as $k => $v) {
				if ($k === 'coma_vmipaddress_a') {
					$value['iplist'] = explode(",", $r['coma_vmipaddress_a']);
				} else {
					$value[$k] = $r[$k];
				}

			}
			$winip[] = $value;
		}
	}

	$this->__var_win_iplist = $winip;




	if ($this->isXen()) {
		return;
	}

	$res = $sq->getRowsWhere("syncserver = '{$this->syncserver}' AND priv_q_uplink_usage != 'Unlimited'", array('nname', 'vpsid', 'coma_vmipaddress_a', 'priv_q_uplink_usage'));

	$return = null;
	if ($res) foreach($res as $r) {
		$value['vpsid'] = $r['vpsid'];
		$value['nname'] = $r['nname'];

		if ($r['coma_vmipaddress_a']) {
			$value['ipaddress'] = explode(",", $r['coma_vmipaddress_a']);
		} else {
			$value['ipaddress'] = null;
		}

		$value['uplink_usage'] = $r['priv_q_uplink_usage'];
		$return[] = $value;
	}

	$this->__var_uplink_list = $return;
	
}

function backupExtraVar(&$vlist)
{
	if ($this->isXenLvm() || !$this->isXen()) {
		$vlist['backupextra_stopvpsflag'] = null;
	}
}

function getXenMaindiskName()
{
	if ($this->isXenLvm()) {
		$vgname = $this->corerootdir;
		$vgname = fix_vgname($vgname);
		$maindisk = "/dev/$vgname/{$this->maindiskname}";
	} else {
		$maindisk = "{$this->corerootdir}/{$this->nname}/{$this->maindiskname}";
	}

	return $maindisk;
}

function createShowRlist($subaction)
{

	//$l = $this->pserverInfo();
	global $gbl, $sgbl, $login, $ghtml; 
	static $rlist;


	//print("hello\n");
	if ($subaction) {
		return null;
	}

	if ($rlist) {
		return $rlist;
	}

	$master_server = $this->__masterserver;
	$slave_server  = $this->syncserver;

	$driverapp = $gbl->getSyncClass($master_server, $slave_server, 'vps');

	if ($this->isXen()) {
		if (if_demo()) {
			$disk['used'] = '300';
			$disk['total'] = '6000';
		}  else {
			// Build the params
			$maindisk   = $this->getXenMaindiskName();
			$is_windows = $this->isWindows();
			$root_path  = $this->corerootdir;
			
			$parameters = array($maindisk, $is_windows, $root_path);
			
			$disk = rl_exec_get($master_server, $slave_server,  array("vps__$driverapp", "getDiskUsage"), $parameters);
		}
		if (!$this->priv->disk_usage) {
			$this->priv->disk_usage = $disk['total'];
			$this->setUpdateSubaction();
			$this->write();
		}
		$rlist[] = array('disk_usage', "Disk:Disk Usage", $disk['used'], $disk['total']);
		$rlist[] = array('realmem_usage', "Mem:Memory Usage", 'NA', $this->priv->realmem_usage);
	} else {

		$l = rl_exec_get($this->__masterserver, $this->syncserver,  array("vps__$driverapp", "vpsInfo"), array($this->getIid(), $this->corerootdir));
		$rlist[] = array('disk_usage', "Disk:Disk Usage", $l['used_s_disk'], $l['priv_s_disk']);
		$rlist[] = array('disk_inode', "Inode:Disk Inodes", $l['used_s_inode'], $l['priv_s_inode']);

		/// Rlist takes an array... 
		$rlist[] = array('memory_usage', "Memory:Memory Usage (MB)",  $l['used_s_memory'], $l['priv_s_memory']);

		if (isset($l['used_s_swap'])) {
			$rlist[] = array('swap_usage', "Swap:Swap Usage (MB)",  $l['used_s_swap'], $l['priv_s_swap']);
		} 

		if (isset($l['used_s_virtual'])) {
			$rlist[] = array('Virtual Memory', "Virtual Memory Usage (MB)",  $l['used_s_virtual'], $l['priv_s_virtual']);
		}
	}

	$rlist['priv'] = null;

	if (!$this->isXen()) {
		$cpu = $l['cpu'];
		foreach($cpu as $k => $c) {
			$rlist[] = array('cpu', "CPU$k speed",  "{$c['used_s_cpuspeed']}", '-');
			//$rlist[] = array('cpu', "CPU$k Speed", $c['used_s_cpuspeed'], '-');
			//$rlist[] = array('cpu', "CPU$k Cache", $c['used_s_cpucache'], '-');
		}
	}

	return $rlist;

}


function isDomainVirtual()
{
	return ($this->ttype === 'virtual');
}

static function initThisListRule($parent, $class)
{
	if ($parent->get__table() === 'pserver') {
		$res[] = array('syncserver', '=', "'{$parent->nname}'");
	} else {
		$res[] = array('parent_clname', '=', "'{$parent->getClName()}'");
	}
	return $res;
}


}

class all_vps extends vps {
static $__desc = array("", "",  "all_VM");
static function createListBlist($parent, $class) { return null; }
static function initThisListRule($parent, $class)
{
	if (!$parent->isAdmin()) {
		throw new lxexception("only_admin_can_access", '', "");
	}

	return "__v_table";
}

}


