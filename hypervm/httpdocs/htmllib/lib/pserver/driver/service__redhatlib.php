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

include_once "htmllib/lib/pserver/driver/service__linuxlib.php";

class Service__Redhat extends lxDriverClass {



	/// We need to properly port this system to debian. I tried using the chkconfig directly on debian, but it seems the individual scripts themselves have to support chkconfig if it has to work, and thus chkconfig fails to run. Now the only way is to use update-rc.d program on debain.

function dbactionAdd()
{
	lxshell_return("chkconfig", $this->main->servicename, 'on');
}

function startStopService($act)
{
	global $gbl, $sgbl, $login, $ghtml; 
	exec_with_all_closed("{$sgbl->__path_real_etc_root}/init.d/{$this->main->servicename} $act");
}

function dbactionUpdate($subaction)
{
	switch($subaction) {


		case "start":
			$this->startStopService("start");
			break;

		case "stop":
			$this->startStopService("stop");
			break;

		case "restart":
			$this->startStopService("stop");
			sleep(2);
			$this->startStopService("start");
			break;

		case "toggle_boot_state":
			if ($this->main->isOn('boot_state')) {
				lxshell_return("chkconfig", $this->main->servicename, 'on');
			} else {
				lxshell_return("chkconfig", $this->main->servicename, 'off');
			}
			break;

		case "toggle_state":
			if ($this->main->isOn('state')) {
				$this->startStopService("start");
			} else {
				$this->startStopService("stop");
			}
			break;
	}
}

static function checkServiceInRc($rc, $service)
{
	foreach($rc as $r) {
		if (preg_match("/^S.*$service/i", $r)) {
			return true;
		}
	}
	return false;
}

static function getServiceDetails($list)
{
	$ps = lxshell_output("ps", "ax");
	$run = Service__linux::getRunLevel();
	$rclist = lscandir_without_dot("__path_real_etc_root/rc$run.d/");
	foreach($list as &$__l) {
		$__l['install_state'] = 'dull';
		$__l['state'] = 'off';
		$__l['boot_state'] = 'off';
		if (lxfile_exists("__path_real_etc_root/init.d/{$__l['servicename']}")) {
			$__l['install_state'] = 'on';
		} else {
			continue;
		}
		if (self::checkServiceInRc($rclist, $__l['servicename'])) {
			$__l['boot_state'] = 'on';
		}
		if ($__l['grepstring']) {
			if (preg_match("/[\/ ]{$__l['grepstring']}/i", $ps)) {
				$__l['state'] = 'on';
			}
		} else {
			$ret = lxshell_return("/etc/init.d/{$__l['servicename']}", "status");
			if ($ret) {
				$__l['state'] = 'off';
			} else {
				$__l['state'] = 'on';
			}
		}

	}
	return $list;

}

static function getServiceList()
{
	return Service__Linux::getServiceList();
}




}
