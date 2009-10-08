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

if (!os_isSelfSystemUser()) {
	print("Not enough privileges\n");
	exit;
}

//initProgram('admin');

$list = get_all_pserver();

foreach($list as $l) {
	try {
		$res = rl_exec_get(null, $l, "exec_with_all_closed_output", $argv[1]);
		print("Got this from server $l\n");
		print_r($res);
		print("\n-----------------\n");
	} catch (Exception $e) {
		print("Got error from $l $e->__full_message\n");
	}
}


