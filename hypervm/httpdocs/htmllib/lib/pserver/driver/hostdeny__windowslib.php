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

class Hostdeny__Windows extends lxDriverClass {


	function dosyncToSystem()
	{
		global $gbl, $sgbl, $login;



		if (if_demo()) {
			return;
		}
		$_filepath="__path_real_etc_root/hosts.deny";
		$string = null;
		foreach((array) $this->main->__var_hostlist as $v) {
			$string .= "ALL: {$v['hostname']}\n";
		}

		if ($this->isDeleted() != "delete") {
			$string .= "ALL: {$this->main->hostname}\n";
		}

		lfile_put_contents($_filepath, $string);

	}

}
