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

class interface_template extends lxdb {

	static $__desc = array("", "",  "interface_template");
	static $__desc_nname = array("n", "",  "interface_template_name", "a=show");
	static $__acdesc_show_client = array("", "",  "client_interface");
	static $__acdesc_show_domain = array("", "",  "domain_interface");
	static $__acdesc_show_vps = array("", "",  "vps_interface");



	function createShowPropertyList(&$alist)
	{
		global $gbl, $sgbl, $login, $ghtml;
		$alist['property'][] = "a=show";
		$alist['property'][] = "a=show&sa=client";
		if ($sgbl->isKloxo()) {
			$alist['property'][] = "a=show&sa=domain";
		} else {
			$alist['property'][] = "a=show&sa=vps";
		}
	}

	function updateUpdate($param)
	{
		foreach($param as $k => $v) {
			$param[$k] = self::fixListVariable($v);
		}
		dprintr($param);
		return $param;
	}

	function updateform($subaction, $param)
	{
		$vlist['domain_show_list'] = null;
		$vlist['client_show_list'] = null;
		$vlist['vps_show_list'] = null;
		return $vlist;
	}

	function showRawPrint($subaction = null)
	{
		global $gbl, $sgbl, $login, $ghtml;
		if (!$subaction) {
			return;
		}
		$class = $subaction;
		$var = "{$subaction}_show_list";
		$alist = exec_class_method($subaction, "get_full_alist");
		foreach ($alist as $k => $a) {
			if ($ghtml->is_special_url($a)) {
				$alist[$k] = $a->purl;
			}
		}

		$dst = null;
		foreach ( (array)$this->$var as $k => $v) {
			if (!csa($v, "__title")) {
				$dst[] = base64_decode($v);
			} else {
				$dst[] = $v;
			}
		}

		$ghtml->print_fancy_select($class, $alist, $dst);
	}

	static function initThisObjectRule($parent, $class, $name = null)
	{
		if ($parent->getSpecialObject('sp_specialplay')->interface_template) {
			return $parent->getSpecialObject('sp_specialplay')->interface_template;
		}
		return 'default';
	}

	static function initThisListRule($parent, $class)
	{
		return "__v_table";
	}


}
