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
class consolessh extends lxclass {

	static $__desc = array("", "", "Console Access");
	static $__desc_nname = array("", "", "Console Access");

	static $__acdesc_show = array("", "",  "Console Access");


	function get() {}
	function write() {}

	function showRawPrint($subaction = null)
	{
		global $gbl, $sgbl, $login, $ghtml;


		$parent = $this->getParentO();

		$parent->makeSureTheUserExists();

		$lgg = $parent->getLogin();

		$sshport = db_get_value("sshconfig", $parent->syncserver, "ssh_port");
		if (!$sshport) { $sshport = "22"; }

		$v = lfile_get_contents("htmllib/filecore/sshterm-applet.htm");

		$ip = $lgg[1];

		$v = str_replace("%username%", $lgg[0], $v);
		$v = str_replace("%host%", $ip, $v);
		$v = str_replace("%port%", $sshport, $v);
		$v = str_replace("%connectimmediately%", "true", $v);
		$string = $login->getKeyword("console_message");

		$string = str_replace("%username%", "<font style='font-weight:bold'>$lgg[0]@$ip:$sshport </font>", $string);
		print(" <table cellpadding=0 cellspacing=0 width=80%> <tr> <td > $string <br> </td> </tr> </table>  \n");
		print($v);
	}

	static function initThisObjectRule($parent, $class) { return "consolessh"; }

	static function initThisObject($parent, $class, $name = null) { return "consolessh"; }


}
