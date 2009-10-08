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

class mimetype extends lxdb {

	static $__desc = array("", "",  "mime_type");
	static $__desc_nname = array("", "",  "mime_type");
	static $__desc_domainname = array("", "",  "domain_name");
	static $__desc_syncserver = array("", "",  "domain_name");
	static $__desc_type = array("n", "",  "type");
	static $__desc_extension = array("n", "",  "extension");
	static $__rewrite_nname_const =    Array("type", "domainname");


	function createExtraVariables()
	{
		$mydb = new Sqlite(null, "mimetype");
		$this->__var_mime_list = $mydb->getRowsWhere("syncserver = '{$this->syncserver}'");
	}

	static function add($parent, $class, $param)
	{
		$param['domainname'] = $parent->nname;
		return $param;
	}


	static function addform($parent, $class, $typetd = null)
	{
		$vlist['type'] = null;
		$vlist['extension'] = null;
		return $vlist;

	}

	static function initThisList($parent, $class)
	{
		$sq = new Sqlite(null, 'mimetype');
		$list = $sq->getRowsWhere("domainname = '$parent->nname'");
		$parent->setListFromArray($parent->__masterserver, $parent->__readserver, 'mimetype', $result, true);
	}

}
