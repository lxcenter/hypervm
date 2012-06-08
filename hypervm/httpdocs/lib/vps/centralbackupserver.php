<?php 

class centralbackupserver extends Lxdb {

static $__desc = array("", "",  "central_backup_server");
static $__desc_ssh_port = array("n", "",  "ssh_port");
static $__desc_nname = array("n", "",  "backup_server_ip/name", 'a=show');
static $__desc_servername = array("n", "",  "backup_server_ip");
static $__desc_slavename = array("n", "",  "slave-id");
static $__desc_snapshotdir = array("n", "",  "back_root_directory");
static $__desc_enable_flag = array("f", "",  "status");
static $__desc_backup_num = array("n", "",  "number_of_backups");
static $__acdesc_show = array("", "",  "central_backup_server");


function createShowUpdateform()
{
	$uflist['edit'] = null;
	return $uflist;
}

function isSync()
{
	return false;
}

static function createListAlist($parent, $class)
{
	$alist[] = "a=list&c=$class";
	$alist[] = "a=addform&c=$class";
	$alist[] = "o=general&a=updateform&sa=browsebackup";
	return $alist;
}


static function createListNlist($parent, $class = NULL)
{
	//$nlist['enable_flag'] = '5%';
	$nlist['nname'] = '100%';
	$nlist['ssh_port'] = '40%';
	$nlist['snapshotdir'] = '40%';
	$nlist['backup_num'] = '40%';
	return $nlist;
}

function updateform($subaction, $param)
{
	//$vlist['enable_flag'] = null;
	$vlist['nname'] = array('M', $this->nname);
	$vlist['slavename'] = array('s', self::get_slave_list());
	$vlist['ssh_port'] = array('m', '22');
	$vlist['snapshotdir'] = null;
	$this->setDefaultValue("backup_num", "3");
	$vlist['backup_num'] = null;
	return $vlist;
}


static function get_slave_list()
{
	global $gbl, $sgbl, $login, $ghtml; 
	$list = $login->getList('pserver');
	$list = get_namelist_from_objectlist($list);
	$list = add_disabled($list);
	return $list;
}

static function addform($parent, $class, $typetd = null)
{
	//$vlist['enable_flag'] = null;
	global $gbl, $sgbl, $login, $ghtml; 
	$vlist['nname'] = null;


	$vlist['slavename'] = array('s', self::get_slave_list());
	$vlist['ssh_port'] = array('m', '22');
	$vlist['snapshotdir'] = null;
	$vlist['backup_num'] = array('m', '3');
	$ret['variable'] = $vlist;
	$ret['action'] = 'add';
	return $ret;
}

function setMyselfUp()
{
	$backupserver = $this->nname;
	$this->ssh_port_string = "-p $this->ssh_port";
	$backup_ssh_port_string = $this->ssh_port_string;

	print("Getting id_dsa\n");
	$backupiddsa = exec("ssh $backup_ssh_port_string $backupserver \"if ! [ -f ~/.ssh/id_dsa.pub ] ; then  ssh-keygen -d -q -N '' -f ~/.ssh/id_dsa ; fi ; cat ~/.ssh/id_dsa.pub\n\"");

	$backupiddsa = trim($backupiddsa);

	exec("rsync -e \"ssh $backup_ssh_port_string\" ../bin/common/rsnapshot $backupserver:");
	exec("rsync -e \"ssh $backup_ssh_port_string\" ../bin/lxvpsbackupserver.sh $backupserver:");
	exec("ssh $backup_ssh_port_string $backupserver chmod 755 rsnapshot lxvpsbackupserver.sh");
	$this->backupiddsa = $backupiddsa;
}


}
