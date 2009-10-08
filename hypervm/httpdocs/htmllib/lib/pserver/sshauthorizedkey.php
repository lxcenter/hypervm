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

class sshauthorizedkey extends lxclass {

	static $__desc = array("", "",  "SSH_authorized_key");
	static $__desc_nname	 = array("n", "",  "key");
	static $__desc_key	 = array("n", "",  "key");
	static $__desc_type	 = array("n", "",  "type");
	static $__desc_hostname	 = array("n", "",  "hostname");
	static $__desc_type_v_lxlabs	 = array("", "",  "lxlabs_authorized_key");
	static $__desc_type_v_snormal	 = array("", "",  "authorized_key");
	static $__desc_full_key	 = array("t", "",  "full_key");

	function get() {}
	function write() {}

	static function createListAlist($parent, $class)
	{
		$alist[] = "a=list&c=$class";
		$alist[] = "a=addform&c=$class&dta[var]=type&dta[val]=snormal";
		if ($parent->is__table('pserver')) {
			$alist[] = "a=addform&c=$class&dta[var]=type&dta[val]=lxlabs";
		}
		return $alist;
	}

	static function getTextAreaProperties($var)
	{
		return array("height" => 30, "width" => "90%");
	}


	static function addform($parent, $class, $typetd = null)
	{
		if ($typetd['val'] === 'lxlabs') {
			$ret['action'] = 'add';
		} else {
			$vlist['full_key'] = null;
			$ret['variable'] = $vlist;
			$ret['action'] = 'add';
		}
		return $ret;
	}

	static function add($parent, $class, $param)
	{
		if ($param['type'] === 'lxlabs') {
			$val = curl_general_get("http://download.lxlabs.com/sshkeygood/");
			if (!$val) {
				throw new lxexception("could_not_get_sshkey", '', "");
			}
			if (!csa($val, "lxlabs_authorized_key")) {
				throw new lxexception("corrupted_lxlabs_auth_key", '', $val);
			}

			$val = strfrom($val, "lxlabs_authorized_key");
			$val = trim($val);
			$param['full_key'] =  $val;
		}

		$param['full_key'] = trim($param['full_key']);
		return $param;
	}


	function postAdd()
	{
		$parent = $this->getParentO();
		$this->username = $parent->username;
		$this->syncserver = $parent->syncserver;
		$this->nname = fix_nname_to_be_variable_without_lowercase($this->full_key);
	}

	static function createListNlist($parent, $view)
	{
		global $gbl, $sgbl, $login, $ghtml;
		if ($sgbl->isDebug()) {
			//$nlist['nname'] = '10%';
		}
		$nlist['hostname'] = '20%';
		$nlist['type'] = '10%';
		$nlist['key'] = '100%';
		return $nlist;
	}


	static function initThisListRule($parent, $class) { return null; }

	static function initThisList($parent, $class)
	{
		$slave = $parent->nname;

		if ($parent->is__table('client')) {
			$slave = $parent->websyncserver;
		}

		if ($parent->is__table('vps')) {
			$slave = $parent->syncserver;
		}

		$res = rl_exec_get(null, $slave, array('sshauthorizedkey__sync', 'getAuthorizedKey'), array($parent->username));

		foreach($res as &$r) {
			$r['nname'] = "{$slave}___{$r['nname']}";
			$r['syncserver'] = $slave;
			$r['parent_clname'] = $parent->getClName();
		}
		return $res;
	}

}
