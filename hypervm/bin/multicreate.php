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

include "lib/include.php";

if ($argc === 1) {
	print("Usage: $argv[0] --admin-password= --count= --v-template_name= --basename=\n");
	print("Example $argv[0] --admin-password= --count=10 --v-template_name=gighost --basename=example\n");
	exit;
}


$opt = parse_opt($argv);

try {
	checkIfVariablesSet($opt, array('basename', 'count', 'admin-password'));
} catch (exception $e) {
	print($e->getMessage(). "\n");
	exit;
}

$base = $opt['basename'];
$count = $opt['count'];
$temp = $opt['v-template_name'];
$admin_passowrd = $opt['admin-password'];

for($i = 1; $i <= $count; $i++) {
	$name = "$base$i.vm";
	$ip = "$ipbase.$i";
	print("Creating $name with ip $ip with password admin from template $temp\n");
	passthru("$sgbl->__path_php_path  ../bin/common/commandline.php --login-class=client --login-name=admin --login-password=$admin_passowrd --class=vps --name=$name --action=add --v-template_name=$temp --v-password=admin", $return);
	if ($return) {
		print("Adding Failed\n");
		exit;
	}
}

