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
