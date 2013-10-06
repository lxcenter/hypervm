<?php

function updatecleanup_main()
{
    global $argc, $argv;
    global $gbl, $sgbl, $login, $ghtml;

    $prognameNice = $sgbl->__var_program_name_nice;

    $opt = parse_opt($argv);

    if ($opt['type'] === 'master') {
        initProgram('admin');
        $flg = "__path_program_start_vps_flag";
        if (!lxfile_exists($flg)) {
            set_login_skin_to_feather();
        }
    } else {
        $login = new Client(null, null, 'update');
    }

    print("Executing UpdateCleanup. This can take a long time. Please be patient\n");
    log_log("update", "Executing Updatecleanup");

    // Do things that is needed before the cleanup starts.
    print("########################################\n");
    print("##        Executing PreCleanup        ##\n");
    print("########################################\n");
    doBeforeUpdate();
    print("########################################\n");
    print("##        Finished PreCleanup         ##\n");
    print("########################################\n");

    if ($opt['type'] === 'master') {
        $sgbl->slave = false;
        if (!is_secondary_master()) {

            print("Update database (if needed)\n");
            updateDatabaseProperly();

            if (call_with_flag("fixExtraDB"))
            {
                print("- Fixed\n");
            }

            print("Update extra issues (if any)\n");
            doUpdateExtraStuff();

            print("Get Driver info\n");
            lxshell_return("__path_php_path", "../bin/common/driverload.php");
        }
        print("Starting Update all slaves\n");
        update_all_slave();
        print("Fix main $prognameNice databasefile\n");
        cp_dbfile();
    } else {
        $sgbl->slave = true;
    }

    if (!is_secondary_master()) {
        print("Starting update cleanups\n");
        cleanupUpdate();
    }

    if (!lxfile_exists($flg))
    {
        lxfile_touch($flg);
    }
}

function doBeforeUpdate()
{
    global $gbl, $sgbl, $login, $ghtml;

    $program = $sgbl->__var_program_name;

    // Cleanup old lxlabs.repo file
    print("Fixing Repo's\n");
    $oldRepoFile = "/etc/yum.repos.d/lxlabs.repo";
    if (lxfile_exists("/etc/yum.repos.d/lxcenter.repo")) {
        if (lxfile_exists($oldRepoFile)) {
            lxfile_mv($oldRepoFile, $oldRepoFile . ".lxsave");
            lxfile_rm($oldRepoFile);
        }
    }

    // Install yum-plugin-replace (New since HyperVM 2.1.0)
    $ret =  install_if_package_not_exist("yum-plugin-replace");
    if ($ret)
    {
        print("Installed RPM package yum-plugin-replace\n");
    }

    // Replace lxphp package (New since HyperVM 2.1.0)
    $ret =  replace_rpm_package("lxphp", "hypervm-core-php");
    if ($ret)
    {
        print("Replaced RPM package lxphp with hypervm-core-php\n");
    }

    // Remove not used dirs/files
    if (lxfile_exists("__path_program_htmlbase/help")) {
        lxfile_rm_rec("__path_program_htmlbase/help");
    }

    // ToDo: Add here new database flag - DT
    // issue at github

}

function cp_dbfile()
{
    global $gbl, $sgbl, $login, $ghtml;

    $progname = $sgbl->__var_program_name;

    lxfile_cp("../sbin/{$progname}db", "/usr/bin/{$progname}db");
    lxfile_generic_chmod("/usr/bin/{$progname}db", "0755");
}

