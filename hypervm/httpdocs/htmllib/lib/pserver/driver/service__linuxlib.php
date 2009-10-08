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


class Service__Linux extends Lxlclass {




	static function getServiceList()
	{
		global $gbl, $sgbl, $login, $ghtml;
		$val = lscandir_without_dot("__path_real_etc_root/init.d");
		$val = array_remove($val, $sgbl->__var_programname_web);
		$val = array_remove($val, $sgbl->__var_programname_dns);
		$val = array_remove($val, $sgbl->__var_programname_imap);
		$val = array_remove($val, $sgbl->__var_programname_mmail);
		$nval = self::getMainServiceList();
		$nval = lx_array_merge(array($nval, $val));
		return $nval;
	}

	static function getMainServiceList()
	{
		global $gbl, $sgbl, $login, $ghtml;
		$nval['httpd'] = '';
		$nval['named'] = 'named';
		$nval['qmail'] = 'qmail';
		$nval['courier-imap'] = 'courier';
		$nval['lighttpd'] = '';
		$nval['spamassassin'] = '';
		$nval['iptables'] = "";
		$nval['djbdns'] = "tinydns";
		return $nval;
	}


	static function checkService($name)
	{
		$servicepath = "__path_real_etc_root/init.d";
		$ret = lxshell_return("$servicepath/$name", "status");
		$state =  ($ret) ? "off": "on";
		return $state;
	}

	static function getRunLevel()
	{
		$v = trim(lxshell_output("runlevel"));
		$v = explode(" ", $v);
		return $v[1];
	}

}
