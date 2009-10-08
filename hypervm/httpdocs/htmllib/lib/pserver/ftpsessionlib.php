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

class ftpsession extends Lxclass {


	static $__desc = array("", "",  "ftp_session");

	static $__desc_pid = array("", "", "pid");
	static $__desc_nname = array("", "", "pid");
	static $__desc_account = array("", "", "account");
	static $__desc_time = array("", "", "time");
	static $__desc_file = array("", "", "file");
	static $__desc_host = array("", "", "host");
	static $__desc_state = array("", "", "state");


	function get() {}
	function write() {}

	static function createListAlist($parent, $class)
	{
		$alist[] = "a=list&c=$class";
		return $alist;
	}
	static function createListNlist($parent, $view)
	{

		$nlist['pid'] = '10%';
		$nlist['state'] = '10%';
		$nlist['account'] = '100%';
		//$nlist['time'] = '10%';
		//$nlist['file'] = '10%';
		//$nlist['host'] = '10%';
		return $nlist;
	}

	static function initThisListRule($parent, $class) { return null; }
	static function initThisList($parent, $class)
	{

		if ($parent->is__table('client')) {
			if ($parent->username) {
				$username = $parent->username;
				$res = rl_exec_in_driver($parent, $class, "getFtpList", array($username));
			} else {
				return null;
			}
		} else {
			$res = rl_exec_in_driver($parent, $class, "getFtpList", array());
		}
		foreach($res as &$__r) {
			$__r['parent_clname'] = $parent->getClName();
		}
		return $res;
	}


}
