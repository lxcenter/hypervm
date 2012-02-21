<?php 

class vps__openvz extends Lxdriverclass {

	static function find_memoryusage()
	{
		$list = lfile("/proc/user_beancounters");
		foreach($list as $l) {
			$l = trimSpaces($l);
	
			if (csa($l, ":")) {
				$vpsid = strtil($l, ":");
				$l = strfrom($l, " ");
			}
	
			if (!csb($l, "privvmpages")) { 
				continue;
			}
	
			$load = explode(" ", $l);
			$mem = round(($load[1]/256) * 1024 * 1024);
			execRrdSingle("memory", "GAUGE", "openvz-$vpsid", $mem);
		}
	}

	static function find_cpuusage()
	{
		$list = lfile("/proc/vz/vestat");
		foreach($list as $l) {
			if (csa($l, "Version")) {
				continue;
			}
	
			if (csa($l, "VEID")) {
				continue;
			}
	
			$l = trimSpaces($l);
			$load = explode(" ", $l);
			$cpu = $load[1] + $load[2] + $load[3];
			execRrdSingle("cpu", "DERIVE", "openvz-$load[0]", $cpu);
		}
	}

	static function find_traffic()
	{
		global $global_dontlogshell;
	
	
	
		$res = lxshell_output("iptables", "-nvx", "-L", "FORWARD");
	
		$res = explode("\n", $res);
	
	
		$outgoing = null;
		foreach($res as $r) {
			$r = trimSpaces($r);
	
			$list = explode(' ', $r);
			if (!isset($list[7])) {
				continue;
			}
	
			if (csb($list[7], "0.0.0")) {
				// Just make sure that we don't calculate this thing twice, which would happen if there are multiple copies of the same rule. So mark that we have already read it in the sourcelist.
				if (!isset($sourcelist[$list[6]])) {
					$outgoing[$list[6]][] = $list[1];
					$sourcelist[$list[6]] = true;
				}
			} else if(csb($list[6], "0.0.0")) {
				if (!isset($dstlist[$list[7]])) {
					$incoming[$list[7]][] = $list[1];
					$dstlist[$list[7]] = true;
				}
			}
		}
	
		if (!$outgoing) {
			return;
		}
	
		if (!isset($incoming)) {
			return;
		}
	
	
		$realtotalincoming = calculateRealTotal($incoming);
		$realtotaloutgoing = calculateRealTotal($outgoing);
	
		foreach($realtotaloutgoing as $k => $v) {
	
			$vpsid = self::get_vpsid_from_ipaddress($k);
	
			if ($vpsid === 0) {
				continue;
			}
	
			if (!isset($vpsoutgoing[$vpsid])) { $vpsoutgoing[$vpsid] = 0; }
			if (!isset($vpsincoming[$vpsid])) { $vpsincoming[$vpsid] = 0; }
	
			$vpsoutgoing[$vpsid] += $realtotaloutgoing[$k];
			$vpsincoming[$vpsid] += $realtotalincoming[$k];
		}
	
	
		foreach($vpsincoming as $k => $v) {
			$tot = $vpsincoming[$k] + $vpsoutgoing[$k];
			execRrdTraffic("openvz-$k", $tot, "-$vpsincoming[$k]", $vpsoutgoing[$k]);
			$stringa[] = time() . " " . date("d-M-Y:H:i") . " openvz-$k $tot $vpsincoming[$k] $vpsoutgoing[$k]";
		}
	
		if ($stringa) {
			$string = implode("\n", $stringa);
			lfile_put_contents("__path_iptraffic_file", "$string\n", FILE_APPEND);
		}
		lxshell_return("iptables", "-Z");
	}

	static function get_vpsid_from_ipaddress($ip)
	{
		static $res;
	
		if (!$res) {
			$res = lxshell_output('vzlist', '-H', '-o', 'vpsid,ip');
		}
	
		$list = explode("\n", $res);
		foreach($list as $l) {
			$l = trimSpaces($l);
			$list = explode(" ", $l);
			if (array_search_bool($ip, $list)) {
				list($vpsid) = explode(" ", $l);
				return $vpsid;
			}
		}
		return 0;
	}

	static function execCommand($vpsid, $command)
	{
		global $global_shell_error, $global_shell_ret;
		$out = lxshell_output("vzctl", "exec", $vpsid, $command);
	
		dprint($out);
	
		return array('output' => $out, 'error' => $global_shell_error);
	}

	static function getStatus($vpsid, $rootdir)
	{
	
		self::checkIfVzOK();
	
		if (lx_core_lock_check_only("background.php", "$vpsid.create")) {
			return 'create';
		}
	
		if (lxfile_exists("__path_program_root/tmp/$vpsid.createfailed")) {
			$reason = lfile_get_contents("__path_program_root/tmp/$vpsid.createfailed");
			return "createfailed: $reason";
		}
		$res = shell_exec("vzctl status $vpsid");
	
		dprint("$res \n");
	
		if (strtilfirst(trim(`hostname`), ".") ===  "root") {
			return 'on';
		}
	
	
		if (csa($res, "running")) {
			return 'on';
		}
	
		if (csa($res, "deleted")) {
			return "deleted";
		}
	
		return 'off';
	}

