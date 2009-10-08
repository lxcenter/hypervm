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

class porthistory extends lxdb {


	static $__desc = array("S", "",  "Port Status History");
	static $__desc_nname =  array("n", "",  "server_name");
	static $__desc_portnumber =  array("n", "",  "port");
	static $__desc_ddate =  array("n", "",  "date");
	static $__desc_portname =  array("n", "",  "Port Description");
	static $__desc_errorstring =  array("", "",  "Last Error");
	static $__desc_laststatustime =  array("", "",  "Last Status Period");
	static $__desc_portstatus =  array("e", "",  "Port Status");
	static $__desc_portstatus_v_on =  array("e", "",  "On");
	static $__desc_portstatus_v_off =  array("e", "",  "Off");

	static $__rewrite_nname_const = array("ddate", "parent_clname");



	static function createListNlist($parent, $view)
	{
		$nlist['portstatus'] = "10%";
		$nlist['ddate'] = '10%';
		$nlist['laststatustime'] = '30%';
		$nlist['errorstring'] = '100%';
		return $nlist;

	}

	static function defaultSort() { return 'ddate' ; }
	static function defaultSortDir() { return 'desc' ; }

	function display($var)
	{
		if ($var === 'ddate') {
			return @ date('Y-M-d:H:i:s', $this->ddate);
		}
		if ($var === 'laststatustime') {
			if ($this->isOn('portstatus')) {
				$statevar = "Downtime";
			} else {
				$statevar = "Uptime";
			}
			return round(($this->$var)/60, 1) . " Minutes $statevar";
		}

		return $this->$var;
	}

	static function initThisListRule($parent, $class)
	{
		return array('portnname', '=', "'{$parent->nname}'");
	}



}
