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
class lxguardwhitelist__sync extends lxDriverClass {


function dbactionAdd()
{
	$this->updateWht();
}

function dbactionDelete()
{
	$this->updateWht();
}

function dbactionUpdate($subaction)
{
	$this->updateWht();
}


function updateWht()
{
	$res = $this->main->__var_whitelist;
	$res = merge_array_object_not_deleted($res, $this->main);
	$list = get_namelist_from_arraylist($res, 'ipaddress');


	$rmt = new Remote();
	$rmt->data = $list;
	lfile_put_serialize("__path_home_root/lxguard/whitelist.info", $rmt);
	lxshell_return("__path_php_path", "../bin/common/lxguard.php");
}

}
