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

class serverftp extends lxdb {


static $__desc = array("", "",  "FTP_config");
static $__acdesc_show = array("", "",  "FTP_config");

static $__desc_enable_anon_ftp = array("f", "", "enable_anonymous_ftp");
static $__desc_highport = array("", "", "high_port_for_passive_ftp");
static $__desc_lowport = array("", "", "low_port_for_passive_ftp");
static $__desc_maxclient = array("", "", "maximum_number_of_clients");
static $__acdesc_update = array("f", "", "update");


function createExtraVariables() 
{ 
	$this->setDefault();
}

function createShowUpdateform()
{
	$uflist['update'] = null;
	return $uflist;
}
function updateform($subaction, $param)
{

	$this->setDefault();
	$vlist['enable_anon_ftp'] = null;
	$vlist['maxclient'] = null;
	$vlist['lowport'] = null;
	$vlist['highport'] = null;
	return $vlist;
}


function setDefault()
{
	$this->setDefaultValue('lowport', "30000");
	$this->setDefaultValue('highport', "50000");
	$this->setDefaultValue('maxclient', "5000");
	$this->setDefaultValue('enable_anon_ftp', "on");
}

}
