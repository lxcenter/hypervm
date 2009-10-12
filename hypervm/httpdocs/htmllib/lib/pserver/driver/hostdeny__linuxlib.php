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

class Hostdeny__Linux extends lxDriverClass {


function dbactionAdd()
{
	global $gbl, $sgbl, $login; 



	if (if_demo()) {
		return;
	}
	$_filepath="__path_home_root/lxguard/hostdeny.info";
	$result =  $this->main->__var_hostlist;
	$result = merge_array_object_not_deleted($result, $this->main);

	$list = get_namelist_from_arraylist($result, 'hostname', 'hostname');
	dprintr($list);

	lfile_put_serialize($_filepath, $list);
	lxshell_return("__path_php_path", "../bin/common/lxguard.php");

}

function dbactionDelete()
{
	$this->dbactionAdd();
}

}
