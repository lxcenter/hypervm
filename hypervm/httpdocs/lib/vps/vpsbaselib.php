<?php

abstract class vpsbase extends vpsCore {

	static $__desc_newlocation	 = array("n", "",  "new Location");
	static $__desc_username	 = array("n", "",  "username");
	static $__desc_networknetmask	 = array("n", "",  "NetMask");
	//static $__desc_vmipaddress_a_num	 = array("q", "",  "ip:number_of_ipaddresses");
	static $__desc_centralbackup_server	 = array("", "",  "central_backup_server");
	static $__desc_secondlevelquota_flag	 = array("q", "",  "second_level_quota_(only_for_openvz)");
	static $__desc_text_inittab	 = array("", "",  "append_to_inittab");
	static $__desc_kloxo_flag	 = array("f", "",  "show_kloxo_buttons");
	static $__desc_text_xen_config	 = array("", "",  "append_to_xen_config");
	static $__desc_fixdev_confirm_f	 = array("f", "",  "confirm_fix_dev");
	static $__desc_recover_ostemplate	 = array("", "",  "could_not_find_your_ostemplate_please_supply_one");
	static $__acdesc_update_changeosimagename	 = array("", "",  "change_os_template_name");
	static $__acdesc_update_createuser	 = array("", "",  "create_console_user");
	static $__acdesc_update_append_inittab	 = array("", "",  "append_to_inttab");
	static $__acdesc_update_append_xen_config	 = array("", "",  "append_to_xen_config");
	static $__acdesc_update_network	 = array("", "",  "network");
	static $__acdesc_update_mount	 = array("", "",  "mount");
	static $__acdesc_update_changesyncserver	 = array("", "",  "change_server_in_db");
	static $__acdesc_update_fixdev	 = array("", "",  "fix_centos_dev");
	static $__desc_vcpu_number	 = array("", "",  "number_of_virtual_cpu");
	static $__desc_networkbridge	 = array("", "",  "network_bridge");




	function isRightParent()
	{
		return ($this->getParentO()->getClName() === $this->parent_clname) ;
	}


	static function continueFormlistpriv($parent, $class, $param, $continueaction)
	{
		$listpriv = $parent->listpriv;

		$more = false;
		if (count($listpriv->vpspserver_list) > 1) {
			$more = true;
		}

		if ($more) {

			$vlist['server_detail_f'] = array('M', pserver::createServerInfo($listpriv->vpspserver_list, "vps"));
			$vlist["vpspserver_sing"] = "";
			$ret["param"] = $param;
			$ret["variable"] = $vlist;
			$ret["action"] = "continue";
			$ret["continueaction"] = "finish";

		} else {
			//$param['listpriv_s_dbtype_list'] = implode($parent->listpriv->dbtype_list);
			$param["listpriv_s_vpspserver_sing"] = implode("", $parent->listpriv->vpspserver_list);
			$ret = exec_class_method($class, 'continueFormFinish', $parent, $class, $param, $continueaction);
		}
		return $ret;
	}



	function updateInformation($param)
	{
		if (isset($param['networkgateway'])) {
			$param['networkgateway'] = trim($param['networkgateway']);
			if ($param['networkgateway']) {
				validate_ipaddress($param['networkgateway'], 'networkgateway');
			}
		}
		return $param;
	}


	function updateCreateUser($param)
	{
		$param['username'] = str_replace(".", "", $this->nname);
		return $param;
	}


	function updateCreateTemplate($param)
	{
		global $gbl, $sgbl, $login, $ghtml;
		if (!$login->isAdmin()) {
			throw new lxexception('only_for_admin', 'parent');
		}


		return $param;
	}

	function updateChangeLocation($param)
	{
		if ($param['newlocation'] === $this->corerootdir) {
			throw new lxexception('no_change', 'newlocation');
		}
		return $param;
	}

	function formChangeLocationOpenvz()
	{

		$location = $this->getLocationlist();
		$llist = get_namelist_from_objectlist($location);
		$nlist = $llist;
		if (!$nlist) {
			throw new lxexception('need_locations');
		}

		$this->newlocation = $this->corerootdir;
		$nlist = array_push_unique($nlist, $this->corerootdir);

		$vlist['newlocation'] = array('s', $nlist);
		return $vlist;
	}
		

	function updateRootpassword($param)
	{
		$this->rootpassword_changed = 'on';
		return $param;
	}


