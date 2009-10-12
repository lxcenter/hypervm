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

class Mssqldbuser__mssql extends LxDriverclass {

function dbactionAdd()
{
	/*$srv = new COM("SQLDMO.SQLServer");
	$srv->LoginSecure = true;
	try{
		$srv->Connect("localhost,1433");
		//$srv->Connect("(local)");
								 //$srv->Connect(".");
	}catch(Exception $e){
		print("\nError:".$e);
	}
	print("\n Mahanersh");
	$NewDBUser="mantuPatil";
	$NewDBUserLogin="mclaranc_bb";
	$DBName="mclaranc_bb";
	$DBUser = new COM("SQLDMO.User");
	$DBUser->Name = $NewDBUser;
	$DBUser->Login = $NewDBUserLogin;
	
	$DB = $srv->Databases($DBName);
	$Role = $DB->DatabaseRoles("DB_DDLADMIN");
	$Role2 = $DB->DatabaseRoles("DB_DATAWRITER");
	try{
		$DB->Users->Add($DBUser);
	}catch(Exception $e) {
		print("\nERROR: ". $e. $NewDBUser." User already exists\n");
	}

	$Role->AddMember($NewDBUser);
	$Role2->AddMember($NewDBUser);
	print("\nNew user permissions to create database objects and to read and write data have been     added to ".$DBName);

	$srv->DisConnect();*/

	$req = mssql_connect("localhost,1433");

	if (!$req) {
		throw new lxException('MsSql Connection is Failed', '', '');
		print("Could not Connect to Database on localhost using root user\n");
		exit();
	}
	$loginname=$this->main->username;
	$dbname=$this->main->dbname;
	print("\n".$loginname);
	$pass=$this->main->dbpassword;
	print("\n". $pass);

	try{
		mssql_query("sp_addlogin '$loginname', '$pass';");
	}catch(Exception $e)
	{
		throw new lxException('user_already_exists', 'username', '');
	}

	mssql_query("use  $dbname ");
	//mssql_query("sp_adduser '$loginname','Administrators';");
	try{
	mssql_query("sp_adduser '$loginname','$loginname','db_owner';");
	}catch(Exception $e)
	{
		throw new lxException('user_already_exists', 'username', '');
	} 
	mssql_close();
	print("\n mmp done");



}


function dbactionDelete()
{
	$srv = new COM("SQLDMO.SQLServer");
	$srv->LoginSecure = true;
	$DB =new COM("SQLDMO.Database");

	try{
		$srv->Connect("localhost,1433");
		//$srv->Connect(".");
	}catch(Exception $e)
	{
		throw new lxException('MsSql Server Connection is Failed', '', '');
		exit();
	}
	$strDatabaseDelete=$this->main->dbname;
	$login=$this->main->username;
	$DB = $srv->Databases($strDatabaseDelete);
	if($DB->IsUser($login) )
		$DB->Users->Remove($login);


	//print("\nRestored\n");


	//$srv->Logins->Remove($login);
	print("\n".$login."\n");

	$srv->DisConnect();


}

function updateDatabase()
{
	    $rdb = mssql_connect("localhost,1433");
		if (!$rdb) {

			throw new lxException('MsSql Connection is Failed', '', '');
			print("Could not Connect to Database on localhost using root user\n");
			exit();
		}

		mssql_query("sp_password @old = null, @new = 'complexpwd',  @loginame ='{$this->main->       username}'");

		mssql_query("ALTER LOGIN {$this->main->username} WITH PASSWORD = '{$this->main->dbpassword}';");

		print("/n Done Alter");
		mssql_close();
		print("\n mmp done");

}


function dbactionUpdate($subaction)
{
	    $this->updateDatabase();
}

}





