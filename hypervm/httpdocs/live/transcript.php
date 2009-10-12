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
include("common.php");

if ($g_login) {
	include_once "lib/define.php";
	include_once "htmllib/phplib/common.inc";
	include_once "lib/common.inc";
	initprogram();
	if (!$login->isAdmin()) {
		Print("Not Admin");
		exit(0);
	}
	if (if_demo()) {
		print("Demo... Not Showing..");
		exit;
	}
}

$list = lfile("__path_program_etc/livetranscript.txt");

foreach($list as $l) {
	if (preg_match("/:\s+lx/", $l)) {
		continue;
	}

	print($l . " <br> ");
}


