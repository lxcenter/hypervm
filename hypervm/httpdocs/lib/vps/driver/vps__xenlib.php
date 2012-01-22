<?php 

class vps__xen extends Lxdriverclass {



static function find_cpuusage()
{
	$out = lxshell_output("xm", "list");
	$list = explode("\n", $out);

	foreach($list as $l) {
		$l = trimSpaces($l);
		$val = explode(" ", $l);

		if (!cse($val[0], ".vm")) {
			continue;
		}
		execRrdSingle("cpu", "DERIVE", $val[0], $val[5]);
	}
}

static function find_traffic()
{
	global $gbl, $sgbl, $login, $ghtml; 

	if (!lxfile_exists("__path_program_etc/xeninterface.list")) {
		return;
	}
	$list = lfile_trim("__path_program_etc/xeninterface.list");

	if (!lxfile_exists("__path_program_etc/newxeninterfacebw.data")) {
		foreach($list as $k) {
			$total[$k] = self::get_bytes_for_interface($k);
		}
		dprintr($total);
		lfile_put_contents("__path_program_etc/newxeninterfacebw.data", serialize($total));
		return;
	}

	$data = unserialize(lfile_get_contents("__path_program_etc/newxeninterfacebw.data"));

	$total = null;


	foreach($list as $k) {
		$total[$k] = self::get_bytes_for_interface($k);

		if (isset($data[$k])) {
			if ($total[$k]['total'] < $data[$k]['total']) {
				$v = $total[$k]['total'];
				$vinc = $total[$k]['incoming'];
				$vout = $total[$k]['outgoing'];
			} else {
				$v =   $total[$k]['total'] - $data[$k]['total'];
				$vinc = $total[$k]['incoming'] - $data[$k]['incoming'];
				$vout = $total[$k]['outgoing'] - $data[$k]['outgoing'];
			}
		} else {
			$v = $total[$k]['total'];
			$vinc = $total[$k]['incoming'];
			$vout = $total[$k]['outgoing'];
		}

		execRrdTraffic("xen-$k", $v, "-$vinc", $vout);
		$stringa[] = time() . " " . date("d-M-Y:H:i") . " $k $v $vinc $vout";
	}

	dprintr($total);
	$string = implode("\n", $stringa);
	lfile_put_contents("/var/log/lxinterfacetraffic.log", "$string\n", FILE_APPEND);
	lfile_put_contents("__path_program_etc/newxeninterfacebw.data", serialize($total));
}

static function get_bytes_for_interface($l)
{
	static $net;

	if (!$net) {
		$net = lfile_get_contents("/proc/net/dev");
		$net = explode("\n", $net);
	}

	foreach($net as $n) {
		$n = trimSpaces($n);
		if (!csb($n, "vif$l:")) {
			continue;
		}

		$n = strfrom($n, "vif$l:");
		$n = trimSpaces($n);
		$b = explode(" ", $n);
		$total = $b[0] + $b[8];
		// It seems for xen it is the reverse. The input for the vif is the output for the virtual machine.
		return array('total' => $total, 'incoming' => $b[8], 'outgoing' => $b[0]);
	}
	return 0;
}


static function execCommand($vpsid, $command)
{
	global $global_shell_error, $global_shell_ret;
}

static function getOsTemplatelist($type = 'add')
{
	$list = lscandir_without_dot("__path_program_home/xen/template/");

	foreach($list as $__l) {
		if ($type === 'add') {
			if (!cse($__l, ".tar.gz") && !cse($__l, ".img")) {
				continue;
			}
		} else if ($type === 'img') {
			if (!cse($__l, ".img")) {
				continue;
			}
		} else if ($type === 'tar.gz') {
			if (!cse($__l, ".tar.gz")) {
				continue;
			}
		}

		if (cse($__l, ".tar.gz")) {
			$size = lxfile_get_uncompressed_size("__path_program_home/xen/template/$__l");
		} else {
			$size = lxfile_size("__path_program_home/xen/template/$__l");
		}

		$newlist[strtil($__l, ".tar.gz")] = strtil($__l, ".tar.gz") . " (" . round($size / (1024 * 1024), 2) . "MB)";
	}
	return $newlist;

}

static function checkIfXenOK()
{
	if (!lxfile_exists("/proc/xen")) {
		throw new lxException("no_kernel_support_for_xen._boot_into_the_right_kernel");
	}
}

static function getStatus($vmname, $rootdir)
{
	self::checkIfXenOK();


	if (lx_core_lock_check_only("background.php", "$vmname.create")) {
		return 'create';
	}

	if (lxfile_exists("__path_program_root/tmp/$vmname.createfailed")) {
		$reason = lfile_get_contents("__path_program_root/tmp/$vmname.createfailed");
		return "createfailed: $reason";
	}

	if (!lxfile_exists("$rootdir/$vmname")) {
		return "deleted";
	}

	/*
	if (lx_core_lock("$vmname.status")) {
		throw new lxException("xm_status_locked");
	}
*/
	exec("xm list $vmname", $output, $ret);

	if (!$ret) {
		return 'on';
	}

	return 'off';

}


static function getDiskUsage($disk, $winflag, $root)
{
	global $global_dontlogshell;
	$global_dontlogshell = true;
	if ($winflag) {
		$cont = lxfile_get_ntfs_disk_usage($disk, $root);
	} else {
		$cont = lxfile_get_disk_usage($disk);
	}
	$global_dontlogshell = false;
	return $cont;
}

function initXenVars()
{
	if ($this->isLvm()) {
		$vgname = $this->main->corerootdir;
		$vgname = fix_vgname($vgname);
		$this->main->maindisk = "/dev/$vgname/{$this->main->maindiskname}";
		$this->main->swapdisk = "/dev/$vgname/{$this->main->swapdiskname}";
	} else {
		$this->main->rootdir = "{$this->main->corerootdir}/{$this->main->nname}/";
		$this->main->maindisk = "{$this->main->rootdir}/{$this->main->maindiskname}";
		$this->main->swapdisk = "{$this->main->rootdir}/{$this->main->swapdiskname}";
	}

	$this->main->configrootdir = "__path_home_dir/xen/{$this->main->nname}/";
}

function doSyncToSystemPre()
{

	if ($this->main->checkIfOffensive()) {
		dprint("Offensive.. Checking...\n");
		$this->main->check_and_throw_error_if_some_else_is_using_vps($this->main->nname);
	}
	$this->initXenVars();
}

function dosyncToSystemPost()
{
	if ($this->main->dbaction === 'update' && $this->main->__var_custom_exec) {
		lxshell_direct($this->main->__var_custom_exec);
	}
}

function dbactionAdd()
{

	
	global $gbl, $sgbl, $login, $ghtml; 

	self::checkIfXenOK();



	$ret = lxshell_return("xm", "--help");

	if ($ret == 127) {
		throw new lxException("no_xen_at_all");
	}


	if (is_unlimited($this->main->priv->disk_usage)) {
		$diskusage = 3 * 1024;
	} else {
		$diskusage = $this->main->priv->disk_usage ;
	}

	if ($this->main->isWindows() && $diskusage < 2 * 1024) {
		//throw new lxException("windows_needs_more_than_2GB");
	}

	if ($this->isLVM()) {
		$freediskspace = vg_diskfree($this->main->corerootdir);
	} else  {
		$freediskspace = lxfile_disk_free_space($this->main->corerootdir);
	}

	if (($freediskspace - $diskusage) < 20) {
		throw new lxException("not_enough_space");
	}



	if ($this->main->dbaction === 'syncadd') {
		$username = vps::create_user($this->main->username, $this->main->password, $this->main->nname, "/usr/bin/lxxen");
		return null;
	}

	if (self::getStatus($this->main->nname, '/home/xen') !== 'deleted') {
		throw new lxException("a_virtual_machine_with_the_same_id_exists");
	}

	if ($this->main->isBlankWindows()) {
		if (!lxfile_exists("/home/wincd.img")) {
			throw new lxException("windows_installation_image_missing");
		}
	}

	/*
	if (!lxfile_exists("__path_program_home/xen/template/{$this->main->ostemplate}.tar.gz")) {
		throw new lxException("could_not_find_the_osimage", '', $this->main->ostemplate);
	}
*/



	$username = vps::create_user($this->main->username, $this->main->password, $this->main->nname, "/usr/bin/lxxen");


	if (!$this->isLvm()) {
		lxfile_mkdir($this->main->rootdir);
	}

	lxfile_mkdir($this->main->configrootdir);
	$this->setMemoryUsage();
	$this->setCpuUsage();
	$this->setSwapUsage();
	$this->setDiskUsage();
	if ($sgbl->isDebug()) {
		$this->doRealCreate();
	} else {
		callObjectInBackground($this, "doRealCreate");
	}

	$ret = array("__syncv_username" => $username);
	return $ret;

}

function doRealCreate()
{

	global $gbl, $sgbl, $login, $ghtml; 
	$nname = $this->main->nname;
	lx_core_lock("$nname.create");

	if (!$this->isLvm()) {
		lxfile_mkdir($this->main->rootdir);
	}


	lxfile_mkdir($this->main->configrootdir);

	$this->setDhCP();

	if ($this->main->isWindows()) {
		$templatefile = "__path_program_home/xen/template/{$this->main->ostemplate}";
	} else {
		$templatefile = "__path_program_home/xen/template/{$this->main->ostemplate}.tar.gz";
	}

	$this->main->getOsTemplateFromMaster($templatefile);

	if (!lxfile_real($templatefile)) {
		log_error("could not create vm. Could not download $templatefile");
		lfile_put_contents("__path_program_root/tmp/$nname.createfailed", "Could not download $templatefile");
		exit;
	}

	$size = 0;
	if ($this->main->isWindows()) {
		$size = lxfile_size($templatefile);
		$size = $size / (1024 * 1024);
	}

	try {
		$this->createConfig();
		$this->setMemoryUsage();
		$this->setCpuUsage();
		$this->setSwapUsage();
		$this->createSwap();
		$this->createDisk($size);
	} catch (Exception $e) {
		log_error("could not create vm. Error was {$e->getMessage()}");
		lfile_put_contents("__path_program_root/tmp/$nname.createfailed", "{$e->getMessage()}");
		exit;
	}



	if (!$this->main->isWindows()) {
		$mountpoint = $this->mount_this_guy();
		lxshell_return("tar", "-C", $mountpoint, "--numeric-owner", "-xpzf", $templatefile);
		$this->setInternalParam($mountpoint);
		$this->copyKernelModules();
		lxfile_cp("../file/sysfile/xen/fstab", "$mountpoint/etc/fstab");
		lunlink("$mountpoint/etc/mtab");
	} else if (!$this->main->isBlankWindows()) {
		$templatefile = expand_real_root($templatefile);
		lxshell_return("parted", "-s", $this->main->maindisk, "mklabel", "msdos");
		$this->runParted();
		$partition = $this->getPartition();
		lxshell_return("ntfsfix", $partition);
		//lxshell_return("dd", "if=$templatefile", "of={$this->main->maindisk}");
		lxshell_return("ntfsclone", "--restore-image", "--force", "-O", $partition, $templatefile);
		$this->kpart_remove();
		$this->expandPartitionToImage();
		//lxshell_return("ntfsclone", "-O", $loop, $templatefile);
		//lo_remove($loop);
	}

	$this->main->status = 'On';

	try {
		$this->toggleStatus();
	} catch (Exception $e) {
	}

	$this->postCreate();
}

function postCreate()
{
	if ($this->main->__var_custom_exec) {
		lxshell_direct($this->main->__var_custom_exec);
	}
}


function runParted()
{
	lxshell_return("parted", "-s", $this->main->maindisk, "--", "unit", "s", "rm", "1");
	lxshell_return("parted", "-s", $this->main->maindisk, "--", "unit", "s", "mkpart", "primary", "ntfs", "63", "-1");
	lxshell_return("parted", "-s", $this->main->maindisk, "set", "1", "boot", "on");
}

function expandPartitionToImage()
{
	/*
	$out = lxshell_output("parted", $this->main->maindisk, "unit", "s", "print", "free");
	$list = explode("\n", $out);
	foreach($list as $l) {
		if (csb($l, "Disk")) {
			$s = explode(":", $l);
			$s = trim($s[1]);
			$s = strtil($s, "s");
			break;
		}
	}
*/
	$this->runParted();
	$partition = $this->getPartition();
	lxshell_return("ntfsfix", $partition);
	//lxshell_expect("ntfsresize", "ntfsresize -f $partition");
	lxshell_return("ntfsresize", "-ff", $partition);
	$this->kpart_remove();
}


function kpart_remove()
{
	$disk = $this->main->maindisk;
	$root = $this->main->corerootdir;
	$lv = basename($disk);
	$root = fix_vgname($root);
	$path = "/dev/mapper/$root-$lv";
	lxshell_return("kpartx", "-d", $path);
}



function copyKernelModules()
{
	$mountpoint = $this->mount_this_guy();
	$kernev = trim(`uname -r`);


	if (!lxfile_exists("$mountpoint/lib/modules/$kernev")) {
		lxfile_cp_rec("/lib/modules/$kernev", "$mountpoint/lib/modules/$kernev");
	}
	if (cse($kernev, "-xen")) {
		$nkernev = strtil($kernev, "-xen");
		if (!lxfile_exists("$mountpoint/lib/modules/$nkernev")) {
			lxfile_cp_rec("/lib/modules/$kernev", "$mountpoint/lib/modules/$nkernev");
		}
	}
	if (csb($this->main->ostemplate, "centos-")) {
		if (lxfile_exists("$mountpoint/lib/tls")) {
			lxfile_rm_rec("$mountpoint/lib/tls.disabled");
			lxfile_mv_rec("$mountpoint/lib/tls", "$mountpoint/lib/tls.disabled");
		}
	}
}


function createDisk($size = 0)
{
	if (is_unlimited($this->main->priv->disk_usage)) {
		$diskusage = 3 * 1024;
	} else {
		$diskusage = $this->main->priv->disk_usage ;
	}

	if (lxfile_exists($this->main->maindisk)) {
		return;
	}

	if ($size && $this->main->isWindows()) {
		//$diskusage = $size;
	}

	if ($this->isLVM()) {
		$freediskspace = vg_diskfree($this->main->corerootdir);
	} else  {
		$freediskspace = lxfile_disk_free_space($this->main->corerootdir);
	}

	if (($freediskspace - $diskusage) < 20) {
		throw new lxException("not_enough_space");
	}

	if ($this->isLVM()) {
		lvm_create($this->main->corerootdir, $this->main->maindiskname, $diskusage);
	} else {
		lxfile_mkdir($this->main->rootdir);
		lxshell_return("dd", "if=/dev/zero", "of={$this->main->maindisk}", "bs=1M", "conv=notrunc", "count=1", "seek=$diskusage");
	}

	if (!$this->main->isWindows()) {
		lxshell_return("mkfs.ext3", "-F", $this->main->maindisk);
	}

}

static function createVpsObject($servername, $input)
{

	$name = "{$input['name']}.vm";
	$vpsobject = new Vps(null, $servername, $name);
	$vpsobject->parent_clname = createParentName('client', 'admin');
	$vpsobject->priv = new priv(null, null, $vpsobject->nname);
	$vpsobject->priv->__parent_o = $vpsobject;
	$vpsobject->used = new used(null, null, $vpsobject->nname);
	$vpsobject->used->__parent_o = $vpsobject;
	$vpsobject->vpsipaddress_a = array();
	$vpsobject->vpsid = '-';
	$vpsobject->password = crypt($name);
	$vpsobject->cpstatus = 'on';
	$vpsobject->status = 'on';
	$vpsobject->ttype = 'xen';
	$vpsobject->iid = $name;
	$vpsobject->ddate = time();

	if ($input['type'] === 'file') {
		$vpsobject->corerootdir = $input['location'];
	} else {
		$vpsobject->corerootdir = "lvm:{$input['location']}";
	}

	$vpsobject->maindiskname = $input['maindiskname'];
	$vpsobject->swapdiskname = $input['swapdiskname'];

	if ($input['type'] === 'file') {
		$vpsobject->maindisk = "{$vpsobject->corerootdir}/{$vpsobject->maindiskname}";
		$vpsobject->swapdisk = "{$vpsobject->corerootdir}/{$vpsobject->swapdiskname}";
	} else {
		$vgname = $vpsobject->corerootdir;
		$vgname = fix_vgname($vgname);
		$vpsobject->maindisk = "/dev/$vgname/{$vpsobject->maindiskname}";
		$vpsobject->swapdisk = "/dev/$vgname/{$vpsobject->swapdiskname}";
	}

	if (isset($input['gateway'])) {
		$vpsobject->networkgateway = $input['gateway'];
	}

	if (isset($input['netmask'])) {
		$vpsobject->networknetmask = $input['netmask'];
	}


	$vpsobject->priv->realmem_usage = $input['memory'];
	$vpsobject->priv->disk_usage = lvm_disksize($vpsobject->maindisk);
	$vpsobject->priv->swap_usage = lvm_disksize($vpsobject->swapdisk);
	$vpsobject->priv->backup_flag = 'on';
	$vpsobject->ostemplate = 'unknown';

	if (isset($input['ipaddress'])) {
		self::importIpaddress($vpsobject, $input['ipaddress']);
	}

	return $vpsobject;

}


static function importIpaddress($vpsobject, $val)
{
	$list = explode(" ", $val);
	foreach($list as $l) {
		$ipadd = new vmipaddress_a(null, $vpsobject->syncserver, $l);
		$vpsobject->vmipaddress_a[$ipadd->nname] = $ipadd;
	}
}



function getRealMemory()
{
	if (is_unlimited($this->main->priv->realmem_usage)) {
		$memory = 512;
	} else {
		$memory = $this->main->priv->realmem_usage;
	}
	return $memory;
}


function getVifString()
{
	if (!is_unlimited($this->main->priv->uplink_usage) && ($this->main->priv->uplink_usage > 0)) {
		$ratestring = "rate = {$this->main->priv->uplink_usage}KB/s,";
	} else {
		$ratestring = null;
	}
	if (trim(lxshell_output("uname", "-r")) === "2.6.16.33-xen0") {
		$vifnamestring = null;
	} else {
		$vifnamestring = "vifname=vif{$this->main->vifname},";
	}

	$ipstring = null;
	if ($this->main->vmipaddress_a) {
		$ilist = get_namelist_from_objectlist($this->main->vmipaddress_a);
		$ips = implode(" ", $ilist);
		$ipstring = "ip=$ips,";
	}

	$mac = $this->main->macaddress;
	if (!csb($mac, "aa:00")) { $mac = "aa:00:$mac"; }
	if (strlen($mac) === 14) { $mac = "$mac:01"; }
	$bridgestring = null;
	if ($this->main->networkbridge && $this->main->networkbridge !== '--automatic--') {
		$bridgestring = ",bridge={$this->main->networkbridge}";
	}
	$string = "vif        = ['$ipstring $vifnamestring $ratestring mac=$mac $bridgestring']\n";
	return $string;
}


function addVcpu()
{
	if (is_unlimited($this->main->priv->ncpu_usage)) {
		$cpunum = os_getCpuNum();
	} else {
		$cpunum = $this->main->priv->ncpu_usage;
	}

	if ($cpunum > 0) {
		return  "vcpus = $cpunum\n";
	}
	return null;
}

function createWindowsConfig()
{
	$memory = $this->getRealMemory();
	if (trim(lxshell_output("uname", "-r")) === "2.6.16.33-xen0") {
		$vifnamestring = null;
	} else {
		$vifnamestring = "vifname=vif{$this->main->vifname},";
	}
	if (!is_unlimited($this->main->priv->uplink_usage) && ($this->main->priv->uplink_usage > 0)) {
		$ratestring = "rate = {$this->main->priv->uplink_usage}KB/s,";
	} else {
		$ratestring = null;
	}

	$mac = $this->main->macaddress;
	if (!csb($mac, "aa:00")) { $mac = "aa:00:$mac"; }
	$count = count($this->main->vmipaddress_a);
	// Big bug workaround. the first vif seems to be ignored. Need to be fixed later.
	$vifnamestring = "vifname=vif{$this->main->vifname},";
	//$vif[] = "'type=ioemu, $vifnamestring $ratestring mac=$mac:00'";
	for ($i = 1; $i <= $count; $i++) {
		$hex = get_double_hex($i);
		$h = base_convert($i, 10, 36);
		$bridgestring = null;
		if ($this->main->networkbridge && $this->main->networkbridge !== '--automatic--') {
			$bridgestring = ",bridge={$this->main->networkbridge}";
		}
		$vifnamestring = "vifname=vif{$this->main->vifname}$h,";
		$vif[] = "'type=ioemu, $vifnamestring $ratestring mac=$mac:$hex $bridgestring'";
	}
	$vif = implode(", ", $vif);
	$vif = "vif = [ $vif ]\n";

		

	$string = null;
	$string .= "import os, re\n";
	$string .= "arch = os.uname()[4]\n";
	$string .= "if re.search('64', arch):\n";
	$string .= "    arch_libdir = 'lib64'\n";
	$string .= "else:\n";
	$string .= "    arch_libdir = 'lib'\n";
	$string .= "name = '{$this->main->nname}'\n";

	$string .= "kernel = '/usr/lib/xen/boot/hvmloader'\n";
	$string .= "builder='hvm'\n";
	if ($this->main->isBlankWindows()) {
		$string .= "boot='d'\n";
	} else {
		$string .= "boot='c'\n";
	}
	$string .= "memory = $memory\n";
	$string .= $vif;
	$string .= "device_model = '/usr/' + arch_libdir + '/xen/bin/qemu-dm'\n";
	$string .= "vnc=1\n";
	$string .= "sdl=0\n";

	$string .= $this->addVcpu();

	$string .= "vnclisten='0.0.0.0'\n";
	$string .= "vncpasswd='{$this->main->realpass}'\n";
	if ($this->main->isBlankWindows()) {
		$string .= "disk = [ 'file:/home/wincd.img,hdc:cdrom,r', 'phy:{$this->main->maindisk},ioemu:hda,w']\n";
		$string .= "acpi=1\n";
	} else {
		$string .= "disk = [ 'phy:{$this->main->maindisk},ioemu:hda,w']\n";
		$string .= "acpi=1\n";
	}
	$string .= "vncunused=0\n";
	$string .= "vncdisplay={$this->main->vncdisplay}\n";

	if ($this->main->text_xen_config) {
		$string .= "{$this->main->text_xen_config}\n";
	}


	lxfile_mkdir($this->main->configrootdir);
	lfile_put_contents("{$this->main->configrootdir}/{$this->main->nname}.cfg", $string);
}


function createConfig()
{
	global $gbl, $sgbl, $login, $ghtml; 

	if ($this->main->isOn('nosaveconfig_flag')) {
		return;
	}

	if ($this->main->isWindows()) {
		$this->createWindowsConfig();
		return;
	}

	$memory = $this->getRealMemory();

	if ($this->isLVM()) { $loc = "phy"; } 
	else { $loc = "file"; }

	$string  = null;

	$sk = "/boot/hypervm-xen-vmlinuz-{$this->main->nname}";

	if (lxfile_exists($sk)) {
		$kern = $sk;
	} else {
		$kern = "/boot/hypervm-xen-vmlinuz";
	}
	$string .= "kernel     = '$kern'\n";


	$customram = "/boot/hypervm-xen-initrd-{$this->main->nname}.img";

	if (lxfile_exists($customram)) {
		$string .= "ramdisk    = '$customram'\n";
	} else if (lxfile_exists('/boot/hypervm-xen-initrd.img')) {
		$string .= "ramdisk    = '/boot/hypervm-xen-initrd.img'\n";
	}

	if (is_unlimited($this->main->priv->cpu_usage)) {
		$cpu = "100" * os_getCpuNum();;
	} else {
		$cpu = $this->main->priv->cpu_usage;
	}

	if (is_unlimited($this->main->priv->cpuunit_usage)) {
		$cpuunit = "1000";
	} else {
		$cpuunit = $this->main->priv->cpuunit_usage;
	}

	if (!is_numeric($cpuunit)) { $cpuunit = '1000'; }
	if (!is_numeric($cpu)) { $cpu = "100" * os_getCpuNum(); }

	$string .= "memory     = $memory\n";
	//$string .= "cpu_cap     = $cpu\n";
	$string .= "cpu_weight     = $cpuunit\n";
	$string .= "name       = '{$this->main->nname}'\n";
	$string .= $this->getVifString();
	$string .= "vnc        = 0\n";

	$string .= $this->addVcpu();

	$string .= "vncviewer  = 0\n";
	$string .= "serial     = 'pty'\n";
	$string .= "disk       = ['$loc:{$this->main->maindisk},sda1,w', '$loc:{$this->main->swapdisk},sda2,w']\n";
	$string .= "root = '/dev/sda1 ro'\n";

	if ($this->main->text_xen_config) {
		$string .= "{$this->main->text_xen_config}\n";
	}

	lxfile_mkdir($this->main->configrootdir);
	lfile_put_contents("{$this->main->configrootdir}/{$this->main->nname}.cfg", $string);


}

function getValueFromFile($file)
{
	$vfile = "{$this->main->configrootdir}/$file";
	if (!lxfile_exists($vfile)) {
		return ;
	}
	$v = lfile_get_contents($vfile);
	lunlink($vfile);
	$v = trim($v);
	return $v;
}

function resizeRootImage()
{

	$v = $this->getValueFromFile("disk.value");
	if (!$v) { return; }

	$this->stop();

	$this->umountThis();

	if ($this->isLVM()) {
		lvm_extend($this->main->maindisk, $v);
		$disk = $this->main->maindisk;
	} else {
		lxshell_return("dd", "if=/dev/zero", "of={$this->main->maindisk}", "bs=1M", "conv=notrunc", "count=1", "seek=$v");
		$disk = $this->main->maindisk;
		//$disk = $this->get_free_loop();
		//$ret = lxshell_return("losetup", $disk, $this->main->maindisk);
	}


	if ($this->main->isWindows()) {
		$this->expandPartitionToImage();
	} else {
		lxshell_return("e2fsck", "-f", "-y", $disk);
		lxshell_return("resize2fs", $disk);
	}
	if (!$this->isLVM()) {
		//lo_remove($disk);
	}

}


function getPartition()
{
	return get_partition($this->main->maindisk, $this->main->corerootdir);
}

function get_free_loop()
{
	return get_free_loop();
}


function isLvm()
{
	return csb($this->main->corerootdir, "lvm:");
}

function createSwap()
{
	global $gbl, $sgbl, $login, $ghtml; 
	global $global_shell_error, $global_shell_ret;

	if ($this->main->isWindows()) {
		return;
	}

	$v = $this->getValueFromFile("swap.value");

	if (!$v) { return ; }

	if ($this->isLVM()) {
		lvm_remove($this->main->swapdisk);
		$ret = lvm_create($this->main->corerootdir, $this->main->swapdiskname, $v);
		if ($ret) {
			throw new lxException("failed_to_create_swap", '', $global_shell_error);
		}
	} else {
		lunlink($this->main->swapdisk);
		lxshell_return("dd", "if=/dev/zero", "of={$this->main->swapdisk}", "bs=1M", "count=1", "seek=$v");
	}

	lxshell_return("mkswap", $this->main->swapdisk);
}


function setvif()
{
	$filelist = lfile_trim("__path_program_etc/xeninterface.list");
	$list = $this->main->getViflist();
	foreach($list as $l) {
		$filelist = array_push_unique($filelist, $l);
	}
	dprintr($filelist);
	lfile_put_contents("__path_program_etc/xeninterface.list", implode("\n", $filelist));
}

function deletevif()
{
	$filelist = lfile_trim("__path_program_etc/xeninterface.list");
	$list = $this->main->getViflist();
	foreach($list as $l) {
		$filelist = array_remove($filelist, $l);
	}
	dprintr($filelist);
	if ($filelist) {
		lfile_put_contents("__path_program_etc/xeninterface.list", implode("\n", $filelist));
	}
}

function dbactionDelete()
{
	global $gbl, $sgbl, $login, $ghtml; 
	$this->deletevif();
	$this->hardstop();
	$this->umountThis();

	if ($this->isLVM()) {
		if ($this->main->isWindows()) {
			lxshell_return("parted", "-s", $this->main->maindisk, "--", "unit", "s", "rm", "1");
			lxshell_return("parted", "-s", $this->main->maindisk, "--", "mklabel", "msdos");
		}
		lvm_remove($this->main->maindisk);
		if (!$this->main->isWindows()) {
			lvm_remove($this->main->swapdisk);
		}
	} else {
		lxfile_rm_rec($this->main->rootdir);
	}

	@ lunlink("__path_program_root/tmp/{$this->main->nname}.createfailed");
	lxfile_rm_rec($this->main->configrootdir);
	lxshell_return("userdel", "-r", $this->main->username);
	lunlink("/etc/xen/auto/{$this->main->nname}.cfg");
}


function toggleStatus()
{
	global $global_shell_out, $global_shell_error, $global_shell_ret;

	if ($this->main->isOn('status')) {
		$ret = $this->start();
		if ($ret) {
			throw new lxException("could_not_start_vps", '', str_replace("\n", ": ", $global_shell_error));
		}
		$ret = lxfile_symlink("{$this->main->configrootdir}/{$this->main->nname}.cfg", "/etc/xen/auto");
	} else {
		$ret = $this->stop();
		lunlink("/etc/xen/auto/{$this->main->nname}.cfg");
	}

	if($ret)
		log_message($ret);
}

function setRootPassword()
{

}



function mount_this_guy()
{
	$this->stop();

	if ($this->main->isWindows()) {
		return;
		throw new lxException("trying_to_mount_windows_image", '', '');
	}

	$mountpoint = "{$this->main->configrootdir}/mnt";
	if ($this->isMounted()) {
		return $mountpoint;
	}

	lxfile_mkdir($mountpoint);


	$loop = $this->main->maindisk;
	lxshell_return("e2fsck", "-y", $loop);

	if ($this->isLVM()) {
		$ret = lxshell_return("mount", $loop, $mountpoint);
	} else {
		$ret = lxshell_return("mount", "-o", "loop", $loop, $mountpoint);
	}

	if ($ret) {
		throw new lxException("could_not_mount_the_root_image");
	}
	return $mountpoint;
}

function takeSnapshot()
{
	lxshell_return("modprobe", "dm-snapshot");
	$tmp = "{$this->main->configrootdir}/snapshot_mount";
	lxfile_mkdir($tmp);
	$tmp = expand_real_root($tmp);
	$size = lvm_disksize($this->main->maindisk);
	$size = $size/3;
	$size = round($size);
	$vgname = $this->main->corerootdir;
	$vgname = fix_vgname($vgname);


	$sfpath = "/dev/$vgname/{$this->main->nname}_snapshot";
	$out = exec_with_all_closed_output("lvdisplay -c $sfpath");

	if (csa($out, ":")) {
		lxshell_return("umount", $sfpath);
		lvm_remove($sfpath);
	}


	$out = exec_with_all_closed_output("lvdisplay -c $sfpath");

	if (csa($out, ":")) {
		throw new lxException("old_snapshot_exists_and_cant_remove");
	}

	$ret = lxshell_return("lvcreate", "-L{$size}M", "-s",  "-n", "{$this->main->nname}_snapshot", $this->main->maindisk);

	if ($ret) {
		throw new lxException("could_not_create_snapshot_lack_of_space");
	}

	if (!$this->main->isWindows()) {
		lxshell_return("e2fsck", "-f", "-y", $sfpath);
		lxshell_return("mount", "-o", "ro", $sfpath, $tmp);
	} else {
		$tmp = $sfpath;
	}


	return $tmp;
}

function changeLocation()
{
	if ($this->main->newlocation === $this->main->corerootdir) {
		throw new lxException("old_new_location_same");
	}

	$this->stop();
	$this->umountThis();
	$this->setMemoryUsage();
	$this->setCpuUsage();
	$this->setDiskUsage();
	$this->__oldlocation = $this->main->corerootdir;
	$this->main->corerootdir = $this->main->newlocation;
	$name = strtil($this->main->nname, ".vm");
	$this->main->maindiskname = "{$name}_rootimg";
	$this->main->swapdiskname = "{$name}_vmswap";
	$this->initXenVars();
	$this->createDisk();
	$this->setSwapUsage();
	$this->createSwap();
	//$this->mount_this_guy();

	if (csb($this->__oldlocation, "lvm:")) {
		$vgname = fix_vgname($this->__oldlocation);
		$oldimage = "/dev/$vgname/{$this->main->maindiskname}";
	} else {
		$oldimage = "{$this->__oldlocation}/{$this->main->nname}/root.img";
	}

	$ret = lxshell_return("dd", "if=$oldimage", "of={$this->main->maindisk}");
	if ($ret) {
		throw new lxException("could_not_clone");
	}


	// Don't do this at all. The saved space is not going to be very important for the short period.
	//lunlink("$this->__oldlocation/{$this->main->nname}/root.img");
	/*
	if (csb($this->__oldlocation, "lvm:")) {
		$vg = fix_vgname($this->__oldlocation);
		lvm_remove("/dev/$vg/{$this->main->swapdiskname}");
	} else {
		lunlink("$this->__oldlocation/{$this->main->nname}/vm.swap");
	}
	*/
	$this->start();

	$ret = array("__syncv_corerootdir" => $this->main->newlocation, "__syncv_maindiskname" => $this->main->maindiskname, "__syncv_swapdiskname" => $this->main->swapdiskname);
	return $ret;

}

function saveXen()
{
	if (self::getStatus($this->main->nname, '/home/xen') !== 'on') {
		return null;
	}
	$tmp = lx_tmp_file("{$this->main->nname}_ram");
	lxshell_return("xm", "save", $this->main->nname, $tmp);
	return $tmp;
}

function restoreXen($file)
{
	if (!$file) {
		return;
	}
	lxshell_return("xm", "restore", $file);
}


function do_backup()
{
	global $gbl, $sgbl, $login, $ghtml; 

	$tmpbasedir = $this->main->__var_tmp_base_dir;

	if ($this->isLVM()) {
		//$file = $this->saveXen();

		if ($this->main->isOn('__var_bc_backupextra_stopvpsflag')) {
			$this->stop();
		}

		try {
			$mountpoint = $this->takeSnapshot();
		} catch (Exception $e) {
			//$this->restoreXen($file);
			$this->start();
			throw $e;
		}


		if ($this->main->isOn('__var_bc_backupextra_stopvpsflag')) {
			$this->start();
		}

		$this->main->__snapshotmount = $mountpoint;
		$this->main->__save_variable['__snapshotmount'] = $mountpoint;

	} else {
		$this->stop();
		$mountpoint = $this->mount_this_guy();
	}

	if (!$this->main->isWindows()) {
		$mountpoint = expand_real_root($mountpoint);
		$list = lscandir_without_dot($mountpoint);
		$list = array_remove($list, "proc");
		if (count($list) < 6) {
			throw new lxException("not_enough_directories_in_vps_root,_possibly_wrong_location", '', '');
		}

	} else {
		$tmpdir = createTempDir($tmpbasedir, "lx_{$this->main->nname}_backup");
		$vgname = fix_vgname($this->main->corerootdir);
		$snapshot = "/dev/$vgname/{$this->main->nname}_snapshot";
		$partition = get_partition($snapshot, $this->main->corerootdir);
		lxshell_return("ntfsfix", $partition);
		$ret = lxshell_return("ntfsclone", "--force", "--save-image", "-O", "$tmpdir/backup.img", $partition);
		if ($ret) { 
			kpart_remove("/dev/mapper/$vgname-{$this->main->nname}_snapshot");
			lvm_remove($snapshot);
			throw new lxException("could_not_clone");
		}
		kpart_remove("/dev/mapper/$vgname-{$this->main->nname}_snapshot");
		$list = array("backup.img");
		$mountpoint = $tmpdir;
		$this->main->__windows_tmpdir = $tmpdir;
		lvm_remove($snapshot);
	}
		
	return array($mountpoint, $list);
}



function do_backup_cleanup($bc)
{
	// I had commented out the starting of the vps after backup. I don't know why. Why is this not done.. The vps should be started after the backup is done.
	$mountpoint = "{$this->main->configrootdir}/mnt";

	if ($this->main->isWindows()) {
		lxfile_rm("{$this->main->__windows_tmpdir}/backup.img");
		lxfile_rm($this->main->__windows_tmpdir);
		return;
	}
	if ($this->isLVM()) {
		lxshell_return("umount", $this->main->__snapshotmount);
		lxfile_rm($this->main->__snapshotmount);
		$vglocation = fix_vgname($this->main->corerootdir);
		$snapshotlvm = "/dev/$vglocation/{$this->main->nname}_snapshot";
		lvm_remove($snapshotlvm);
	} else {
		$this->start();
	}
}


function do_restore($docd)
{
	global $gbl, $sgbl, $login, $ghtml;

	$this->hardstop();
	$this->createDisk();

	$tmpbasedir = $this->main->__var_tmp_base_dir;

	if ($this->checkForSnapshot()) {
		lvm_remove($this->getSnapshotName());
		if ($this->checkForSnapshot()) {
			throw new lxException("snapshot_for_this_exists_and_coudnt_remove");
		}
	}

	if (!$this->main->isWindows()) {
		$mountpoint = $this->mount_this_guy();
		lxshell_unzip_numeric_with_throw($mountpoint, $docd);
		//lxshell_return("tar", "-C", "$mountpoint/dev", "-xzf", "__path_program_root/file/vps-dev.tgz");

		if ($this->main->__old_driver !== 'xen') {
			log_restore("Restoring {$this->main->nname} from a different driver {$this->main->__old_driver} to xen");

			/*
			if (!lxfile_exists("__path_program_home/xen/template/{$this->main->ostemplate}.tar.gz")) {
				throw new lxException("migrating_from_{$this->main->__old_driver}_needs_osImage");
		}
		*/
			//lxshell_return("tar", "-C", $mountpoint, "-xzf", "__path_program_home/xen/template/{$this->main->ostemplate}.tar.gz", "etc/rc.d", "sbin", "etc/hotplug.d", "etc/dev.d", "etc/udev", "lib", "usr", "bin", "etc/inittab", "etc/sysconfig");
			//lxshell_return("tar", "-C", $mountpoint, "-xzf", "__path_program_home/xen/template/{$this->main->ostemplate}.tar.gz", "etc/rc.d", "sbin", "etc/hotplug.d", "etc/dev.d", "etc/udev", "lib", "usr", "bin", "etc/inittab");
			lxfile_cp("../file/sysfile/xen/fstab", "$mountpoint/etc/fstab");
			lxfile_cp("__path_program_root/file/sysfile/xen/inittab", "$mountpoint/etc/inittab");
			lunlink("$mountpoint/etc/mtab");
			lunlink("$mountpoint/etc/init.d/vzquota");
			$this->copyKernelModules();
		}

		lxfile_mkdir("$mountpoint/proc");
		$this->createConfig();
		$this->setMemoryUsage();
		$this->setCpuUsage();
		$this->setSwapUsage();
	} else {
		$tmpdir = createTempDir($tmpbasedir, "lx_{$this->main->nname}_backup");
		lxshell_unzip_with_throw($tmpdir, $docd);
		$partition = $this->getPartition();
		lxshell_return("ntfsclone", "--restore-image", "--force", "-O", $partition, "$tmpdir/backup.img");
		lxfile_tmp_rm_rec("$tmpdir");
		$this->kpart_remove();
	}

	$this->main->status = 'on';

	try {
		$this->toggleStatus();
	} catch (Exception $e) {
	}

	$this->start();

	// Saving state doesn't seem to be an option. The thing is, it is the file system itself that's left in an inconsistent state, and there's little we can do about it.

	/*
	$statefile = "$mountpoint/__hypervm_xensavestate";
	if (lxfile_exists($statefile)) {
		$tmp = lx_tmp_file("/tmp", "xen_ram");
		lxfile_mv($statefile, $tmp);
		$this->umountThis();
		$this->restoreXen($tmp);
		lunlink($tmp);
	} else {
		$this->start();
	}
*/
}



function setCpuUsage()
{
	if (is_unlimited($this->main->priv->cpu_usage)) {
		$cpu = "100" * os_getCpuNum();;
	} else {
		$cpu = $this->main->priv->cpu_usage;
	}
	lxshell_return("xm", "sched-credit", "-d", $this->main->nname, "-c", $cpu);
}

function setMemoryUsage()
{
	if (is_unlimited($this->main->priv->realmem_usage)) {
		$memory = 512;
	} else {
		$memory = $this->main->priv->realmem_usage;
	}

	$this->createConfig();
	lxshell_return("xm", "mem-set", $this->main->nname, $memory);
	lfile_put_contents("{$this->main->configrootdir}/memory.value", $memory);
}

function setSwapUsage()
{

	if ($this->main->isWindows()) {
		return;
	}

	if (is_unlimited($this->main->priv->swap_usage)) {
		$memory = 512;
	} else {
		$memory = $this->main->priv->swap_usage;
	}

	lfile_put_contents("{$this->main->configrootdir}/swap.value", $memory);
}

function setDiskUsage()
{
	if (is_unlimited($this->main->priv->disk_usage)) {
		$diskusage = 3 * 1024;
	} else {
		$diskusage = $this->main->priv->disk_usage ;
	}

	lfile_put_contents("{$this->main->configrootdir}/disk.value", $diskusage);
	
}


function reboot()
{
	global $global_shell_out, $global_shell_error, $global_shell_ret;
	$this->stop();
	$ret = $this->start();

	if ($ret) {
		throw new lxException("could_not_start_vps", '', str_replace("\n", ": ", $global_shell_error));
	}
}


function rebuild()
{

	if (!$this->main->isOn('rebuild_confirm_f')) {
		throw new lxException("need_confirm_rebuild", 'rebuild_confirm_f');
	}


	if ($this->main->isWindows()) {
		$templatefile = "__path_program_home/xen/template/{$this->main->ostemplate}";
	} else {
		$templatefile = "__path_program_home/xen/template/{$this->main->ostemplate}.tar.gz";
	}

	if(!lxfile_nonzero($templatefile)) {
		$this->main->getOsTemplateFromMaster($templatefile);
	}

	if (!lxfile_nonzero($templatefile)) {
		throw new lxException("no_template_and_could_not_download", 'rebuild_confirm_f');
	}

	$this->stop();

	if ($this->main->isNotWindows()) {
		$mountpoint = $this->mount_this_guy();
		if ($this->main->isOn('rebuild_backup_f')) {
			lxfile_mkdir("/home/hypervm/vps/{$this->main->nname}/__backup/");
			$date = date('Y-m-d-') . time();
			$dir = "/home/hypervm/vps/{$this->main->nname}/__backup/rebuild-backup.$date";
			lxfile_cp_rec($mountpoint, $dir);
		}
	}

	$this->umountThis();

	if ($this->main->isNotWindows()) {
		if ($this->isLvm()) {
			lxshell_return("mkfs.ext3", "-F", $this->main->maindisk);
		} else {
			lxfile_rm_rec($this->main->maindisk);
			$this->createDisk();
		}

		$mountpoint = $this->mount_this_guy();
		$ret = lxshell_return("tar", "-C", $mountpoint, '--numeric-owner', "-xpzf", $templatefile);

		if ($ret) {
			throw new lxException("rebuild_failed_could_not_untar");
		}
	} else {
		$templatefile = expand_real_root($templatefile);
		lxshell_return("parted", "-s", $this->main->maindisk, "mklabel", "msdos");
		$this->runParted();
		$partition = $this->getPartition();
		//lxshell_return("dd", "if=$templatefile", "of={$this->main->maindisk}");
		lxshell_return("ntfsclone", "--restore-image", "-O", $partition, $templatefile);
		$this->kpart_remove();
		$this->expandPartitionToImage();
	}


	$this->start();
}


function installkloxo()
{
	$this->rebuild();
}

function recoverVps()
{
	if (!$this->main->isOn('recover_confirm_f')) {
		throw new lxException("need_confirm_recover", 'recover_confirm_f');
	}
	$this->stop();
	$mountpoint = $this->mount_this_guy();
	$this->main->coreRecoverVps($mountpoint);
	$this->start();
}

function setInformation()
{
	//lxshell_return("vzctl", "set", $this->main->vpsid, "--hostname", $this->main->hostname);
}

function createTemplate()
{

	$stem = explode("-", $this->main->ostemplate);
	if ($this->main->isWindows()) {
		$name = "{$stem[0]}-";
	} else {
		$name = "{$stem[0]}-{$stem[1]}-{$stem[2]}-";
	}


	$templatename = "$name{$this->main->newostemplate_name_f}";
	if ($this->main->isWindows()) {
		$tempfpath = "__path_program_home/xen/template/$templatename.img";
	} else {
		$tempfpath = "__path_program_home/xen/template/$templatename.tar.gz";
	}


	$this->stop();

	if ($this->main->isWindows()) {
		$partition = $this->getPartition();
		lxshell_return("ntfsfix", $partition);
		lxshell_return("ntfsclone", "--save-image", "--force", "-O", $tempfpath, $partition);
		$this->kpart_remove();
	} else {
		$list = lscandir_without_dot("{$this->main->configrootdir}/mnt");
		$ret = lxshell_return("tar", "-C", "{$this->main->configrootdir}/mnt", '--numeric-owner', "-czf", $tempfpath, $list);
	}
	$this->start();

	$filepass = cp_fileserv($tempfpath);
	$ret = array("__syncv___ostemplate_filepass" => $filepass, "__syncv___ostemplate_filename" => basename($tempfpath));
	return $ret;

}

function hardstop()
{
	if (self::getStatus($this->main->nname, '/home/xen') !== 'on') {
		//$this->mount_this_guy();
		return;
	}

	lxshell_return("xm", "shutdown", $this->main->nname);

	$count = 0;
	while (self::getStatus($this->main->nname, '/home/xen') === 'on') {
		$count++;
		sleep(5);
		if ($count === 3) {
			lxshell_return("xm", "destroy", $this->main->nname);
			break;
		}
	}

	while (self::getStatus($this->main->nname, '/home/xen') === 'on') {
		sleep(5);
	}

	usleep(100 * 1000);
	sleep(10);


}

function stop()
{
	if (self::getStatus($this->main->nname, '/home/xen') !== 'on') {
		//$this->mount_this_guy();
		return;
	}

	lxshell_return("xm", "shutdown", $this->main->nname);

	sleep(40);

	if (self::getStatus($this->main->nname, '/home/xen') === 'on') {
		lxshell_return("xm", "destroy", $this->main->nname);
	}


	sleep(3);

	if (self::getStatus($this->main->nname, '/home/xen') === 'on') {
		throw new lxException("could_not_stop_vps");
	}

	$this->mount_this_guy();
}

function isMounted()
{
	$mountpoint = "{$this->main->configrootdir}/mnt";
	$mountpoint = expand_real_root($mountpoint);
	$cont = lfile_get_contents("/proc/mounts");
	if (csa($cont, $mountpoint)) {
		return true;
	}
	dprint("$mountpoint is not in /proc/mounts\n");
	return false;
}

function umountThis()
{
	$mountpoint = "{$this->main->configrootdir}/mnt";
	lxshell_return("sync");
	$count = 0;
	while (true) {
		$count++;
		if ($count > 10) {
			throw new lxException("cannot_unmount_after_10_attempts");
		}
		if (!$this->isMounted()) {
			break;
		}

		$ret = lxshell_return("umount", $mountpoint);
		if ($ret) {
			//lxshell_return("umount", "-l", $mountpoint);
			throw new lxException("umounting_file_system_failed");
		}
	}
}

function checkForSnapshot()
{
	if ($this->isLvm()) {
		if (lxfile_exists($this->getSnapshotName())) {
			log_log("critical_snapshot", "Found snapshot for {$this->main->nname} removing...");
			return true;
		}
	}

	return false;
}

function getSnapshotName()
{
	$vgname = fix_vgname($this->main->corerootdir);
	$snap = "/dev/$vgname/{$this->main->nname}_snapshot";
	return $snap;
}


function start() 
{

	if (self::getStatus($this->main->nname, '/home/xen') === 'on') {
		return;
	}

	$this->createConfig();
	$this->createSwap();
	$this->setvif();
	$this->resizeRootImage();

	if ($this->checkForSnapshot()) {
		//lvm_remove($this->getSnapshotName());
	}

	if (!$this->main->isWindows()) {
		$mountpoint = $this->mount_this_guy();
		$this->setInternalParam($mountpoint);
		$this->copyKernelModules();
		$this->umountThis();
	}

	return lxshell_return("xm", "create", "{$this->main->configrootdir}/{$this->main->nname}.cfg"); 
}


function setInternalParam($mountpoint)
{
	$name = $this->main->ostemplate;

	if ($this->main->isWindows()) { return; } 

	if (!$mountpoint) { return; }

	if ($name === 'unknown') { return; }



	$name = strtolower($name);
	$mountpoint = expand_real_root($mountpoint);
	$result = $this->getScriptS($name);
	dprint("Distro Name $name, Scripts: \n");
	dprintr($result);

	$init = strtilfirst($name, "-");

	dprint("File is  $init.inittab\n");
	if (lxfile_exists("../file/sysfile/inittab/$init.inittab")) {
		dprint("Copying $init.inittab\n");
		$content = lfile_get_contents("../file/sysfile/inittab/$init.inittab");
		if ($this->main->text_inittab) {
			$content .= "\n{$this->main->text_inittab}";
		}
		lfile_put_contents("$mountpoint/etc/inittab", $content);
	}

	$iplist = get_namelist_from_objectlist($this->main->vmipaddress_a);
	if ($this->main->mainipaddress) {
		$main_ip = $this->main->mainipaddress;
		$iplist = array_remove($iplist, $main_ip);
	} else {
		$main_ip = array_shift($iplist);
	}

	if ($this->main->networknetmask) {
		$main_netmask = $this->main->networknetmask;
	} else {
		$main_netmask = "255.255.255.0";
	}

	$iplist = implode(" ", $iplist);

	$ipadd = $result['ADD_IP'];
	$sethostname = $result['SET_HOSTNAME'];
	$setuserpass = $result['SET_USERPASS'];
	$ipdel = $result['DEL_IP'];

        if ($this->main->networkgateway) {
                $gw = $this->main->networkgateway;
        } else {
                $gw = os_get_network_gateway();
        }

<<<<<<< HEAD
        $gwn = strtil($gw, '.') . '.0';
=======
        $gwn = strtil($gw, ".") . ".0";
>>>>>>> 05e46d6ac4912d091d65d872af143798c1669c1e

        $hostname = $this->main->hostname;
        if (!$hostname) { $hostname = os_get_hostname(); }

<<<<<<< HEAD
	if ($result['STARTUP_SCRIPT'] != 'systemd'){
            $name = createTempDir("$mountpoint/tmp", 'xen-scripts');
=======
	if ($result['STARTUP_SCRIPT'] != "systemd"){
            $name = createTempDir("$mountpoint/tmp", "xen-scripts");
>>>>>>> 05e46d6ac4912d091d65d872af143798c1669c1e
	    lxfile_cp_rec("__path_program_root/bin/xen-dists/scripts/functions", $name);
            lxfile_cp_rec("__path_program_root/bin/xen-dists/scripts/$ipadd", $name);
	    lxfile_cp_rec("__path_program_root/bin/xen-dists/scripts/$sethostname", $name);
            lxfile_cp_rec("__path_program_root/bin/xen-dists/scripts/$setuserpass", $name);
            lxfile_cp_rec("__path_program_root/bin/xen-dists/scripts/$ipdel", $name);

	    $basepath = strfrom($name, $mountpoint);
	    lfile_put_contents("$name/tmpfile.sh", "source /$basepath/functions\nsource /$basepath/$ipdel\n");
	    $delipstring = "IPDELALL=yes chroot $mountpoint bash /$basepath/tmpfile.sh";

            log_shell($delipstring);
            log_shell(system($delipstring,$ret1) . ":return $ret1");

	    putenv("VE_STATE=stopped");
	    lfile_put_contents("$name/tmpfile.sh", "source /$basepath/functions\n source /$basepath/$ipadd\n");
	    $string = "IPDELALL=yes MAIN_NETMASK=$main_netmask MAIN_IP_ADDRESS=$main_ip IP_ADDR=\"$iplist\" NETWORK_GATEWAY=$gw NETWORK_GATEWAY_NET=$gwn chroot $mountpoint bash /$basepath/tmpfile.sh";

	    log_shell($string);
<<<<<<< HEAD
	    log_shell(system($string, $ret1) . ":return $ret1");
=======
	    log_shell(system($string,$ret1).":return $ret1");
>>>>>>> 05e46d6ac4912d091d65d872af143798c1669c1e

	    lfile_put_contents("$name/tmpfile.sh", "source /$basepath/functions\n source /$basepath/$sethostname\n");
	    $string = "HOSTNM=$hostname chroot $mountpoint bash /$basepath/tmpfile.sh";
	    log_shell($string);
	    log_shell(system($string,$ret1).":return $ret1");

	    if (($this->main->subaction === 'rebuild') || ($this->main->dbaction === 'add') || ($this->main->isOn('__var_rootpassword_changed') && $this->main->rootpassword)) {
		$rootpass = "root:{$this->main->rootpassword}";
		lfile_put_contents("$name/tmpfile.sh", "source /$basepath/functions\n source /$basepath/$setuserpass\n");
		$string = "USERPW=$rootpass chroot $mountpoint bash /$basepath/tmpfile.sh";
		log_shell($string);
		log_shell(system($string));
	    }
		
		lxfile_rm_rec($name);
	}
<<<<<<< HEAD
	else if ($result['STARTUP_SCRIPT'] == 'systemd'){
		$script_dir = createTempDir("$mountpoint", "hypervm-runonce");
		lxfile_cp_rec("__path_program_root/bin/xen-dists/scripts/functions", $script_dir);
		lxfile_cp_rec("__path_program_root/bin/xen-dists/scripts/$ipadd", $script_dir);
		lxfile_cp_rec("__path_program_root/bin/xen-dists/scripts/$sethostname", $script_dir);
        lxfile_cp_rec("__path_program_root/bin/xen-dists/scripts/$setuserpass", $script_dir);
		lxfile_cp_rec("__path_program_root/bin/xen-dists/scripts/$ipdel", $script_dir);
		$basepath = strfrom($script_dir, $mountpoint);
		$startupdir = 'lib/systemd/system';
		$startupscript = 'fedora-startup.service';

		$setrootpass = '';
		if (($this->main->subaction === 'rebuild') || ($this->main->dbaction === 'add') || ($this->main->isOn('__var_rootpassword_changed') && $this->main->rootpassword)) {
			$rootpass = "root:{$this->main->rootpassword}";
			$setrootpass = " & USERPW=$rootpass source $basepath/$setuserpass";
		}

		$run_once_script = "#!/bin/bash\n" .
			"source $basepath/functions\n" .
			'(' .
			"IPDELALL=yes source $basepath/$ipdel" .
			" & IPDELALL=yes VE_STATE=stopped MAIN_NETMASK=$main_netmask MAIN_IP_ADDRESS=$main_ip IP_ADDR=\"$iplist\" NETWORK_GATEWAY=$gw NETWORK_GATEWAY_NET=$gwn source $basepath/$ipadd" .
			" & HOSTNM=$hostname source $basepath/$sethostname" .
			"$setrootpass)\n" .
			"service fedora-startup disable\nrm -f /$startupdir/$startupscript\nrm -rf $basepath";
		lfile_put_contents("$script_dir/hypervm-runonce.sh", $run_once_script);

		lxfile_cp_rec("__path_program_root/bin/xen-dists/scripts/$startupscript", "$mountpoint/$startupdir");
		lfile_put_contents("$mountpoint/$startupdir/$startupscript", 
			lfile_get_contents("$mountpoint/$startupdir/$startupscript") .
			"ExecStart=$basepath/hypervm-runonce.sh\n");
		system("ln -s /lib/systemd/system/fedora-startup.service $mountpoint/etc/systemd/system/multi-user.target.wants/fedora-startup.service");
		system("chmod 755 $script_dir/hypervm-runonce.sh");
	}

=======
	else if ($result['STARTUP_SCRIPT'] == "systemd"){
			$script_dir = createTempDir("$mountpoint", "hypervm-runonce");
            lxfile_cp_rec("__path_program_root/bin/xen-dists/scripts/functions", $script_dir);
            lxfile_cp_rec("__path_program_root/bin/xen-dists/scripts/$ipadd", $script_dir);
            lxfile_cp_rec("__path_program_root/bin/xen-dists/scripts/$sethostname", $script_dir);
            lxfile_cp_rec("__path_program_root/bin/xen-dists/scripts/$setuserpass", $script_dir);
            lxfile_cp_rec("__path_program_root/bin/xen-dists/scripts/$ipdel", $script_dir);
            $basepath = strfrom($script_dir, $mountpoint);
			$startupdir = "lib/systemd/system";
			$startupscript = "fedora-startup.service";
			$ro_a = "#!/bin/bash\n";
			$ro_b = "source $basepath/functions\n";
			$ro_c = "(";
			$ro_d = "IPDELALL=yes source $basepath/$ipdel";
			$ro_e = " & IPDELALL=yes VE_STATE=stopped MAIN_NETMASK=$main_netmask MAIN_IP_ADDRESS=$main_ip IP_ADDR=\"$iplist\" NETWORK_GATEWAY=$gw NETWORK_GATEWAY_NET=$gwn source $basepath/$ipadd";
			$ro_f = " & HOSTNM=$hostname source $basepath/$sethostname";
			$ro_g = ")\n";
			if (($this->main->subaction === 'rebuild') || ($this->main->dbaction === 'add') || ($this->main->isOn('__var_rootpassword_changed') && $this->main->rootpassword)) {
                $rootpass = "root:{$this->main->rootpassword}";
				$ro_g = " & USERPW=$rootpass source $basepath/$setuserpass)\n";
            }
			$ro_h = "service fedora-startup disable\nrm -f /$startupdir/$startupscript\nrm -rf $basepath";
            lfile_put_contents("$script_dir/hypervm-runonce.sh", $ro_a.$ro_b.$ro_c.$ro_d.$ro_e.$ro_f.$ro_g.$ro_h);

			lxfile_cp_rec("__path_program_root/bin/xen-dists/scripts/$startupscript", "$mountpoint/$startupdir");
			lfile_put_contents("$mountpoint/$startupdir/$startupscript", 
				lfile_get_contents("$mountpoint/$startupdir/$startupscript").
				"ExecStart=$basepath/hypervm-runonce.sh\n");
			system("ln -s /lib/systemd/system/fedora-startup.service $mountpoint/etc/systemd/system/multi-user.target.wants/fedora-startup.service");
			system("chmod 755 $script_dir/hypervm-runonce.sh");
	}


>>>>>>> 05e46d6ac4912d091d65d872af143798c1669c1e
	if ($this->main->nameserver) {
		$nlist = explode(" ", $this->main->nameserver);
		$nstring = null;
		foreach($nlist as $l) {
			$nstring .= "nameserver $l\n";
		}
		lfile_put_contents("$mountpoint/etc/resolv.conf", $nstring);
	}

	if ($this->main->timezone) {
		lxfile_rm("$mountpoint/etc/localtime");

		$cmdstring = "ln -sf ../usr/share/zoneinfo/{$this->main->timezone} $mountpoint/etc/localtime";
		log_log("localtime", $cmdstring);
		do_exec_system('__system__', "/", $cmdstring, $out, $err, $ret, null);
		//lxfile_cp("/usr/share/zoneinfo/{$this->main->timezone}", "$mountpoint/etc/localtime");
	}

	lunlink("$mountpoint/etc/sysconfig/network-scripts/ifcfg-venet0");
	lunlink("$mountpoint/etc/sysconfig/network-scripts/ifcfg-venet0:0");

	$this->main->doKloxoInit($mountpoint);
}

function getScriptS($name)
{
	$v = $name;
	while (true) {
		$v = strtil($v, "-");
		if (!$v) {
			$v = 'default';
			break;
		}
		dprint("Checking for conf $v\n");
		if (lxfile_exists("__path_program_root/bin/xen-dists/$v.conf")) {
			break;
		}

		if (!csa($v, "-")) {
			$v = "default";
			break;
		}

	}
	$file = "__path_program_root/bin/xen-dists/$v.conf";
	$list = lfile_trim($file);

	foreach($list as $l) {
		if (csb($l, "#")) {
			continue;
		}

		if (csa($l, "=")) {
			$v = explode("=", $l);
			$result[$v[0]] = $v[1];
		}
	}
	return $result;
}


function changeUserPassword()
{
	$pass = $this->main->password;
	lxshell_return("usermod", "-p", $pass, $this->main->username);
}



function dbactionUpdate($subaction)
{

	global $gbl, $sgbl, $login, $ghtml; 



	switch($subaction) {
		case "changelocation":
			return $this->changelocation();
			break;

		case "rebuild":
			$this->rebuild();
			break;

		case "recovervps":
			$this->recovervps();
			break;


		case "mount":
			$this->mount_this_guy();
			break;

		case "createuser":
			return $this->main->syncCreateUser();
			break;


		case "full_update":
			$this->setDiskUsage();
			$this->setMemoryUsage();
			$this->setCpuUsage();
			$this->setSwapUsage();
			$this->toggleStatus();
			break;


		case "password":
			$this->changeUserPassword();
			break;

		case "createtemplate":
			return $this->createTemplate();
			break;

		case "disable":
		case "enable":
		case "toggle_status":
			$this->toggleStatus();
			break;


		case "change_disk_usage":
			$this->setDiskUsage();
			break;

		case "change_realmem_usage":
			$this->setMemoryUsage();
			break;


		case "change_swap_usage":
			$this->setSwapUsage();
			break;

		case "change_process_usage":
			//$this->setProcessUsage();
			break;
			
		case "rootpassword":
			$this->setRootPassword();
			break;

		case "installkloxo":
			$this->installKloxo();
			break;

		case "network":
		case "information":
			$this->setInformation();
			$this->setDhCP();
			break;

		case "add_vmipaddress_a":
			$this->setDhCP();
			break;

		case "delete_vmipaddress_a":
			$this->setDhCP();
			break;

		case "boot":
			$this->start();
			break;

		case "poweroff":
			$this->stop();
			$this->mount_this_guy();
			break;


		case "reboot":
			$this->reboot();
			break;

		case "change_cpu_usage":
			$this->setCpuUsage();
			break;

		case "hardpoweroff":
			$this->hardstop();
			break;


		case "createconfig":
			$this->createConfig();
			$ret = lxfile_symlink("{$this->main->configrootdir}/{$this->main->nname}.cfg", "/etc/xen/auto");
			break;

		case "graph_traffic":
			return rrd_graph_vps("traffic", "xen-{$this->main->vifname}.rrd", $this->main->rrdtime);
			break;

		case "graph_cpuusage":
			return rrd_graph_vps("cpu", "{$this->main->nname}.rrd", $this->main->rrdtime);
			break;


	}
}

function setDhCP()
{
	if (!$this->main->isWindows()) {
		return;
	}

	$this->main->iplist = get_namelist_from_objectlist($this->main->vmipaddress_a);
	$res = merge_array_object_not_deleted($this->main->__var_win_iplist, $this->main);
	dhcp__dhcpd::createDhcpConfFile($res);
}

function setProcessUsage()
{
}

static function getCompleteStatus($list)
{
	foreach($list as $l) {
		$r['status'] = self::getStatus($l['nname'], '/home/xen');
		$disk = self::getDiskUsage($l['diskname'], $l['winflag'], $l['corerootdir']);
		$r['ldiskusage_f'] = $disk['used'];
		$res[$l['nname']] = $r;
	}
	return $res;
}

}
