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

fixlogdir_main();

function fixlogdir_main()
{
	global $gbl, $sgbl, $login, $ghtml;

	$progname = $sgbl->__var_program_name;

	$logl = lscandir_without_dot("../log");
	lxfile_mkdir("../processed_log");
	@ lunlink("../log/access_log");
	@ lunlink("/usr/local/lxlabs/ext/php/error.log");
	$dir = getNotexistingFile("../processed_log", "proccessed");
	system("mv ../log ../processed_log/$dir");
	mkdir("../log");

	$list = lscandir_without_dot("../processed_log");
	foreach($list as $l) {
		remove_directory_if_older_than_a_day("../processed_log/$l", 6);
	}
	foreach($logl as $l) {
		lxfile_touch("../log/$l");
	}
	lxfile_generic_chown_rec("../log", "lxlabs:lxlabs");
	lxfile_generic_chmod("../log", "0700");
	os_restart_program();


}
