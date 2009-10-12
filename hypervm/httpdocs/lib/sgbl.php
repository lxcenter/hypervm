<?php 

include_once "htmllib/phplib/lib/sgbllib.php";

class Sgbl extends Sgbllib {

function getlType($classname)
{
	$v =  array_search($classname, $this->__var_ltype);
	return $v;
}
function isDebug()
{
	return ($this->dbg > 0);
}

function isLive() { return true ; }
function __construct()
{

	$this->__var_service_desc['httpd'] = "Apache Web Server";
	$this->__var_service_desc['apache2'] = "Apache Web Server";
	$this->__var_service_desc['qmail'] = "Qmail Mail Server";
	$this->__var_service_desc['named'] = "Bind Dns Server";
	$this->__var_service_desc['bind9'] = "Bind Dns Server";
	$this->__var_service_desc['pure-ftpd'] = "Pureftp Ftp Server";
	$this->__var_service_desc['courier'] = "Courier Pop/Imap Server";
	$this->__var_service_desc['courier-imap'] = "Courier Pop/Imap Server";


	$this->__ver_major = "2";
	$this->__ver_minor = "0";
	$this->__ver_release = "7993";
	$this->__ver_enterprise = null;
	$this->__ver_type = "production";
	$this->__ver_extra = "Stable";
	$this->__ver_major_minor = $this->__ver_major . "." . $this->__ver_minor;
	$this->__ver_major_minor_release = $this->__ver_major . "." . $this->__ver_minor . "." . $this->__ver_release;
	$this->__var_nname_impstr = "___";
	$this->__var_prog_port = "8888";
	$this->__var_prog_ssl_port = "8887";

	$this->__var_cttype = array();
	$this->__var_cttype['superadmin'] = 4;
	$this->__var_cttype['superclient'] = 5;
	$this->__var_cttype['node'] = 7;
	$this->__var_cttype['admin'] = 11;
	$this->__var_cttype['master'] = 15;
	$this->__var_cttype['wholesale'] = 20;
	$this->__var_cttype['reseller'] = 30;
	$this->__var_cttype['customer'] = 40;
	$this->__var_cttype['pserver'] = 50;
	$this->__var_cttype['domain'] = 60;
	$this->__var_cttype['uuser'] = 70;
	$this->__var_cttype['ftpuser'] = 80;
	$this->__var_cttype['mailaccount'] = 90;

	$this->__var_ltype = array();
	$this->__var_ltype['hypervmaccount'] = 'client';
	$this->__var_ltype['serveradmin'] = 'pserver';
	$this->__var_ltype['domainowner'] = 'domain';
	$this->__var_ltype['sysuser'] = 'uuser';
	$this->__var_ltype['mailuser'] = 'mailaccount';
	$this->__var_ltype['ftpuser'] = 'ftpuser';
	$this->__var_ltype['superclient'] = 'superclient';


	parent::__construct();

	if (windowsOs()) {
		$this->__var_quote_char = "\"";
		$this->__var_database_type = "mssql";

		$this->__path_mysqldump_path = "C:/Program Files/lxlabs/ext/Mysql/";

	 	$this->__path_named_path = "c:/var/named";
		$this->__path_named_conf = "c:/etc/named.conf";
		$this->__path_apache_root = "c:/home/hypervm/httpd";
		$this->__path_apache_path = "d:/etc/httpd/conf";

		$this->__path_etc_root = "C:/Program Files";

		$this->__path_program_home = "c:/hypervm";

		$this->__path_home_dir = "d:/";
		$this->__path_client_root = "c:/home/hypervm/client";
		$this->__path_hypervm_httpd_root = "c:/Program Files/hypervmdata";
		$this->__path_lxlabs_base = "c:/Program Files/lxlabs";
		$this->__path_program_root = "c:/Program Files/lxlabs/hypervm";
		$this->__path_program_htmlbase = "c:/Program Files/lxlabs/hypervm/httpdocs";
		$this->__path_mail_root = "c:/home/hypervm/mail/domains";
		$this->__path_httpd_root  = "c:/webroot";
		$this->__path_program_etc = "C:/Program Files/lxlabs/hypervm/etc";
		$this->__path_php_path =  $this->__path_lxlabs_base . "/ext/php/php.exe";
	} else {
		$this->__var_quote_char = "'";
		$this->__var_database_type = "mysql";
		$this->__path_mysqlclient_path = "mysql";
		$this->__path_program_home = "/home/hypervm";
		$this->__path_mysqldump_path = "mysqldump";
		$this->__var_noaccess_shell = '/sbin/nologin';
		$this->__path_named_path = "/var/named";
		$this->__path_iptraffic_file = "/var/log/lxiptraffic.log";
		$this->__path_vps_root = "/vz/private/";

		$this->__path_home_dir = "/home";
		$this->__path_named_conf = "/etc/hypervm.named.conf";
		$this->__path_named_chroot = "";
		$this->__path_home_root = "/home/hypervm";
		$this->__path_apache_path = "/etc/httpd/conf/";
		$this->__path_cron_root = '/var/spool/cron/';
		$this->__path_real_etc_root = "/etc/";



		$this->__path_httpd_root = "/home/httpd";
		$this->__path_client_root = "/home/hypervm/client";
		$this->__path_mail_root = "/home/hypervm/mail";
		$this->__path_hypervm_httpd_root = "/home/hypervm/httpd";
		$this->__path_lxlabs_base = "/usr/local/lxlabs";
		$this->__path_program_etc = "/usr/local/lxlabs/hypervm/etc/";
		$this->__path_program_root = "/usr/local/lxlabs/hypervm";
		$this->__path_program_htmlbase = "/usr/local/lxlabs/hypervm/httpdocs";
		$this->__path_php_path = $this->__path_lxlabs_base . "/ext/php/php";
	}

	$this->__var_program_name = 'hypervm';

	$this->__path_serverfile = $this->__path_lxlabs_base . "/hypervm/serverfile";
	$this->__path_download_dir = $this->__path_lxlabs_base . "/hypervm/download";
	$this->__path_program_start_vps_flag = "{$this->__path_program_root}/etc/flag/start_vps.flg";

	//Default Values that will be overrriden in the hypervmconf file.
	$this->__var_programuser_dns = 'named';
	$this->__path_named_chroot = "/var/named/chroot/";
	$this->__var_programname_web = 'httpd';
	$this->__var_programname_ftp = 'pure-ftpd';
	$this->__var_programname_syslog = 'syslog';
	//$this->__var_programname_mysql = 'mysqld';
	$this->__var_programname_dns = 'named';
	$this->__var_programname_mmail = 'qmail';
	$this->__var_programname_imap = 'courier';
	$this->__var_programuser_dns = 'named';

	$this->__var_no_sync = false;

	$this->__path_ssl_root = $this->__path_hypervm_httpd_root . "/ssl";

	$this->__var_mssqlport = '7773';
	$this->__var_local_port = '8886';
	$this->__var_remote_port = '8889';

	$conffile = "$this->__path_program_root/file/conf/os.conf";

	if (!file_exists($conffile)) {

		$ret = findOperatingSystem();
		$os = $ret['os'];

		copy("$this->__path_program_root/file/conf/$os.conf", $conffile);
	}

	$this->__var_exit_char = "___...___";
	$this->__var_remote_char = "_._";

	$this->__var_connection_type = "tcp";
	include_once $conffile;

	if (!$conf) {
		print("Error Reading Config File...\n");
		exit;
	}

	foreach($conf as $k => $v) {
		if (!is_array($v)) {
			print("Error in Config File Syntax... n");
			exit;
		}

		$vvarcore = "__{$k}_";
		foreach($v as $nk=> $nv) {
			$vvar =  $vvarcore . $nk;
			$this->$vvar = $nv;
		}
	}


	$this->__path_dbschema = "$this->__path_program_root/file/.db_schema";

	
	//$this->__var_dbf = $this->__path_program_etc . "/conf/lxa-" . $this->__ver_major . ".db";
	$this->__var_dbf = "hypervm1_0";

	$this->__path_super_pass = $this->__path_program_etc . "/conf/superadmin.pass";
	$this->__path_admin_pass = $this->__path_program_etc . "/conf/hypervm.pass";
	$this->__path_master_pass = $this->__path_program_etc . "/conf/hypervm.pass";

	$this->__path_named_realpath = "$this->__path_named_chroot/$this->__path_named_path";

	$this->__var_super_user = "lxasuper";
	$this->__var_admin_user = "hypervm";

	$this->__path_slave_db = $this->__path_program_etc . "/conf/slave-db.db";
	//$this->__path_supernode_db = $this->__path_program_etc . "/conf/supernode-db.db";
	$this->__path_supernode_db = "lxasuper";

	$this->__path_sql_file_supernode  = "$this->__path_program_htmlbase/sql/supernode";
	$this->__path_sql_file  = "$this->__path_program_htmlbase/sql/full";
	$this->__path_sql_file_common  = "$this->__path_program_htmlbase/sql/common";

	$this->__path_lxmisc = $this->__path_program_root . "/sbin/lxmisc";

	$this->__path_mailman = "/var/mailman";
	$this->__var_action_class = array('vps');


	$this->__var_rolelist = array("web", "mail", "dns", "secondary_master");
	//$this->__var_dblist = array("mysql", "pgsql", "mssql");
	$this->__var_dblist = array("mysql");

	$this->__var_error_file = "__path_program_root/httpdocs/.php.err";

	$this->__var_ticket_subcategory = null;
}
	
}
