<?php 

class Client extends ClientBase {

static $__desc_vps_num = array("q", "", "VMs:number_of_VMs");
static $__desc___v_priv_used_vps_num    = array("S","",  "VMs"); 

static $__desc_vps_l = array("qLdRt", "", "");
static $__desc_all_vps_l = array("", "", "");
static $__desc_vpstemplate_l = array("d", "", "");
static $__desc_resourceplan_l = array("db", "",  "");

static $__desc_vps_count_f = array("n", "", "VM_count");
static $__desc_vps_template_name_f = array("n", "", "VM_template");
static $__desc_vps_basename_f = array("n", "", "basename");
static $__desc_vps_admin_password_f = array("n", "", "admin_password");
static $__acdesc_update_multivpscreate = array("n", "", "create_multiple_VMs");
static $__acdesc_update_ippool = array("", "", "recaliberate_ippool");
static $__acdesc_update_xentemplate = array("", "", "xen_ostemplate_manager");
static $__acdesc_update_openvztemplate = array("", "", "openvz_ostemplate_manager");

static $__desc_monitorserver_l = array("qdtbB", "",  "");
static $__desc_centralbackupserver_l = array("", "",  "");
static $__desc_emailalert_l = array("", "", "", "");
static $__desc_reversedns_l = array("d", "", "", "");
static $__desc_datacenter_l = array("", "", "", "");
static $__desc_sslcert_l = array("", "", "", "");
static $__desc_all_dns_l = array("", "", "", "");
static $__desc_xenostemplate_o = array("", "", "", "");
static $__desc_openvzostemplate_o = array("", "", "", "");
static $__desc_ippool_l = array("db", "", "", "");
static $__desc_auxiliary_l = array("db", "", "", "");

function updateMultiVpsCreate($param)
{
	global $gbl, $sgbl, $login, $ghtml; 

	if (!check_password($param['vps_admin_password_f'], $this->password)) {
		throw new lxException ("wrong_password", 'vps_admin_password_f');
	}

	$res = rl_exec_get(null, 'localhost',  'createMultipLeVps', array($param));
	$url = $ghtml->getFullUrl('a=list&c=vps');
	$gbl->__this_redirect = $url . "&frm_smessage=vps_creation_in_background";
	return $param;
}


function updateIPpool($param)
{
	lxshell_php("../bin/fix/fixippool.php");
}

static function createListNlist($parent, $view)
{
	global $gbl, $sgbl, $login, $ghtml; 

	$name_list["cpstatus"] = "3%";
	$name_list["status"] = "3%";
	$name_list["state"] = "3%";

	$name_list["cttype"] = "3%";
	$name_list["nname"] = "100%";


	$name_list["ddate"] = "20%";

	if ($parent->isLte('wholesale')) {
		$name_list["__v_priv_used_client_num"] = "3%";
	}

	$name_list["__v_priv_used_vps_num"] = "10%";
	$name_list["traffic_usage"] = "5%";
	$name_list["disk_usage"] = "5%";
	$name_list["abutton_updateform_s_information"] = "5%";
	$name_list["abutton_updateform_s_password"] = "5%";
	$name_list["abutton_list_s_ticket"] = "5%";
	$name_list["abutton_list_s_utmp"] = "5%";
	$name_list["abutton_updateform_s_limit"] = "5%";
	return $name_list;
}

function getQuickClass()
{
	return 'vps';
}

static function addform($parent, $class, $typetd = null)
{
	return parent::addform($parent, $class, $typetd);
	$vlist[''] = array('M', 'disabled. Use_vps_as_login');
	$ret['variable'] = $vlist;
	//$ret['action'] = 'add';
	return $ret;
}

function getVpsServers($type)
{
	global $gbl, $sgbl, $login, $ghtml; 
	$list = $login->getServerList('vps');
	$outlist = null;
	foreach($list as $l) {
		$driverapp = $gbl->getSyncClass(null, $l, 'vps');
		if ($driverapp === $type) {
			$outlist[] = $l;
		}
	}
	return $outlist;
}


function createShowMainImageList()
{
	$vlist['status'] = null;
	$vlist['cttype'] = 1;
	return $vlist;
}

function createShowRlist($subaction)
{
	if ($this->isCustomer() && !$this->priv->isOn('vps_limit_flag')) {
		//return null;
	}

	return parent::createShowRlist($subaction);
}

function createShowPlist($subaction)
{
	if ($this->isCustomer() && !$this->priv->isOn('vps_limit_flag')) {
		return null;
	}

	return parent::createShowPlist($subaction);
}


function createShowPropertyList(&$alist)
{
	global $gbl, $sgbl, $login, $ghtml; 

	if ($ghtml->frm_subaction === 'forcedeletepserver') {
		$alist['property'] = pserver::createListAlist($this, 'pserver');
		return;
	}


	if ($ghtml->frm_subaction === 'deleteorphanedvps') {
		$alist['property'] = vps::createListAlist($this, 'pserver');
		return;
	}


	if ($ghtml->frm_subaction === 'ippool') {
		$alist['property'] = ippool::createListAlist($this, 'ippool');
	} else {
		$alist['property'][] = "a=show";
		$alist['property'][] = "o=sp_specialplay&a=updateform&sa=skin";
	}
}

function hasFunctions() { return true; }
function createShowActionList(&$alist) 
{
	$this->getToggleUrl($alist);
	$this->getCPToggleUrl($alist);
}

function getAnyErrorMessage()
{
	global $gbl, $sgbl, $login, $ghtml; 
	if (!$this->isAdmin()) { return; }
	if ($this->isOff('smtp_server_flag')) {
		$ghtml->__http_vars['frm_emessage'] = "smtp_server_not_running";
	}
	parent::getAnyErrorMessage();
}




function createShowAlist(&$alist, $subaction = null)
{

	global $gbl, $sgbl, $login, $ghtml; 




	$alist['__title_administer'] = $login->getKeywordUc('administration');


	// Very special case, when he is a customer without any add vps flag, then there are no top row buttons at all.


	if ($this->isAdmin()) {
		$alist[] = "a=list&c=actionlog";
	}
	if ($this->isLogin() && $login->isAuxiliary()) {
		$alist['__v_dialog_pas'] = "o=auxiliary&a=updateform&sa=password";
	} else {
		$alist['__v_dialog_apas'] = "a=updateform&sa=password";
	}

	$alist['__v_dialog_info'] = "a=updateForm&sa=information";
	if ($this->isAdmin()) {
		$alist[] = 'o=lxupdate&a=show';

	}


	if (!$this->isAuxiliary()) {
		$alist[] = "a=list&c=blockedip";
	}
	if ($this->isAdmin()) {
		$alist[] = "a=list&c=auxiliary";
	}

	if ($this->isAdmin()) {
		$alist[] = "a=list&c=custombutton";
	}



	if ($this->isAdmin()) {
		//$turl = "a=show&l[class]=ffile&l[nname]=xentemplate";
		$turl = "n=xenostemplate&a=show&l[class]=ffile&l[nname]=/";
		$alist[] = create_simpleObject(array('url' => "$turl", 'purl' => "a=updateform&sa=xentemplate", 'target' => "", '__internal' => true));
		$turl = "n=openvzostemplate&a=show&l[class]=ffile&l[nname]=/";
		$alist[] = create_simpleObject(array('url' => "$turl", 'purl' => "a=updateform&sa=openvztemplate", 'target' => "", '__internal' => true));

	}



	if (!$this->isLogin()) {
		$alist[] = "a=update&sa=dologin";
	}


	$alist[] = "a=list&c=emailalert";
	if ($this->canSeePserver()) {
		$alist[] = "a=list&c=pserver";
	}

	if (!$this->isAdmin()) {
		$alist[] = "a=updateForm&sa=pserver";
	}

	if ($this->isAdmin() && !$this->isAuxiliary()) {
		$alist[] = "a=list&c=datacenter";
		$alist[] = "a=list&c=centralbackupserver";
		$alist[] = "a=list&c=sslcert";
	}



	if (!$this->isLogin()) {
		$alist['__v_dialog_limit'] = "a=updateForm&sa=limit";
		$alist['__v_dialog_plan'] = "a=updateForm&sa=change_plan";
	}

	$alist['__title_virtual'] = $login->getKeywordUc('resource');

	if ($this->isAdmin()) {
		$alist[] = "a=list&c=ippool";
	}
	if ($this->priv->isOn('vps_add_flag')) {
		$this->getListActions($alist, 'resourceplan'); 
		//$this->getListActions($alist, 'clienttemplate'); 
		//$this->getListActions($alist, 'vpstemplate'); 
	}
	$alist[] = "a=list&c=vps";
	$alist[] = "a=list&c=all_vps";

	if (!$this->isCustomer()) {
		$this->getListActions($alist, 'client'); 
	}


	if ($this->isAdmin()) {
		//$alist[] = "a=updateform&sa=multivpscreate";
		$revc = $login->getObject('general')->reversedns_b;
		if ($revc && $revc->isOn('enableflag')) {
			$alist[] = "a=list&c=reversedns";
		}
		$alist[] = "a=list&c=all_dns";
	}


				     


	if (!$this->isLogin()) {
		//Both wall and message not done through message board.
		//$alist[] = 'a=updateForm&sa=message';
	}

	// Client Traffic history. Doesn't know if I should add the history of HIS clients too, or just use the traffic for the domains under him. So hashing for the present.
	//$alist[] = 'a=list&c=domaintraffichistory';

	$alist['__title_misc'] = $login->getKeywordUc('help');

	$this->getListActions($alist, 'utmp'); 

	$this->getTicketMessageUrl($alist);





	//$this->getLxclientActions($alist);

	if (!$this->isLogin() && !$this->isLteAdmin() && csb($this->nname, "demo_")) {
		$alist[] = "o=sp_specialplay&a=updateform&sa=demo_status";
	}

	if ($this->isAdmin()) {
		//$alist[] = "a=list&c=blockedip";
		//$alist[] = "o=general&a=updateForm&sa=attempts";
		//$alist[] = "a=list&c=module";

	} else {
	}

	if ($this->isAdmin()) {
		$alist[] = "a=list&c=monitorserver";
	} else {
		if (is_unlimited($this->priv->monitorport_num) || $this->priv->monitorport_num > 0) {
			$alist[] = "a=list&c=monitorserver";
		}
	}

	if ($this->isAdmin()) {
		$so = $this->getFromList('pserver', 'localhost');
		$this->getAlistFromChild($so, $alist);
	}


	$alist['__title_advanced'] = $login->getKeywordUc('advanced');

	if ($this->isAdmin()) {
		//$alist['__v_dialog_tick'] = "a=updateform&sa=ticketconfig&o=ticketconfig";
		//$alist[] = "o=general&c=helpdeskcategory_a&a=list";
		$alist['__v_dialog_sca'] = "o=general&a=updateform&sa=scavengetime";
		$alist['__v_dialog_gen'] = "o=general&a=updateform&sa=generalsetting";
		$alist['__v_dialog_main'] = "o=general&a=updateform&sa=maintenance";
		$alist['__v_dialog_self'] = "o=general&a=updateform&sa=selfbackupconfig";
		//$alist['__v_dialog_ssh'] = "o=general&a=updateform&sa=ssh_config";
		//$alist['__v_dialog_ipcheck'] = "o=general&a=updateform&sa=session_config";
		$alist['__v_dialog_download'] = "o=general&a=updateform&sa=download_config";
		$alist['__v_dialog_forc'] = "a=updateform&sa=forcedeletepserver";

		$alist['__v_dialog_hack'] = "o=general&a=updateform&sa=hackbuttonconfig";
		$alist['__v_dialog_rev'] = "o=general&a=updateform&sa=reversedns";
		$alist['__v_dialog_cust'] = "o=general&a=updateform&sa=customaction";
		$alist['__v_dialog_orph'] = "a=updateform&sa=deleteorphanedvps";
		$alist['__v_dialog_lxc'] = "o=general&a=updateform&sa=kloxo_config";
		//$alist[] = "a=show&o=ostemplatelist";
		$alist[] = "a=list&c=customaction";


	}

	if (!$this->isAdmin()) {
		$alist[] = "a=updateform&sa=ostemplatelist";
	}


	if ($this->canHaveChild()) {
		$alist['__v_dialog_ch'] = "o=sp_childspecialplay&a=updateform&sa=skin";
	}


	$alist['__v_dialog_not'] = "a=updateform&sa=update&o=notification";
	$alist['__v_dialog_misc'] = "a=updateform&sa=miscinfo";
	if ($this->isAdmin()) {
		$alist[] = "o=general&a=updateform&sa=portconfig";
	}

	if (!$this->isLogin() && !$this->isLteAdmin() && csb($this->nname, "demo_")) {
		$alist['__v_dialog_demo'] = "o=sp_specialplay&a=updateform&sa=demo_status";
	}


	if (!$this->isLogin()) {
		$alist['__v_dialog_disa'] = "a=updateform&sa=disable_per";
	}

	if ($login->priv->isOn('logo_manage_flag') && $this->isLogin()) {
		$alist['__v_dialog_uplo'] = "o=sp_specialplay&a=updateForm&sa=upload_logo";
	}

	if (!$this->isLogin()) {
		$alist['__v_dialog_resend'] = "a=updateform&sa=resendwelcome";
	}

	if (!$this->isLogin()) {
		$alist[] = "a=updateForm&sa=changeowner";
	}
	if ($this->isLogin()) {
		$alist['__v_dialog_login'] = "o=sp_specialplay&a=updateform&sa=login_options";
	}

	if ($this->isAdmin()) {
		$alist[] = "a=updateform&sa=license&o=license";
	}


	$this->getCustomButton($alist);

	return $alist;

}



static function createListSlist($parent)
{
	global $gbl, $sgbl, $login, $ghtml; 
	$nlist['nname'] = null;
	$nlist['contactemail'] = null;

	$nlist['resourceplan_used'] = null;
	$nlist['status'] = array('s', array('--any--', 'on', 'off'));
	$nlist['cttype'] = array('s', array('--any--', 'reseller', 'customer'));


	return $nlist;
}

function createShowInfoList($subaction)
{
	global $gbl, $sgbl, $login, $ghtml; 
	if ($subaction) {
		return;
	}
	$this->getLastLogin($ilist);
	return $ilist;
}


static function get_full_alist()
{

	global $gbl, $sgbl, $login, $ghtml; 
	$alist[] = "o=auxiliary&a=updateform&sa=password";
	$alist[] = "a=updateform&sa=password";

	$alist[] = "a=updateForm&sa=information";
	$alist[] = 'k[class]=pserver&k[nname]=localhost&o=lxupdate&a=show';


	$alist[] = "a=list&c=blockedip";
	$alist[] = "a=list&c=auxiliary";


	$alist[] = "a=list&c=emailalert";
	$alist[] = "a=list&c=pserver";

	$alist[] = "a=updateForm&sa=pserver";

	$alist[] = "a=list&c=datacenter";



	$alist[] = "a=updateForm&sa=limit";
	$alist[] = "a=updateForm&sa=change_plan";

	$alist['__title_virtual'] = $login->getKeywordUc('resource');

	$alist[] = "a=list&c=ippool";
	$alist[] = "a=list&c=resourceplan";
	$alist[] = "a=list&c=vps";
	$alist[] = "a=list&c=all_vps";

	$alist[] = "a=list&c=client";



	$alist['__title_misc'] = $login->getKeywordUc('help');

	$alist[] = "a=list&c=utmp";

	$alist[] = "a=list&c=ticket";

	$alist[] = "a=list&c=monitorserver";
	$alist[] = "a=list&c=smessage";
	$alist[] = "a=list&c=monitorserver";

	return $alist;
}




function getMultiUpload($var)
{
	if ($var === 'pserver') {
		return array("pserver_s");
	}
	/*
	if ($var === 'limit') {
		//return array('limit_s', 'change_plan');

	}
*/
	return $var;
}

static function getPserverListPriv()
{
	$array = array('vpspserver');
	return $array;
}

static function continueFormClientFinish($parent, $class, $param, $continueaction)
{

	$ret['action'] = 'add';
	$ret['variable'] = $vlist;
	$ret['param'] = $param;
	return $ret;
}


}