	function formChangeLocationXen()
	{
		/*
		 if ($this->isXenLvm()) {
		 $vlist['newlocation'] = array('M', 'Already on LVM');
		 $vlist['__v_button'] = array();
		 return $vlist;
		 }
		 */

		$location = $this->getLocationlist();
		$llist = get_namelist_from_objectlist($location);
		dprintr($location);
		$nlist = null;
		foreach($llist as $l) {
			if (csb($l, "lvm:")) {
				$nlist[] = $l;
			}
		}

		if (!$nlist) {
			throw new lxexception('need_lvm_location');
		}

		$vlist['newlocation'] = array('s', $nlist);
		return $vlist;
	}
		
	function updateMacaddress($param)
	{
		$list = explode(":", $param['macaddress']);
		if (count($list) > 5) {
			throw new lxexception('macaddress_only_five');
		}
		return $param;
	}

	function updateform($subaction, $param)
	{

		global $gbl, $sgbl, $login, $ghtml;
		$parent = $this->getParentO();

		$gen = $login->getObject('general')->generalmisc_b;

		switch($subaction) {

			case "recovervps":
				/*
				 if (!lxfile_exists("{$this->getOstemplatePath()}/{$this->ostemplate}.tar.gz")) {
				 $list = exec_class_method("vps__{$this->ttype}", "getOsTemplatelist");
				 $vlist['recover_ostemplate'] = array('A', $list);
				 }
				 */
				$vlist['recover_confirm_f'] = null;
				return $vlist;

			case "mount":
				$vlist['confirm_f'] = array('M', "confirm");
				return $vlist;


			case "macaddress":
				$vlist['macaddress'] = null;
				return $vlist;

			case "ostemplatelist":
				getResourceOstemplate($vlist, $this->ttype);
				$vlist['__v_updateall_button'] = array();
				return $vlist;


			case "hardpoweroff":
				$vlist['confirm_f'] = array("M", "Confirm");
				return $vlist;

			case "reboot":
				$vlist['confirm_f'] = array("M", "Confirm");
				return $vlist;

			case "shutdown":
				$vlist['confirm_f'] = array("M", "Confirm");
				return $vlist;

			case "mainipaddress":
				$vlist['mainipaddress'] = array('s', get_namelist_from_objectlist($this->vmipaddress_a));
				return $vlist;

			case "livemigrate":
				$serverlist = $login->getServerList($this->get__table());
				$rs = null;
				foreach($serverlist as $l) {
					$driverapp = $gbl->getSyncClass(null, $l, 'vps');
					if ($driverapp === 'openvz') {
						$rs[] = $l;
					}
				}
				$serverlist = $rs;
				if (!$this->checkIfLockedForAction('livemigrate')) {
					if ($this->olddeleteflag === 'doing') {
						$this->olddeleteflag = 'program_interrupted';
						$this->setUpdateSubaction();
					}
				}
				$vlist['olddeleteflag'] = array('M', null);
				$vlist['server_detail_f'] = array('M', pserver::createServerInfo($serverlist, $this->get__table()));
				$vlist['syncserver'] = array('s', $serverlist);
				return $vlist;

			case "append_inittab":
				$vlist['text_inittab'] = array('t', $this->text_inittab);
				return $vlist;

			case "append_xen_config":
				$vlist['text_xen_config'] = array('t', $this->text_xen_config);
				return $vlist;

			case "changelocation":

				if ($this->isXen()) {
					return $this->formChangeLocationXen();
				} else {
					return $this->formChangeLocationOpenvz();
				}

				break;


			case "createuser":
				$this->username = str_replace(".", "", $this->nname);
				$vlist['username'] = array('M', null);
				return $vlist;


			case "changeosimagename":
				$vlist['ostemplate'] = null;
				return $vlist;

			case "clonevps":
				$vlist['__v_button'] = array();
				return $vlist;

			case "createtemplate":
				$stem = explode("-", $this->ostemplate);
				if ($this->isWindows()) {
					$name = "{$stem[0]}-";
				} else {
					$name = "{$stem[0]}-{$stem[1]}-{$stem[2]}-";
				}
				$vlist['newostemplate_name_f'] = array("m", array("pretext" => $name));
				$vlist['__v_button'] = 'Create';
				return $vlist;

			case "rebuild":
				if ($this->isWindows()) {
					$type = "img";
				} else {
					$type = "tar.gz";
				}

				$ostlist = vps::getVpsOsimage($this, $this->ttype, $type);
				$vlist['ostemplate'] = array('A', $ostlist);
				if ($this->isNotWindows() && $this->priv->isOn('backup_flag')) {
					//$vlist['rebuild_backup_f'] = null;
				}
				$vlist['rebuild_confirm_f'] = null;
				return $vlist;

			case "installkloxo":
				$this->kloxo_type = 'master';
				$vlist['rebuild_confirm_f'] = null;
				$vlist['__v_button'] = 'Install';
				return $vlist;

			case "commandcenter":
				return $this->commandCenter($param);

			case "fixdev":
				$vlist['fixdev_confirm_f'] = null;
				return $vlist;

			case "rootpassword":
				if ($this->isXen()) { $vlist['__m_message_pre'] = 'xen_restart_message'; }
				$vlist['rootpassword'] = null;
				return $vlist;

				//ONly for Template...
			case "description":
				$vlist['description'] = null;
				//$vlist['share_status'] = null;
				if (!$this->isRightParent()) {
					$this->convertToUnmodifiable($vlist);
				}
				return $vlist;

			case "timezone":
				$vlist['timezone'] = array('s', pserver::getTimeZoneList());
				return $vlist;

			case "information":

				$vlist['nname'] = array('M', $this->nname);
				$vlist['corerootdir'] = array('M', $this->corerootdir);
				$vlist['ddate']= array('M', @ date('d-m-Y', $this->ddate));
				$vlist['kloxo_flag'] = null;

				if ($login->isAdmin() && $this->isXen()) {
					$vlist['nosaveconfig_flag'] = null;
				}

				if (!$this->isXen()) {
					$vlist['vpsid']= array('M', $this->vpsid);
				}


				if ($this->isXen() && $login->isAdmin()) {
					$vlist['vcpu_number'] = array('s', range(1, 10));
				}

				if ($login->isAdmin()) {
					$use_slaves = "--use-slaves-backup-server--";
					if (!$this->centralbackup_server) {
						$this->centralbackup_server = $use_slaves;
					}
					$sq = new Sqlite(null, "centralbackupserver");
					$list = get_namelist_from_arraylist($sq->getTable(array('nname')));
					$list = lx_merge_good(array($use_slaves), $list);
					//$vlist['centralbackup_flag'] = null;
					$vlist['centralbackup_server'] = array('s', $list);
				}
				$vlist['ostemplate']= array('M', null);
				$vlist['parent_name_f'] = array('M', $this->getParentName());
				//$vlist['dbtype_list'] = array('M', $this->listpriv->dbtype_list);
				$vlist['contactemail'] = "";
				if (!$this->isLogin()) {
					$vlist['text_comment'] = null;
				}
				return $vlist;

				// Only for template. For the main guy, it comes as 'rebuild'

			case "network":
				if ($this->isXen()) { $vlist['__m_message_pre'] = 'xen_restart_message'; }

				if (!$this->isLogin() || !$gen->isOn('disable_hostname_change')) {
					$vlist['hostname'] = null;
				}
				if ($this->isXen()) {
					if (!$this->networknetmask) { $this->networknetmask = "255.255.255.0"; }
					$vlist['networkgateway'] = null;
					$vlist['networknetmask'] = null;
					if ($login->priv->isOn('ip_manage_flag') || $login->isAdmin()) {
						$vlist['networkbridge'] = array('s', array("--automatic--", "xenbr0", "xenbr1", "xenbr2", "xenbr3", "xenbr4"));
					}
				}
				$iplist = $this->vmipaddress_a;
				$iplist = implode(", ", get_namelist_from_objectlist($iplist, 'nname'));
				$vlist['one_ipaddress_f']= array('M', $iplist);
				$vlist['nameserver'] = null;
				return $vlist;

			case "ostemplate":
				$driverapp = $gbl->getSyncClass($this->__masterserver, $this->listpriv->vpspserver_sing, 'vps');
				$ostlist = rl_exec_get(null, $this->listpriv->vpspserver_sing, array("vps__$driverapp", "getOsTemplatelist"));
				$ostlist = lx_merge_good(array('--defer-osimage--' => '--defer-osimage--'), $ostlist);
				$vlist['ostemplate'] = array('A', $ostlist);
				return $vlist;

			case "changesyncserver":
				$sq = new Sqlite(null, 'pserver');
				$list = $sq->getTable(array('nname'));
				$list = get_namelist_from_arraylist($list);
				$vlist['syncserver'] = array('s', $list);
				$gbl->__ajax_refresh = true;
				return $vlist;

				// Only for template...
			case "vpspserver_s":
				$listpriv = $parent->listpriv;
				$vlist['server_detail_f'] = array('M', pserver::createServerInfo($listpriv->vpspserver_list, "vps"));
				$parent = $this->getParentO();
				$vlist['vpspserver_sing'] = null;
				if (!$this->isRightParent()) {
					$this->convertToUnmodifiable($vlist);
				}
				return $vlist;
		}

		return parent::updateform($subaction, $param);
	}


}

