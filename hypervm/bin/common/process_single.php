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

ob_start();
include_once "htmllib/lib/include.php";

process_main();

function process_main()
{
	global $gbl, $sgbl, $login, $ghtml; 

	global $argv;

	$list = parse_opt($argv);

	$exitchar = $sgbl->__var_exit_char;

	$res = new Remote();
	$res->exception = null;
	$res->ddata = "hello";
	$res->message = "hello";
	$total = file_get_contents($list['temp-input-file']);
	@ lunlink($list['temp-input-file']);
	$string = explode("\n", $total);
	if (csb($total, "__file::")) {
		ob_end_clean();
		file_server(null, $total);
	} else {
		$reply = process_server_input($total);
		//fprint(unserialize(base64_decode($reply)));
		ob_end_clean();
		print("$reply\n$exitchar\n");
		flush();
	}
	exit;
}


