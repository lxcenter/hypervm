<?php 

function lxshell_expect($strtype, $cmd)
{
	global $gbl, $sgbl, $login, $ghtml; 

	$a = array("ntfsresize" => "expect \"Are you sure you want to proceed (y/[n])? \"\nsend \"y\\r\"\n\n");
	$t = lx_tmp_file("expect");
	$string = $a['ntfsresize'];
	lfile_put_contents($t, "spawn $cmd\n$string");
	log_shell("expect $t $cmd");
	system("expect $t");
	//lunlink($t);
}

function lxshell_getzipcontent($path)
{
	
	$type = os_getZipType($path);
	if ($type === 'zip') {
		return lxshell_output("unzip", "-l", $path);
	} else if ($type === 'tgz') {
		return lxshell_output("tar", "-tzf", $path);
	} else if ($type === 'tar') {
		return lxshell_output("tar", "-tf", $path);
	}
}

function lxshell_exists_in_zip($archive, $file)
{
	$dir = createTempDir("/tmp", "testzip");
	$ret = lxshell_unzip($dir, $archive, array($file));
	lxfile_tmp_rm_rec($dir);

	if (!$ret) { return true; }
	return false;

}

// Normally the value is returned in MBs, but if you want to, you can force it to be bytes.
function lxfile_dirsize($path, $byteflag = false)
{

	global $global_dontlogshell;
	// This actually has to return the in Mega byts. The calculation is not actually correct now, and I need to find out how to properly do it.

	$old = $global_dontlogshell;
	$global_dontlogshell = true;

	$path = expand_real_root($path);
	if (!lxfile_exists($path)) {
		return 0;
	}
	$rt = lxshell_output("du", "-sc", $path);
	$os  = preg_replace("/\s+/", ":", $rt);
	$ret = explode(":", $os);
	$t = $ret[2];

	$global_dontlogshell = $old;

	if ($byteflag) {
		return round($t * (1024), 1);
	} else {
		return round($t/(1024), 1);
	}
}

function lxfile_symlink($src, $dst)
{
	$src = expand_real_root($src);
	$dst = expand_real_root($dst);
	if (is_dir($dst)) {
		$dst = "$dst/" . basename($src);
	}
	log_filesys("Linking $src to $dst");
	symlink($src, $dst);
}


function kpart_remove($disk)
{
	lxshell_return("kpartx", "-d", $disk);
}

function get_partition($disk, $root)
{
	$lv = basename($disk);
	$root = fix_vgname($root);
	$path = "/dev/mapper/$root-$lv";
	lxshell_return("kpartx", "-a", $path);
	$out = lxshell_output("kpartx", "-l", $path);
	$o = explode("\n", $out);
	$o = trimSpaces($o[0]);
	$o = explode(" ", $o);
	$partition = trimSpaces($o[0]);
	$partition = "/dev/mapper/$partition";
	//lxshell_return("ntfsfix", $partition);
	return $partition;
}

function get_free_loop()
{
	global $global_shell_error, $global_shell_ret, $global_shell_out;
	lxfile_unix_chmod("__path_program_root/sbin/findfreeloop", "0755");
	$ret = lxshell_return("__path_program_root/sbin/findfreeloop");

	$loop = trim($global_shell_out);
	if (!$ret) {
		return $loop;
	}

	$num = strfrom($loop, "/dev/loop");

	if ($num >= 128) {
		throw new lxException("could_not_find_free_loop");
	}

	lxshell_return("mknod", "-m660", $loop, "b", "7", $num);
	return $loop;
}

function lxshell_zip_add($dir, $zipname, $filelist)
{
	$ret = lxshell_zip_core("zipadd", $dir, $zipname, $filelist);
	return $ret;
}

function lxshell_zip($dir, $zipname, $filelist)
{
	$ret = lxshell_zip_core("zip", $dir, $zipname, $filelist);
	return $ret;
}

function lxshell_tgz($dir, $zipname, $filelist)
{
	$ret = lxshell_zip_core("tgz", $dir, $zipname, $filelist);
	return $ret;
}

function lxshell_tar($dir, $zipname, $filelist)
{
	$ret = lxshell_zip_core("tar", $dir, $zipname, $filelist);
	return $ret;
}

