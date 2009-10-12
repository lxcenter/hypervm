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
mysql_connect("localhost", "root", $mysqlpass);
$cmd = "grant all on $db.* to $username@localhost identified by '$newpass'";
print("$cmd\n");
mysql_query($cmd);
lfile_put_contents("../etc/conf/$program.pass", $newpass);



