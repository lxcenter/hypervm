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

class BlockedIp extends Lxdb {

static $__desc = array("", "",  "blocked_ip",);
static $__desc_nname   =  array("", "",  "blockedip");
static $__desc_ipaddress =  array("n", "",  "blocked_ip");
static $__rewrite_nname_const = array("ipaddress", "parent_clname");




function isSync() 
{ 
	if_demo_throw_exception('ip');
	return false ; 
}


static function createListNlist($parent, $view)
{
	$nlist["ipaddress"] = "100%";
	return $nlist;
}

static function createListAlist($parent, $class)
{
	return allowedip::createListAlist($parent, $class);
}

static function addform($parent, $class, $typetd = null)
{
	global $gbl, $sgbl, $login, $ghtml; 

	$vlist['ipaddress'] = null;
	$ret['variable'] = $vlist;
	$ret['action'] = 'add';
	return $ret;
}


}

class LoginAttempt extends Lxdb {


static $__desc_nname =  array("", "",  "device_name");


static function initThisListRule($parent, $class)
{

	return "__v_table";
}


}

