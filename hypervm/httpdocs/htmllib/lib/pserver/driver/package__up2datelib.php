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


class package__up2date extends lxDriverClass {

	static function getYumCommand()
	{
		return array('up2date', '--nosig', '--list', '--show-channels');
	}

	static function getPackages($nocache = false)
	{

		$cmd = self::getYumCommand();
		$file = fix_nname_to_be_variable(implode(" ", $cmd));
		$file = "__path_program_root/cache/$file";

		if ($nocache) {
			$val = `$cmd`;
		} else {
			$val = get_with_cache($file, $cmd);
		}


		$list = explode("\n", $val);

		dprintr($list);

		$match = false;
		$res = null;
		foreach($list as $l) {
			$l = trim($l);
			if (strstr($l, "---------------------")) {
				$match = true;
				continue;
			}

			if ($match) {
				if (!$l) {
					break;
				}
			}

			if (!$match) {
				continue;
			}

			$l = trimSpaces($l);

			$l = trim($l);
			if (!$l) {
				continue;
			}
			$v = explode(" ", $l);
			$out['nname'] = $v[0];
			//$out['arch'] = $v[1];
			$out['update_version'] = $v[1];
			$out['kloxo_status'] = 'dull';
			if ($v[3] === 'lxlabs-updates') {
				$out['kloxo_status'] = 'on';
			}
			$out['repo'] = $v[3];
			$res[] = $out;
		}

		//dprintr($res);
		return $res;



	}

	static function doUpdate($list)
	{
		$cmd = self::getYumCommand();
		$file = fix_nname_to_be_variable(implode(" ", $cmd));
		$file = "__path_program_root/cache/$file";
		$plist = implode(" ", $list);
		while(true) {
			system("up2date --nox --install --nosig $plist", $return_value);
			if (!$return_value) {
				break;
			}
			dprint("Got error from up2date...\n");
		}
		lunlink($file);

	}

}
