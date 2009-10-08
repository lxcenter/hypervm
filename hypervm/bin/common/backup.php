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

backup_main();

function backup_main()
{
	global $argc, $argv;
	global $gbl, $sgbl, $login, $ghtml;

	$progname = $sgbl->__var_program_name;


	if ($argc === 1) {
		print("Usage: $argv[0] --class= --name= --v-backup_file_name= \n");
		exit;
	}



	//sleep(60);
	$opt = parse_opt($argv);

	$class = $opt['class'];
	$name = $opt['name'];
	$param = get_variable($opt);
	initProgram("admin");
	$object = new $class(null, 'localhost', $name);
	$object->get();

	if ($object->dbaction === 'add') {
		print("No objectc\n");
		exit;
	}

	if (!$object->isLxclient()) {
		print("This is not a backupable object... This object alone cannot be backed up\n");
		//exit;
	}


	$backup = $object->getObject('lxbackup');

	if (lx_core_lock("$class-$name.backup")) {
		exit;
	}

	/*
	 if (!testAllServersWithMessage()) {
		mail($object->contactemail, "Backup Failed..", "Could not connect to slave servers.");
		$backup->backupstage = "Failed due to: could not connect to slave servers";
		clearLxbackup($backup);
		exit;
		}
		*/

	if ($object->dbaction === 'add') {
		print("object $name doesn exist\n");
		$backup->backupstage = "Failed due to: $name doesn't exist";
		clearLxbackup($backup);
		log_error("Backup.php Client $name doesnt exist");
		exit;
	}

	if (isset($opt['priority']) && $opt['priority'] === 'low') {
		//sleep(20);
	}

	if ($opt['v-backup_file_name']) {
		$param['backup_to_file_f'] = $opt['v-backup_file_name'];
	} else {
		$param['backup_to_file_f'] = "$progname-scheduled";
	}

	foreach($opt as $k => $v) {
		if (csb($k, "--v-backupextra_")) {
			$kk = strfrom($k, "--v-");
			$param[$kk] = $v;
		}
	}


	try {
		$backup->doupdateBackup($param);
		$backup->backupstage = 'done';
		print("Backup has been saved in $sgbl->__path_program_home/{$backup->getParentO()->get__table()}/{$backup->getParentO()->nname}/__backup/{$param['backup_to_file_f']}\n");
	} catch (exception $e) {
		$mess = "{$e->__full_message}\n";
		$backup->backupstage = "Failed due to: $mess";
		print("Backup failed due to: $mess\n");
		mail($object->contactemail, "Backup Failed..", "Backup Failed for $object->nname with the Message $mess");
	}

	clearLxbackup($backup);



}

