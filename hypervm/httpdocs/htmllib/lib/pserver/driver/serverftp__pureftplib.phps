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

class serverftp__pureftp extends lxDriverclass {



function dbactionAdd()
{
}

function dbactionUpdate($subaction)
{
	$this->updateXinConfig();
}

function updateXinConfig()
{
	if ($this->main->isOn('enable_anon_ftp')) { $anonval = "";
	} else { $anonval = "-E"; }

	$txt = lfile_get_contents("../file/template/pureftp");
	$txt = str_replace("%lowport%", $this->main->lowport, $txt);
	$txt = str_replace("%highport%", $this->main->highport, $txt);
	$txt = str_replace("%maxclient%", $this->main->maxclient, $txt);
	$txt = str_replace("%anonymous%", $anonval, $txt);
	lfile_put_contents("/etc/xinetd.d/pureftp", $txt);
	createRestartFile('xinetd');
}

}
