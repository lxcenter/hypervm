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

class sshconfig__linux extends lxDriverClass {

function dbactionUpdate($subaction)
{
	if (if_demo()) { throw new lxException ("demo", $v); }

	if ($this->main->ssh_port && !($this->main->ssh_port > 0)) {
		throw new lxException('invalid_ssh_port', 'ssh_port', '');
	}
	dprint($this->main->ssh_port);

	$this->main->ssh_port = trim($this->main->ssh_port);
	if (!$this->main->ssh_port) {
		$port = "22";
	} else {
		$port = $this->main->ssh_port;
	}

	if (lxfile_exists("/etc/fedora-release")) {
		$str = lfile_get_contents("../file/template/sshd_config-fedora-2");
	} else {
		$str = lfile_get_contents("../file/template/sshd_config");
	}

	$str = str_replace("%ssh_port%", $port, $str);
	if ($this->main->isOn('without_password_flag')) {
		$wt = 'without-password';
	} else {
		$wt = 'yes';
	}

	if ($this->main->isOn('disable_password_flag')) {
		$pwa = 'no';
	} else {
		$pwa = 'yes';
	}

	$str = str_replace("%permit_root_login%", $wt , $str);
	$str = str_replace("%permit_password%", $pwa , $str);
	$ret = lfile_put_contents("/etc/ssh/sshd_config", $str);
	if (!$ret) {
		throw new lxException('could_not_write_config_file', '', '');
	}
	exec_with_all_closed("/etc/init.d/sshd restart");

}


}