	static function vpsInfo($vpsid)
	{
		global $global_dontlogshell;
		$global_dontlogshell = true;
		$path = "/proc/user_beancounters";
		
		$data = `vzctl exec $vpsid cat /proc/user_beancounters`;
	
		$res = explode("\n", $data);
		$match = true;
		foreach($res as $r) {
			/*
			if (csa($r, "$vpsid:")) {
				$match = true;
			}
		*/
	
			if ($match && csa($r, "privvmpages")) {
				break;
			}
		}
	
		$data = trimSpaces($r);
	
		dprint($data . "\n");
	
		$result = explode(" ", $data);
		$max = $result[4];
	
		$use = $result[1];
	
		$use = $use/256;
		$max = $max/256;
		
		$ret['priv_s_memory'] = $max;
		$ret['used_s_memory'] = $use;
	
		$diskcont = lxshell_output("vzquota", "stat", $vpsid);
	
		$dcont = explode("\n", $diskcont);
	
		foreach($dcont as $dc) {
			$dc = trimSpaces($dc);
			if (csb($dc, '1k-blocks')) {
				$ddc = explode(" ", $dc);
				$ret['used_s_disk'] = round($ddc[1]/1024);
				$ret['priv_s_disk'] = round($ddc[2]/1024);
			}
			if (csb($dc, 'inodes')) {
				$ddc = explode(" ", $dc);
				$ret['used_s_inode'] = $ddc[1];
				$ret['priv_s_inode'] = $ddc[2];
			}
		}
	
	
		foreach ($ret as &$vvv) {
			$vvv = round($vvv);
		}
	
		exec("vzctl exec $vpsid cat /proc/cpuinfo", $data);
		$processornum = 0;
		
		foreach($data as $v) {
			if (!trim($v)){
				continue;
			}
			$d = explode(':', $v);
			$d[0] = trim($d[0]);
			$d[1] = trim($d[1]);
			if ($d[0] === 'processor') {
				$processornum = $d[1];
				continue;
			}
	
			if ($d[0] === 'model name') {
				$cpu[$processornum]['used_s_cpumodel'] = $d[1];
			}
			if ($d[0] === 'cpu MHz') {
				$cpu[$processornum]['used_s_cpuspeed'] = round($d[1]/100)/10 . "GHz";
			}
			if ($d[0] === 'cache size') {
				$cpu[$processornum]['used_s_cpucache'] = $d[1];
			}
		}
	
		$global_dontlogshell = false;
		$ret['cpu'] = $cpu;
		return $ret;
	}

	static function checkIfVzOK()
	{
		global $global_dontlogshell;
	
		$v = $global_dontlogshell;
		$global_dontlogshell = true;
	
		if (!lxfile_exists("/proc/vz")) {
			throw new lxException("no_kernel_support_for_openvz_check_if_right_kernel");
		}
	
		$res = lxshell_output("vzctl", "status", "10000");
		if (!trim($res)) {
			//throw new lxException("vzctl_doesnt_work_most_likely_vz_service_is_not_running");
		}
	
		$global_dontlogshell = $v;
	}

	function dbactionAdd()
	{
		global $gbl, $sgbl, $login, $ghtml; 
	
		self::checkIfVzOK();
		$ret = lxshell_return("vzctl", "--help");
		if ($ret) {
			throw new lxException("no_vzctl");
		}
	
		@ lunlink("__path_program_root/tmp/{$this->main->vpsid}.createfailed");
		if ($this->main->dbaction === 'syncadd') {
			$username = vps::create_user($this->main->username, $this->main->password, $this->main->vpsid, "/usr/bin/lxopenvz");
			return null;
		}
	
		$vpsid = $this->main->vpsid;
	
		dprintr("vpsid. $vpsid. ..\n");
		if (lxfile_exists("/etc/vz/conf/$vpsid.conf")) {
			throw new lxException("a_vps_with_the_same_id_exists", '', $vpsid);
		}
	
		if (self::getStatus($vpsid, $this->main->corerootdir) === 'create') {
			throw new lxException("a_vps_of_same_id_is_getting_created", '', $vpsid);
		}
	
		if (self::getStatus($vpsid, $this->main->corerootdir) !== 'deleted') {
			throw new lxException("a_vps_with_the_same_id_exists", '', $vpsid);
		}
	
		/*
		if (!lxfile_exists("/vz/template/cache/{$this->main->ostemplate}.tar.gz")) {
			throw new lxException("could_not_find_the_osimage");
		}
	*/
	
		$username = vps::create_user($this->main->username, $this->main->password, $this->main->vpsid, "/usr/bin/lxopenvz");
			
		if ($sgbl->isDebug()) {
			$this->doRealCreate();
		} else {
			callObjectInBackground($this, "doRealCreate");
		}
	
		//$this->doRealCreate();
		
		$ret = array("__syncv_username" => $username);
		return $ret;
	
	}

	function doRealCreate()
	{
		global $global_shell_error, $global_shell_ret;
	
		$vpsid = $this->main->vpsid;
		lxfile_mkdir("__path_program_root/tmp");
	
		lx_core_lock("$vpsid.create");
	
		$templatefile = "/vz/template/cache/{$this->main->ostemplate}.tar.gz";
	
		$this->main->getOsTemplateFromMaster($templatefile);
	
	
		if (!lxfile_real($templatefile)) {
			log_error("could not create vm. Could not download $templatefile");
			lfile_put_contents("__path_program_root/tmp/$vspid.createfailed", "Could not download $templatefile");
			exit;
		}
	
		dprint($templatefile . "\n");
	
		$ret = lxshell_return("nice", "-n", "19", "vzctl", "--verbose", "create", $this->main->vpsid, "--private", "{$this->main->corerootdir}/{$this->main->vpsid}", "--ostemplate", $this->main->ostemplate);
	
	
		if ($ret) {
			lunlink("__path_program_root/tmp/$vpsid.create");
			lfile_put_contents("__path_program_root/tmp/$vpsid.createfailed", $global_shell_error);
			exit;
		}
	
		$this->setIpaddress($this->main->vmipaddress_a, true);
		$this->enableSecondLevelQuota();
		//lxshell_return("vzctl", "set", $this->main->vpsid, "--quotaugidlimit", "1000", "--save");
		$this->setInformation();
		$ret = lxshell_return("vzctl", "set", $this->main->vpsid, "--onboot", "yes", "--save");
	
		$this->setEveryThing();
		$this->setRootPassword();
	
	
		$this->main->doKloxoInit("{$this->main->corerootdir}/{$this->main->vpsid}");
		// It appears sometimes they don't setup the ostemplate properly.
		$this->changeConf("OSTEMPLATE", $this->main->ostemplate);
		$this->stop();
		$this->start();
		lunlink("__path_program_root/tmp/$vpsid.create");
		$this->postCreate();
	}

	function postCreate()
	{
		if ($this->main->__var_custom_exec) {
			lxshell_direct($this->main->__var_custom_exec);
		}
	}

	function dropQuota()
	{
		lxshell_return("vzquota", "drop", $this->main->vpsid);
	}

	function changeLocation()
	{
		$this->stop();
		$this->dropQuota();
		lxfile_mkdir($this->main->newlocation);
		if (lxfile_exists("{$this->main->newlocation}/{$this->main->vpsid}")) {
			throw new lxException("vpsid_already_exists_in_new_location", 'newlocation', $this->main->vpsid);
		}
	
		$ret = lxfile_cp_rec("{$this->main->corerootdir}/{$this->main->vpsid}", "{$this->main->newlocation}/");
		if ($ret) {
			throw new lxException("copy_of_vps_failed", 'newlocation', $this->main->vpsid);
		}
	
		$this->changeConf("VE_PRIVATE", "{$this->main->newlocation}/\$VEID");
		lxfile_rm_rec("{$this->main->corerootdir}/{$this->main->vpsid}");
	
		
		$this->start();
		$ret = array("__syncv_corerootdir" => $this->main->newlocation);
		return $ret;
	}

