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

exit_if_not_system_user();

system("/bin/cp htmllib/filecore/php.ini /usr/local/lxlabs/ext/php/etc/");
system("/usr/local/lxlabs/ext/php/php ../bin/common/tmpupdatecleanup.php {$argv[1]}");
exit;

/*
 include_once "htmllib/lib/include.php";
 include_once "lib/updatelib.php";
 include_once "htmllib/lib/updatelib.php";

 updatecleanup_main();

 function updatecleanup_main()
 {
 global $argc, $argv;
 global $gbl, $sgbl, $login, $ghtml;

 $program = $sgbl->__var_program_name;
 $login = new Client(null, null, 'upgrade');

 log_log("update", "Execing Updatecleanup");
 $opt = parse_opt($argv);


 if ($opt['type'] === 'master') {
 $sgbl->slave = false;
 updateDatabaseProperly();
 doUpdateExtraStuff();
 lxshell_return("__path_php_path", "../bin/common/driverload.php");
 update_slave();
 } else {
 $sgbl->slave = true;
 }

 updatecleanup();
 }

 */
