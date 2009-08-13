<?php

class VpsTraffichistory extends TrafficHistory {

static $__desc_outgoing_usage     =  Array("", "",  "outgoing_(mb)");
static $__desc_incoming_usage     =  Array("", "",  "incoming_(mb)");
//Core

//Data

static function createListNlist($parent, $view)
{
	//$nlist['nname'] = '100%';
	$nlist['month'] = '100%';
	$nlist['incoming_usage'] = '50%';
	$nlist['outgoing_usage'] = '50%';
	$nlist['traffic_usage'] = '50%';
	return $nlist;
}



function isSync() { return false; }
static function initThisList($parent, $class)
{

	$result =  self::getTrafficMonthly($parent, 'vpstraffic', array('incoming_usage', 'outgoing_usage'));
	return $result;
}

}