function update_main()
{
	global $argc, $argv;
	global $gbl, $sgbl, $login, $ghtml; 

	debug_for_backend();
	$prognameNice = $sgbl->__var_program_name_nice;
	$login = new Client(null, null, 'upgrade');


	$opt = parse_opt($argv);

	print("Getting Version Info from the Server...\n");
    print("Connecting... Please wait....\n");

    if ((isset($opt['till-version']) && $opt['till-version']) || lxfile_exists("__path_slave_db")) {
		$sgbl->slave = true;
		$upversion = findNextVersion($opt['till-version']);
		$type = 'slave';
	} else {
		$sgbl->slave = false;
		$upversion = findNextVersion();
		$type = 'master';
	}

    $thisversion = $sgbl->__ver_major_minor_release;

	if ($upversion) {
		do_upgrade($upversion);
		print("Upgrade Done!\nStarting the Cleanup.\n");
		flush();
		} else {
		print("$prognameNice is the latest version ($thisversion)\n");
        print("Run 'sh /script/cleanup' if you want restore/fix possible issues.\n");
        exit;
	}


	if (is_running_secondary()) {
		print("Not running the Update Cleanup, because this server is a secondary.\n");
		exit;
	}

    // Needs to be here. So any php.ini change takes immediately effect.
    print("Copy Core PHP.ini\n");
    lxfile_cp("htmllib/filecore/php.ini", "/usr/local/lxlabs/ext/php/etc/php.ini");

	pcntl_exec("/bin/sh", array("../bin/common/updatecleanup-core.sh", "--type=$type"));
	print("$prognameNice is Ready!\n\n");

}



function cleanupUpdate()
{
    global $gbl, $sgbl, $login, $ghtml;

    $prognameNice = $sgbl->__var_program_name_nice;

	print("Checking $prognameNice service\n");
	os_create_program_service();

	print("Checking $prognameNice permissions\n");
	os_fix_lxlabs_permission();

	print("Restart $prognameNice\n");
	os_restart_program();

	print("Start $prognameNice cleanups\n");
	cleanupProcess();
}

function update_all_slave()
{
	$db = new Sqlite(null, "pserver");

	$list = $db->getTable(array("nname"));

	foreach($list as $l) {
		if ($l['nname'] === 'localhost') {
			continue;
		}
		try {
			print("Upgrading Slave {$l['nname']}...\n");
			rl_exec_get(null, $l['nname'], 'remotetestfunc', null);
		} catch (exception $e) {
			print($e->getMessage());
			print("\n");
		}
	}

}

function findNextVersion($lastversion = null)
{
	global $gbl, $sgbl, $login, $ghtml; 

	$thisversion = $sgbl->__ver_major_minor_release;

	$upgrade = null;
	$nlist = getVersionList($lastversion);
	dprintr($nlist);
	$k = 0;
	foreach($nlist as $l) {
		if (version_cmp($thisversion, $l) === -1) {
			$upgrade = $l;
			break;
		}
		$k++;
	}
	if (!$upgrade) {
		return 0;
	}

	print("Updating from $thisversion to $upgrade\n");
	return $upgrade;

}

function do_upgrade($upversion)
{
	global $gbl, $sgbl, $login, $ghtml; 
	if (file_exists("/usr/local/lxlabs/.git")) {
		print("Development system.. Not upgrading --> exit!...\n");
		exit;
	}

	$program = $sgbl->__var_program_name;

	$programfile = "$program-" . $upversion . ".zip";

	lxfile_rm_rec("__path_program_htmlbase/htmllib/script");
	lxfile_rm_rec("__path_program_root/pscript");

	$saveddir = getcwd();
	lxfile_rm_rec("__path_program_htmlbase/download");
	lxfile_mkdir("download");
	chdir("download");
	print("Downloading $programfile.....\n");
	download_source("/$program/$programfile");
	print("Download Done....\n");
	lxshell_unzip("../..", $programfile);
	chdir($saveddir);
}

function move_clients_to_client()
{
	if (lxfile_exists("__path_program_home/client")) {
		return false;
	}
	lxfile_mv_rec("__path_program_home/clients", "__path_program_home/client");
    return true;
}

