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
chdir("../../");

include_once "htmllib/lib/displayinclude.php";
//initProgram();
?>
<link
	href="/htmllib/css/header_new.css" rel="stylesheet" type="text/css" />
<link
	href="/htmllib/css/common.css" rel="stylesheet" type="text/css" />
<?php
$ghtml->print_jscript_source("/htmllib/js/lxa.js");
print("<body topmargin=0 leftmargin=0> ");
print("<div id=statusbar  style='background:#f0f0ff;scroll:auto;height:100%;width:100%;border-top:1px solid #aaaacf;margin:0 0 0 0:vertical-align:top;text-align:top'></div> </body> ");
