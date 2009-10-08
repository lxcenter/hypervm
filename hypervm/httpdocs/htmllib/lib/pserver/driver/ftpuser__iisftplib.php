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

class ftpuser__iisftp extends lxDriverClass {



	function dbactionAdd()
	{

	 $msftpsvc = new lxCOM("IIS://LocalHost/MSFTPSVC");




	 //Creating FTP Virtual Directory

	 /*
	  $newFtpServer= msftpsvc->(new COM("IIsFtpServer", $this->main->nname);
	  $RootDir=newFtpServer->(new COM("IIsFtpVirtualDir","ROOT");
	  $VirtualDir=RootDir->Creat("IIsFtpVirtualDir",$this->main->directory);

	  $VirtualDir->Path = array('d:\sfu\home\root\\' . $this->main->nname);
	  $VirtualDir->AccessFlags = array(513);
	  $VirtualDir->SetInfo();
	  */
	}
	function dbactionDelete()
	{
		$newFtpServer= new lxCOM("IIsFtpServer",  $this->main->nname);
		$RootDir= $newFtpServer->a("IIsFtpVirtualDir","ROOT");
		if ($RootDir) {
			$VirtualDir=$RootDir->Delete("IIsFtpVirtualDir",$this->main->directory);
		}
	}

	function dbactionUpdate($subaction)
	{
		switch($subaction) {

			case "password":
				break;

		}
	}




}

