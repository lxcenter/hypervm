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

chdir("../../");
include_once "htmllib/lib/displayinclude.php";

$info = unserialize(base64_decode($ghtml->frm_info));

if (!$info) {
	print("No info");
	exit;
}

$filepass = $info->filepass;


/*
 $ip = $_SERVER['REMOTE_ADDR'];

 if ($res['ip'] !== $ip) {
 print("You are trying to access this file from a different Ip, than the one you accessed the master with, which is prohibited <br> Possibly an attempt to hack. \n");
 exit;
 }
 */

$size = $filepass['size'];

while (@ob_end_clean());
header("Content-Disposition: attachment; filename={$filepass['realname']}");
header('Content-Type: application/octet-stream');
header("Content-Length: $size");
printFromFileServ('localhost', $filepass);




