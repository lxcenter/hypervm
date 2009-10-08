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

class Dbadmin__sync extends lxDriverClass {



	function dbactionUpdate($subaction)
	{

		switch($this->main->dbtype) {
			case "mysql":
				$this->mysql_reset_pass();
				break;
		}

	}

	function dbactionAdd()
	{
		$dbadmin = $this->main->dbadmin_name;
		$dbpass = $this->main->dbpassword;
		$rdb = mysql_connect('localhost', $dbadmin, $dbpass);
		if (!$rdb) {
			log_error(mysql_error());
			throw new lxException('the_mysql_admin_password_is_not_correct', '', '');
		}
	}

	function dosyncToSystemPost()
	{
		dprint("in synctosystem post\n");
		$a['mysql']['dbpassword'] = $this->main->dbpassword;
		slave_save_db("dbadmin", $a);
	}

	function mysql_reset_pass()
	{
		$this->lx_mysql_connect("localhost", $this->main->dbadmin_name, $this->main->old_db_password);
		$res = mysql_query("set password=Password('{$this->main->dbpassword}');");
		if (!$res) {
			throw new lxException('mysql_password_reset_failed', '', '');
		}
	}

	function lx_mysql_connect($server, $dbadmin, $dbpass)
	{
		$rdb = mysql_connect('localhost', $dbadmin, $dbpass);
		if (!$rdb) {
			log_error(mysql_error());
			throw new lxException('could_not_connect_to_db_admin', '', '');
		}
		return $rdb;
	}


}
