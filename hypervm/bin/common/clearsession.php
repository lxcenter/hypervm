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

include_once "htmllib/lib/displayinclude.php";
clearsession_main();


function clearsession_main()
{
	global $gbl, $sgbl, $login, $ghtml;
	initProgramlib('admin');
	$login->__session_timeout = true;

	$ulist = $login->getList('utmp');
	foreach($ulist as $u) {
		if ($u->timeout < time()) {
			$u->setUpdateSubaction('');
			$u->logouttime = time();
			$u->logoutreason = 'Session Expired';
			$u->write();
		}
	}

	$slist = $login->getList("ssessionlist");

	foreach($slist as $s) {
		if ($s->timeout < time()) {
			$s->dbaction = 'delete';
			$s->write();
		}
	}
}
sleep(600);
