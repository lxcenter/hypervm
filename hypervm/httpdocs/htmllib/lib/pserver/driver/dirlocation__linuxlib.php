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

class dirlocation__linux extends lxDriverClass {

	function dbactionUpdate($subaction)
	{

		dprint("here\n");
		switch($subaction)
		{
			case "add_xen_location_a":
				$this->check_xen_dirlocation();
				break;

		}

	}


	static function getSizeForAll($list)
	{
		foreach($list as $l) {
			if (csa($l, "lvm:")) {
				$ret[$l] = vg_diskfree($l);
			} else {
				$ret[$l] = lxfile_disk_free_space($l);
			}
		}
		dprintr($ret);
		return $ret;
	}


	function check_xen_dirlocation()
	{
		$diro = getFirstFromList($this->main->__t_new_xen_location_a_list);
		$dirlocation = $diro->nname;

		if (!csb($dirlocation, "lvm:")) {
			return;
		}

		$dirlocation = fix_vgname($dirlocation);

		$ret = exec_with_all_closed_output("vgdisplay -c $dirlocation");

		if (!csa($ret, ":")) {
			throw new lxException ("the_lvm_doesnt_exist", 'nname', $dirlocation);
		}
	}


}
