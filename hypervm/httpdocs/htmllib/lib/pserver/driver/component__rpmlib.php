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

class Component__rpm extends lxDriverClass {



	static function getDetailedInfo($name)
	{
		$ret = lxshell_output("rpm", "-qi", $name);
		return $ret;
	}


	static function getVersion($list, $name)
	{
		foreach($list as $v) {
			if (csb($v, $name) || csa($v, " $name ")) {
				$ret[] = $v;
			}
		}

		return implode(", ", $ret);
	}

	static function getListVersion($syncserver, $list)
	{

		$list[]['componentname'] = 'mysql';
		$list[]['componentname'] = 'perl';
		//$list[]['componentname'] = 'postgresql';
		$list[]['componentname'] = 'httpd';
		$list[]['componentname'] = 'qmail';
		$list[]['componentname'] = 'courier-imap-toaster';
		$list[]['componentname'] = 'php';
		$list[]['componentname'] = 'lighttpd';
		$list[]['componentname'] = 'djbdns';
		$list[]['componentname'] = 'bind';
		$list[]['componentname'] = 'spamassassin';
		$list[]['componentname'] = 'pure-ftpd';

		foreach($list as $l) {
			$nlist[] = $l['componentname'];
		}
		$complist = implode(" ", $nlist);
		$file = fix_nname_to_be_variable("rpm -q $complist");
		$file = "__path_program_root/cache/$file";

		$cmdlist = lx_array_merge(array(array("rpm", "-q"), $nlist));
		$val = get_with_cache($file, $cmdlist);

		$res = explode("\n", $val);

		$ret = null;
		foreach($list as $k => $l) {
			$name = $list[$k]['componentname'];
			$sing['nname'] = $name . "___" . $syncserver;
			$sing['componentname'] = $name;

			$sing['version'] = self::getVersion($res, $name);
			$status = strstr($sing['version'], "not installed");
			$sing['status'] = $status? 'off': 'on';

			/*
			 if (isOn($sing['status'])) {
			 $sing['full_version'] = `rpm -qi $name`;
			 } else {
			 $sing['full_version'] = $sing['version'];
			 }
			 */
			$ret[] = $sing;
		}

		return $ret;
	}

}