	function setMainIpaddress()
	{
		$ret = lxshell_return("vzctl", "set", $this->main->vpsid, "--ipdel", "all", "--save");
	
		sleep(10);
		$ret = lxshell_return("vzctl", "set", $this->main->vpsid, "--ipadd", $this->main->mainipaddress, "--save");
	
		sleep(10);
		foreach($this->main->vmipaddress_a as $ip) {
			if ($ip->nname === $this->main->mainipaddress) {
				continue;
			}
			$ret = lxshell_return("vzctl", "set", $this->main->vpsid, "--ipadd", $ip->nname, "--save");
		}
	}

	function setIpaddress($list, $vpsflag)
	{
		foreach($list as $ip) {
			if ($vpsflag) {
				$ret = lxshell_return("vzctl", "set", $this->main->vpsid, "--ipadd", $ip->nname, "--save");
			}
			lxshell_return("iptables", "-A", "FORWARD", "-s", $ip->nname);
			lxshell_return("iptables", "-A", "FORWARD", "-d", $ip->nname);
		}
		return $ret;
	}

	function deleteIpaddress($list, $vpsflag)
	{
		foreach($list as $ip) {
			if ($vpsflag) {
				lxshell_return("vzctl", "set", $this->main->vpsid, "--ipdel", $ip->nname, "--save");
			}
			lxshell_return("iptables", "-D", "FORWARD", "-s", $ip->nname);
			lxshell_return("iptables", "-D", "FORWARD", "-d", $ip->nname);
		}
	}

	function dbactionDelete()
	{
		try {
			$this->stop();
		} catch (Exception $e) {
		}
	
		lxshell_return("vzctl", "set", $this->main->vpsid, "--ipdel", "all", "--save");
	
		lxshell_return("vzctl", "umount", $this->main->vpsid);
		lxshell_return("vzctl", "destroy", $this->main->vpsid);
		$this->deleteIpaddress($this->main->vmipaddress_a, false);
		lxshell_return("userdel", "-r", $this->main->username);
	
		@ lunlink("__path_program_root/tmp/{$this->main->vpsid}.create");
		@ lunlink("__path_program_root/tmp/{$this->main->vpsid}.createfailed");
	
		// Just making sure. Sometimes the file doesn't properly get deleted.
		if (lxfile_exists("/etc/vz/conf/{$this->main->vpsid}.conf")) {
			lunlink("/etc/vz/conf/{$this->main->vpsid}.conf");
		}
		//lxfile_rm_rec("__path_program_home/vps/{$this->main->nname}");
	}

	function toggleStatus()
	{
		global $global_shell_out, $global_shell_error, $global_shell_ret;
	
		if ($this->main->isOn('status')) {
			$ret = $this->start();
			if ($ret) {
				throw new lxException("could_not_start_vps", '', str_replace("\n", ": ", $global_shell_error));
			}
			$ret = lxshell_return("vzctl", "set", $this->main->vpsid, "--onboot", "yes", "--save");
		} else {
			$ret = $this->stop();
			$ret = lxshell_return("vzctl", "set", $this->main->vpsid, "--onboot", "no", "--save");
		}
	
		if($ret)
			log_message($ret);
	}

	function setRootPassword()
	{
		lxshell_return("vzctl", "set", $this->main->vpsid, "--save", "--userpasswd", "root:{$this->main->rootpassword}");
	}
	
	function setMemoryUsage()
	{
	
		if (is_unlimited($this->main->priv->memory_usage)) {
			$memory = 999999 * 256;
		} else {
			$memory = $this->main->priv->memory_usage * 256;
		}
	
		lxshell_return("vzctl", "set", $this->main->vpsid, "--save", "--privvmpages", $memory);
		lxshell_return("vzctl", "set", $this->main->vpsid, "--save", "--meminfo", "pages:$memory");
	}

	function do_backup()
	{
		if ($this->main->isOn('__var_bc_backupextra_stopvpsflag')) {
			$this->stop();
		}
	
		$list = lscandir_without_dot("{$this->main->corerootdir}/{$this->main->vpsid}");
		$list = array_remove($list, "{$this->main->vpsid}.conf");
		$list = array_remove($list, "proc");
	
		if (count($list) < 6) {
			throw new lxException("not_enough_directories_in_vps_root,_possibly_wrong_location", '', '');
		}
		return array("{$this->main->corerootdir}/{$this->main->vpsid}", $list);
	}

	function do_backup_cleanup($list)
	{
		// I had commented out the starting of the vps after backup. I don't know why. Why is this not done.. The vps should be started after the backup is done.
	
		if ($this->main->isOn('__var_bc_backupextra_stopvpsflag')) {
			$this->start();
		}
	}

	function do_restore($docd)
	{
		global $gbl, $sgbl, $login, $ghtml;
	
		$this->stop();
		$this->dropQuota();
	
		$mountpoint = "{$this->main->corerootdir}/{$this->main->vpsid}";
	
		lxfile_mkdir($mountpoint);
	
		if (lxshell_exists_in_zip($docd, "{$this->main->vpsid}.conf")) {
			log_restore("Got an Old Backup Restore for {$this->main->vpsid} $docd");
			lxshell_unzip_with_throw($this->main->corerootdir, $docd, array("{$this->main->vpsid}/*"));
		} else {
			lxshell_unzip_with_throw("{$this->main->corerootdir}/{$this->main->vpsid}", $docd);
		}
	
		//lxshell_return("tar", "-C", "{$this->main->corerootdir}/{$this->main->vpsid}/dev", "-xzf", "__path_program_root/file/vps-dev.tgz");
	
		if ($this->main->__old_driver !== 'openvz') {
			log_restore("Restoring {$this->main->nname} from a different driver {$this->main->__old_driver} to openvz");
			lxfile_cp("__path_program_root/file/sysfile/openvz/fstab", "$mountpoint/etc/fstab");
	
			//if (!lxfile_exists("/vz/template/cache/{$this->main->ostemplate}.tar.gz")) {
				//throw new lxException("migrating_from_{$this->main->__old_driver}_needs_osImage");
			//}
			//lxshell_return("tar", "-C", $mountpoint, "-xzf", "__path_program_home/xen/template/{$this->main->ostemplate}.tar.gz", "etc/rc.d", "sbin", "etc/hotplug.d", "etc/dev.d", "etc/udev", "lib", "usr", "bin", "etc/inittab", "etc/sysconfig");
			//lxshell_return("tar", "-C", $mountpoint, "-xzf", "/vz/template/cache/{$this->main->ostemplate}.tar.gz", "etc/rc.d", "sbin", "lib", "usr", "bin", "etc/inittab");
			lunlink("$mountpoint/etc/mtab");
			lxfile_symlink("/proc/mounts", "$mountpoint/etc/mtab");
		}
	
		if (!lxfile_exists("$mountpoint/usr/bin")) {
			throw new lxException("the_vps_directory_is_empty", '', '');
		}
	
		lxfile_mkdir("$mountpoint/proc");
		$this->createBaseConf();
		$this->setIpaddress($this->main->vmipaddress_a, true);
		$this->setEveryThing();
		$this->setInformation();
		$this->setRootPassword();
		$this->dropQuota();
		$this->start();
	}

