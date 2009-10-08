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

class openvzostemplate extends lxclass {

	static $__desc = array("PN", "",  "file");

	function get() {}
	function write() {}

	function getFfileFromVirtualList($name)
	{

		//$root = "/home/hypervm/xen/template/";
		$root = "/vz/template/cache/";
		$ffile= new Ffile(null, 'localhost', $root, $name, "root");
		$ffile->__parent_o = $this;
		$ffile->__var_extraid = "template";
		$ffile->ostemplate = 'on';
		$ffile->get();
		return $ffile;
	}

	static function initThisObjectRule($parent, $class, $name = null) { return null ; }
	static function initThisObject($parent, $class, $name = null)
	{

		$ob = new openvzostemplate(null, null, $name);
		return $ob;

	}

}
