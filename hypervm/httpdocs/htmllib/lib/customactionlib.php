<?php 


class customaction extends lxdb {

static $__desc = array("", "",  "custom_exec");
static $__desc_nname = array("", "",  "action");
static $__desc_action = array("", "",  "action");
static $__desc_action_v_add = array("e", "",  "add_exec");
static $__desc_action_v_update = array("e", "",  "update_exec");
static $__desc_class = array("", "",  "class");
static $__desc_where_to_exec = array("", "",  "Where_to_execute");
static $__desc_subaction = array("", "",  "subaction");
static $__desc_exec = array("n", "",  "exec");
static $__acdesc_update_update = array("", "",  "update");
static $__rewrite_nname_const =    Array("class", "action", "subaction", "where_to_exec");


function createShowUpdateform()
{
	$uf['update'] = null;
	return $uf;
}

static function addform($parent, $class, $typetd = null)
{
	global $gbl, $sgbl, $login, $ghtml; 

	//$vlist['action'] = array('s', array('add'));
	//$vlist['subaction'] = 
	$vlist['class'] = array('s', $sgbl->__var_action_class);
	$vlist['where_to_exec'] = array('s', array('master', 'slave'));
	if ($typetd['val'] === 'update') {
		$vlist['subaction'] = array('s', 'rebuild');
	}
	$vlist['exec'] = null;
	$ret['action'] = 'add';
	$ret['variable'] = $vlist;
	return $ret;

}


static function createListAlist($parent, $class)
{
	$alist[] = "a=list&c=$class";
	$alist[] = "a=addform&dta[var]=action&dta[val]=add&c=$class";
	$alist[] = "a=addform&&dta[var]=action&dta[val]=update&c=$class";
	return $alist;
}

function isSync() { return false;}

static function createListNlist($parent, $view)
{
	//$nlist['nname'] = '10%';
	$nlist['action'] = '10%';
	$nlist['subaction'] = '10%';
	$nlist['class'] = '10%';
	$nlist['where_to_exec'] = '10%';
	$nlist['exec'] = '100%';
	return $nlist;
}

function updateform($subaction, $param)
{
	$vlist['action'] = array('m', $this->action);
	$vlist['class'] = array('m', $this->class);
	$nlist['where_to_exec'] =  array('m', $this->where);
	$vlist['exec'] = null;
	return $vlist;
}

}
