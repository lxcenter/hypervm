<?php 

function fixExtraDB()
{
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
	$sq->rawQuery("update vps set kloxo_flag = lxadmin_flag where kloxo_flag is null");
	$sq->rawQuery("update vps set kloxo_flag = lxadmin_flag where kloxo_flag = ''");


	db_set_default('vps', 'kloxo_flag', 'on');
	db_set_default('vps', 'priv_q_managedns_flag', 'on');
	db_set_default('client', 'priv_q_managedns_flag', 'on');
	db_set_default('vps', 'priv_q_managereversedns_flag', 'on');
	db_set_default('client', 'priv_q_managereversedns_flag', 'on');
	db_set_default('client', 'priv_q_centralbackup_flag', 'on');

	db_set_default('vps', 'priv_q_rebuildvps_flag', 'on');
	db_set_default('client', 'priv_q_rebuildvps_flag', 'on');
	db_set_default('ippool', 'freeflag', 'on');
	initDbLoginPre();
	migrateResourceplan('vps');
	$sq->rawQuery("update resourceplan set realname = nname where realname = ''");
	$sq->rawQuery("update resourceplan set realname = nname where realname is null");
	$sq->rawQuery("alter table dns change ser_dns_record_a ser_dns_record_a longtext");
	lxshell_php("../bin/common/fixresourceplan.php");
	call_with_flag("convert_favorite");
}


function doUpdateExtraStuff()
{
	global $gbl, $sgbl, $login, $ghtml; 
	
	lxfile_mkdir("__path_program_etc/flag");
	convertIpaddressToComa();
	print("Fix extra database\n");
	fixExtraDB();
	//$wel = lfile_get_contents("../file/welcome.txt");
	//$clname = createParentName('client', 'admin');
	//$sq->rawQuery("update notification set text_newaccountmessage = '$wel' where nname = '$clname'");
	print("Set some defaults\n");
	db_set_default('vps', 'ttype', 'openvz');
	db_set_default('pserver', 'coma_psrole_a', 'vps');
	db_set_default("vps", "swapdiskname", "vm.swap", "ttype = 'xen'");
	db_set_default("vps", "maindiskname", "root.img", "ttype = 'xen'");
	db_set_default('vps', 'corerootdir', '/vz/private', "ttype = 'openvz'");
	db_set_default("vps", "corerootdir", "/home/xen", "ttype = 'xen'");

print("Fixing database passwords\n");
	$a = null;
	fix_mysql_root_password('localhost');
	$dbadmin = new Dbadmin(null, 'localhost', "mysql___localhost");
	$dbadmin->get();
	$pass = $dbadmin->dbpassword;
	$a['mysql']['dbpassword'] = $pass;
	slave_save_db("dbadmin", $a);

print("Fixing OS template permissions\n");
	lxfile_unix_chmod_rec("/vz/template/cache/", "0755");
	lxfile_unix_chmod_rec("/home/hypervm/xen/template/", "0755");

	call_with_flag("dofixParentClname");
	print("Check License\n");
	passthru("$sgbl->__path_php_path htmllib/lbin/getlicense.php");
	print("Fix OpenVZ resources\n");
	fixOpenVZResource();
	print("Move clients to client of needed\n");
	move_clients_to_client();
	print("create backup dirs\n");
	add_vps_backup_dir();
	print("Parse SQL Data\n");
	parse_sql_data();
	print("Fix IP POOL\n");
	lxshell_return("__path_php_path", "../bin/fix/fixippool.php");
	print("Fix IP adresses in database\n");
	fix_ipaddress_column_type();
	fix_vmipaddress();
	print("Checking HIB template\n");
	get_kloxo_ostemplate();
	print("Set admin email\n");
	save_admin_email();
	print("Checking Skin Images\n");
	copy_image();
	system("mysql -u hypervm -p`cat ../etc/conf/hypervm.pass` hypervm1_0 < ../file/interface/interface_template.dump");

	if (lxfile_exists("/etc/init.d/libvirtd")) {
	print("Make sure libvirtd is not started after reboot\n");
	system("chkconfig libvirtd off 2>/dev/null");
	}
	
if (is_openvz()) {
print("Fixing Base OS templates\n");
	if (!lxfile_real("/vz/template/cache/centos-5-i386-afull.tar.gz")) {
		system("mkdir -p /vz/template/cache/ ; cd /vz/template/cache/ ; rm centos-5-i386-afull.tar.gz; wget download.lxcenter.org/download/openvztemplates/base/centos-5-i386-afull.tar.gz ");
			system("rm /vz/template/cache/index.html* 2>/dev/null");
	}
	} else {
	if (!lxfile_real("/home/hypervm/xen/template/centos-5-i386-afull.tar.gz")) {
	system("mkdir -p /home/hypervm/xen/template ; cd /home/hypervm/xen/template/ ; rm centos-5-i386-afull.tar.gz;  wget download.lxcenter.org/download/xentemplates/base/centos-5-i386-afull.tar.gz ");
	system("rm /home/hypervm/xen/template/index.html* 2>/dev/null");
	}
	}
	print("Fix SSL\n");
	fix_self_ssl();
	print("Fix database password\n");
	critical_change_db_pass();
	print("Delete old repo's\n");
if (lxfile_exists("/etc/yum.repos.d/lxlabs.repo")) {
		lxfile_mv("/etc/yum.repos.d/lxlabs.repo","/etc/yum.repos.d/lxlabs.repo.lxsave");
		system("rm -f /etc/yum.repos.d/lxlabs.repo");
	print("Removed lxlabs.repo\n");
		}
}

