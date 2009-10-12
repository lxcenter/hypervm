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


class SuperClient extends ClientBase {


static $__desc  = array("","",  "super_admin"); 

static $__desc_nname =     array("", "",  "name");
static $__desc_node_num	 = array("q", "",  "number_of_nodes");

static $__desc_node_l = array("Rq", "",  "");

static $__acdesc_update_license_superadmin  =  array("","",  "license"); 
static $__acdesc_update_collectusage  =  array("","",  "collect_usage"); 




function updatecollectusage()
{
	global $gbl, $sgbl, $login, $ghtml; 
	$this->collectQuota();
}

function hasDriverClass()
{
	return false;
}

function createShowAlist(&$alist, $subaction = null)
{
	global $gbl, $sgbl, $login, $ghtml; 
	$alist['__title_main'] = $login->getKeywordUc('resource');
	$alist[] = 'a=list&c=node';
	$this->getLxclientActions($alist);
	$alist[] = 'a=updateform&sa=license';
	$alist[] = 'a=resource';
	$alist[] = 'a=update&sa=collectusage';
	return $alist;
}

function dosyncToSystem()
{
	global $last_error;
	switch($this->dbaction) {

		case "update":
			{
				switch($this->subaction) {
					case "password":
						{
							$this->changeSuperAdminPass();
							break;
						}
				}
				break;

			}


	}
}

function changeAdminPass()
{
	if ($this->main->nname === 'admin') {
		$newp = client::createDbPass($this->main->realpass);

		//exec("mysqladmin -u kloxo -p$oldpass password $newp 2>&1", $out, $return);
		$sql = new Sqlite(null, "client");
		//$sql->rawQuery("grant all on kloxo.* to kloxo@'localhost' identified by $newp");
		//$sql->rawQuery("grant all on kloxo.* to kloxo@'%' identified by $newp");
		$return = $sql->setPassword($newp);
		dprint("i am here <br> \n");
		dprintr($return);
		dprint("i am here <br> \n");
		if ($return) {
			log_log("admin_error", "mysql change password Failed . $out");
			throw new lxException ("could_not_change_admin_pass", '');
		}
		$return = lfile_put_contents("__path_admin_pass", $newp);
		if (!$return) {
			log_log("admin_error", "Admin pass change failed  $last_error");
			throw new lxException ("could_not_change_admin_pass", '');
		}

	}
}


function changeSuperAdminPass()
{
	if ($this->nname === 'superadmin') {
		$oldpass = getAdminDbPass();

		$newp = client::createDbPass($this->realpass);

		$return = lfile_put_contents("__path_super_pass", $newp);
		if (!$return) {
			log_log("admin_error", "Admin pass change failed  $last_error");
			throw new lxException ("could_not_change_superadmin_pass", '');
		}
		$return = $sql->setPassword($newp);
		if ($return) {
			$return = lfile_put_contents("__path_super_pass", $oldpass);
			log_log("admin_error", "mysqladmin Failed . $out");
			throw new lxException ("could_not_change_superadmin_pass", '');
		}
	}
}


}
