<?php 

class Mysqldb__mysql extends lxDriverClass {


function lx_mysql_connect($server, $dbadmin, $dbpass) 
{
	$rdb = mysqli_connect('localhost', $dbadmin, $dbpass);
	if (!$rdb) {
		log_error(mysqli_error($rdb));
		throw new lxException('could_not_connect_to_db', '', '');
	}
	return $rdb;
}

function createDatabase()
{
	dprint("here\n");
	$rdb = $this->lx_mysql_connect('localhost', $this->main->__var_dbadmin, $this->main->__var_dbpassword);
	mysqli_query($rdb,"use mysql");
	$res = mysqli_query($rdb,"select * from user where User = '{$this->main->username}'");
	$ret = null;
	if ($res) {
		$ret = mysqli_fetch_row($res);
	}


	if ($ret) {
		throw new lxException("database_user_already_exists__{$this->main->username}", 'username', '');
	}

	mysqli_query($rdb,"create database {$this->main->dbname};");
	$this->log_error_messages($rdb);

	mysqli_query($rdb,"grant all on {$this->main->dbname}.* to '{$this->main->username}'@'%' identified by '{$this->main->dbpassword}';");
	mysqli_query($rdb,"grant all on {$this->main->dbname}.* to '{$this->main->username}'@'localhost' identified by '{$this->main->dbpassword}';");

	if ($this->main->__var_primary_user) {
		$parentname = $this->main->__var_primary_user;
		mysqli_query($rdb,"grant all on {$this->main->dbname}.* to '{$parentname}'@'localhost';");
		mysqli_query($rdb,"grant all on {$this->main->dbname}.* to '{$parentname}'@'%';");
	}
	$this->log_error_messages($rdb,false);
	mysqli_query($rdb,"flush privileges;");
}

function extraGrant()
{
	//mysql_query("revoke show databases on *.* from '{$this->main->username}'@'%' identified by '{$this->main->dbpassword}';");
	//$this->log_error_messages($rdb,false);
	//mysql_query("grant SELECT,INSERT,UPDATE,DELETE,CREATE,DROP,ALTER on {$this->main->dbname}.* to '{$this->main->username}'@'localhost' identified by '{$this->main->dbpassword}';");
	//$this->log_error_messages($rdb,false);
	//mysql_query("revoke show databases on *.* from '{$this->main->username}'@'localhost' identified by '{$this->main->dbpassword}';");
	//$this->log_error_messages($rdb,false);
}

function deleteDatabase()
{
	$rdb = $this->lx_mysql_connect('localhost', $this->main->__var_dbadmin, $this->main->__var_dbpassword);
	mysqli_query($rdb,"drop database {$this->main->dbname};");
	$this->log_error_messages($rdb,false);
	mysqli_query($rdb,"delete from mysql.user where user = '{$this->main->username}';");
	$this->log_error_messages($rdb,false);
	mysqli_query($rdb,"flush privileges;");
}

function updateDatabase()
{
	$rdb = $this->lx_mysql_connect('localhost', $this->main->__var_dbadmin, $this->main->__var_dbpassword);
	mysqli_query($rdb,"update mysql.user set password = PASSWORD('{$this->main->dbpassword}') where user = '{$this->main->username}';");
	$this->log_error_messages($rdb);
	mysqli_query($rdb,"flush privileges;");

}

function log_error_messages($link, $throwflag = true)
{
	if (mysqli_errno($link)) {
		dprint(mysqli_error($link));
		log_error(mysqli_error($link));
		if ($throwflag) {
			throw new lxException('mysql_error', '', mysqli_error($link));
		}
	}
}

static function take_dump($dbname, $dbuser, $dbpass, $docf)
{

	global $gbl, $sgbl, $login, $ghtml; 
	$arg[0] = "$sgbl->__path_mysqldump_path";
	$arg[1] = "--add-drop-table";
	$arg[2] = "-u";
	$arg[3] = $dbuser;
	$arg[4] = $dbname;
	if ($dbpass) {
		$arg[6] = "-p'$dbpass'";
	}

	$cmd = implode(" ", $arg);

	$output = null;
	$ret = null;
	if (!windowsos()) {
		exec("exec $cmd > $docf", $output, $ret);
	} else {
		exec("$cmd", $output, $ret);
		file_put_contents($docf, $output);
	}
}


static function drop_all_table($dbname, $dbuser, $dbpass)
{
    // TODO: REPLACE MYSQL_CONNECT
	$con = mysqli_connect("localhost", $dbuser, $dbpass,$dbname);
	mysqli_select_db($dbname);
	$query = mysqli_query($con,"show tables");
	while($res = mysqli_fetch_array($query)) {
		$total[] = getFirstFromList($res);
	}
	foreach($total as $k => $v) {
		mysqli_query($con,"drop table $v");
	}
	mysqli_close($con);
}

static function restore_dump($dbname, $dbuser, $dbpass, $docf)
{
	self::drop_all_table($dbname, $dbuser, $dbpass);
	$cont = lfile_get_contents($docf);

	if ($dbpass) {
		$ret = lxshell_input($cont, "__path_mysqlclient_path", "-u", $dbuser, "-p$dbpass", $dbname);
	} else {
		$ret = lxshell_input($cont, "__path_mysqlclient_path", "-u", $dbuser, $dbname);
	}
}

function do_backup()
{
	global $gbl, $sgbl, $login, $ghtml; 
	$dbadmin = $this->main->__var_dbadmin;
	$dbpass = $this->main->__var_dbpassword;
	$vd = tempnam("/tmp", "mysqldump");
	lunlink($vd);
	mkdir($vd);
	$docf = "$vd/mysql-{$this->main->dbname}.dump";

	$arg[0] = "$sgbl->__path_mysqldump_path";
	$arg[1] = "--add-drop-table";
	$arg[2] = "-u";
	$arg[3] = $dbadmin;
	$arg[4] = $this->main->dbname;
	if ($dbpass) {
		$arg[6] = "-p'$dbpass'";
	}

	$cmd = implode(" ", $arg);

	$output = null;
	$ret = null;
	if (!windowsos()) {
		exec("exec $cmd > $docf", $output, $ret);
	} else {
		exec("$cmd", $output, $ret);
		file_put_contents($docf, $output);
	}

	if ($ret) {
		lxfile_tmp_rm_rec($vd);
		throw new lxException('could_not_create_mysql_dump', 'nname', $this->main->dbname);
	}

	return array($vd, array(basename($docf)));

}

function do_backup_cleanup($list)
{
	lxfile_tmp_rm_rec($list[0]);
}


function fix_grant_all()
{
	$rdb = $this->lx_mysql_connect('localhost', $this->main->__var_dbadmin, $this->main->__var_dbpassword);
	mysqli_query($rdb,"grant all on {$this->main->dbname}.* to '{$this->main->username}'@'%'");
	mysqli_query($rdb,"grant all on {$this->main->dbname}.* to '{$this->main->username}'@'localhost'");
}

function do_restore($docd)
{
	global $gbl, $sgbl, $login, $ghtml; 
	$dbadmin = $this->main->__var_dbadmin;
	$dbpass = $this->main->__var_dbpassword;
	$vd = tempnam("/tmp", "mysqldump");
	lunlink($vd);
	mkdir($vd);


	$docf = "$vd/mysql-{$this->main->dbname}.dump";
	$ret = lxshell_unzip_with_throw($vd, $docd);

	if (!lxfile_exists($docf)) {
		throw new lxException('could_not_find_matching_dumpfile_for_db', '', '');
	}

	$cont = lfile_get_contents($docf);
	if ($this->main->dbpassword) {
		$ret = lxshell_input($cont, "__path_mysqlclient_path", "-u", $this->main->username, "-p{$this->main->dbpassword}", $this->main->dbname);
	} else {
		$ret = lxshell_input($cont, "__path_mysqlclient_path", "-u", $this->main->username, $this->main->dbname);
	}
	if ($ret) {
		log_restore("Mysql restore failed.... Copying the mysqldump file $docf to $sgbl->__path_kloxo_httpd_root...");
		lxfile_cp($docf, "__path_kloxo_httpd_root");
		throw new lxException('mysql_error_could_not_restore_data', '', '');
	}
	lunlink($docf);
	lxfile_tmp_rm_rec($vd);
}


function doSyncToSystemPre()
{
	global $gbl, $sgbl, $login, $ghtml; 
	databasecore::loadExtension('mysql');
}

function dbactionAdd()
{
	$this->createDatabase();
}

function dbactionDelete()
{
	$this->deleteDatabase();
}

function dbactionUpdate($subaction)
{
	$this->fix_grant_all();
	$this->updateDatabase();
}

}
