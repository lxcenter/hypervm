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

if (!os_isSelfSystemOrLxlabsUser()) {
	print("Must be Root \n");
	exit;
}

if (!isset($argv[1])) {
	print("Usage: $argv[0] <master/slave> <password>\n");
	exit;
}


if ($argv[1] === 'master') {
	initProgram('admin');
	$login->password = crypt($argv[2]);
	$login->realpass = $argv[2];
	$login->setUpdateSubaction('password');
	$login->createSyncClass();
	$login->was();
} else if ($argv[1] === 'slave') {
	if (!lxfile_exists("__path_slave_db")) {
		print("Not Slave\n");
		exit;
	}
	$rmt = unserialize(lfile_get_contents('__path_slave_db'));
	$rmt->password = crypt($argv[2]);
	lfile_put_contents('__path_slave_db', serialize($rmt));
} else {
	print("first argument is master/slave\n");
	exit;
}