	function do_restore_old($docd)
	{
		global $gbl, $sgbl, $login, $ghtml;
	
		lxshell_unzip_with_throw($this->main->corerootdir, $docd, array("{$this->main->vpsid}/*", "{$this->main->vpsid}.conf"));
		// Just create the iptables entries only...
		lunlink("{$this->main->corerootdir}/{$this->main->vpsid}.conf");
	}

	function setGuarMemoryUsage()
	{
	
		if (is_unlimited($this->main->priv->guarmem_usage)) {
			$memory = 500;
		} else {
			$memory = $this->main->priv->guarmem_usage;
		}
	
		lxshell_return("vzctl", "set", $this->main->vpsid, "--save", "--vmguarpages", "{$memory}M:2147483647");
		lxshell_return("vzctl", "set", $this->main->vpsid, "--save", "--oomguarpages", "{$memory}M:2147483647");
		lxshell_return("vzctl", "set", $this->main->vpsid, "--save", "--shmpages", "{$memory}M:{$memory}M");
		lxshell_return("vzctl", "set", $this->main->vpsid, "--save", "--physpages", "0:2147483647");
		$tcp = round(($memory * 1024)/5, 0);
		$process = $this->main->priv->process_usage;
		if (is_unlimited($process) || $process > 5555) {
			$process = 5555;
		}
		dprint("Process Usage $process\n");
		$limit = $tcp + 2 * $process * 16;
		if (!$tcp) { $tcp = 1; }
		$tcp .= "K";
		$limit .= "K";
		$tcp = "$tcp:$limit";
		lxshell_return("vzctl", "set", $this->main->vpsid, "--save", "--tcpsndbuf", $tcp, "--tcprcvbuf", $tcp, "--othersockbuf", $tcp, "--dgramrcvbuf", $tcp);
	}

	function createBaseConf()
	{
		lxfile_cp("__path_program_root/file/sysfile/openvz/base-openvz.conf", "/etc/vz/conf/{$this->main->vpsid}.conf");
		$this->changeConf("OSTEMPLATE", $this->main->ostemplate);
		$this->changeConf("VE_PRIVATE", "{$this->main->corerootdir}/\$VEID");
	}

	function setCpuUsage()
	{
		if (is_unlimited($this->main->priv->cpu_usage)) {
			$cpu = "100" * os_getCpuNum();
		} else {
			$cpu = $this->main->priv->cpu_usage;
		}
	
		lxshell_return("vzctl", "set", $this->main->vpsid, "--save", "--cpulimit", $cpu);
	}

	function setDiskUsage()
	{
		if (is_unlimited($this->main->priv->disk_usage)) {
			$diskusage = 99999 * 1024;
		} else {
			$diskusage = $this->main->priv->disk_usage * 1024;
		}
	
		lxshell_return("vzctl", "set", $this->main->vpsid, "--save", "--diskspace", $diskusage, "--diskinodes", round($diskusage/2));
	}

	// Added by Semir @ 2011 march 14
	function setSwapUsage()
	{
	        if (is_unlimited($this->main->priv->swap_usage)) {   
	                $memory = 2048;   
	        } else {
	                $memory = $this->main->priv->swap_usage;
	        }
	
	    $memory = "0:" . $memory . "M";
	
	    lxshell_return("vzctl", "set", $this->main->vpsid, "--save", "--swappages", $memory);
	}

	function setProcessUsage()
	{
		if (is_unlimited($this->main->priv->process_usage)) {
			$process = 999999;
		} else {
			$process = $this->main->priv->process_usage;
		}
	
		$avnumproc = $process/2;
	
		$sockets = $avnumproc * 16; 
		$numfile = $sockets * 3;
		$dcachesize = $numfile * 384;
	
		$kernelmem = 40 * 1024 * $avnumproc + $dcachesize *100;
	
		$kernelmem = 2147483646;
		$kernelmem = $this->limitMaxMemory($kernelmem);
		$dcachesize = $this->limitMaxMemory($dcachesize);
		$numfile = $this->limitNumber($numfile);
		$sockets = $this->limitNumber($sockets);
		$process = $this->limitNumber($process);
	
		lxshell_return("vzctl", "set", $this->main->vpsid, "--save", "--numproc", $process);
	
		$avnumproc = round($avnumproc);
	
		lxshell_return("vzctl", "set", $this->main->vpsid, "--save", "--numtcpsock", $sockets, "--numothersock", $sockets, "--numfile", $numfile, "--numflock", $process, "--numsiginfo", $process, "--numpty", $avnumproc);
	
		lxshell_return("vzctl", "set", $this->main->vpsid, "--save", "--dcachesize", $dcachesize, "--kmemsize", $kernelmem);
		lxshell_return("vzctl", "set", $this->main->vpsid, "--save", "--numiptent", $process);
		lxshell_return("vzctl", "set", $this->main->vpsid, "--save", "--lockedpages", $process);
	
		$this->setGuarMemoryUsage();
	}

	function limitMaxMemory($value)
	{
		if ($value > 2147483646) { $value = 2147483646; }
		return $value;
	}

	function limitNumber($value)
	{
		if ($value > 214748364) { $value = 214748364; }
		return $value;
	}

