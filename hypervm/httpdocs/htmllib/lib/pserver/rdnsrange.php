<?php 

class rdnsrange extends lxdb {

static $__desc = array("", "", "rdns_range");

static $__desc_nname	 = array("n", "",  "name", URL_SHOW);
static $__desc_firstip	 = array("n", "",  "first_ip");
static $__desc_lastip	 = array("n", "",  "last_ip");
static $__acdesc_update_update	 = array("n", "",  "update");


function createShowUpdateform()
{
	$uflist['update'] = null;
	return $uflist;
}

static function createListNlist($parent, $view)
{
	$nlist['firstip'] = '10%';
	$nlist['lastip'] = '10%';
	$nlist['nname'] = '100%';

	return $nlist;
}


static function createListAlist($parent, $class)
{
	return reversedns::createListAlist($parent, $class);
}


function updateform($subaction, $param)
{
	$vlist['firstip'] = null;
	$vlist['lastip'] = null;
	return $vlist;

}

function updateUpdate($param)
{
	self::check_first_last_ip($param);
	return $param;
}

static function check_first_last_ip($param)
{
	$first = strtil($param['firstip'], ".");
	$last = strtil($param['lastip'], ".");
	if ($first !== $last) {
		throw new lxException ("first_and_last_should_be_same_network", 'lastip');
	}
}
static function add($parent, $class, $param)
{
	self::check_first_last_ip($param);
	return $param;
}

static function createListAddForm($parent, $class) { return true;}

static function addform($parent, $class, $typetd = null)
{
	$vlist['nname'] = null;
	$vlist['firstip'] = null;
	$vlist['lastip'] = null;
	$ret['variable'] = $vlist;
	$ret['action'] = 'add';
	return $ret;
}

static function initThisListRule($parent, $class)
{
	return "__v_table";
}

}
