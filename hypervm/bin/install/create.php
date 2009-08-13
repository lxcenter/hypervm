<?php 
include_once "htmllib/lib/include.php";
include_once "htmllib/lib/initlib.php";


create_main();

function create_main()
{
	global $argc, $argv;
	global $gbl, $sgbl, $login, $ghtml; 
	$opt = parse_opt($argv);

	lxfile_mkdir("{$sgbl->__path_program_etc}/conf");
	lxfile_mkdir("{$sgbl->__path_program_root}/pid");
	lxfile_mkdir("{$sgbl->__path_program_root}/log");
	lxfile_mkdir("{$sgbl->__path_httpd_root}");

	os_create_program_service();

	if (isset($opt['admin-password'])) {
		$admin_pass = $opt['admin-password'];
	} else {
		$admin_pass = 'admin';
	}


	if ($opt['install-type'] == 'master') {
		create_mysql_db('master', $opt, $admin_pass);
		create_database();
		create_general();
		add_admin($admin_pass);
		create_servername();
		lxshell_return("__path_php_path", "../bin/collectquota.php");
		print("Updating the system. Will take a while\n");
		system("/usr/local/lxlabs/ext/php/php ../bin/common/tmpupdatecleanup.php --type=master");
	} else if ($opt['install-type'] == 'slave') {
		init_slave($admin_pass);
		print("Updating the system. Will take a while\n");
		system("/usr/local/lxlabs/ext/php/php ../bin/common/tmpupdatecleanup.php --type=slave");
	} else {
		print("Unknown Install type\n");
		flush();
	}

	system("rm -f /etc/sysconfig/network-scripts/ifcfg-*-range*");
	//system("$sgbl->__path_php_path ../bin/misc/fixcentos5xen.php");
	//os_fix_some_permissions();
	system("cp ../sbin/lxxen ../sbin/lxopenvz /usr/bin");
	system("chmod 4755 /usr/bin/lxxen /usr/bin/lxopenvz");
	//os_set_iis_ftp_root_path();
}
