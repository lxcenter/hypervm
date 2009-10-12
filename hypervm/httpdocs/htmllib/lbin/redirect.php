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

chdir("../..");
include_once "htmllib/lib/displayinclude.php";



$name = $ghtml->frm_redirectname;


if (!$ghtml->frm_redirectaction) {
	$ghtml->print_redirect_back('you_didnt_specify_an_action', 'nname');
}

$action = base64_decode($ghtml->frm_redirectaction);

$url = str_replace("__tmp_lx_name__", $name, $action);

header("Location: $url");