	function reboot()
	{
		global $global_shell_out, $global_shell_error, $global_shell_ret;
		$this->stop();
		#$this->changeConf("CAPABILITY", "SYS_TIME:on");
		$this->main->doKloxoInit("{$this->main->corerootdir}/{$this->main->vpsid}");
	
		$ret = $this->start();
	
		if ($ret) {
			throw new lxException("could_not_start_vps", '', str_replace("\n", ": ", $global_shell_error));
		}
	}

	// temproary version without quotes...
	function staticChangeConf($file, $var, $val)
	{
		$list = lfile_trim($file);
		$match = false;
		foreach($list as $k => $__l) {
			if (csb($__l, "$var=")) {
				if ($val) {
					$list[$k] = "$var=$val";
				} else {
					unset($list[$k]);
				}
				$match = true;
			}
		}
	
		if (!$match) {
			if ($val) {
				$list[] = "$var=$val";
			}
		}
	
		lfile_put_contents($file, implode("\n", $list));
	}

	function removeConf($var)
	{
		$list = lfile_trim("/etc/vz/conf/{$this->main->vpsid}.conf");
		$match = false;
		foreach($list as $k => $__l) {
			if (csb($__l, "$var=")) {
				if ($val) {
					//$list[$k] = "$var=\"$val\"";
				} else {
					unset($list[$k]);
				}
				$match = true;
			}
		}
	
		if (!$match) {
			return;
		}
	
		lfile_put_contents("/etc/vz/conf/{$this->main->vpsid}.conf", implode("\n", $list));
	}

	function changeConf($var, $val)
	{
		$list = lfile_trim("/etc/vz/conf/{$this->main->vpsid}.conf");
		$match = false;
		foreach($list as $k => $__l) {
			if (csb($__l, "$var=")) {
				if ($val) {
					$list[$k] = "$var=\"$val\"";
				} else {
					unset($list[$k]);
				}
				$match = true;
			}
		}
	
		if (!$match) {
			if ($val) {
				$list[] = "$var=\"$val\"";
			}
		}
	
		lfile_put_contents("/etc/vz/conf/{$this->main->vpsid}.conf", implode("\n", $list));
	}

	function recoverVps()
	{
		if (!$this->main->isOn('recover_confirm_f')) {
			throw new lxException("need_confirm_recover", 'recover_confirm_f');
		}
	
		$this->stop();
		$this->main->coreRecoverVps("{$this->main->corerootdir}/{$this->main->vpsid}");
		$this->start();
	}

	function rebuild()
	{
		if (!$this->main->isOn('rebuild_confirm_f')) {
			throw new lxException("need_confirm_rebuild", 'rebuild_confirm_f');
		}
	
		$templatefile = "/vz/template/cache/{$this->main->ostemplate}.tar.gz";
		$this->main->getOsTemplateFromMaster($templatefile);
	
		if(!lxfile_nonzero($templatefile)) {
			throw new lxException("no_template_and_could_not_download", 'rebuild_confirm_f');
		}
	
		$this->stop();
		$this->dropQuota();
		if ($this->main->isOn('rebuild_backup_f')) {
			lxfile_mkdir("/home/hypervm/vps/{$this->main->nname}/__backup/");
			$date = date('Y-m-d-') . time();
			$dir = "/home/hypervm/vps/{$this->main->nname}/__backup/rebuild-backup.$date";
			lxfile_mv_rec("{$this->main->corerootdir}/{$this->main->vpsid}", $dir);
		} else {
			$dir = getNotexistingFile($this->main->corerootdir, "tmp.{$this->main->vpsid}");
			lxfile_mv_rec("{$this->main->corerootdir}/{$this->main->vpsid}", $dir);
			lxfile_rm_rec($dir);
		}
		lxfile_mkdir("{$this->main->corerootdir}/{$this->main->vpsid}");
		$ret = lxshell_return("tar", "-C", "{$this->main->corerootdir}/{$this->main->vpsid}", '--numeric-owner', "-xzpf", $templatefile);
	
		if ($ret) {
			throw new lxException("rebuild_failed_could_not_untar");
		}
	
		$this->changeConf("OSTEMPLATE", $this->main->ostemplate);
	
		$this->dropQuota();
		$this->start();
		$this->setRootPassword();
	
		if (lxfile_exists("/etc/hypervm/rebuild_fix")) {
			$this->setEveryThing();
			$this->setIpaddress($this->main->vmipaddress_a, true);
			$this->enableSecondLevelQuota();
			//lxshell_return("vzctl", "set", $this->main->vpsid, "--quotaugidlimit", "1000", "--save");
			$this->setInformation();
			$ret = lxshell_return("vzctl", "set", $this->main->vpsid, "--onboot", "yes", "--save");
		}
	}

	function installkloxo()
	{
		$this->rebuild();
	}

	function oldinstallkloxo()
	{
	    //TODO: Remove?
		$ret = lxshell_return("vzctl", "exec2", $this->main->vpsid, "ping -n -c 1 -w 5 lxlabs.com");
	
		if ($ret) {
			throw new lxException("no_network_inside_the_vps_possibly_lack_of_dns");
		}
	
		$type = $this->main->kloxo_type;
		if (lxfile_exists("{$this->main->corerootdir}/{$this->main->vpsid}/kloxo-install-$type.sh")) {
			throw new lxException("old_kloxo_found");
		}
		if (lxfile_exists("{$this->main->corerootdir}/{$this->main->vpsid}/usr/local/lxlabs/kloxo")) {
			throw new lxException("old_kloxo_found");
		}
	
		addLineIfNotExistPattern("{$this->main->corerootdir}/{$this->main->vpsid}/etc/sysconfig/rhn/up2date", "networkSetup[comment]=None", "networkSetup[comment]=None\nnetworkSetup=1\n");
	
		lxshell_return("vzctl", "exec", $this->main->vpsid, "wget download.lxlabs.com/download/kloxo/production/kloxo-install-$type.sh");
		lxshell_return("vzctl", "exec", $this->main->vpsid, "sh ./kloxo-install-$type.sh > hyperVm-kloxo_install.log 2>&1 &");
	}

	function setInformation()
	{
		if ($this->main->hostname) {
			lxshell_return("vzctl", "set", $this->main->vpsid, "--hostname", $this->main->hostname, "--save");
		}
	
		if ($this->main->nameserver) {
			lxshell_return("vzctl", "set", $this->main->vpsid, "--nameserver", $this->main->nameserver, "--save");
		}
		lxfile_cp("/usr/share/zoneinfo/{$this->main->timezone}", "{$this->main->corerootdir}/{$this->main->vpsid}/etc/localtime");
	}

