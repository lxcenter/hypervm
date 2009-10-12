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


class Package extends Lxclass {


static $__desc = array("P", "",  "updated_package");
static $__desc_nname	 = array("", "",  "package_name");
static $__desc_kloxo_status	 = array("e", "",  "l");
static $__desc_kloxo_status_v_dull	 = array("e", "",  "package_is_part_of_kloxo");
static $__desc_kloxo_status_v_off	 = array("e", "",  "package_is_not_part_of_kloxo");
static $__desc_version	 = array("", "",  "version");
static $__desc_update_version	 = array("", "",  "updated_version");
static $__desc_update_status	 = array("", "",  "new_version");

static $__acdesc_update_doupdate = array("", "",  "update");
static $__acdesc_list = array("", "",  "packages");

function get() { }
function write() {}


static function perPage()
{
	return 50000;
}
static function createListNlist($parent, $view)
{
	$nlist['kloxo_status'] = '5%';
	$nlist['nname'] = '100%';
	$nlist['update_version'] = '10%';
	return $nlist;
}

static function createListBlist($parent, $class)
{
	$blist[] = array("c=$class&a=update&sa=doupdate");
	return $blist;

}

static function createListAlist($parent, $class)
{
	$alist[] = "a=list&c=$class";
	return $alist;
}

static function initThisList($parent, $class)
{

	global $gbl, $sgbl, $login, $ghtml; 
	$driverapp = $gbl->getSyncClass($parent->__masterserver, $parent->__readserver, 'package');
	$list = rl_exec_get($parent->__masterserver, $parent->__readserver,  array("package__$driverapp", "getPackages"), null);

	return $list;
}




}


