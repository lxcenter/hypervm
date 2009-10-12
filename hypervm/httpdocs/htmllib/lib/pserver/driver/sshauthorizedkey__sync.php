<?php 

class sshauthorizedkey__sync extends Lxdriverclass {


function writeAuthorizedKey($key)
{
	if_demo_throw_exception('sshkey');
	$username = $this->main->username;

	$p = os_get_home_dir($username);
	if (!$p) { return; }
	lxfile_mkdir("$p/.ssh");

	$f = "$p/.ssh/authorized_keys";
	lfile_put_contents($f, $key);
	lxfile_unix_chown_rec("$p/.ssh", "$username:$username");
	lxfile_unix_chmod("$p/.ssh", "0700");
	lxfile_unix_chmod($f, "0700");
}

static function readAuthorizedKey($username)
{
	$p = os_get_home_dir($username);

	if ($p === '/tmp' && $username) {
		lxfile_mkdir("/home/$username");
		lxshell_return("usermod", "-d", "/home/$username", $username);
		lxfile_unix_chown_rec("/home/$username", "$username:$username");
		$p = "/home/$username";
	}

	if (!$p) { return; }

	$f = "$p/.ssh/authorized_keys";
	if (lxfile_exists("{$f}2")) {
		$s = lfile_get_contents("{$f}2");
		$s = "\n$s\n";
		lfile_put_contents($f, $s, FILE_APPEND);
		lunlink("{$f}2");
	}
	return lfile_get_contents($f);
}

function getCurrentAuthKey()
{

	$res = self::getAuthorizedKey($this->main->username);
	foreach($res as $k => $v) {
		if ("{$this->main->syncserver}___{$v['nname']}" === $this->main->nname) {
			continue;
		}
		$output[] = $v['full_key'];
	}

	return $output;
}

function dbactionAdd()
{
	$output = $this->getCurrentAuthKey();
	$output[] = $this->main->full_key;
	$output = implode("\n", $output);
	$this->writeAuthorizedKey($output);
}

function dbactionDelete()
{
	//dprintr($this);
	$output = $this->getCurrentAuthKey();
	$output = implode("\n", $output);
	$this->writeAuthorizedKey($output);
}


static function getAuthorizedKey($username)
{
	$v = self::readAuthorizedKey($username);
	$list = explode("\n", $v);

	foreach($list as $l) {
		$l = trim($l);
		if (!$l) { continue; }
		$l = trimSpaces($l);
		$vv = explode(" ", $l);
		$r['nname'] = fix_nname_to_be_variable_without_lowercase($vv[1]);
		$r['full_key'] = $l;
		$r['key'] = substr($vv[1], 0, 50);;
		$r['key'] .= " .....";
		$r['hostname'] = $vv[2];
		$r['username'] = $username;
		$r['type'] = $vv[0];
		$res[$r['nname']] = $r;
	}

	return $res;

}
}

