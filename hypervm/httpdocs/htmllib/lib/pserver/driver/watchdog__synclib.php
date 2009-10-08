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

class watchdog__sync extends Lxdriverclass {



	static function watchRun()
	{
		global $gbl, $sgbl, $login, $ghtml;

		if (lx_core_lock_check_only("scavenge.php", "scavenge.php.pid")) {
			log_log("watchdog", "scavenge is running");
			dprint("Savenge is running\n");
			return;
		}

		// Don't restart service while booting.
		$time = os_getUptime();
		if ($time < 600) { return; }

		$list = lfile_get_unserialize("../etc/watchdog.conf");
		foreach((array)$list as $l) {
			if (!isOn($l['status'])) {
				print("{$l['servicename']} is disabled\n");
				continue;
			}
			if (check_if_port_on($l['port'])) {
				continue;
			}

			if (csb($l['action'], "__driver_")) {
				$class = strfrom($l['action'], "__driver_");
				$driverapp = slave_get_driver($class);
				createRestartFile($driverapp);
				$action = "$driverapp restart";
			} else {
				$action = $l['action'];
				exec_with_all_closed("$action >/dev/null 2>&1");
			}

			log_log("watchdog", "$action executed for port {$l['port']}");
			send_system_monitor_message_to_admin("Port: {$l['port']}\nAction: $action");
		}
	}

	function dbactionUpdate($subaction)
	{
		$result = $this->main->__var_watchlist;
		unset($this->main->__var_watchlist);
		$result = merge_array_object_not_deleted($result, $this->main);
		lfile_put_serialize("../etc/watchdog.conf", $result);
	}

	function dbactionAdd()
	{
		$this->dbactionUpdate("");
	}

}
