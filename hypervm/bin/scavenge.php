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
		$global_dontlogshell = true;
	exit_if_another_instance_running();
// Selfbackup
	passthru("$sgbl->__path_php_path ../bin/common/mebackup.php");
	passthru("$sgbl->__path_php_path ../bin/gettraffic.php");
	passthru("$sgbl->__path_php_path ../bin/collectquota.php");
	passthru("$sgbl->__path_php_path ../bin/common/schedulebackup.php");
	passthru("$sgbl->__path_php_path ../bin/fix/fixippool.php");
	passthru("$sgbl->__path_php_path ../bin/common/clearsession.php");
	initProgram('admin');
	checkClusterDiskQuota();
// If auto-update is on check for new HyperVM Version when
// the update day is reached
	auto_update();
// Rotate HyperVM logs
	passthru("$sgbl->__path_php_path ../bin/common/fixlogdir.php");
