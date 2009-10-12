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

if (!isset($argv[1])) {
	print("Usage: lphp.exe $argv[0] lang \nEg   : lphp.exe $argv[0] fr \n\n");
	print("The language you provide will be compared with the default English, and any missing values will be printed\n");
	exit;
}
// First load the english one.
include_once "lang/en/desclib.php";
$eng_description = $__description;
$__description = null;

include_once "lang/en/messagelib.php";
$eng_information = $__information;
$__information = null;


// Load the other language
include_once "lang/$argv[1]/desclib.php";

foreach($eng_description as $k => $v) {
	if (!isset($__description[$k])) {
		print("__description $k doesn't exist\n");
	} 
}


include_once "lang/$argv[1]/messagelib.php";


foreach($eng_information as $k => $v) {
	if (!isset($__information[$k])) {
		print("__information $k doesn't exist\n");
	} 
}
