<?php 

include_once "htmllib/lib/include.php";

include_once "lib/vpsbackuplib.php";
exit_if_another_instance_running();

$vpb = new vpsbackup();

try { 
	$vpb->main();
} catch( Exception $e ) {
	print("Program Did not Complete because of error {$e->getMessage()}\n");
	exit;
}


class vpsbackup {
public $bserver_list;
public $global_list;


function main()
{
	global $gbl, $sgbl, $login, $ghtml; 
	global $argv;

	initProgram('admin');
	$login->loadAllObjects('vps');

	$this->bserver_l = $login->getList('centralbackupserver');
	$list = $login->getList('vps');

	foreach($this->bserver_l as $bso) {
		$bso->setMyselfUp();
	}

	$opt = parse_opt($argv);

	if (!isset($opt['newarg'])) {
		$mess = "\n\nThe architecture of centralized backup has been completely rewritten, and now we have per slave backup-server; you will need to supply --newarg=true for this to work. ";
		$mess .= "More info at http://wiki.lxcenter.org/.";
		print($mess);
		log_log("centralbackup_flag", $mess);
		send_mail_to_admin("Central Backup Failed", $mess);
		exit;
	}


	$this->stopvps = opt_get_single_flag($opt, 'stopvps');
	//$stopxen = opt_get_single_flag($opt, 'stopxen');
	//$stopopenvz = opt_get_single_flag($opt, 'stopopenvz');


	foreach($list as $l) {
		$this->backup_one_vps($l);
	}

	foreach((array) $this->global_list as $k => $s) {
		//$res = rl_exec_get(null, $k, 'remove_scpid', array($backupiddsa));
	}

}

function backup_one_vps($l)
{
	if (!$l->priv->isOn('centralbackup_flag')) {
		print("Central Backup flag is off for vps $l->nname... skipping...\n");
		return;
	}

	$l->truehostname = getInternalNetworkIp($l->syncserver);


	$bserver = $l->getBackupServer();

	if ($l->isWindows()) {
		print("Windows Cannot be backed up...\n");
		return;
	}


	if (is_disabled_or_null($bserver)) {
		$msg = "Backup server for $l->syncserver is disabled.. Skipping $l->nname\n";
		print($msg);
		log_log("centralbackup_error", $msg);
		return;
	}

	if (!isset($this->bserver_l[$bserver])) {
		print("The backup server $bserver doesn't exist\n");
		return;
	}

	$bs = $this->bserver_l[$bserver];


	if (!isset($this->global_list[$l->syncserver])) {

		try {
			$res = rl_exec_get(null, $l->syncserver,  'setup_scpid', array($bs->backupiddsa));
		} catch( Exception $e ) {
			$msg = "Could not connect to $l->syncserver Skipping $l->nname: {$e->getMessage()}\n";
			log_log("centralbackup_error", $msg);
			print($msg);
			return;
		}

		$this->addknownHost($bs, "$l->truehostname $res");
		$this->global_list[$l->syncserver] = $l->truehostname;
	} else {
	}

	$msg = "Backing Up $l->nname on $l->truehostname to $bs->nname($bs->ssh_port):$bs->snapshotdir \n";
	log_log("centralbackup", $msg);
	print($msg);
	$l->setUpdateSubaction('top_level_central_back');

	$l->__var_bc_backupextra_stopvpsflag = ($this->stopvps? 'on': 'off');


	try {
		$res = rl_exec_set(null, $l->syncserver,  $l);
	} catch( Exception $e ) {
		$msg = "Failed Preparing... Skipping $l->nname on $l->syncserver: {$e->getMessage()}\n";
		log_log("centralbackup_error", $msg);
		print($msg);
		return;
	}

	$l->subaction = null;
	if ($res['savelist']) foreach($res['savelist'] as $k => $v) {
		$l->$k = $v;
	}

	$l->__save_bc = $res['back'];
	if ($l->isXen()) {
		$localdir = $l->nname;
	} else {
		$localdir = "$l->vpsid-$l->nname";
	}
	$ssh_port_for_slave = db_get_value("sshconfig", $l->syncserver, "ssh_port");
	if (!$ssh_port_for_slave) { $ssh_port_for_slave = "22"; }

	$l->__var_remotedir = $res['back'][0];
	$l->__var_ssh_port_for_slave = $ssh_port_for_slave;
	$this->run_rsnapshot($bs, $l);
	$l->setUpdateSubaction('top_level_central_back_clean');
	$res = rl_exec_set(null, $l->syncserver,  $l);
}






function run_rsnapshot($bs, $vps)
{
	$machine = $vps->truehostname;
	$corerootdir = $bs->snapshotdir; 
	$vmtype =  $vps->ttype;
	$template = $vps->ostemplate;
	$vmname = $vps->nname;
	$ssh_port_for_slave = $vps->__var_ssh_port_for_slave;
	$remotedir = $vps->__var_remotedir;
	$backup_ssh_port_string = $bs->ssh_port_string;
	$backupserver = $bs->nname;

	if (!$remotedir) {
		print("Critical Error, no remotedir set for $vmname\n");
		return;
	}

	print_time('rsnapshot');
	if (!lxfile_exists("../file/rsnapshot.conf")) {
		lxfile_cp("../file/rsnapshot.conf.dist", "../file/rsnapshot.conf");
	}
	$string  = lfile_get_contents("../file/rsnapshot.conf");
	$string .= "\n";

	$string .= "ssh_args	-p $ssh_port_for_slave\n";
	$string .= "interval	daily	$bs->backup_num\n";

	$string .= "logfile	$corerootdir/rsnapshot.log\n";
	$string .= "lockfile	$corerootdir/rsnapshot.pid\n";
	$string .= "snapshot_root	$corerootdir/vps/$vmtype/$vmname\n";
	$string .= "backup	root@$machine:$remotedir/*	./\n";
	$cmd = "ssh $backup_ssh_port_string $backupserver '(tmpnm=/tmp/rsnapshot_config.$$ ; cat > \$tmpnm ; sh ./lxvpsbackupserver.sh $corerootdir $vmtype $template $vmname \$tmpnm ; )'";
	$cmd = "echo '$string' | $cmd";
	print("Execing rsnapshot\n");
	exec($cmd, $output, $returnvalue);
	$msg = print_time("rsnapshot", "Rsnaphost of $machine $remotedir took:", -1);
	print("\n$msg\n");
	log_log("centralbackup", $msg);
	log_log("rsnapshot_log", implode("\n", $output));
}

function addknownHost($bs, $rsakey)
{
	$backup_ssh_port_string = $bs->ssh_port_string;
	$server = $bs->nname;
	$rsakey = trim($rsakey);
	print("Adding known host...\n");
	$cmd = "ssh $backup_ssh_port_string $bs->nname \"(if ! grep -i '$rsakey' ~/.ssh/known_hosts ; then  echo '$rsakey' >> ~/.ssh/known_hosts; fi)\"";
	//dprint($cmd);
	exec($cmd);

}


}
