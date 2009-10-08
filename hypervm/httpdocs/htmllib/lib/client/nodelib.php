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

class Node extends ClientBase {


	static $__desc  = array("","",  "node");

	static $__desc_nname =     array("", "",  "node", URL_SHOW);

	static $__desc_client_o = array("qR", "",  "");



	static function add($parent, $class, $param)
	{
		$param['realpass'] = $param['password'];
		$param['password'] = crypt($param['password']);
		return $param;
	}

	static function addform($parent, $class, $typetd = null)
	{
		$vlist['nname'] = null;
		$vlist['password'] = null;
		$ret['variable'] = $vlist;
		$ret['action'] = 'add';
		return $ret;
	}

	function createShowAlist(&$alist, $subaction = null)
	{
		global $gbl, $sgbl, $login, $ghtml;
		$alist['__title_main'] = $login->getKeywordUc('resource');
		$this->getLxclientActions($alist);
		$alist[] = 'a=show&o=client';
		return $alist;
	}

}

