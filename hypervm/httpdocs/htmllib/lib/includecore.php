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
function print_time($var, $mess = null, $dbg = 2) 
{
	static $last;

	$now = microtime(true);
	if (!isset($last[$var])) {
		$last[$var] = $now;
		return;
	}
	$diff = round($now - $last[$var], 7);
	$now = round($now, 7);
	$last[$var] = $now;
	if (!$mess) {
		return;
	}
	$diff = round($diff, 2);

	if ($dbg <= -1) {
	} else {
		dprint("$mess: $diff <br> \n", $dbg);
	}

	return "$mess: $diff seconds";
}

print_time('full');

function windowsOs() 
{
	if (getOs() == "Windows") {
		return true;
	}
	return false;
}

function getOs()
{
	return (substr(php_uname(), 0, 7) == "Windows")? "Windows": "Linux";
}

if(!isset($_SERVER['DOCUMENT_ROOT'])) {
	if (isset($_SERVER['SCRIPT_NAME'])) {
		$n = $_SERVER['SCRIPT_NAME'];
		$f = ereg_replace('\\\\', '/',$_SERVER['SCRIPT_FILENAME']);
		$f = str_replace('//','/',$f);
		$_SERVER['DOCUMENT_ROOT'] = eregi_replace($n, "", $f);
	}
}

if (!$_SERVER['DOCUMENT_ROOT']) {
	$_SERVER['DOCUMENT_ROOT'] = $dir;
}

if (WindowsOs()) {
	//ini_set("include_path", ".;{$_SERVER['DOCUMENT_ROOT']}");
} else {
	ini_set("include_path", "{$_SERVER['DOCUMENT_ROOT']}");
}

function getreal($vpath)
{
     return  $_SERVER["DOCUMENT_ROOT"] . "/". $vpath; 
}

function readvirtual($vpath)
{
     readfile($_SERVER["DOCUMENT_ROOT"] . $vpath);
}
