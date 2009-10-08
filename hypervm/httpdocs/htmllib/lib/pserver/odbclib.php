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

class odbcdetails_b extends LxaClass {
	static $__desc_nname = array("", "",  "odbc_param");
	static $__desc_login = array("", "",  "odbc_param");

	static $__desc_msaccess_file = array("n", "",  "db_file_path");
	static $__desc_msaccess_loginid = array("", "",  "login_id");
	static $__desc_msaccess_password = array("", "",  "password");
	static $__desc_msaccess_pagetimeout = array("", "",  "page_time_out");
	static $__desc_msaccess_maxbuffersize = array("", "",  "the_size_of_the_internal_buffer");
	static $__desc_msaccess_safetranction = array("", "",  "safe_tranctions");
	static $__desc_msaccess_thread = array("", "",  "threads");
	static $__desc_msaccess_implicommitsync = array("", "",  "implicit_commit_syncronization");
	static $__desc_msaccess_usercommitsync = array("", "",  "user_commit_syncronization");


	static $__desc_mssql_server = array("n", "",  "server");
	static $__desc_mssql_loginid = array("n", "",  "login_id");
	static $__desc_mssql_password = array("n", "",  "password");
	static $__desc_mssql_database = array("", "",  "default_datbase");
	static $__desc_mssql_app = array("", "",  "appname");
	static $__desc_mssql_wsid = array("", "",  "workstation_id");
	static $__desc_mssql_language = array("", "",  "default_national_language_to_use");
	static $__desc_mssql_oemtoansi = array("", "",  "this_parameter_specifies_whether_to_convert_extended_characters_to_oem_values");


	static $__desc_mysql_server = array("n", "",  "server");
	static $__desc_mysql_loginid = array("n", "",  "login_id");
	static $__desc_mysql_password = array("n", "",  "password");
	static $__desc_mysql_database = array("", "",  "default_datbase");
	static $__desc_mysql_port = array("", "",  "port");
	static $__desc_mysql_socket = array("", "",  "socket");
	//static $__desc_mysql_option = array("", "",  "odbc_param");


	static $__desc_msexcel_file = array("", "",  "odbc_param");
	static $__desc_msexcel_dir = array("", "",  "odbc_param");
	static $__desc_msexcel_ver = array("", "",  "odbc_param");
	static $__desc_msexcel_maxbuffsize = array("", "",  "odbc_param");
	static $__desc_msexcel_pagetimeout = array("", "",  "odbc_param");



}

class odbc extends Lxdb {

	static $__desc  = array("","",  "odbc_driver");
	static $__desc_nname  = array("","",  "odbc_name");
	static $__desc_odbcname  = array("n","",  "odbc_name", URL_SHOW);
	static $__desc_description  = array("","",  "description");
	static $__desc_driver  = array("","",  "driver");
	static $__acdesc_update_update  = array("","",  "edit_odbc_paramters");
	static $__rewrite_nname_const =    Array("odbcname", "syncserver");


	static function GetOdbcDriverList()
	{
		return array("SQL Server", "Microsoft Access Driver", "MySQL Server");
	}

	static function add($parent, $class, $param)
	{
		$param['syncserver'] = $parent->syncserver;
		return $param;

	}
	static function addform($parent, $class, $typetd = null)
	{
		$vlist['odbcname'] = null;
		$vlist['description'] = null;
		$vlist['driver'] = array('s', self::GetOdbcDriverList());
		$ret['variable'] = $vlist;
		$ret['action'] = 'continue';
		return $ret;

	}

	static function createListNlist($parent, $view)
	{
		$nlist['odbcname'] = '20%';
		$nlist['description'] = '100%';
		return $nlist;
	}

	function updateform($subaction, $param)
	{
		$vlist['odbcname'] = array('M', null);
		$vlist = lx_array_merge(array($vlist, self::createVlist($this->driver)));
		return $vlist;
	}



	function createShowUpdateform()
	{
		$uflist['update'] = null;
		return $uflist;
	}

	static function createVlist($driver)
	{
		switch($driver) {
			case "SQL Server":
				{
					$vlist['odbcdetails_b_s_mssql_server'] = null;
					$vlist['odbcdetails_b_s_mssql_loginid'] = null;
					$vlist['odbcdetails_b_s_mssql_password'] = null;
					$vlist['odbcdetails_b_s_mssql_database'] = null;
					$vlist['odbcdetails_b_s_mssql_app'] = null;
					$vlist['odbcdetails_b_s_mssql_wsid'] = null;
					$vlist['odbcdetails_b_s_mssql_language'] = null;
					//$vlist['odbcdetails_b_s_mssql_oemtoansi'] = null;

					break;
				}

			case "Microsoft Access Driver":
				{
					$vlist['odbcdetails_b_s_msaccess_file'] = null;
					$vlist['odbcdetails_b_s_msaccess_loginid'] = null;
					$vlist['odbcdetails_b_s_msaccess_password'] = null;
					$vlist['odbcdetails_b_s_msaccess_pagetimeout'] = null;
					$vlist['odbcdetails_b_s_msaccess_maxbuffersize'] = null;
					$vlist['odbcdetails_b_s_msaccess_safetranction'] = null;
					$vlist['odbcdetails_b_s_msaccess_thread'] = null;
					$vlist['odbcdetails_b_s_msaccess_implicommitsync'] = null;
					$vlist['odbcdetails_b_s_msaccess_usercommitsync'] = null;
					break;
						
				}
			case "MySQL Server":
				{
					$vlist['odbcdetails_b_s_mysql_server'] = null;
					$vlist['odbcdetails_b_s_mysql_loginid'] = null;
					$vlist['odbcdetails_b_s_mysql_password'] = null;
					$vlist['odbcdetails_b_s_mysql_database'] = null;
					$vlist['odbcdetails_b_s_mysql_port'] = null;
					$vlist['odbcdetails_b_s_mysql_socket'] = null;

					break;

				}

		}
		return $vlist;
	}

	static function continueForm($parent, $class, $param, $continueaction)
	{

		$vlist = self::createVlist($param['driver']);

		$ret['variable'] = $vlist;
		$ret['action'] = 'add';
		$ret['param'] = $param;
		return $ret;


	}
}


