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


//array('add-under-class', 'add-under-name', 'subaction');


commandline_main();

function commandline_main()
{
	global $gbl, $sgbl, $login, $ghtml; 
	global $argv;
	initProgram('admin');
	$must = array('action');
	$p = parse_opt($argv);
	$pk = array_keys($p);
	foreach($must as $m) {
		if (!array_search_bool($m, $pk)) {
			Print("Need action, class and name\n");
			exit;
		}
	}

	$func = "__cmd_desc_{$p['action']}";

	try {
		$list = $func($p);
		if ($list) {
			if (isset($p['output-type'])) {
				if ($p['output-type'] === 'json') {
					$out = json_encode($list);
					print($out);
				} else if ($p['output-type'] === 'serialize') {
					$out = serialize($list);
					print($out);
				}
			} else {
				foreach($list as $l) {
					print("$l\n");
				}
			}
		} else {
			print("{$p['action']} succesfully executed\n");
		}
		exit(0);
	} catch (exception $e) {
		print($e->__full_message);
		print("\n");
		exit(8);
	}
}




