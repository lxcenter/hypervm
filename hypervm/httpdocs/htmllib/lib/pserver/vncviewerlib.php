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

class vncviewer extends lxclass {

	static $__desc = array("", "", "VNC_client");
	static $__desc_nname = array("", "", "VNC_client");

	static $__acdesc_show = array("", "",  "VNC_client");


	function get() {}
	function write() {}


	function showRawPrint($subaction = null)
	{
		global $gbl, $sgbl, $login, $ghtml;
		$parent = $this->getParentO();


		$lgg = $parent->getVncLogin();

		$v = lfile_get_contents("htmllib/filecore/vncviewer-applet.html");


		$port = 5900 + $lgg[0];
		//$port = 5900;
		$v = str_replace("%port%", $port, $v);
		$v = str_replace("%host%", $lgg[1], $v);

		$ghtml->print_curvy_table_start("100");
		print("You are actually logging into the vncviewer (<b>$lgg[1]:$port</b>) on the HOST machine, which is the direct console graphical login for the vps. The password is your Control Panel password for the vps. You can also login to this vnc port <b> \"$lgg[1]:$port\" </b> using a vnc client, and there too on a successful login, you will be logged into the vps. Note that you don't need an ipaddress to be configured on the vps to use this facility. You are basically connecting to the HOST through vnc,  which gives you the console access to the vps. <br>");
		$ghtml->print_curvy_table_end("100");
		print("<table cellpadding=0 cellspacing=0 height=10> <tr> <td ></td> </tr> </table> ");

		print($v);
	}

	static function initThisObjectRule($parent, $class) { return "sshclient"; }

	static function initThisObject($parent, $class, $name = null) { return "sshclient"; }


}
