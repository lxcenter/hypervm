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

class dskshortcut_a extends Lxaclass {
	static $__desc = array("e", "",  "favorite");
	static $__desc_nname  	 = array("nS", "",  "link", "a=show");
	static $__desc_ddate  	 = array("n", "",  "date", "a=show");
	static $__desc_description  	 = array("n", "",  "description", "a=show");
	static $__desc_external  	 = array("nS", "",  "description", "a=show");
	static $__desc_default_description  	 = array("nS", "",  "default_description", "a=show");


	function getId() { return $this->display('description'); }

	function isSync() { return false; }

	static function perPage() { return 5000; }

	static function createListAlist($parent, $class)
	{
		$alist[] = "a=list&c=$class";
		$alist[] = "a=addform&c=$class";
		return $alist;

	}

	function postUpdate()
	{
		global $gbl, $sgbl, $login, $ghtml;

		$gbl->setSessionV("__refresh_lpanel", true);
	}

	function postAdd()
	{
		global $gbl, $sgbl, $login, $ghtml;
		if (!$this->isOn('external')) {
			$url = base64_decode($this->nname);
			if ($sgbl->isHyperVM() && isset($this->vpsparent_clname)) {
				$url = kloxo::generateKloxoUrl($this->vpsparent_clname, null, $url);
				$gbl->__this_redirect = "$url&frm_refresh_lpanel=true";
			} else {
				$gbl->__this_redirect = $url;
				$gbl->setSessionV("__refresh_lpanel", true);
			}
		}
		$gbl->setSessionV("__refresh_lpanel", true);
		$this->ddate = time();
	}

	static function add($parent, $class, $param)
	{
		if (isset($param['external']) && isOn($param['external'])) {
			$param['nname'] = base64_encode($param['nname']);
		}
		return $param;
	}

	static function addform($parent, $class, $typetd = null)
	{
		$vlist['nname'] = null;
		$vlist['description'] = null;
		$vlist['external'] = array('h', 'on');
		$ret['variable'] = $vlist;
		$ret['action'] = 'add';
		return $ret;
	}

	static function createListNlist($parent, $view)
	{
		$nlist['ddate'] = '10%';
		$nlist['description'] = '100%';
		return $nlist;
	}
	function display($var)
	{
		global $gbl, $sgbl, $login, $ghtml;
		if ($var === 'description') {

			if (isset($this->$var) && $this->$var) {
				return $this->$var;
			}
			$url = base64_decode($this->nname);
			$buttonpath = get_image_path() . "/button/";
			$description = $ghtml->getActionDetails($url, null, $buttonpath, $path, $post, $file, $name, $image, $__t_identity);
			return "$description[2] for $__t_identity";
		}

		return parent::display($var);
	}

	function updateform($subaction, $param)
	{
		global $gbl, $sgbl, $login, $ghtml;
		if (!$this->description) {
			$this->description = $this->display('description');
		}
		$vlist['description'] = null;
		return $vlist;
	}

	function createShowUpdateform()
	{
		$uflist['description'] = null;
		return $uflist;
	}

}
