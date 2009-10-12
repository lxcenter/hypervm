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

class Ipaddress__Windows extends lxDriverClass{ 


static function listSystemIps($machinename)
{
	global $gbl, $sgbl, $login, $ghtml; 
	$result = self::getCurrentIps();
	foreach($result as &$_res) {
		$_res['status'] = 'on';
	}

	foreach($result as $r) {
		if ($sgbl->isKloxo()) {
			ipaddress::copyCertificate($r['devname'], $machinename);
		}
	}

	return $result;
}

static function getCurrentIps()
{

	$ipconf = new COM("winmgmts://./root/cimv2");
	$list = $ipconf->ExecQuery("select * from Win32_NetworkAdapterConfiguration where IPEnabled=TRUE");
	foreach($list as $l) {
		if ($l->IPAddress) {
			//for($i = 0; $i< count($l->IPAddress); $i++) {
			foreach($l->IPAddress as $ip) {
				$res['ipaddr'] = $ip;
				$res['devname'] = "Ethernet-" . $l->Index;
				foreach($l->IPSubnet as $s) {
					$sub[] = "$s";
				}
				foreach($l->DefaultIPGateway as $d) {
					$dg[] = "$d";
				}
				$res['netmask'] = implode(",", $sub);
				$res['gateway'] = implode(",", $dg);
				$result[] = $res;
			}
		}
	}

	return $result;
}
}
