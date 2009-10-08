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

include_once "htmllib/lib/include.php"; 

$list = lfile_trim("../etc/xeninterface.list");

system("service iptables stop");
system("echo 1 > /proc/sys/net/bridge/bridge-nf-call-iptables");
//foreach($list as $l) {
foreach(range(0, 50) as $i) {
	system("iptables -I FORWARD -m physdev --physdev-out tap$i -p udp --dport 135:139 -j REJECT");
	system("iptables -I FORWARD -m physdev --physdev-out tap$i -p tcp --dport 135:139 -j REJECT");
}
