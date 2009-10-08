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

class ftpsession__pureftp extends lxDriverClass {

	function dbactionDelete()
	{
		lxshell_return("kill", $this->main->nname);
	}

	static function getFtpList($username = null)
	{


		$list = process__linux::readProcessList();

		$ret = null;
		foreach($list as $l) {
			if (!csa($l['command'], "pure-ftp")) {
				continue;
			}

			dprintr($l);
			$r['pid'] = $l['nname'];
			$r['nname'] = $r['pid'];

			if ($username && $username !== $l['username']) {
				continue;
			}

			$r['account'] = $username;

			$r['state'] = $l['state'];
			$ret[] = $r;
		}
		return $ret;

	}

}
