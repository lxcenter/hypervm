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

class ClientTemplate extends ClienttemplateBase {

	static $__desc_vps_num = array("q", "", "number_of_vpses");
	static $__desc___v_priv_used_vps_num    = array("S","",  "vpses");





	function createShowPropertyList(&$alist)
	{
		$alist['property'][] = 'a=show';
		$alist['property'][] = "a=updateForm&sa=description";
		$alist['property'][] = "a=updateForm&sa=disable_per";
	}

	function createShowAlist(&$alist, $subaction = null)
	{
		return $alist;

	}

}
