<?php 

class datacenter extends Lxdb {

static $__desc = array("", "",  "data_center");
static $__desc_nname = array("n", "",  "DC_name", "a=show");
static $__desc_description = array("n", "",  "description");
static $__desc_pserver_l = array("", "",  "");
static $__acdesc_update_edit = array("", "",  "Info");


function isSync() { return false; }


function createShowClist($subaction)
{
	$clist['pserver'] = null;
	return $clist;

}

function createShowPropertyList(&$alist)
{
	$alist['property'][] = 'a=show';
	$alist['property'][] = 'a=updateForm&sa=edit';
	return $alist;
}

static function createListNlist($parent, $view)
{
	$nlist['nname'] = '10%';
	$nlist['description'] = '100%';
	return $nlist;
}

static function addform($parent, $class, $typetd = null)
{
	$vlist['nname'] = null;
	$vlist['description'] = null;
	$ret['action'] = 'add';
	$ret['variable'] = $vlist;
	return $ret;
}

function updateForm($subaction, $param)
{
	$vlist['nname'] = array('M', null);
	$vlist['description'] = null;
	return $vlist;

}
}