function lxshell_zip_core($updateflag, $dir, $zipname, $filelist)
{
	$dir = expand_real_root($dir);
	foreach($filelist as &$__f) {
		$__f = expand_real_root($__f);
	}
	$zipname = expand_real_root($zipname);


	$files = null;

	if ($filelist) {
		foreach($filelist as &$__nf) {
			$__nf = "'$__nf'";
		}
		$files = implode(" ", $filelist);
	}


	if ($updateflag === 'zipadd') {
		$command = "zip -y -rq -u";
	} else if ($updateflag === 'zip'){
		$command = "zip -y -rq";
	} else if ($updateflag === 'tar') {
		$command = "tar -cf";
	} else {
		$command = "tar -czf";
	}

	if ($zipname[0] !== '/') {
		$fullpath = getcwd() . "/$zipname";
	} else {
		$fullpath = $zipname;
	}

	if (!$files) {
		lxfile_touch("$dir/lxblank_file");
		$files = "'lxblank_file'";
	}

	print_time("zipfile");
	$fcmd = "$command $fullpath $files";
	$fcmd = str_replace(";", "", $fcmd);
	do_exec_system("__system__", $dir, "nice -n 15 $fcmd", $out, $err, $ret, null);
	print_time("zipfile", "Ziptook");
	return $ret;
}


function lxshell_unzip($dir, $file, $filelist = null)
{
	$dir = expand_real_root($dir);
	$file = expand_real_root($file);

	$files = null;

	if ($filelist) {
		foreach($filelist as &$__nf) {
			$__nf = "'$__nf'";
		}
		$files = implode(" ", $filelist);
	}

	if ($file[0] !== '/') {
		$fullpath = getcwd() . "/$file";
	} else {
		$fullpath = $file;
	}
	$ztype = os_getZipType($fullpath);

	if ($ztype === 'tgz') {
		$command = "tar -xzf";
	} else if ($ztype === 'tar') {
		$command = "tar -xf";
	} else {
		$command = "unzip -oq";
	}


	$fcmd = "$command $fullpath $files";
	$fcmd = str_replace(";", "", $fcmd);
	do_exec_system("__system__", $dir, "nice -n 15 $fcmd", $out, $err, $ret, null);
	return $ret;
}


function lxshell_unzip_numeric($dir, $file, $filelist = null)
{
	$dir = expand_real_root($dir);
	$file = expand_real_root($file);

	$files = null;

	if ($filelist) {
		foreach($filelist as &$__nf) {
			$__nf = "'$__nf'";
		}
		$files = implode(" ", $filelist);
	}

	if ($file[0] !== '/') {
		$fullpath = getcwd() . "/$file";
	} else {
		$fullpath = $file;
	}
	$ztype = os_getZipType($fullpath);

	if ($ztype === 'tgz') {
		$command = "tar --numeric-owner -xzf";
	} else if ($ztype === 'tar') {
		$command = "tar --numeric-owner -xf";
	} else {
		$command = "unzip -oq";
	}


	do_exec_system("__system__", $dir, "nice -n 15 $command $fullpath $files", $out, $err, $ret, null);
	return $ret;
}


function os_getZipType($file)
{
	$out = lxshell_output("file", "-b", $file);
	if (csa($out, "gzip")) {
		return "tgz";
	} else if (csa($out, "tar")) {
		return "tar";
	} else {
		return "zip";
	}
}

function lxfile_get_uncompressed_size($file)
{
	global $global_dontlogshell;

	$tmp = $global_dontlogshell;

	$global_dontlogshell = true;

	$out = lxshell_output("gzip", "-l", $file);
	if (csa($out, "not in")) {
		return 0;
	}
	$out = trim($out);
	if (!$out) {
		return 0;
	}
	$out = explode("\n", $out);
	$out = trimSpaces($out[1]);
	$out = explode(" ", $out);

	$global_dontlogshell = $tmp;

	return $out[1];
}

function lxfile_tmp_rm_rec($file)
{
	$file = expand_real_root($file);
	$file = remove_extra_slash($file);
	if ($file == '/') {
		return;
	}
	lxshell_return("rm", "-r", $file);
}