	static function getOsTemplatelist()
	{
		$list = lscandir_without_dot("/vz/template/cache/");
	
		foreach($list as $__l) {
			if (!cse($__l, ".tar.gz") && !cse($__l, ".img")) {
				continue;
			}
			$size = lxfile_get_uncompressed_size("/vz/template/cache/$__l");
			$newlist[strtil($__l, ".tar.gz")] = strtil($__l, ".tar.gz") . " (" . round($size / (1024 * 1024), 2) . "MB)";
		}
		
		return $newlist;
	}

	function createTemplate()
	{
		$stem = explode("-", $this->main->ostemplate);
		$name = "{$stem[0]}-{$stem[1]}-{$stem[2]}-";
		$templatename = "$name{$this->main->newostemplate_name_f}";
	
		$this->stop();
	
		$list = lscandir_without_dot("{$this->main->corerootdir}/{$this->main->vpsid}");
		lxshell_return("tar", "-C", "{$this->main->corerootdir}/{$this->main->vpsid}/", '--numeric-owner', "-czf", "/vz/template/cache/$templatename.tar.gz", $list);
		$this->start();
		$filepass = cp_fileserv("/vz/template/cache/$templatename.tar.gz");
		$ret = array("__syncv___ostemplate_filepass" => $filepass, "__syncv___ostemplate_filename" => "$templatename.tar.gz");
		
		return $ret;
	}

	function stop()
	{ 
		global $gbl, $sgbl, $login, $ghtml; 
	
		global $global_shell_error;
		$ret =  lxshell_return("vzctl", "stop", $this->main->vpsid);
		if (self::getStatus($this->main->vpsid, $this->main->corerootdir) === 'on') {
			throw new lxException("could_not_stop_vps");
		}
	}

	function dropOldQuota()
	{
		lxshell_return("vzquota", "drop", $this->main->__var_oldvpsid);
	}

	function stopOldId()
	{ 
		global $gbl, $sgbl, $login, $ghtml; 
	
		global $global_shell_error;
		$ret =  lxshell_return("vzctl", "stop", $this->main->__var_oldvpsid);
		if (self::getStatus($this->main->__var_oldvpsid, $this->main->corerootdir) === 'on') {
			throw new lxException("could_not_stop_vps");
		}
	}

	function start() 
	{ 
	
		if (self::getStatus($this->main->vpsid, $this->main->corerootdir) === 'on') {
			return;
		}
		return lxshell_return("vzctl", "start", $this->main->vpsid); 
	}

	function changeUserPassword()
	{
		dprint("hello\n");
		$pass = $this->main->password;
		lxshell_return("usermod", "-p", $pass, $this->main->username);
	}
	
	function getBeancounter()
	{
		$vpsid = $this->main->vpsid;
		$path = "/proc/user_beancounters";
		
		$data = `cat /proc/user_beancounters`;
	
		$res = explode("\n", $data);
		$match = false;
	
		$savelist = array();
		foreach($res as $r) {
			$r = trim($r);
			if (csb($r, "$vpsid:")) {
				$match = true;
			}
	
			if (!$match) {
				continue;
			}
	
			if (csa($r, ":")) {
				$r = strfrom($r, ":");
				$r = substr($r, 1);
			}
	
			$r = trimSpaces($r);
			$r = explode(" ", $r);
	
			// Check whether we have already encountered this variable. That means, we are now on the next vps.
			if (array_search_bool($r[0], $savelist)) {
				break;
			}
			if ($r[0] !== 'dummy') {
				$savelist[] = $r[0];
			}
	
			$return['nname'] = $r[0];
			$return['descr'] = $r[0];
			$return['used'] = $r[1];
			$return['max'] = $r[2];
			$return['barrier'] = $r[3];
			$return['limit'] = $r[4];
			$return['failcnt'] = $r[5];
	
			$ret[] = $return;
		}
	
		return $ret;
	}
	
	function setEveryThing()
	{
		$this->setDiskUsage();
		$this->setCpuUsage();
		$this->setMemoryUsage();
		$this->setProcessUsage();
		$this->setSwapUsage();
		$this->setIptables();
		$this->changeConf("OSTEMPLATE", $this->main->ostemplate);
		$this->setRestUsage();
	}

	function setRestUsage()
	{
		static $once;
	
		if ($once) { return; }
	
		dprint("Execing\n");
		$once = true;
	
		if (is_unlimited($this->main->priv->ncpu_usage)) {
			$cpun = os_getCpuNum();
		} else {
			$cpun = $this->main->priv->ncpu_usage;
		}
	
		$this->setVpsParam("cpus", $cpun);
	
		if ($this->main->priv->ioprio_usage >= 0) {
			$this->setVpsParam("ioprio", $this->main->priv->ioprio_usage);
		}
	
		if ($this->main->priv->cpuunit_usage >= 0) {
			$this->setVpsParam("cpuunits", $this->main->priv->cpuunit_usage);
		}
	}

	function setVpsParam($name, $value)
	{
		lxshell_return("vzctl", "set", $this->main->vpsid, "--$name", $value, "--save");
	}

	function setIptables()
	{
		$this->removeConf("IPTABLES");
		return;
	
		if ($this->main->priv->isOn('iptables_flag')) {
			$this->changeConf("IPTABLES", "iptable_filter iptable_mangle ipt_limit ipt_multiport ipt_tos ipt_TOS ipt_REJECT ipt_TCPMSS ipt_tcpmss ipt_ttl ipt_LOG ipt_length ip_conntrack ip_conntrack_ftp ip_conntrack_irc ipt_conntrack ipt_state  ipt_helper  iptable_nat ip_nat_ftp ip_nat_irc ipt_REDIRECT");
		} else {
			$this->changeConf("IPTABLES", "");
		}
	}
	
	function enableSecondLevelQuota()
	{
		if ($this->main->priv->isOn('secondlevelquota_flag')) {
			lxshell_return("vzctl", "set", $this->main->vpsid, "--quotaugidlimit", "10000", "--save");
		} else {
			$this->changeConf("QUOTAUGIDLIMIT", "");
		}
	}

