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

class mailqueue extends lxclass {

	static $__desc = array("", "",  "mail_queue");

	static $__desc_nname = array("", "",  "id", "a=show");
	static $__desc_subject = array("", "",  "subject", "a=show");
	static $__desc_from = array("", "",  "from", "a=show");
	static $__desc_message = array("", "",  "message");
	static $__desc_log = array("", "",  "log");
	static $__desc_to = array("", "",  "to");
	static $__desc_type = array("", "",  "type");
	static $__desc_date = array("", "",  "date");
	static $__desc_size = array("", "",  "size");
	static $__desc_type_v_remote = array("", "",  "remote");
	static $__desc_type_v_local = array("", "",  "local");
	static $__acdesc_list = array("", "",  "mail_queue");


	function get() {}
	function write() {}

	function createShowUpdateform()
	{
		$uflist['update'] = null;
		return $uflist;

	}

	function updateform($subaction, $param)
	{
		$vlist['message'] = array('T', $this->message);
		$vlist['log'] = array('T', $this->log);
		$vlist['__v_button'] = array();
		return $vlist;
	}

	static function createListAlist($parent, $class)
	{
		$alist[] = "a=list&c=$class";
		return $alist;
	}

	static function createListBlist($parent, $class)
	{
		$blist[] = array("a=update&sa=mailqueuedelete");
		$blist[] = array("a=update&sa=mailqueueflush", 1);
		return $blist;
	}

	//function isSelect() { return false; }


	static function canGetSingle() { return true; }

	static function initThisObject($parent, $class, $name = null)
	{
		$res = rl_exec_in_driver($parent, 'mailqueue', "readSingleMail", array($name));

		$ob = new mailqueue(null, $parent->syncserver, $name);
		foreach($res as $k => $r) {
			$ob->$k = $r;
			$ob->parent_clname = $parent->getClName();
		}
		return $ob;

	}

	function canGetSelfList() { return false; }
	function display($var)
	{
		if ($var === 'size') {
			return getKBOrMB(round($this->$var/1024));
		}
		return parent::display($var);
	}

	static function createListNlist($parent, $view)
	{
		$nlist['nname'] = '10%';
		$nlist['type'] = '10%';
		$nlist['from'] = '100%';
		$nlist['to'] = '10%';
		$nlist['subject'] = '100%';
		$nlist['date'] = '10%';
		$nlist['size'] = '10%';
		return $nlist;
	}


	static function initThisListRule($parent, $class) { return null; }

	static function initThisList($parent, $class)
	{
		dprint("I shouldn't get called when single is read\n");
		$res = rl_exec_in_driver($parent, 'mailqueue', "readMailqueue", array());

		foreach($res as &$_r) {
			$_r['syncserver'] = 'localhost';
			$_r['parent_clname'] = $parent->getClName();
		}

		return $res;
	}


}
