<?php 

abstract class VpsCore extends Lxclient {

static $__desc_traffic_last_usage	 = array("D", "",  "LTraffic:traffic_usage_for_last_month_(mb)");
//static $__desc_validity_time	 = array("q", "",  "validity_period");
static $__desc_parent_name_change = array("", "",  "owner");

static $__desc_ostemplate = array("", "", "OST:ostemplate");

static $__acdesc_update_vpspserver = array("", "", "vps_server_pool");

static $__desc_traffic_usage	 = array("q", "",  "traffic:traffic_(MB/month)");

static $__desc_ddate	 = array("", "",  "date");
static $__desc_status  = array("e", "",  "s:status", URL_TOGGLE_STATUS);
static $__desc_status_v_on  = array("", "",  "enabled"); 
static $__desc_status_v_off  = array("", "",  "disabled"); 
static $__desc_disable_reason  = array("", "",  "", 'a=updateForm&sa=limit'); 
static $__desc_vpsid  = array("", "",  "Vpsid");
//static $__desc_dbtype_list =  array("Q", "",  "database_types");
static $__desc_vpspserver_sing =  array("Q", "",  "vps_server");
static $__desc_vmachinepserver_sing =  array("Q", "",  "Virtual Machine server");
static $__desc_ipaddress_sing =  array("Q", "",  "ip_address");
static $__desc_networkgateway =  array("", "",  "gateway (IP)");
static $__desc_nameserver	 = array("", "",  "resolv_entries_(space_separated)");


static $__desc_corerootdir = array("", "",  "Location");
static $__desc_rootpassword = array("", "",  "root_password");
static $__desc_kloxo_type = array("", "",  "kloxo_install_type");
static $__desc_newostemplate_name_f = array("n", "",  "new_osimage_name");
static $__desc_rebuild_confirm_f = array("f", "",  "confirm_rebuild");
static $__desc_reboot_confirm_f = array("f", "",  "confirm_reboot");
static $__desc_poweroff_confirm_f = array("f", "",  "confirm_poweroff");
static $__desc_recover_confirm_f = array("f", "",  "confirm_recover");


static $__acdesc_update_changeowner = array("", "",  "change_owner");
static $__acdesc_update_pserver_s = array("", "",  "server_info");
static $__acdesc_update_description = array("", "",  "information");
static $__acdesc_update_rebuild = array("", "",  "rebuild");
static $__acdesc_update_recovervps = array("", "",  "recover_corrupted_vps");
static $__acdesc_update_createtemplate = array("", "",  "create_ostemplate");


static $__desc_state  = array("e", "",  "st", 'a=updateForm&sa=limit');
static $__desc_state_v_ok  = array("", "",  "alright");
static $__desc_state_v_exceed  = array("", "",  "exceeded");
static $__desc_status_v_deleted  = array("", "",  "Deleted");
static $__desc_status_v_create  = array("", "",  "Creating");
static $__desc_status_v_createfailed  = array("", "",  "Create failed");

static $__desc_iptables_flag =  array("q", "",  "enable_iptables_(only_for_openvz)");
static $__desc_vswap_flag =  array("q", "",  "enable_vswap");
static $__desc_backup_flag =  array("q", "",  "allow_backing_up");
static $__desc_backupschedule_flag =  array("q", "",  "allow_backup_schedule");


static $__desc_owner_f = array("ef", "",  "owner");
static $__desc_template_info_f = array("", "",  "supported_templates");
static $__desc_owner_f_v_on = array("", "",  "you_are_the_owner_of_plan");
static $__desc_owner_f_v_off = array("", "",  "you_are_the_not_owner_of_plan");
static $__desc_lxbackup_o = array('qd', '', '', '');
static $__desc_sshclient_o = array('', '', '', '');
static $__acdesc_update_show_stats = array("", "",  "show_stats");
static $__acdesc_update_rootpassword = array("", "",  "root_password");
static $__acdesc_update_information =  array("","",  "information"); 
static $__acdesc_update_view =  array("","",  "view_site"); 
static $__acdesc_show_config  =  array("","",  "advanced"); 
static $__acdesc_show_graph  =  array("","",  "graph"); 
static $__acdesc_update_poweroff  =  array("","",  "poweroff"); 
static $__acdesc_update_installkloxo  =  array("","",  "install_kloxo"); 
static $__acdesc_update_reboot =  array("","",  "Reboot"); 
static $__acdesc_update_changenname =  array("","",  "Change Name"); 

static $__desc_sp_specialplay_o = array("db", "",  "");
static $__desc_notification_o = array("db", "",  "");


function getSyncserverIP()
{
	return getFQDNforServer($this->syncserver);
}
function isSimpleBackup() { return true ; }

function getLogin()
{
	if ($this->isLocalhost()) {
		$server = 'localhost';
	} else {
		$server = $this->syncserver;
	}

	if (!$this->isWindows()) {
		return array($this->username, getFQDNforServer($server));
	} else {
		return array(getFQDNforServer($server), 5900 + $this->vncdisplay);
	}

}

function getVncLogin()
{
	if ($this->isLocalhost()) {
		$server = 'localhost';
	} else {
		$server = $this->syncserver;
	}

	return array($this->vncdisplay, getFQDNforServer($server));
}


function updateform($subaction, $param)
{
	switch($subaction) {
	}

	return parent::updateform($subaction, $param);
}




}
