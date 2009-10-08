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

class actionlog extends Lxdb {

	static $__desc = array("", "", "action_log");
	static $__desc_ddate = array("", "", "date");
	static $__desc_class = array("", "", "class");
	static $__desc_ipaddress = array("", "", "ipaddress");
	static $__desc_objectname = array("", "", "objectname");
	static $__desc_action = array("", "", "action");
	static $__desc_subaction = array("", "", "subaction");
	static $__desc_login = array("", "", "loginid");
	static $__desc_auxiliary_id = array("", "", "auxiliary");


	static function createListAlist($parent, $class)
	{
		$alist[] = "a=list&c=$class";
		return $alist;
	}


	static function defaultSort() { return 'ddate'; }
	static function defaultSortdir() { return 'desc'; }
	function isSelect() { return false; }

	static function createListBlist($parent, $class)
	{
		return null;
	}

	static function createListSlist($parent)
	{
		$slist['ipaddress'] = null;
		$slist['login'] = null;
		$slist['auxiliary_id'] = null;
		$slist['class'] = null;
		$slist['objectname'] = null;
		$slist['action'] = null;
		$slist['subaction'] = null;
		return $slist;
	}

	static function createListNlist($parent, $view)
	{
		$nlist['ddate'] = '4%';
		$nlist['ipaddress'] = '4%';
		$nlist['login'] = '4%';
		$nlist['auxiliary_id'] = '4%';
		$nlist['class'] = '4%';
		$nlist['objectname'] = '4%';
		$nlist['action'] = '10%';
		$nlist['subaction'] = '100%';
		return $nlist;
	}

	static function initThisListRule($parent, $class)
	{
		if ($parent->isAdmin()) {
			return "__v_table";
		}

		return array("loginclname", "=", "'{$parent->getClName()}'");
	}


}
