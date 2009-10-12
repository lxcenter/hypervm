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

class dirlocation_a extends LxaClass {

static $__desc_nname	 = array("n", "",  "location");
static $__desc_diskfree	 = array("n", "",  "free");


static function createListAddForm($parent, $class) { return true;}

static function getExtraParameters($parent, $list)
{
	return $parent->getExtraP('xen_location_a', $list);
}

static function createListNlist($parent, $view)
{
	$nlist['nname'] = '100%';
	$nlist['diskfree'] = '50%';
	return $nlist;
}
static function createListAlist($parent, $class)
{

	global $gbl, $sgbl, $login, $ghtml; 

	$driverapp = $gbl->getSyncClass(null, $parent->nname, 'vps');
	$alist[] = "goback=1&a=show&o=dirlocation";
	$alist[] = "a=addform&c={$driverapp}_location_a";
	return $alist;
}

static function add($parent, $class, $param)
{
	if (!csb($param['nname'], "lvm:") && !csb($param['nname'], "/")) {
		throw new lxexception('location_is_either_full_path_or_lvm', 'nname', '');
	}
	return $param;
}


static function isdefaultHardRefresh() { return true; }
function display($var)
{
	static $flag;

	if ($var === 'diskfree') {
		return getGBOrMB($this->$var);
	}

	return parent::display($var);
}
}

class xen_location_a extends dirlocation_a {
static $__desc = array("", "",  "xen location");
}

class openvz_location_a extends dirlocation_a {
static $__desc = array("", "",  "openvz location");
}

class common_location_a extends dirlocation_a {
static $__desc = array("", "",  "common location");
}

class dirlocation extends lxdb {
static $__desc = array("", "",  "location");


function createShowPropertyList(&$alist)
{
	global $gbl, $sgbl, $login, $ghtml; 
	$driverapp = $gbl->getSyncClass(null, $this->nname, 'vps');
	$alist['property'][] = 'a=show';
	$alist['property'][] = "a=addform&c={$driverapp}_location_a";
}

function createShowAlist(&$alist, $subaction = null)
{
	global $gbl, $sgbl, $login, $ghtml; 
	$driverapp = $gbl->getSyncClass(null, $this->nname, 'vps');
	return $alist;
}

function getExtraP($class, $list)
{
	$res =  rl_exec_get(null, $this->nname, array("dirlocation__linux", "getSizeForAll"), array(get_namelist_from_objectlist($list)));
	foreach($res as $k => $v) {
		$list[$k]->diskfree = $v;
	}
	return $list;
}


function createShowClist($subaction)
{
	global $gbl, $sgbl, $login, $ghtml; 
	$driverapp = $gbl->getSyncClass(null, $this->nname, 'vps');
	$clist["{$driverapp}_location_a"] = null;
	return $clist;
}
}

