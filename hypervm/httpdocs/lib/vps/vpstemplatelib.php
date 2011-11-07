<?php 

class vpstemplate extends vpsBase {


//Core
static $__desc = array("", "",  "VM_plan");

//Data
static $__desc_nname =  array("n", "",  "plan_name", URL_SHOW);
static $__desc_description = array("", "",  "description");
static $__desc_share_status = array("ef", "",  "share:share_this_plan_with_your_children");
static $__desc_share_status_v_on = array("", "",  "template_is_shared");
static $__desc_share_status_v_off = array("", "",  "template_is_not_shared");
static $__desc_dnspserver = array("s", "",  "dns_server");
static $__desc_ipaddress = array("s", "",  "ip_address");
static $__desc_secdnspserver = array("s", "",  "secondary_dns_server");
static $__desc_mmailpserver = array("s", "",  "mail_server");
static $__desc_webpserver = array("s", "",  "web_server");
static $__desc_dnstemplate = array("s", "",  "dns_template");

//Objects


static $__desc_process_usage	 = array("qh", "",  "number_of_processes");
static $__desc_guarmem_usage	 = array("qh", "",  "Guaranteed_Memory_(MB)");
static $__desc_swap_usage	 = array("qh", "",  "swap:swap_(MB)");
static $__desc_disk_usage	 = array("qh", "",  "disk_quota_(MB)");
static $__desc_memory_usage	 = array("qh", "",  "burst_mem:burstable_memory_(MB)(openvz_only)");
static $__desc_realmem_usage	 = array("q", "",  "realmem:real_memory_usage_(MB)(xen_only)");
static $__desc_cpu_usage	 = array("qh", "",  "cpu_usage_(%)_Max = 100/CPU");
static $__desc_backup_num = array("q", "",  "number_of_backups");
static $__desc_uplink_usage = array("qh", "",  "uplink_traffic(KB/s)");
static $__desc_backup_flag =  array("q", "",  "allow_backing_up");
static $__desc_backupschedule_flag =  array("q", "",  "allow_backup_schedule");
//static $__desc_monitorserver_num =   array("q","",  "number_of_monitored_servers");
//static $__desc_monitorport_num =   array("q","",  "number_of_monitored_ports");

//Lists
static $__desc_dnstemplate_o = array("", "",  "");

static $__acdesc_update_pserver = array("", "",  "servers");
static $__acdesc_update_ipaddress = array("", "",  "ipaddress");
static $__acdesc_update_vpspserver_s = array("", "",  "set_node");
static $__acdesc_update_ostemplate = array("", "",  "set_Osimage");

function display($var)
{

	if ($var === "status_client") {
		return $this->status;
	}

	if ($var === 'owner_f') {
		if ($this->isRightParent()) {
			return 'on';
		} else {
			return 'off';
		}
	}



	if ($var === "pvview") {
		return "";
	}

	return parent::display($var);

}


static function add($parent, $class, $param)
{
	Client::fixpserver_list($param);
	return $param;
}

// This is to override the continueformfinish in the domainbaselib. The continueformlistpriv will call finish, in domain it will call the complex finish, and in templates it will call the simple one.
static function continueFormFinish($parent, $class, $param, $continueaction)
{

	global $gbl, $sgbl, $login, $ghtml; 
	$driverapp = $gbl->getSyncClass('localhost', $param['listpriv_s_vpspserver_sing'], 'vps');
	$ostlist = rl_exec_get(null, $param['listpriv_s_vpspserver_sing'], array("vps__$driverapp", "getOsTemplatelist"));
	$ostlist = lx_merge_good(array('--defer-osimage--' => '--defer-osimage--'), $ostlist);
	$vlist['ostemplate'] = array('A', $ostlist);


	$ret['variable'] = $vlist;
	$ret['action'] = 'add';
	$ret['param'] = $param;
	return $ret;
}

static function continueForm($parent, $class, $param, $continueaction)
{

	$param['nname'] = trim($param['nname']);
	if ($continueaction === 'server') {
		$ret = self::continueFormlistpriv($parent, $class, $param, $continueaction);
	} else {
		$ret = self::continueFormFinish($parent, $class,  $param, $continueaction);
	}

	return $ret;

}

static function addform($parent, $class, $typetd = null)
{

	$vlist['nname'] = null;
	$vlist['description'] = null;
	//$vlist['share_status'] = null;
	$qvlist = getQuotaListForClass('vps', array());
	$vlist = lx_merge_good($vlist, $qvlist);
	$ret['action'] = "add";
	//$ret['continueaction'] = "server";
	$ret['variable'] = $vlist;

	return $ret;
}

function isSync()
{
	return false;
}

function isSelect()
{
	if ($this->nname === '__default__') {
		return false;
	}
	return true;

}

static function createListAlist($parent, $class)
{
	/*
	if ($parent->isLogin() && !$parent->priv->isOn('domain_add_flag')) {
		return null;
	}
*/

	return parent::createListAlist($parent, $class);


}


function createShowImageList()
{
	$vlist = ClientTemplate::createShowImageList();
	return $vlist;

}
function createShowUpdateform()
{

	$uflist = null;
	$uflist['limit'] = null;
	return $uflist;
}

function createShowRlist($subaction)
{
	return null;
}

function createShowPlist($subaction)
{
	return null;
}

function createShowPropertyList(&$alist)
{
	$alist['property'][] = 'a=show';
	$alist['property'][] = "a=updateForm&sa=description";
	//$alist['property'][] = "a=updateform&sa=ostemplate";
	//$alist['property'][] = "a=updateform&sa=vpspserver_s";
	
}


function createShowAlist(&$alist, $subaction = null)
{
	global $gbl, $sgbl, $login, $ghtml; 
	//$alist['property'][] = "a=updateForm&sa=limit";
	//$alist[] = "a=updateForm&sa=pserver";
	//$alist[] = "a=updateForm&sa=limit";

	return $alist;


}

function update($subaction, $param)
{
	if ($this->getparentO()->getClName() != $this->parent_clname) {
		throw new lxexception('template_not_owner', 'parent');
	}
	return $param;
}

static function createListNlist($parent, $view)
{
	//$nlist['owner_f'] = '3%';
	//$nlist['share_status'] = '3%';
	$nlist['nname'] = '30%';
	$nlist['description'] = '100%';
	return $nlist;
}


/*
static function initThisList($parent, $class)
{

	$db = new Sqlite($parent->__masterserver, "domaintemplate");
	$result = $db->getRows('parent_name', $parent->nname);
	//$newresult = $db->getRows('nname', "__default__");
	//$result = lx_array_merge(array($result, $newresult));

	$parent->setListFromArray($parent->__masterserver, $parent->__readserver, 'domaintemplate', $result);
	if ($parent->isAdmin()) {
		return null;
	}
	$pparent = $parent->getparentO();

	if ($pparent) {
		$list = $pparent->getList('domaintemplate');

		$list = filter_object_list($list, '$this->isOn("share_status")');

		if (!$parent->domaintemplate_l) {
			$parent->domaintemplate_l = array();
		}
		if (!$list) {
			$list = array();
		}
		$new = $parent->domaintemplate_l +  $list;
	}

	return null;


}
*/




}



