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

class datacenter extends Lxdb {

	static $__desc = array("", "",  "data_center");
	static $__desc_nname = array("n", "",  "DC_name", "a=show");
	static $__desc_description = array("n", "",  "description");
	static $__desc_pserver_l = array("", "",  "");
	static $__acdesc_update_edit = array("", "",  "Info");


	function isSync() { return false; }


	function createShowClist($subaction)
	{
		$clist['pserver'] = null;
		return $clist;

	}

	function createShowPropertyList(&$alist)
	{
		$alist['property'][] = 'a=show';
		$alist['property'][] = 'a=updateForm&sa=edit';
		return $alist;
	}

	static function createListNlist($parent, $view)
	{
		$nlist['nname'] = '10%';
		$nlist['description'] = '100%';
		return $nlist;
	}

	static function addform($parent, $class, $typetd = null)
	{
		$vlist['nname'] = null;
		$vlist['description'] = null;
		$ret['action'] = 'add';
		$ret['variable'] = $vlist;
		return $ret;
	}

	function updateForm()
	{
		$vlist['nname'] = array('M', null);
		$vlist['description'] = null;
		return $vlist;

	}
}
