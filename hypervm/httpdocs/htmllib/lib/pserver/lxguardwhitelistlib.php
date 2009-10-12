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

class lxguardwhitelist extends lxdb {

static $__desc = array("", "",  "white_list");
static $__desc_ipaddress = array("", "",  "ipaddress");
static $__desc_cur_ip = array("", "",  "your_current_ip");
static $__rewrite_nname_const = array("ipaddress", "syncserver");
static $__acdesc_list = array("", "",  "white_list");


function createExtraVariables()
{
	$parent = $this->getParentO();
	$sq = new Sqlite(null, "lxguardwhitelist");
	$res = $sq->getRowsWhere("syncserver = '$parent->syncserver'", array('nname', 'ipaddress'));
	$this->__var_whitelist = $res;
}

static function createListAddForm($parent, $class) { return true ; }

static function add($parent, $class, $param)
{
	$param['ipaddress'] = trim($param['ipaddress']);
	$param['syncserver'] = $parent->nname;
	return $param;
}

static function createListAlist($pserver, $class)
{
	$alist[] = 'a=show';
	$alist[] = 'a=list&c=lxguardhitdisplay';
	$alist[] = 'a=list&c=lxguardwhitelist';
	return $alist;
}

static function createListNlist($parent, $view)
{
	$nlist['ipaddress'] = '100%';
	return $nlist;
}


static function addform($parent, $class, $typetd = null)
{
	$vlist['cur_ip'] = array('M', $_SERVER['REMOTE_ADDR']);
	$vlist['ipaddress'] = null;
	$ret['action'] = 'add';
	$ret['variable'] = $vlist;
	return $ret;
}

}