	function setUplinkUsage()
	{
		$dev = os_get_default_ethernet();
	
		if (!$dev) {
			log_error("Could not get Default Ethernet");
			return;
		}
	
		$string = null;
		$string .= "#!/bin/sh\n";
		$string .= "export PATH=\$PATH:/sbin\n";
		$string .= "tc qdisc del dev $dev root\n";
		$string .= "tc qdisc add dev $dev root handle 1: cbq avpkt 1000 bandwidth 100mbit\n";
	
		$i = 1;
	
		$this->main->ipaddress = get_namelist_from_objectlist($this->main->vmipaddress_a);
		$this->main->uplink_usage = $this->main->priv->uplink_usage;
	
		$result = $this->main->__var_uplink_list;
	
		dprintr($result);
		$result = merge_array_object_not_deleted($result, $this->main);
	
		foreach((array) $result as $v) {
			if (!$v['ipaddress']) {
				continue;
			}
	
			if (!($v['uplink_usage'] > 0)) {
				continue;
			}
	
			$string .= "#vpsid {$v['vpsid']}\n";
			$string .= "tc class add dev $dev parent 1: classid 1:$i cbq rate {$v['uplink_usage']}kbps allot 1500 prio 5 bounded isolated\n";
			foreach($v['ipaddress'] as $vip) {
				$vip = trim($vip);
				if (!$vip) continue;
				$string .= "tc filter add dev $dev parent 1: protocol ip prio 16 u32 match ip src $vip flowid 1:$i\n";
			}
			$string .= "tc qdisc add dev $dev parent 1:$i sfq perturb 1\n";
			$i++;
		}
	
		$string .= "\nif [ -f /etc/openvz_tc.local.sh ] ; then \n sh /etc/openvz_tc.local.sh ;\n fi ; \n"; 
		lfile_put_contents("__path_program_etc/openvz_tc.sh", $string);
		lxfile_unix_chmod("__path_program_etc/openvz_tc.sh", "0755");
		createRestartFile("openvz_tc");
	}

	/**
	* @todo UNDOCUMENTED
	*
	* @author Anonymous <anonymous@lxcenter.org>
	* @author Ángel Guzmán Maeso <angel.guzman@lxcenter.org>
	*
	* @return void
	*/
	function doSyncToSystemPre()
	{
		if ($main->checkIfOffensive()) {
			dprint('Offensive checking...' . PHP_EOL);
			
			$virtual_machine_name = $main->nname;
				
			$main->checkVPSLock($virtual_machine_name);
		}
	
		if (!$main->corerootdir) {
			$main->corerootdir = '/vz/private';
		}
	}

	function dosyncToSystemPost()
	{
		// For add, it is done in dorealcreate.
	
		if ($this->main->dbaction === 'update' && $this->main->__var_custom_exec) {
			dprint("Execing custom exec {$this->main->__var_custom_exec}\n");
			lxshell_direct($this->main->__var_custom_exec);
		}
	}

	function fixdev()
	{
		if (!$this->main->isOn('fixdev_confirm_f')) {
			throw new lxException("need_to_confirm_fix_dev");
		}
		$this->stop();
		lxfile_mkdir("{$this->main->corerootdir}/{$this->main->vpsid}/dev/");
		lxshell_return("tar", "-C", "{$this->main->corerootdir}/{$this->main->vpsid}/dev/", "-xzf", "__path_program_root/file/vps-dev.tgz");
		lxfile_mv("{$this->main->corerootdir}/sbin/udevd", "{$this->main->corerootdir}/sbin/udevd.back");
		$this->start();
		sleep(10);
		lxshell_return("vzctl", "exec", $this->main->vpsid, "/sbin/MAKEDEV", "tty");
		lxshell_return("vzctl", "exec", $this->main->vpsid, "/sbin/MAKEDEV", "pty");
	}

	function changevpsid()
	{
	
		if (lxfile_exists("/etc/vz/conf/{$this->main->vpsid}.conf")) {
			throw new lxException("conf_file_for_new_vps_exists");
		}
	
		if (lxfile_exists("{$this->main->corerootdir}/{$this->main->vpsid}")) {
			throw new lxException("private_dir_for_new_vps_exists");
		}
	
		$this->stopOldId();
		$this->dropOldQuota();
		lxfile_mv_rec("/etc/vz/conf/{$this->main->__var_oldvpsid}.conf", "/etc/vz/conf/{$this->main->vpsid}.conf");
		lxfile_mv_rec("{$this->main->corerootdir}/{$this->main->__var_oldvpsid}", "{$this->main->corerootdir}/{$this->main->vpsid}");
		$this->changeConf("VE_PRIVATE", "{$this->main->corerootdir}/\$VEID");
		$this->changeConf("VE_ROOT", "/vz/root/\$VEID");
	}