function download_thirdparty()
{
    global $sgbl;
    $prgm = $sgbl->__var_program_name;

    // TODO: remove this when hypervm 2.1.0 is released
    // Check for git because we dont want a old 2009 package version into hypervm development!
    if (file_exists('/usr/local/lxlabs/.git')) {
        print("Development GIT version found. Skipping download from LxCenter.\n");
    } else {
        // Fixes #303 and #304
        $string = file_get_contents("http://download.lxcenter.org/download/thirdparty/$prgm-version.list");
        if ($string != "") {
            $string = trim($string);
            $string = str_replace("\n", "", $string);
            $string = str_replace("\r", "", $string);
            core_installWithVersion("/usr/local/lxlabs/$prgm/", "$prgm-thirdparty", $string);
        }
    }
}

function fixExtraDB()
{

    print("Fix database\n");

    $sq = new Sqlite(null, 'client');

    $sq->rawQuery("update monitorserver set priv_q_monitorport_num = 'Unlimited'");
    $sq->rawQuery("update client set priv_q_monitorport_num = 'Unlimited' where nname = 'admin'");
    $sq->rawQuery("update client set priv_q_monitorserver_num = 'Unlimited' where nname = 'admin'");
    $sq->rawQuery("update client set priv_q_vmipaddress_a_num = 'Unlimited' where nname = 'admin'");
    $sq->rawQuery("update client set priv_q_backup_num = 'Unlimited' where nname = 'admin'");
    $sq->rawQuery("update client set priv_q_process_usage = 'Unlimited' where nname = 'admin'");
    $sq->rawQuery("update client set priv_q_realmem_usage = 'Unlimited' where nname = 'admin'");
    $sq->rawQuery("update client set priv_q_backup_num = 'Unlimited' where nname = 'admin'");
    $sq->rawQuery("update client set priv_q_ncpu_usage = 'Unlimited' where nname = 'admin'");
    $sq->rawQuery("update client set priv_q_ioprio_usage = 'Unlimited' where nname = 'admin'");
    $sq->rawQuery("update client set priv_q_cpuunit_usage = 'Unlimited' where nname = 'admin'");
    $sq->rawQuery("update client set priv_q_swap_usage = 'Unlimited' where nname = 'admin'");
    $sq->rawQuery("update client set priv_q_backup_flag = 'On' where nname = 'admin'");
    $sq->rawQuery("update client set priv_q_ip_manage_flag = 'on' where nname = 'admin'");
    $sq->rawQuery("update client set priv_q_iptables_flag = 'On' where nname = 'admin'");
    $sq->rawQuery("update client set priv_q_vps_add_flag = 'On' where nname = 'admin'");
    $sq->rawQuery("update client set priv_q_vps_limit_flag = 'On' where nname = 'admin'");
    $sq->rawQuery("update client set priv_q_secondlevelquota_flag = 'On' where nname = 'admin'");
    $sq->rawQuery("update client set priv_q_centralbackup_flag = 'On' where nname = 'admin'");
    $sq->rawQuery("update pserver set parent_clname = 'client-admin'");
    $sq->rawQuery("update vps set iid = vpsid where iid is null");
    $sq->rawQuery("update vps set iid = vpsid where iid = ''");
    $sq->rawQuery("update vps set priv_q_swap_usage = 2 * priv_q_realmem_usage where priv_q_swap_usage = ''");
    $sq->rawQuery("update vps set priv_q_swap_usage = 2 * priv_q_realmem_usage where priv_q_swap_usage is null");
    $sq->rawQuery("update vps set priv_q_swap_usage = 'Unlimited' where priv_q_swap_usage = 0");
    $sq->rawQuery("update vps set priv_q_centralbackup_flag = centralbackup_flag where priv_q_centralbackup_flag = ''");
    $sq->rawQuery("update vps set priv_q_centralbackup_flag = centralbackup_flag where priv_q_centralbackup_flag is null");

    db_set_default('vps', 'kloxo_flag', 'on');
    db_set_default('vps', 'priv_q_managedns_flag', 'on');
    db_set_default('vps', 'priv_q_managereversedns_flag', 'on');
    db_set_default('vps', 'priv_q_rebuildvps_flag', 'on');

    db_set_default('client', 'priv_q_managedns_flag', 'on');
    db_set_default('client', 'priv_q_managereversedns_flag', 'on');
    db_set_default('client', 'priv_q_centralbackup_flag', 'on');
    db_set_default('client', 'priv_q_rebuildvps_flag', 'on');

    db_set_default('ippool', 'freeflag', 'on');

    db_set_default('vps', 'ttype', 'openvz');
    db_set_default("vps", "swapdiskname", "vm.swap", "ttype = 'xen'");
    db_set_default("vps", "maindiskname", "root.img", "ttype = 'xen'");
    db_set_default('vps', 'corerootdir', '/vz/private', "ttype = 'openvz'");
    db_set_default("vps", "corerootdir", "/home/xen", "ttype = 'xen'");

    db_set_default('pserver', 'coma_psrole_a', 'vps');

    initDbLoginPre();

    print("Fix database (Resource plans)\n");

    migrateResourceplan('vps');

    $sq->rawQuery("update resourceplan set realname = nname where realname = ''");
    $sq->rawQuery("update resourceplan set realname = nname where realname is null");
    $sq->rawQuery("alter table dns change ser_dns_record_a ser_dns_record_a longtext");

    lxshell_php("../bin/common/fixresourceplan.php");

    print("Fix database (Favorites)\n");

    call_with_flag("convert_favorite");

    lxfile_touch($file);

}


