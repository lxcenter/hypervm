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

class Firewall extends Lxdb {
	// Core
	public $class_key = "domain_name";
	static $__desc = array("", "",  "firewall_rule");
	//static $__desc_s_s = array("S", "",  "subdomain");

	// Mysql
	static $__desc_nname	 = array("n", "",  "[%v]_name");
	static $__desc_id  = array("", "",  "id");
	static $__desc_status  = array("e", "",  "s:status", URL_TOGGLE_STATUS);
	static $__desc_status_v_off  = array("e", "",  "disabled");
	static $__desc_status_v_on  = array("e", "",  "enabled");
	static $__desc_syncserver  = array("", "",  "s");
	static $__desc_from_address  = array("E", "",  "from_address");
	static $__desc_from_port  = array("E", "",  "from_port");
	static $__desc_to_address  = array("E", "",  "to_address");
	static $__desc_to_port  = array("E", "",  "to_port");
	static $__desc_action  = array("s", "",  "action");

	static $__rewrite_nname_const =    Array("id", "syncserver");




	static function createListNlist($parent, $view)
	{
		$nlist['status'] = '5%';
		$nlist['id'] = '10%';
		$nlist['from_address'] = '10%';
		$nlist['from_port'] = '10%';
		$nlist['to_address'] = '10%';
		$nlist['to_port'] = '10%';
		$nlist['action'] = '100%';
		return $nlist;
	}

	static function defaultSort()
	{
		return 'id';
	}

	static function add($parent, $class, $param)
	{
		$array = array('from_address', 'from_port', 'to_address', 'to_port');
		$param['syncserver'] = $parent->nname;
		foreach($array as $a) {
			if (isset($param[$a]['existing'])) {
				$param[$a] = $param[$a]['existing'];
			} else {
				$param[$a] = $param[$a]['new'];
			}
		}
		return $param;
	}

	static function addform($parent, $class, $typetd = null)
	{
		$vlist['id'] = null;
		$vlist['from_address'] = null;
		$vlist['from_port'] = null;
		$vlist['to_address'] = null;
		$vlist['to_port'] = null;
		$vlist['action'] = null;
		$ret['action'] = 'add';
		$ret['variable'] = $vlist;
		return $ret;
	}

	static function getSelectList($parent, $var)
	{
		if ($var === 'action') {
			return array('drop', 'accept', 'forward');
		}
		return array('any');
	}

}


