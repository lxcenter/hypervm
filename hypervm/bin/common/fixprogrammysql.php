<?php 

include_once "htmllib/lib/include.php"; 

if ($argv[1]) {
	$mysqlpass = $argv[1];
} else {
	$mysqlpass = slave_get_db_pass();
}

$db = $sgbl->__var_dbf;
$username = $sgbl->__var_program_name;
$program = $username;
$newpass = randomString(9);
$newpass = client::createDbPass($newpass);
// TODO: REPLACE MYSQL_CONNECT
$dblink = mysqli_connect("localhost", "root", $mysqlpass,$db);
$query = "GRANT ALL ON $db.* TO $username@localhost IDENTIFIED BY '$newpass'";
print("$query\n");
mysqli_query($dblink,$query);
lfile_put_contents("../etc/conf/$program.pass", $newpass);