function doUpdateExtraStuff()
{
    global $gbl, $sgbl, $login, $ghtml;

    lxfile_mkdir("__path_program_etc/flag");

    print("Check database password\n");
    $a = null;
    fix_mysql_root_password('localhost');
    $dbadmin = new Dbadmin(null, 'localhost', "mysql___localhost");
    $dbadmin->get();
    $pass = $dbadmin->dbpassword;
    $a['mysql']['dbpassword'] = $pass;
    slave_save_db("dbadmin", $a);

    print("Check the core database\n");
    parse_sql_data();

    if (call_with_flag("convertIpaddressToComa"))
    {
        print("Converted IP addresses in database\n");

    }

    if (call_with_flag("fixExtraDB"))
    {
        print("- Fixed\n");
    }

    print("Set OS template permissions\n");
    if (is_openvz())
    {
        lxfile_unix_chmod_rec("/vz/template/cache/", "0755");
    } else {
        lxfile_unix_chmod_rec("/home/hypervm/xen/template/", "0755");
    }

    call_with_flag("dofixParentClname");

    print("Get License\n");
    // ToDo: Why is this called this way....
    passthru("$sgbl->__path_php_path htmllib/lbin/getlicense.php");

    if (is_openvz()) {
        print("Check OpenVZ resources\n");
        if (call_with_flag("fixOpenVZResource"))
        {
            print("- Fixed\n");
        }
    }

    if (move_clients_to_client()) {
        print("Renamed clients directory to client.\n");
    }

    print("Checking backup dirs\n");
    if (!add_vps_backup_dir())
    {
        print ("- Everything is fine.\n");
    }

    print("Fix IP POOL\n");
    lxshell_return("__path_php_path", "../bin/fix/fixippool.php");

    if (call_with_flag("fix_ipaddress_column_type")) {
        print("Fixed IP address column in database\n");
    }

    if (call_with_flag("fix_vmipaddress")) {
        print("Fixed VM IP addresses in database\n");
    }

    print("Checking HIB template\n");
    get_kloxo_ostemplate();

    if (db_get_value("client", "admin", "contactemail"))
    {
        print("Set admin email\n");
        save_admin_email();
    }
    // Unknown usage within HyperVM, anyone can tell what this is doing?
    $file = "__path_program_root/etc/fixed_interface_template_sql";
    if (lxfile_exists($file)) {
        lxfile_touch($file);
        print("Check Interface Template (database)\n");
        system("mysql -u hypervm -p`cat ../etc/conf/hypervm.pass` hypervm1_0 < ../file/interface/interface_template.sql");
    }

    if (lxfile_exists("/etc/init.d/libvirtd")) {
        print("Make sure libvirtd is not started after reboot\n");
        system("chkconfig libvirtd off 2>/dev/null");
    }

    if (is_openvz()) {
        print("Checking for Base default OS template\n");
        $OSTemplateDir = "/vz/template/cache";
        $defaultOSTemplate = "centos-6-x86.tar.gz";
        $defaultOSTemplateName = "centos-6-x86";

        if (!lxfile_exists($OSTemplateDir))
        {
            lxfile_mkdir($OSTemplateDir);
        }

        if (!lxfile_real("$OSTemplateDir/$defaultOSTemplate"))
        {
            lxfile_rm("$OSTemplateDir/$defaultOSTemplate");
            system("cd $OSTemplateDir/ ; wget download.lxcenter.org/download/openvztemplates/base/$defaultOSTemplate");
            system("rm $OSTemplateDir/index.html* 2>/dev/null");
            system("rm $OSTemplateDir/robots.txt* 2>/dev/null");
        }

        // Added in HyperVM 2.1.0
        if (lxfile_exists("/usr/sbin/vztmpl-dl"))
        {
        print("Checking for latest version of $defaultOSTemplateName at OpenVZ.org website\n");
        $res = system("/usr/sbin/vztmpl-dl --update $defaultOSTemplateName 2>/dev/null");
        dprint("res: $res\n");
        }

    } else {
        if (!lxfile_real("/home/hypervm/xen/template/centos-5-i386.tar.gz")) {
            system("mkdir -p /home/hypervm/xen/template ; cd /home/hypervm/xen/template/ ; rm centos-5-i386.tar.gz;  wget download.lxcenter.org/download/xentemplates/base/centos-5-i386.tar.gz ");
            system("rm /home/hypervm/xen/template/index.html* 2>/dev/null");
            system("rm /home/hypervm/xen/template/robots.txt* 2>/dev/null");
        }
    }


    print("Check for old critical database password bug\n");
    if (critical_change_db_pass()) {
        print("- Fixed critical database password bug!!!\n");
    } else {
        print("- Good! Already bug free :-)\n");
    }

    if (lxfile_exists("/etc/yum.repos.d/lxlabs.repo")) {
        print("Delete old repo's\n");
        lxfile_mv("/etc/yum.repos.d/lxlabs.repo","/etc/yum.repos.d/lxlabs.repo.lxsave");
        system("rm -f /etc/yum.repos.d/lxlabs.repo");
        print("Removed lxlabs.repo\n");
    }
}

