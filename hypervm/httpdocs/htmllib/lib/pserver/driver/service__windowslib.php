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

class Service__Windows extends LxDriverclass {


function dbactionAdd()
{
	
    // lxshell_return("chkconfig", $this->main->servicename, 'on');
   
}

static function getServiceList()
{
	$obj = new COM("winmgmts:{impersonationLevel=impersonate}//./root/cimv2");
    $servlist = $obj->execQuery("select * from Win32_Service");

	$list = null;
	foreach($servlist as $s) {
		$list[] = strtolower($s->Properties_("Name"));
	}
	foreach($list as $l) {
		$nlist[] = "$l";
	}
	return $nlist;
}

function dbactionUpdate($subaction)
{
	$obj = new COM("winmgmts:{impersonationLevel=impersonate}//./root/cimv2");
	$serv = $obj->execQuery("select * from Win32_Service where Name = '{$this->main->servicename}'");
	switch($subaction) {

		case "toggle_boot_state":
			{
				if ($this->main->isOn('boot_state')) {
					foreach($serv as $s) {
						$s->changeStartMode('Automatic');
					}	
				} else 	{
					foreach($serv as $s) {
						$s->changeStartMode('Disabled');
					}
				}
				break;
			}

		case "toggle_state":
			{
				if ($this->main->isOn('state')) {
					foreach($serv as $s) {
						$s->startService();
					}
				} else {
					foreach($serv as $s) {
						dprint("{$s->Properties_("name")}\n");
						$s->stopService();
					}
				}
				break;
			}
	}
}
	

static function getServiceDetails($list)
{

	$obj = new COM("winmgmts:{impersonationLevel=impersonate}//./root/cimv2");
    $servlist = $obj->execQuery("select * from Win32_Service");

	foreach($list as &$__l) {
		$__l['install_state'] = 'dull';
		$__l['state'] = 'off';
		$__l['boot_state'] = 'off';
	}

	foreach($servlist as $s) {
		$name = $s->Properties_("Name");
		$name = strtolower($name);
		$name = trim($name);
		if (isset($list[$name])) {
			//dprint($s->Properties_("Name"));
			$__l = &$list[$name];
			$__l['install_state'] = 'on';
			if (trim($s->Properties_("StartMode")) === 'Auto') {
				
				$__l['boot_state'] = 'on';
			}
			if (trim($s->Properties_("State")) === 'Running') {
				$__l['state'] = 'on';
			}
		}
	}
	return $list;

}

static function getMainServiceList()
{
	global $gbl, $sgbl, $login, $ghtml; 
	$nval[] = "iisadmin";
	//$nval[] = $sgbl->__var_programname_imap;
	//$nval[] = $sgbl->__var_programname_ftp;
	return $nval;
}

}