function lxfile_rm_content($dir)
{
	$username = "__system__";
	$dir = expand_real_root($dir);


	$list = lscandir_without_dot($dir);

	foreach($list as $l) {
		lxfile_rm("$dir/$l");
	}
}

function lxfile_rm_rec_content($file)
{
	$file = expand_real_root($file);
	$file = remove_extra_slash($file);
	$list = explode("/", $file);
	if (count($list) <= 2) {
		return;
		//throw new lxException("recursive_removal_low_level_directories_not_allowed$file", '', $file);
	}

	if (preg_match("/\*/", $file)) {
		throw new lxException('no_stars_allowed', '');
	}

	$list = lscandir_without_dot($file);

	foreach($list as $l) {
		if (!$l) { continue; }
		lxshell_return("rm", "-r", "$file/$l");
	}

}

function lxfile_rm_rec($file)
{
	$file = expand_real_root($file);
	$file = remove_extra_slash($file);
	$list = explode("/", $file);
	if (count($list) <= 2) {
		return;
		//throw new lxException("recursive_removal_low_level_directories_not_allowed$file", '', $file);
	}

	if (preg_match("/\*/", $file)) {
		throw new lxException('no_stars_allowed', '');
	}
	lxshell_return("rm", "-r", $file);
	
}

function lxfile_generic_chmod($file, $mod)
{
	lxfile_unix_chmod($file, $mod);
}

function lxfile_generic_chmod_rec($file, $mod)
{
	if (!$file) { return; }
	lxfile_unix_chmod_rec($file, $mod);
}

function lxfile_generic_chown($file, $mod)
{
	lxfile_unix_chown($file, $mod);
}

function lxfile_generic_chown_rec($file, $mod)
{
	if (!$file) { return; }
	lxfile_unix_chown_rec($file, $mod);
}

function lxfile_is_symlink($file)
{
	return lis_link($file);

}
function lxfile_unix_chown_rec($file, $mod)
{
	if (!$file) { return; }
	$file = expand_real_root($file);
	if (lxfile_is_symlink($file)) {
		lxshell_return("chown", $mod, $file);
	} else {
		lxshell_return("chown", "-R", $mod, $file);
	}
}

function lxfile_unix_chmod_rec($file, $mod)
{
	if (!$file) { return; }
	$file = expand_real_root($file);
	if (lxfile_is_symlink($file)) {
		lxshell_return("chmod",  $mod, $file);
	} else {
		lxshell_return("chmod", "-R", $mod, $file);
	}
}



function lxfile_mv_rec($dirsource, $dirdest)
{
	$username = "__system__";
	$dirdest = expand_real_root($dirdest);
	$dirsource = expand_real_root($dirsource);
	$arglist = array($dirsource, $dirdest);
	$cmd = getShellCommand("mv", $arglist);
	return do_exec_system($username, null, $cmd, $out, $err, $ret, null);
}


function lxfile_cp_content_file($dirsource, $dirdest)
{
	$username = "__system__";
	$dirdest = expand_real_root($dirdest);
	$dirsource = expand_real_root($dirsource);

	if (!lxfile_exists($dirdest)) {
		lxfile_mkdir($dirdest);
	}

	$list = lscandir_without_dot($dirsource);

	foreach($list as $l) {
		if (!is_dir("$dirsource/$l")) {
			lxfile_cp("$dirsource/$l", "$dirdest/$l");
		}
	}
}


function lxfile_cp_content($dirsource, $dirdest)
{
	$username = "__system__";
	$dirdest = expand_real_root($dirdest);
	$dirsource = expand_real_root($dirsource);

	if (!lxfile_exists($dirdest)) {
		lxfile_mkdir($dirdest);
	}

	$list = lscandir_without_dot($dirsource);

	foreach($list as $l) {
		lxfile_cp_rec("$dirsource/$l", "$dirdest/$l");
	}
}

function lxfile_cp_rec($dirsource, $dirdest)
{ 
	
	$username = "__system__";
	$dirdest = expand_real_root($dirdest);
	$dirsource = expand_real_root($dirsource);
	$arglist = array("-a", $dirsource, $dirdest);
	$cmd = getShellCommand("cp", $arglist);
	return do_exec_system($username, null, $cmd, $out, $err, $ret, null);
} 