function fix_ipaddress_column_type()
{
    global $gbl, $sgbl, $login, $ghtml;

    // ToDo: is this also the case when the database is created at install time? Else this can be removed in HyperVM 2.1.0 release
    $sq = new Sqlite(null, 'vps');
    $sq->rawQuery('alter table vps modify coma_vmipaddress_a text');
}

function get_kloxo_ostemplate()
{
    $ver = getHIBversion();
    if (!$ver) {
        return;
    }

    if (is_openvz()) {
        if (lxfile_exists("/vz/template/cache")) {
            if (!lxfile_real("/vz/template/cache/centos-5-i386-hostinabox$ver.tar.gz")) {
                system("cd /vz/template/cache/ ;rm -f centos-?-i386-lxadmin*.tar.gz ; rm -f centos-?-i386-hostinabox*.tar.gz; wget download.lxcenter.org/download/openvztemplates/base/centos-5-i386-hostinabox$ver.tar.gz");
            }
        }
    } else {
        if (lxfile_exists("/home/hypervm/xen/template/")) {
            if (!lxfile_nonzero("/home/hypervm/xen/template/centos-5-i386-hostinabox$ver.tar.gz")) {
                system("cd /home/hypervm/xen/template/ ; rm -f centos-?-i386-lxadmin*.tar.gz; rm -f centos-?-i386-hostinabox*.tar.gz; wget download.lxcenter.org/download/xentemplates/base/centos-5-i386-hostinabox$ver.tar.gz");
            }
        }
    }

}

