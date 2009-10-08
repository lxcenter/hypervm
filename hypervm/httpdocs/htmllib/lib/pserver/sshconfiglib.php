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

class sshconfig extends lxdb {

	static $__desc = array("", "",  "SSH_config");
	static $__acdesc_show = array("", "",  "SSH_config");
	static $__desc_ssh_port = array("", "",  "SSH_port");
	static $__desc_without_password_flag = array("f", "",  "do_not_allow_password_based_access_to_root");
	static $__desc_disable_password_flag = array("f", "",  "completely_disable_password_based_access");
	static $__desc_config_flag = array("f", "",  "dont_warn_me_about_password_access_to_root");


	static function initThisObjectRule($parent, $class, $name = null) { return $parent->nname; }

	function createShowUpdateform()
	{
		$uflist['update'] = null;
		return $uflist;
	}


	function updateform($subaction, $param)
	{
		$vlist['ssh_port'] = null;
		$vlist['without_password_flag'] = null;
		$vlist['disable_password_flag'] = null;
		$vlist['config_flag'] = null;
		return $vlist;
	}

}
