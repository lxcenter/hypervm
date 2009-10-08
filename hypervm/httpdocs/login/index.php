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

chdir("..");
include_once "htmllib/lib/displayinclude.php";

init_language();
$cgi_clientname = $ghtml->frm_clientname;
$cgi_class = $ghtml->frm_class;
$cgi_password = $ghtml->frm_password;
$cgi_forgotpwd = $ghtml->frm_forgotpwd;
$cgi_email = $ghtml->frm_email;

$cgi_classname = 'client';
if ($cgi_class) {
	$cgi_classname = $cgi_classname;
}
ob_start();
include_once "htmllib/lib/indexcontent.php";



function index_print_header()
{
	?>
<table width=100% height=" 64" border="0" valign="top" align="center"
	cellpadding="0" cellspacing="0">

	<tr>
		<td width=100%
			style='background: url(/img/skin/hypervm/default/default/background.gif)'>
		</td>
		<td width=326
			style='background: url(/img/skin/hypervm/default/default/background.gif); background-repeat: repeat'>
		<table width=326>
			<tr align=right>
				<td width=200>&nbsp; &nbsp;</td>
				<td align=right><img id=main_logo width=84 height=23
					src="/img/hypervm-logo.gif"></td>
				<td width=10%>&nbsp; &nbsp;</td>
			</tr>
		</table>
		</td>
	</tr>
	<tr>
		<td width="100%" colspan=5 bgcolor="#003366" width="10" height="2"></td>
	</tr>
</table>

<?php 

}



