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

