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

