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


$list = scandir($argv[1]);

foreach($list as $f) {

	if (!csb($f, "Copy")) {
		continue;
	}
	preg_match( "/Copy \((.*)\).*/i", $f, $match);
	if (!isset($match[1])) {
		system("rm -rf $sgbl->__path_program_httdocs/img/skin/color001");
		system("cp -a '{$argv[1]}/$f' $sgbl->__path_program_httdocs/img/skin/color001");
		continue;
	}
	$num = createZeroString(3 - strlen($match[1])) . $match[1];
	system("rm -rf $sgbl->__path_program_httdocs/img/skin/color$num");
	system("cp -a '{$argv[1]}/$f' $sgbl->__path_program_httdocs/img/skin/color$num");
}