function lxfile_size($file)
{
	if (!lxfile_exists($file)) {
		return 0;
	}
	$file = expand_real_root($file);
	$size = (float) exec('stat -c %s '. escapeshellarg($file));
	return $size;
}

function lxfile_unix_chmod($file, $mod) 
{
	$file = expand_real_root($file);
	/*
	if ($mod & S_ISUID || $mod & S_ISGID) {
		throw new lxException('setuid_not_allowed_in_chmod', '');
	}
*/
	$ret =  lxshell_return("chmod", $mod, $file);

	// There is difference in the return values betweent eh internal and the external chmod
	if ($ret) {
		dprint("Chmod Error in file $file\n");
	}

}

function lxfile_unix_chown($file, $mod)
{
	$file = expand_real_root($file);
	log_filesys("Chown $file to $mod");

	$stat = stat($file);

	//dprintr($stat);
	if (!is_dir($file) && $stat['nlink'] > 1) {
		log_log("link_error", "$file is a hard link not chowning");
		return;
	}

	if(lis_link($file)) {
		log_log("link_error", "$file is link so no chown to $mod");
		return;
	}
	$group = null;
	if (csa($mod, ':')) {
		list($user, $group) = explode(':', $mod);
	} else {
		$user = $mod;
	}

	if (is_numeric($group)) {
		$group = (int) $group;
	}
	if ($group) {
		chgrp($file, $group);
	}

	if (is_numeric($user)) {
		$user = (int) $user;
	}

	$ret = chown($file, $user);
	if (!$ret) {
		dprint("Chown Error in file $file\n");
	}
	return $ret;
	
}

function lxshell_background($cmd)
{
	global $gbl, $sgbl, $login, $ghtml; 
	global $global_dontlogshell;
	$username = '__system__';
	$start = 1;
	eval($sgbl->arg_getting_string);
	$cmd = getShellCommand($cmd, $arglist);
	$cmd .= " >/dev/null 2>&1 &";
	$pwd = getcwd();
	if (!$global_dontlogshell) {
		log_shell("Background: ($pwd) $cmd");
	} else {
		log_log("other_cmd", "Background: ($pwd) $cmd");
	}

	exec($cmd);
	return true;
}

function do_exec_system($username, $dir, $cmd, &$out, &$err, &$ret, $input) 
{
	//dprint("<hr>$dir <hr> ");
	global $gbl, $sgbl, $login, $ghtml; 
	global $global_shell_out, $global_shell_error, $global_shell_ret;

	global $global_dontlogshell;

	$path = "$sgbl->__path_lxmisc";

	$fename = tempnam($sgbl->__path_tmp, "system_errr");

	$execcmd = null;
	if ($username !== '__system__') {
		$execcmd = "$path -u $username";
		chmod($path, 0700);
	}


	$oldpath = null;
	if ($dir) {
		lxfile_mkdir($dir);
		$oldpath = getcwd();
		chdir($dir);
	}


	$descriptorspec = array( 0 => array("pipe", "r"), 1 => array("pipe", "w"), 2 => array("file", $fename, "a"));

	os_set_path();
	$process = proc_open("$cmd", $descriptorspec, $pipes);
	$out = null;
	if (is_resource($process)) {
		// $pipes now looks like this:
		// 0 => writeable handle connected to child stdin
		// 1 => readable handle connected to child stdout
		// Any error output will be appended to $fename

		if ($input) {
			fwrite($pipes[0], $input);
		}
		fclose($pipes[0]);

		while (!feof($pipes[1])) {
			$out .= fgets($pipes[1], 1024);
		}
		fclose($pipes[1]);
		// It is important that you close any pipes before calling
		// proc_close in order to avoid a deadlock
		$ret = proc_close($process);
	}

	$err = lfile_get_contents($fename);
	unlink($fename);

	$tcwd = getcwd();

	if ($ret) {
		log_shell_error("$err: [($username:$tcwd) $cmd]");
	}

	if ($global_dontlogshell) {
		log_log("other_cmd", "$ret: $err [($username:$tcwd) $cmd]");
	} else {
		log_shell("$ret: $err [($username:$tcwd) $cmd]");
	}

	$global_shell_ret = $ret;
	$global_shell_out = $out;
	$global_shell_error = $err;

	if ($oldpath) {
		chdir($oldpath);
	}

}
