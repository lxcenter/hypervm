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