function fix_ipconntrack()
{
    addLineIfNotExistInside("/etc/sysctl.conf", "net.ipv4.ip_conntrack_max=32760", null);
    lxshell_return("sysctl", "-p");

}

function fixOpenVZResource()
{
    global $gbl, $sgbl, $login, $ghtml;

    $login->loadAllVps();
    $list = $login->getList('vps');

    foreach($list as $l) {
        if ($l->isXen()) {
            continue;
        }

        $l->setUpdateSubaction('fix_everything');
        try {
            $l->was();
        } catch (Exception $e) {
        }
    }
}

function fix_vmipaddress()
{

    $sq = new Sqlite(null, "vps");
    $res = $sq->getTable(array('nname', 'coma_vmipaddress_a'));
    foreach($res as $r) {
        $ip = $r['coma_vmipaddress_a'];
        $ip = trim($ip);
        if (!$ip) {
            continue;
        }
        $iplist = explode(",", $ip);

        foreach($iplist as &$__ip) {
            $__ip = trim($__ip);
        }

        $ip = implode(",", $iplist);
        $ip = ",$ip,";
        $sq->rawQuery("update vps set coma_vmipaddress_a = '$ip' where nname = '{$r['nname']}'");
    }
}

function convertIpaddressToComa()
{
    global $gbl, $sgbl, $login, $ghtml;
    initProgram('admin');

    $login->loadAllObjects('vps');
    $VPSList = $login->getList('vps');

    if (!isset($VPSList)) { return false; };

    foreach($VPSList as $Convert) {

        $vpsiplist = $Convert->getList('vpsipaddress_a');
        if (!$vpsiplist) {
            dprint("No ip for $Convert->nname\n");
            return null;
        }
        $vmlist = null;
        foreach($vpsiplist as $vpsip) {
            $vmip = new vmipaddress_a(null, null, $vpsip->nname);
            $vmlist[$ip->nname] = $vmip;
        }
        $Convert->vmipaddress_a = $vmlist;
        $Convert->setUpdateSubaction();
        $Convert->write();
    }
    return null;
}

function our_file_get_contents($file)
{
    $string = null;

    $fp = fopen($file, "r");

    if (!$fp) {
        return null;
    }


    while(!feof($fp)) {
        $string .= fread($fp, 8192);
    }
    fclose($fp);
    return $string;

}

function our_file_put_contents($file, $contents, $appendflag = false)
{

    if ($appendflag) {
        $flag = "a";
    } else {
        $flag = "w";
    }

    $fp = fopen($file, $flag);

    if (!$fp) {
        return null;
    }

    fwrite($fp, $contents);

    fclose($fp);
}

function find_os_version()
{
    if (file_exists("/etc/fedora-release")) {
        $release = trim(file_get_contents("/etc/fedora-release"));
        $osv = explode(" ", $release);
        if (strtolower($osv[1]) === 'core') {
            $osversion = "fedora-" . $osv[3];
        } else {
            $osversion = "fedora-" . $osv[2];
        }

        return $osversion;
    }
    if (file_exists("/etc/redhat-release")) {
        $release = trim(file_get_contents("/etc/redhat-release"));
        $osv = explode(" ", $release);
        if(isset($osv[6])) {
            $osversion = "rhel-" . $osv[6];
        } else{
            $oss = explode(".", $osv[2]);
            $osversion = "centos-" . $oss[0];
        }
        return $osversion;
    }


    print("This Operating System is Currently Not supported.\n");
    exit;
}

