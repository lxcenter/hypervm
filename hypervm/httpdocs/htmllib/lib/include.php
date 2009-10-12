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

$path = __FILE__;
$dir = dirname(dirname(dirname($path)));
include_once "$dir/htmllib/lib/includecore.php";

print_time("include");
include_once "htmllib/phplib/lib/lxclass.php"  ;
include_once "htmllib/lib/commonfslib.php";
include_once "htmllib/lib/objectactionlib.php";
include_once "htmllib/lib/commandlinelib.php";
include_once "lib/sgbl.php";
include_once "lib/gbl.php";
include_once "htmllib/lib/lib.php";
include_once "htmllib/phplib/lxlib.php" ;
include_once "htmllib/phplib/common.inc";
include_once "htmllib/lib/remotelib.php";
include_once "htmllib/phplib/lib/lxdb.php";
include_once "lib/define.php";
include_once "lib/driver_define.php";
include_once "lib/sgbl.php";
include_once "lib/common.inc";
//include_once "htmllib/lib/xmlinclude.php";
// This is the program specific common lib. There is no need dump everything htmllib/lib/lib.php which has become too large.
include_once "lib/programlib.php";

if (lxfile_exists("../etc/classdefine")) {
	$list = lscandir_without_dot("../etc/classdefine");
	foreach($list as $l) {
		if (cse($l, "phps")) {
			include_once "../etc/classdefine/$l";
		}
	}
}

if (WindowsOs()) {
	include_once "htmllib/lib/lxcomlib.php";
}


//print_time("include", "include");
