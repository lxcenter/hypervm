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

class ost_ostl_a extends LxaClass {

static $__desc_nname	 = array("n", "",  "ostemplate");


static function createListAddForm($parent, $class) { return true;}

static function createListNlist($parent, $view)
{
	$nlist['nname'] = '100%';
	return $nlist;
}

static function addform($parent, $class, $typetd = null)
{
	$class = strtilfirst($class, "_");
	$class = "vps__$class";
	$list = exec_class_method($class, "getOsTemplatelist");

	$vlist['nname'] = array('A', $list);
	$ret['variable'] = $vlist;
	$ret['action'] = 'add';
	return $ret;

}

function isSync () { return false; }

static function createListAlist($parent, $class)
{

	global $gbl, $sgbl, $login, $ghtml; 

	//$driverapp = $gbl->getSyncClass(null, $parent->nname, 'vps');
	$alist[] = "goback=1&a=show&o=ostemplatelist";
	$alist[] = "a=list&c=openvz_ostl_a";
	$alist[] = "a=list&c=xen_ostl_a";
	//$alist[] = "a=addform&c=openvz_ostl_a";
	return $alist;
}

}

class xen_ostl_a extends ost_ostl_a {
static $__desc = array("", "",  "xen ostemplate");
}

class openvz_ostl_a extends ost_ostl_a {
static $__desc = array("", "",  "openvz ostemplate");
}


class ostemplatelist extends lxdb {
static $__desc = array("", "",  "ostemplate");
static $__acdesc_show = array("", "",  "ostemplate_list");


function createShowPropertyList(&$alist)
{
	global $gbl, $sgbl, $login, $ghtml; 
	$alist['property'][] = 'a=show';
	$alist['property'][] = "a=list&c=openvz_ostl_a";
	$alist['property'][] = "a=list&c=xen_ostl_a";
}

function createShowAlist(&$alist, $subaction = null)
{
	global $gbl, $sgbl, $login, $ghtml; 
	//$driverapp = $gbl->getSyncClass(null, $this->nname, 'vps');
	return $alist;
}


function isSync () { return false; }

}