	function dbactionUpdate($subaction)
	{
	
		global $global_shell_error;
	
		if (!$this->main->vpsid) {
			throw new lxException("no_vpsid_fatal_internal_error");
		}
	
		dprint("In dbactionUpdate\n");
		flush();
	
		switch($subaction) {
			case "changelocation":
				return $this->changeLocation();
				break;
	
			case "rebuild":
				$this->rebuild();
				break;
	
			case "recovervps":
				$this->recovervps();
				break;
	
			case "installkloxo":
				$this->installKloxo();
				break;
	
	
			case "full_update":
				if (!lxfile_exists("/etc/vz/conf/{$this->main->vpsid}.conf")) {
					$this->createBaseConf();
				}
				$this->setEveryThing();
				$this->setRootPassword();
				$this->setIpaddress($this->main->vmipaddress_a, true);
				$this->setInformation();
				$this->toggleStatus();
				break;
	
	
			case "password":
				$this->changeUserPassword();
				break;
	
			case "createtemplate":
				return $this->createTemplate();
				break;
	
			case "fixdev":
				$this->fixdev();
				break;
	
			case "disable":
			case "enable":
			case "toggle_status":
				$this->toggleStatus();
				break;
	
			case "change_disk_usage":
				$this->setDiskUsage();
				break;
	
			case "change_cpu_usage":
				$this->setCpuUsage();
				break;
	
			case "change_uplink_usage":
				$this->setUplinkUsage();
				break;
	
			case "change_memory_usage":
				$this->setMemoryUsage();
				break;
	
			case "change_guarmem_usage":
				$this->setGuarMemoryUsage();
				break;
	
	        case "change_swap_usage":
	              $this->setSwapUsage();
	            break;
	
			case "change_process_usage":
				$this->setProcessUsage();
				break;
	
			case "change_ioprio_usage":
			case "change_ncpu_usage":
			case "change_cpuunit_usage":
				$this->setRestUsage();
				break;
				
			case "enable_iptables_flag":
				$this->setIptables();
				break;
	
			case "enable_secondlevelquota_flag":
				$this->enableSecondLevelQuota();
				break;
	
			case "rootpassword":
				$this->setRootPassword();
				break;
	
	
			case "network":
			case "information":
				$this->setInformation();
				break;
	
			case "add_vmipaddress_a":
				$ret = $this->setIpaddress($this->main->__t_new_vmipaddress_a_list, true);
				if ($ret) {
					throw new lxException("adding_ipaddress_failed", '', $global_shell_error);
				}
	
				break;
	
			case "delete_vmipaddress_a":
				$this->deleteIpaddress($this->main->__t_delete_vmipaddress_a_list, true);
				break;
	
			case "getBeancounter":
				return $this->getBeanCounter();
				break;
	
			case "changevpsid":
				$this->changevpsid();
				break;
	
	
			case "fix_everything":
				$this->setEveryThing();
				break;
	
			case "reboot":
				$this->reboot();
				break;
	
			case "boot":
				$this->start();
				break;
	
			case "poweroff":
				$this->toggleStatus();
				break;
	
			case "timezone":
				$this->setInformation();
				break;
	
			case "createuser":
				return $this->main->syncCreateUser();
				break;
	
			case "mainipaddress":
				return $this->setMainIpaddress();
	
			case "graph_traffic":
				return rrd_graph_vps("traffic", "openvz-{$this->main->vpsid}.rrd", $this->main->rrdtime);
				break;
	
			case "graph_cpuusage":
				return rrd_graph_vps("cpu", "openvz-{$this->main->vpsid}.rrd", $this->main->rrdtime);
	
			case "graph_memoryusage":
				return rrd_graph_vps("memory", "openvz-{$this->main->vpsid}.rrd", $this->main->rrdtime);
		}
	}

	static function get_list_of_vps()
	{
		$res = lxshell_output('vzlist', '-H', '-o', 'vpsid');
		$list = explode("\n", $res);
		foreach($list as $l) {
			$l = trim($l);
			if (!$l) {
				continue;
			}
			$nlist[] = $l;
		}
		return $nlist;
	}

	static function importIpaddress($vpsobject, $val)
	{
		$list = explode(" ", $val);
		foreach($list as $l) {
			$ipadd = new vmipaddress_a(null, $vpsobject->syncserver, $l);
			$vpsobject->vmipaddress_a[$ipadd->nname] = $ipadd;
		}
	}

	static function importOnboot($vpsobject, $val)
	{
		if ($val === 'no')  {
			$vpsobject->status = 'off';
		} else {
			$vpsobject->status = 'on';
		}
	}

	static function importLimitVar($vpsobject, $var, $val)
	{
		if ($var === 'CPULIMIT') {
			$rval = $val;
		} else {
			list($rval, $rvv) = explode(":", $val);
		}
	
		$priv = $vpsobject->priv;
	
		switch($var) {
			case "CPULIMIT":
				$priv->cpu_usage = $rval;
				break;
	
			case "PRIVVMPAGES":
				$priv->memory_usage = $rval/256;
				break;
	
			case "DISKSPACE":
				$priv->disk_usage = $rval/1024;
				break;
	
			case "NUMPROC":
				$priv->process_usage = $rval;
				break;
	
			case "VMGUARPAGES":
				$priv->guarmem_usage = $rval/256;
				break;
		}
	}

	static function importOStemplate($vpsobject, $val)
	{
		$vpsobject->ostemplate = $val;
	}
	
	static function importLocation($vpsobject, $val)
	{
		$vpsobject->corerootdir = dirname($val);
	}

	static function createVpsObject($servername, $file)
	{
		$vpsid = strtil($file, ".conf");
	
		$name = "openvz$vpsid.vm";
		$vpsobject = new Vps(null, $servername, $name);
		$vpsobject->parent_clname = createParentName('client', 'admin');
		$vpsobject->priv = new priv(null, null, $vpsobject->nname);
		$vpsobject->priv->__parent_o = $vpsobject;
		$vpsobject->used = new used(null, null, $vpsobject->nname);
		$vpsobject->used->__parent_o = $vpsobject;
		$vpsobject->vpsipaddress_a = array();
		$vpsobject->vpsid = $vpsid;
		$vpsobject->password = crypt("$vpsid");
		$vpsobject->cpstatus = 'on';
		$vpsobject->ttype = 'openvz';
		$vpsobject->iid = $vpsid;
		$vpsobject->ddate = time();
		$varlist = lfile_trim("/etc/vz/conf/$file");
	
		foreach($varlist as $v) {
			if (!$v) {
				continue;
			}
	
			if ($v[0] === '#') {
				continue;
			}
	
			if (!csa($v, "=")) {
				continue;
			}
	
			list ($var, $val) = explode("=", $v);
	
			$val = str_replace('"', "", $val);
	
			switch($var) {
	
				case "IP_ADDRESS":
					self::importIpaddress($vpsobject, $val);
					break;
	
				case "ONBOOT":
					self::importOnboot($vpsobject, $val);
					break;
	
				case "NUMPROC":
				case "PRIVVMPAGES":
				case "VMGUARPAGES":
				case "DISKSPACE":
				case "CPULIMIT":
					self::importLimitVar($vpsobject, $var, $val);
					break;
	
				case "VE_PRIVATE":
					self::importLocation($vpsobject, $val);
					break;
	
				case "HOSTNAME":
					$vpsobject->hostname = $val;
					break;
	
	
				case "OSTEMPLATE":
					self::importOStemplate($vpsobject, $val);
					break;
	
			}
		}
	
		if (!$vpsobject->corerootdir) {
			$vpsobject->corerootdir = '/vz/private';
		}
	
		return $vpsobject;
	}

	static function getCompleteStatus($list)
	{
		foreach($list as $l) {
			$r['status'] = self::getStatus($l['vpsid'], $l['corerootdir']);
			$list = self::vpsInfo($l['vpsid']);
			$r['lmemoryusage_f'] = $list['used_s_memory'];
			$r['ldiskusage_f'] = $list['used_s_disk'];
			$res[$l['nname']] = $r;
		}
		return $res;
	}
}