function cleanupProcess()
{
    global $gbl, $sgbl, $login, $ghtml, $osversion;

    print("Download 3rdparty\n");
    // Fixes #303 and #304
    download_thirdparty();

    print("Installing binaries\n");
    if (is_openvz())
    {
        lxfile_cp("__path_program_root/cexe/lxopenvz", "/usr/bin");
    } else {
        lxfile_cp("__path_program_root/cexe/lxxen", "/usr/bin");
    }
    print("Fixing binaries permissions\n");
    if (is_openvz())
    {
        lxfile_generic_chmod("/usr/bin/lxopenvz", "6755");
    } else {
        lxfile_generic_chmod("/usr/bin/lxxen", "6755");
    }
    print("Checking for missing RPM packages...\n");

    $ret = install_if_package_not_exist("rrdtool");
    if ($ret) { print("- Installed rrdtool\n"); }

    if (!is_openvz())
    {
        $ret = install_if_package_not_exist("ntfsprogs");
        if ($ret) { print("- Installed ntfsprogs\n"); }
        $ret = install_if_package_not_exist("parted");
        if ($ret) { print("- Installed parted\n"); }
        $ret = install_if_package_not_exist("kpartx");
        if ($ret) { print("- Installed kpartx\n"); }
    }

    $ret = install_if_package_not_exist("openssl");
    if ($ret) { print("- Installed openssl\n"); }

    $ret = install_if_package_not_exist("openssl-devel");
    if ($ret) { print("- Installed openssl-devel\n"); }

    if (!is_openvz())
    {
        $ret = install_if_package_not_exist("dhcp");
        if ($ret) { print("- Installed dhcp\n"); }
        system("chkconfig dhcpd on");
        print("Enabled dhcpd at system startup\n");
    }

    if (!is_openvz())
    {
      if (lxfile_exists("/etc/xen")) {
            lxfile_mkdir("/etc/xen/hypervm");
            if (!lxfile_exists("/boot/hypervm-xen-vmlinuz")) {
                system("cd /boot ; ln -sf vmlinuz-2.6-xen hypervm-xen-vmlinuz; ln -sf initrd-2.6-xen.img hypervm-xen-initrd.img");
            }

            $list = lscandir_without_dot("/etc/xen/auto");
            foreach($list as $l) {
                $dir = strtil($l, ".cfg");
                lunlink("/etc/xen/auto/$l");
                if (lxfile_exists("/home/xen/$dir/$l")) {
                    lxfile_symlink("/home/xen/$dir/$l", "/etc/xen/auto/$l");
                }
            }
        }

        if (lxfile_exists("/home/hypervm/xen/template")) {
            print("Check Xen windows-lxblank.img template\n");
            system("echo hypervm-windows > /home/hypervm/xen/template/windows-lxblank.img");
        }

    }
    if (lxfile_exists("/var/log/loadvg.log")) {
        lunlink("/var/log/loadvg.log");
    }
    if (lxfile_exists("/etc/vz")) {
        lxfile_cp("__path_program_root/file/sysfile/openvz/ve-vps.basic.conf-sample", "/etc/vz/conf");
        print("Set NEIGHBOUR_DEVS=all to vz.conf\n");
        vps__openvz::staticChangeConf("/etc/vz/vz.conf", "NEIGHBOUR_DEVS", "all");
    }

    $osversion = find_os_version();

    // Populate openvz repository
    // The repository files will be updated in the future with rpm package
    if (!lxfile_exists("/etc/yum.repos.d/openvz.repo")) {
        print("Installing openvz repo for $osversion\n");

        if (is_centossix()) {
            lxfile_cp("../file/centos-6-openvz.repo.template", "/etc/yum.repos.d/openvz.repo");
        } else {
            lxfile_cp("../file/centos-5-openvz.repo.template", "/etc/yum.repos.d/openvz.repo");
        }
    }

    // Populate lxcenter repository
    // The repository files will be updated in the future with rpm package
    if (!lxfile_exists("/etc/yum.repos.d/lxcenter.repo")) {
        print("Installing lxcenter repo for $osversion\n");
        $cont = our_file_get_contents("../file/lxcenter.repo");
        $cont = str_replace("%distro%", $osversion, $cont);
        our_file_put_contents("/etc/yum.repos.d/lxcenter.repo", $cont);
    }

    print("Fix RHN\n");
    fix_rhn_sources_file();

    print("Fix ipconntrack\n");
    fix_ipconntrack();

    print("Fix memory graph\n");
    memoryGraphFix();

    print("Fix permission of closeallinput\n");
    lxfile_unix_chmod("../cexe/closeallinput", "0755");
    print("Check binaries\n");
    system("cp ../sbin/lxrestart /usr/sbin/");
    system("chown root:root /usr/sbin/lxrestart");
    system("chmod 755 /usr/sbin/lxrestart");
    system("chmod ug+s /usr/sbin/lxrestart");
    system("chmod 777 /tmp");
    system("chmod o+t /tmp");
    print("Create script dir\n");
    copy_script();
    if (lxfile_exists("/usr/local/lxlabs/kloxo/")) {
        print("Remove /usr/local/lxlabs/kloxo/ as it should not be here!\n");
        system("rmdir /usr/local/lxlabs/kloxo/httpdocs/ >/dev/null 2>&1");
        system("rmdir /usr/local/lxlabs/kloxo/ >/dev/null 2>&1");
    }
    if (!lxfile_exists("/var/named/chroot/etc/kloxo.named.conf")) {
        if (lxfile_exists("/var/named/chroot/etc/lxadmin.named.conf")) {
            remove_line("/var/named/chroot/etc/named.conf", "lxadmin.named.conf");
            $pattern='include "/etc/kloxo.named.conf";';
            $file = "/var/named/chroot/etc/named.conf";
            $comment = "//Kloxo";
            @ addLineIfNotExistInside($file, $pattern, $comment);
            @ lxfile_mv("/var/named/chroot/etc/lxadmin.named.conf", "/var/named/chroot/etc/kloxo.named.conf");
        }
    }
}

