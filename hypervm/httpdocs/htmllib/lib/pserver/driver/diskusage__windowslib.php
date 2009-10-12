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

class DiskUsage__Windows extends lxDriverClass {


/*
Set objFSO = CreateObject("Scripting.FileSystemObject")
Set colDrives = objFSO.Drives
For Each objDrive in colDrives
 Wscript.Echo "Drive letter: " & objDrive.DriveLetter
Next
*/

static function getDiskUsage()
{

	try {
		$obj = new COM("Winmgmts://./root/cimv2");
	} catch (exception $e) {
		throw new lxException("com_failed", 'disk');
	}


	$i = 0;
	$list = $obj->execQuery("select * from Win32_LogicalDisk");

	foreach($list as $l) {
		$result[$i]['nname'] = $l->Name;
		$result[$i]['kblock'] = round($l->Size/1000);
		$result[$i]['available'] = round($l->FreeSpace/1000);
		$result[$i]['used'] = $result[$i]['kblock'] - $result[$i]['available'];
		$result[$i]['pused'] = $result[$i]['used']/ $result[$i]['kblock'];
		$result[$i]['mountedon'] = $l->Name;
		$i++;
	 }
	 return $result;
}

}
