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

child_main();

function child_main()
{
	global $argv;
	//sleep(100);
	ob_start();
	$rem = unserialize(lfile_get_contents($argv[1]));
	unlink($argv[1]);

	if (!$rem) { exit; }

	if (isset($rem->sleep)) {
		sleep($rem->sleep);
	}

	if ($rem->__type == 'object') {
		$func = $rem->func;
		$ret = $rem->__exec_object->$func();
	} else {
		$ret = call_user_func_array($rem->func, $rem->arglist);
	}


	$var = base64_encode(serialize($ret));
	ob_end_clean();
	print($var);
	exit;
}


