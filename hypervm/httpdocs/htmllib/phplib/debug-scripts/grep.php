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

include_once "htmllib/phplib/lxlib.php";

grep_main() ;


function grep_main()
{

	recurse_dir(".", "find_expr");

}

function find_expr($file)
{
	global $gbl, $sgbl, $argc, $argv;

	if (is_dir($file)) {
		return;
	}
	$dl = file($file);


	$count = 0;
	foreach($dl as $l) {
		$count++;
		if (preg_match('/' . $argv[1] . '/', $l)) {
			print("$file:$count:$l");
		}
	}
}

	





