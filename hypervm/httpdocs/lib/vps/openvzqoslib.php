<?php 

class openvzqos extends lxclass {


static $__desc = array("", "",  "VPS Qos");

static $__desc_nname =  array("n", "",  "name");
static $__desc_descr =  array("", "",  "description");
static $__desc_used =  array("", "",  "current_used");
static $__desc_state =  array("e", "",  "state");
static $__desc_state_v_ok =  array("e", "",  "clean");
static $__desc_state_v_exceed =  array("e", "",  "failed");
static $__desc_max =  array("", "",  "maximum_held");
static $__desc_barrier =  array("", "",  "barrier");
static $__desc_limit =  array("", "",  "limit");
static $__desc_failcnt =  array("", "",  "fail_count");
static $__acdesc_list =  array("", "",  "Qos");

function get() {}
function write() {}

static function createListNlist($parent, $view)
{
	$nlist['state'] = '5%';
	$nlist['descr'] = '100%';
	$nlist['used'] = '10%';
	$nlist['max'] = '10%';
	$nlist['barrier'] = '10%';
	$nlist['limit'] = '10%';
	$nlist['failcnt'] = '10%';
	return $nlist;
}

function isSelect() { return false; }

function display($var)
{
	if ($var === 'state') {
		if ($this->failcnt > 0) {
			return 'exceed';
		} else {
			return 'ok';
		}
	}

	return $this->$var;

}

static function createListAlist($parent, $class)
{
	$alist[] = "a=list&c=$class";
	return $alist;
}

static function perPage() { return 500; }

static function initThisList($parent, $class)
{
	$parent->setUpdateSubaction('getBeancounter');
	$res = rl_exec_set($parent->__masterserver, $parent->syncserver, $parent);
	$parent->dbaction = 'clean';

	if ($res) foreach($res as &$__rt) {
		$__rt['syncserver'] = $parent->syncserver;
		$__rt['parent_clname'] = createParentName("vps", $parent->nname);
	}
	dprint($res);
	return $res;
}



}