function memoryGraphFix()
{
    global $gbl, $sgbl, $login, $ghtml;
    $file = "__path_program_root/etc/openvzmemorygraphfix";
    if (lxfile_exists($file)) {
        return;
    }
    lxfile_touch($file);
    system("rm $sgbl->__path_program_root/data/memory/*");
    print("Memory Graph fixed\n");
}

function add_vps_backup_dir()
{

    $sq = new Sqlite(null, 'vps');

    $res = $sq->getTable(array('nname'));
    if (isset($res)) {
        foreach($res as $r) {
            if (!lxfile_exists("__path_program_home/vps/{$r['nname']}/__backup")) {
            lxfile_mkdir("__path_program_home/vps/{$r['nname']}/__backup");
            $vpsbackupdirname = $r['nname'];
            print("- Backup dir created for $vpsbackupdirname \n");
            }
        }
        return true;
    }
    return false;
}

function convert_ipaddress()
{
    global $gbl, $sgbl, $login, $ghtml;

    initProgram('admin');
    $login->loadAllVps();

    $vpslist = $login->getList('vps');

    foreach($vpslist as $vps) {

        if (isset($vps->vmipaddress_a) && is_array($vps->vmipaddress_a)) {
            continue;
        }

        $iplist = $vps->getList('vpsipaddress');

        $vpsinternalip = null;
        foreach($iplist as $ip) {
            $internalip = new vmipaddress_a(null, null, $ip->ipaddress);
            $vpsinternalip[$internalip->nname] = $internalip;
        }
        $vps->vmipaddress_a = $vpsinternalip;
        $vps->setUpdateSubaction();
        $vps->write();
    }

}