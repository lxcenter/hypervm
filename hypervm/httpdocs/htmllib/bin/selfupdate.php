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

include_once "htmllib/lib/include.php"; 


if ($ghtml->frm_clientname !== 'admin') {
	print("__error_clientname_has_to_be_admin\n");
	exit;
}

if (!check_raw_password('client', 'admin', $ghtml->frm_password)) {
	print("__error_wrong_password\n");
	exit;
}

try {
	rl_exec_get(null, 'localhost', 'update_self', null);
} catch (Exception $e) {
	print("__error_{$e->getMessage()}\n");
	exit;
}

print("__success_upgrade\n");

