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

logout_main();

function logout_main()
{
	global $gbl, $sgbl, $login, $ghtml;
	initProgram();

	clear_all_cookie();

	$cl = $login->getList("ssession");
	Utmp::updateUtmp($gbl->c_session->nname, $login, "Logout");

	$gbl->c_session->delete();
	$gbl->c_session->was();
	if ($gbl->c_session->ssl_param) {
		$ghtml->print_redirect($gbl->c_session->ssl_param['backurl']);
	} else if ($gbl->c_session->consuming_parent) {
		$ret = $gbl->getSessionV('return_url');
		$ghtml->print_redirect($ret);
	} else {
		$ghtml->print_redirect_self("/login/");
	}
}

