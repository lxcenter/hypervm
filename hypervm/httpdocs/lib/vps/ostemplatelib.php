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

class ostemplate_xen extends  ostemplate {
}

class ostemplate extends lxclass {

static $__desc = array("", "",  "ostemplate");
static $__acdesc_show = array("", "",  "ostemplate");

static function initThisObjectRule($parent, $class, $name = null) { return $parent->nname ; }

function get() {}
function write() {}

function createShowPropertyList(&$alist)
{
	$alist['property'][] = 'a=show';
	$alist['property'][] = 'a=show&l[class]=ffile&l[nname]=/';
}

function getFfileFromVirtualList($name)
{
	global $gbl, $sgbl, $login, $ghtml; 

	$parent = $this->getParentO();

	if ($this->isClass('ostemplate_xen')) {
		$path = "d";
	}

	$root = "$gen->rootdir/vps/$parent->ttype/$parent->nname/";

	$name = coreFfile::getRealpath($name);
	$name = "/$name";

	$ffile= new Ffile(null, $server, $root, $name, "root");
	$ffile->__parent_o = $this;
	$ffile->get();
	$ffile->browsebackup = 'on';
	return $ffile;
}
}
