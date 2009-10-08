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

include_once "lib/pserver/driver/service__linuxlib.php";

class Service__Debian extends lxDriverClass {


	/// We need to properly port this system to debian. I tried using the chkconfig directly on debian, but it seems the individual scripts themselves have to support chkconfig if it has to work, and thus chkconfig fails to run. Now the only way is to use update-rc.d program on debain.

	function dbactionAdd()
	{
		lxshell_return("update-rc.d", $this->main->servicename, 'defaults');
	}


	function dbactionUpdate($subaction)
	{
		switch($subaction)
		{

			case "toggle_status":
				{

					if ($this->main->isOn('status')) {
						lxshell_return("update-rc.d", $this->main->servicename, 'defaults');
					} else {
						lxshell_return("update-rc.d", "-f", $this->main->servicename, 'remove');
					}

					break;
				}

			case "toggle_state":
				{
					if ($this->main->isOn('state')) {
						lxshell_return("__path_real_etc_root/init.d/{$this->main->servicename}", "start");
					} else {
						lxshell_return("__path_real_etc_root/init.d/{$this->main->servicename}", "stop");
					}
					break;
				}
		}

	}


	static function getServiceList()
	{
		return Service__Linux::getServiceList();
	}


	static function checkService($name)
	{
		return Service__Linux::checkService($name);
	}

}