function fix_ipaddress_column_type()
{
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

	$file = "__path_program_root/etc/newnewfixed_openvz_resource";
	if (lxfile_exists($file)) {
		return;
	}

	lxfile_touch($file);

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
	$file = "__path_program_root/etc/flag/newfixed_vmipaddress";
	if (lxfile_exists($file)) { return; }
	lxfile_touch($file);

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
	if (!$ret) {
		return;
	}
	$login->loadAllObjects('vps');
	$list = $login->getList('vps');

	foreach($list as $l) {
		$vpsiplist = $l->getList('vpsipaddress_a');
		if (!$vpsiplist) {
			dprint("No ip for $l->nname\n");
			return;
		}
		$vmlist = null;
		foreach($vpsiplist as $vpsip) {
			$vmip = new vmipaddress_a(null, null, $vpsip->nname);
			$vmlist[$ip->nname] = $vmip;
		}
		$l->vmipaddress_a = $vmlist;
		$l->setUpdateSubaction();
		$l->write();
	}
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

function updateApplicableToSlaveToo()
{
	global $gbl, $sgbl, $login, $ghtml, $osversion; 
	print("Download 3rdparty\n");
    // Fixes #303 and #304
	download_thirdparty();

	print("Installing binaries\n");
	lxfile_cp("__path_program_root/cexe/lxxen", "/usr/bin");
	lxfile_cp("__path_program_root/cexe/lxopenvz", "/usr/bin");
	print("Fixing binaries permissions\n");
	lxfile_generic_chmod("/usr/bin/lxopenvz", "6755");
	lxfile_generic_chmod("/usr/bin/lxxen", "6755");
	print("Install missing rpm packages if any");
	install_if_package_not_exist("rrdtool");
	print("-rrdtool-");
    install_if_package_not_exist("ntfsprogs");
	print("-ntfsprogs-");
	install_if_package_not_exist("parted");
	print("-parted-");
	install_if_package_not_exist("kpartx");
	print("-kpartx-");
	install_if_package_not_exist("dhcp");
	print("-dhcp-");
	install_if_package_not_exist("openssl");
	print("-openssl-");
	install_if_package_not_exist("openssl-devel");
	print("-openssl-devel-");
	system("chkconfig dhcpd on");
	print("\nEnable dhcpd at system startup");
	print("\n");

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

	if (lxfile_exists("/proc/xen")) {
		//lxshell_php("../bin/blocknetbios.phps");
	}

	//system("echo 'hwcap 0 nosegneg' > /etc/ld.so.conf.d/libc6-xen.conf");
	if (lxfile_exists("/var/log/loadvg.log")) {
	lunlink("/var/log/loadvg.log");
	}
	if (lxfile_exists("/etc/vz")) {
		lxfile_cp("__path_program_root/file/sysfile/openvz/ve-vps.basic.conf-sample", "/etc/vz/conf");
		print("Set NEIGHBOUR_DEVS=all to vz.conf");
		vps__openvz::staticChangeConf("/etc/vz/vz.conf", "NEIGHBOUR_DEVS", "all");
	}
	
	print("Fixing openvz repo\n");
	// add openvz.repo
	lxfile_cp("../file/openvz.repo", "/etc/yum.repos.d/openvz.repo");
	print("Fixing lxcenter repo\n");
	// add lxcenter.repo
	$osversion = find_os_version();
	print("- Your OS $osversion\n");
	$cont = our_file_get_contents("../file/lxcenter.repo");
	$cont = str_replace("%distro%", $osversion, $cont);
	our_file_put_contents("/etc/yum.repos.d/lxcenter.repo", $cont);	
	print("Fix RHN");
	fix_rhn_sources_file();
	print("Fix ipconntrack");
	fix_ipconntrack();
	if (lxfile_exists("/home/hypervm/xen/template")) {
			print("Check Xen windows-lxblank.img template\n");
		system("echo hypervm-windows > /home/hypervm/xen/template/windows-lxblank.img");
	}
	print("Fix memory graph\n");
   memoryGraphFix();
	print("Fix permission of closeallinput");
	lxfile_unix_chmod("../cexe/closeallinput", "0755");
	print("Fix LxEtc\n");
	installLxetc();
	print("Check binaries\n");
	system("cp ../sbin/lxrestart /usr/sbin/");
	system("chown root:root /usr/sbin/lxrestart");
	system("chmod 755 /usr/sbin/lxrestart");
	system("chmod ug+s /usr/sbin/lxrestart");
	system("chmod 777 /tmp");
	system("chmod o+t /tmp");
	print("Create script dir\n");
	copy_script();
	if (!lxfile_exists("/usr/local/lxlabs/kloxo/")) {
	print("Remove kloxo things as it should not be here\n");
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
	print("Memory Graph fix not needed\n");
	return;
	}
	lxfile_touch($file);
	system("rm $sgbl->__path_program_root/data/memory/*");
}

function add_vps_backup_dir()
{
	if (lxfile_exists("__path_program_home/vps")) {
		print("VPS backupdir already exist...\n");
		return;
	}

	$sq = new Sqlite(null, 'vps');

	$res = $sq->getTable(array('nname'));
	foreach($res as $r) {
		lxfile_mkdir("__path_program_home/vps/{$r['nname']}/__backup");
	$vpsbackupdirname = $r['nname'];
	print("Backup dir created for $vpsbackupdirname \n");
	}
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