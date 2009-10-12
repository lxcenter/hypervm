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


class Hostdeny extends Lxdb
{

//Core
static $__desc = array("", "",  "blocked_host");

//Data
static $__desc_nname = array("", "",  "blocked_host");
static $__desc_parent_name  = array("", "",  "blocked_host");
static $__desc_syncserver = array("", "",  "blocked_host");
static $__desc_hostname = array("", "",  "host_name");

static $__rewrite_nname_const  = array("hostname","syncserver");


function createExtraVariables()
{
	$pserver = $this->getParentO();
	$hdb = new Sqlite($this->__masterserver, 'hostdeny');
	$string = "syncserver = '{$pserver->nname}' " ;
	$hlist = $hdb->getRowsWhere($string);
	$this->__var_hostlist = $hlist;
	dprintr($this->__var_hostlist);

}


static function createListNlist($parent, $view)
{
	
	//$nlist["nname"] = "100%";
	$nlist["hostname"] = "100%";

	return $nlist;
}

static function createListAlist($parent, $class)
{
	$alist[] = "a=list&c=$class";
	return $alist;

}

static function add($parent, $class, $param)
{
	$param['syncserver'] = $parent->nname;
	return $param;
}

static function addform($parent, $class, $typetd = null)
{

	$vlist['hostname'] = array('m', null);
	$ret['action'] = "add";
	$ret['variable'] = $vlist;
	return $ret;
}

static function createListAddForm($parent, $class)
{
	return true;

}



}



