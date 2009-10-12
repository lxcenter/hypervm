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

class allowedip extends lxdb {

static $__desc = array("", "",  "allowed_ip");
static $__desc_nname = array("", "",  "allowed_ip_range");
static $__desc_ipaddress = array("n", "",  "allowed_ip_range");
static $__desc_current_ip_f = array("", "",  "your_current_ip");
static $__rewrite_nname_const =    Array("ipaddress", "parent_clname");

function isSync() 
{ 
	if_demo_throw_exception('ip');
	return false ; 
}

static function createListAlist($parent, $class)
{
	$alist[] = "a=list&c=allowedip";
	$alist[] = "a=addform&c=allowedip";
	$alist[] = "a=list&c=blockedip";
	$alist[] = "a=addform&c=blockedip";
	return $alist;
}

static function addform($parent, $class, $typetd = null)
{
	$vlist['current_ip_f'] = array('M', $_SERVER['REMOTE_ADDR']);
	$vlist['ipaddress'] = null;
	$ret['variable'] = $vlist;
	$ret['action'] = 'add';
	return $ret;
}

static function createListNlist($parent, $view)
{
	$nlist['ipaddress'] = null;
	return $nlist;
}

}
