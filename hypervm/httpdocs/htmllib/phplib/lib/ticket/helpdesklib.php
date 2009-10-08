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


class HelpDesk  extends Lxclass {

	static $__desc = array("", "",  "help_desk");


	function write() { }
	function get() { }

	function createShowAlist(&$alist, $subaction = null)
	{
		global $gbl, $sgbl, $login, $ghtml;

		$class = "ticket";

		$alist['__title_main'] = $login->getKeywordUc('resource');

		$alist[] = "a=list&c=$class&frm_filter[show]=nonclosed";
		$alist[] = "a=list&c=$class&frm_filter[show]=open";
		$alist[] = "a=list&c=$class&frm_filter[show]=all";
	}


	static function initThisObjectRule($parent, $class, $name = null) { return null; }

	static function initThisObject($parent, $class, $name = null)
	{
		$o = new HelpDesk($parent->__masterserver, $parent->__readserver, $parent->nname);

		return $o;
	}



}
