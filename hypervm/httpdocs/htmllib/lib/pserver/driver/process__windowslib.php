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

class Process__Windows extends lxDriverClass {


static function readProcessList()
{
	
	try {
		$obj = new COM("Winmgmts:{impersonationLevel=impersonate}!//./root/cimv2");
	} catch (exception $e) {
		throw new lxException("com_failed", 'disk');
	}


	try {
		$list = $obj->execQuery("select * from Win32_Process");
	} catch (exception $e) {
	}


	$i = 0;
	$v = new Variant(42);
	foreach($list as $l) {
		try {
			$result[$i]['nname'] = $l->ProcessId;
			$result[$i]['command'] = $l->Caption;
			$ret = $l->getOwner($v);
			if ($ret) {
			} else {
				$result[$i]['username'] = "$v";
			}
			$result[$i]['state'] = "ZZ";
			$i++;
		} catch (exception $e) {
			$result[$i]['state'] = "ZZ";
			$result[$i]['nname'] = "Error";
			$result[$i]['command'] = $e->getMessage();
			$result[$i]['username'] = $e->getCode();
		}
	}

	return $result;
}

function dbactionUpdate($subaction)
{
	if ($this->main->signal === "KILL") {
		// forcibly Kill
	} else if ($this->main->signal === "TERM") {
		// Send Term
	}


}


}
