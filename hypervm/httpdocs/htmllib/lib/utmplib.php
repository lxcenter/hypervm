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

class Utmp extends Lxdb {

static $__desc =  array("", "",  "login_history");
static $__desc_nname = array("", "",  "name");
static $__desc_parent_clname = array("", "",  "name");
static $__desc_ip_address = array("", "",  "ip_address");
static $__desc_ssession = array("", "",  "name");
static $__desc_logintime = array("", "",  "login_time_");
static $__desc_logouttime = array("", "",  "logout_time");
static $__desc_auxiliary_id = array("", "",  "auxiliary_id");
static $__desc_consuming_parent = array("", "",  "consuming_parent");
static $__desc_logoutreason = array("", "",  "logout_reason");

static $__acdesc_list = array("", "",  "login_history");


static function createListNlist($parent, $view)
{
	$nlist['parent_clname'] = '100%';
	$nlist['ip_address'] = '15%';
	$nlist['logintime'] = '10%';
	$nlist['logouttime'] = '10%';
	$nlist['logouttime'] = '10%';
	$nlist['auxiliary_id'] = '10%';
	$nlist["consuming_parent"] = "20%";
	$nlist['logoutreason'] = '10%';

	return $nlist;

}

static function searchVar() { return "parent_clname"; }
static function defaultSort() { return "logintime"; }
static function defaultSortDir() { return "desc"; }
function isSelect() { return false; }
function isSync() { return false; }


function display($var)
{
	if ($var === "logintime") {
		return lxgettime($this->$var);
	}

	if ($var === 'logouttime') {
		if ($this->$var !== 'Still Logged') {
			return lxgettime($this->$var);
		}
	}
	return parent::display($var);
}

static function createListAlist($parent, $class)
{
	$alist[] = "a=list&c=$class";
	return $alist;
}

static function updateUtmp($sesname, $parent, $reason = '-')
{
	global $gbl, $sgbl, $login, $ghtml; 
	$nname = implode("_", array($sesname, $parent->getClName()));
	try {
		$utmp = $login->getFromList("utmp", $nname);
		$utmp->logouttime = time();
		$utmp->parent_clname = $login->getClName();
		$utmp->logoutreason = $reason;
		$utmp->setUpdateSubaction();
		$utmp->write();
	} catch (Exception $e) { }

}

static function initThisListRule($parent, $class)
{
	if ($parent->isAdmin()) {
		if(isset($parent->__session_timeout) && $parent->__session_timeout) {
			return array('logouttime', '=', "'Still Logged'");
		}
		return "__v_table";
	} else {
		return array("parent_clname", '=', "'{$parent->getClName()}'");
	}
}

}
