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

class globalization_b extends LxaClass {
}

class aspnetmisc_b extends LxaClass {
}

class aspnet extends Lxdb {

static $__desc = array("", "",  "aspnet_version");
static $__desc_nname = array("", "",  "aspnet_version");
static $__desc_version = array("", "",  "aspnet_version");
static $__desc_encoding = array("", "",  "aspnet_version");
static $__acdesc_update_update = array("", "",  "aspnet_configuration");

function updateform($subaction, $param)
{
	/*
	$sq = new Sqlite($this->__masterserver, 'aspnet');
	$rs = $sq->getRowsWhere("parent_clname = 'pserver_s_vv_p_{$this->syncserver}'");
	foreach($rs as $r) {
		$res[] = $r['version'];
	}*/

	$domain = $this->getParentO();
	$resout = rl_exec_get(null, $domain->syncserver, array('aspnet', 'getAspnetVersion'), null);
	$res = explode("*", $resout);
	//$res = array("1.1","1.4");
	//$res = array($res); 

	foreach($res as $r) {
		$r = trim($r);
		if (!$r) {
			continue;
		}

		if (strtolower($r) === 'machineaccounts') {
			continue;
		}
		$rr[] = $r;
	}

	$vlist['version'] =  array('s', $rr);
	//$vlist['encoding'] = null;
	return $vlist;
}
static function createListAlist($parent, $class)
{
	$alist[] = "a=list&c=$class";
	return $alist;
}


static function getAspnetVersion()
{
	//print("\nI am here\n");
	//$r=exec("c:\regASPVer.vbs");
	//$cm="cmd /K CD C:\Dir";

	//print("\n INASP VER \n");
	$stout = lxshell_output("cscript", "-b", "C:\\Program Files\\lxlabs\\kloxo\\bin\\regASPVer.vbs");

	//print("\n".$strOut."\n");
	//print($r."\n");
	//$r = system('cscript.exe c:\regASPVer.vbs.vbs', $a);

	//cscript.exe regASPVer.vbs
	//regASPVer.vbs
	/*foreach($a as $b) {
		$res['version'] = "aab";
		$res['sss'] = 'ab';
		$ret[] = $res;
	}*/
	return $stout;

}
}
