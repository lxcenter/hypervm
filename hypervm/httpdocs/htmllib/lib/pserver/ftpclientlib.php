<?PHP
//
//    HyperVM, Server Virtualization GUI for OpenVZ and Xen
//
//    Copyright (C) 2000-2009     LxLabs
//    Copyright (C) 2009          LxCenter
//
//    This program is free software: you can redistribute it and/or modify
//    it under the terms of the GNU Affero General Public License as
//    published by the Free Software Foundation, either version 3 of the
//    License, or (at your option) any later version.
//
//    This program is distributed in the hope that it will be useful,
//    but WITHOUT ANY WARRANTY; without even the implied warranty of
//    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
//    GNU Affero General Public License for more details.
//
//    You should have received a copy of the GNU Affero General Public License
//    along with this program.  If not, see <http://www.gnu.org/licenses/>.
?>

<?php 


class ftpclient extends lxclass {


function __construct($masterserver, $readserver, $rmuser, $rmpass, $rmdir, $name) 
{
	$this->rmuser = $rmuser;
	$this->rmpass = $rmpass;
	$this->rmdir = $rmdir;
	parent::__construct(null, null, $name);
}




function get()
{
	global $gbl, $sgbl, $login, $ghtml; 
	
	static $st;


	if (isset($this->download_f) && $this->download_f) {
		$numlines = 'download';
	} else {
		if ($this->getParentO()->is__table('llog')) {
			$numlines = 20;
		} else {
			$numlines = null;
		}
	}
	if ($st > 0) {
		print("Called more than once\n");
	}
	$st++;

	$this->duflag = $gbl->getSessionV('ffile_duflag');
	$gbl->setSessionV('ffile_duflag', false);
	$this->numlines = $numlines;
	$stat = rl_exec_get(null, $this->__readserver,  array("coreFfile", "getLxStat"), array($this->__username_o, $this->getFullPath(), $numlines, $this->duflag));

	//dprintr($stat);

	if (!isset($this->readonly)) { $this->readonly = 'off'; }


	$this->setFromArray($stat);
	if (!$this->isOn('readonly')) {
		$this->__flag_showheader = true;
	}
	$this->setFileType();
}


static function initThisList($parent, $class)
{

	$fpathp = $parent->fullpath;


	if (!$parent->is_dir()) {
		return null;
	}

	$duflag = $parent->duflag;

	$list = rl_exec_get($parent->__masterserver, $parent->__readserver,  array("coreFfile", "get_full_stat"), array($parent->__username_o, $fpathp, $duflag));


	foreach((array) $list as $stat) {
		$file = basename($stat['name']);
		if ($file === "") {
			continue;
		}
		if ($file === ".")
			continue;

		$fpath = $fpathp . "/" . $file;

		$file = $parent->nname . "/" . $file;
		if (!isset($parent->ffile_l)) {
			$parent->ffile_l = null;
		}
		$parent->ffile_l[$file] = new Ffile($parent->__masterserver, $parent->__readserver,  $parent->root, $file, $parent->__username_o);
		$parent->ffile_l[$file]->setFromArray($stat);
		$parent->ffile_l[$file]->__parent_o = $parent->getParentO();
		$parent->ffile_l[$file]->setFileType();

	}
	$__tv = null;
	return $__tv;
}


}
