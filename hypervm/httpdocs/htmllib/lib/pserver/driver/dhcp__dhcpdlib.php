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

class dhcp__dhcpd extends lxDriverClass {


function dbactionAdd()
{
}

function dbactionUpdate($subaction)
{
	createDhcpConfFile();

}

static function createDhcpConfFile($slist)
{
	$list = os_get_allips();

	foreach($list as $l) {
		$base = strtil($l, ".");
		$subnet[$base] = null;
	}

	$string = null;
	$string .= "ddns-update-style interim;\n";
	$string .= "ignore client-updates;\n\n\n";

	$string .= self::getSubnetString($k, $slist);
	/*
	foreach($subnet as $k => $v) {
		$string .= self::getSubnetString($k, $slist);
	}
*/

	foreach($slist as $s) {
		$string .= self::getHostString($s);
	}

	lfile_put_contents("/etc/dhcpd.conf", $string);
	createRestartFile("dhcpd");


}


static function getSubnetString($subnet, $slist)
{
	$string = null;
	//$string .= "subnet $subnet.0 netmask 255.255.255.0 {\n";
	$string .= "subnet 0.0.0.0 netmask 0.0.0.0 {\n";
	$string .= "option subnet-mask		255.255.255.0;\n";
	$string .= "authoritative; \n";
	//$string .= "option domain-name		"domain.org";";

#option time-offset		-18000;	# Eastern Standard Time
#	option ntp-servers		192.168.1.1;
#	option netbios-name-servers	192.168.1.1;
# --- Selects point-to-point node (default is hybrid). Don't change this unless
# -- you understand Netbios very well
#	option netbios-node-type 2;

	$string .= "default-lease-time 21600000;\n";
	$string .= "max-lease-time 432000000;\n";

	$string .= "}\n\n\n\n";
	return $string;
}


static function getHostString($s)
{
	if (!$s['nameserver']) {
		$nm = "127.0.0.1";
	} else {
		$nm = str_replace(" ", ", ", $s['nameserver']);
	}
	$string = null;
	$string .= "option domain-name-servers	$nm;\n";
	$string .= "option domain-name \"{$s['hostname']}\";\n";
	$string .= "option host-name	\"{$s['hostname']}\";\n";
	$i = 1;
	foreach($s['iplist'] as $ip) {
		$ip = trim($ip);
		if (!$ip) { continue; }
		$hex = get_double_hex($i);
		$string .= "host {$s['nname']}$i {\n";
		$string .= "hardware ethernet {$s['macaddress']}:$hex; \n";
		if (!$s['networkgateway']) {
			$gw = os_get_network_gateway();
		} else {
			$gw = $s['networkgateway'];
		}
		$string .= "option routers	$gw;\n";
		$string .= "fixed-address $ip;\n";
		$i++;


		$string .= "}\n\n";
	}
	return $string;
}

}
