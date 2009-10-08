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

class DiskUsage__Linux extends lxDriverClass {

	static function getDiskUsage()
	{
		$cont = lxshell_output("df", "-P");
		$result = self::parseDiskUsage($cont);
		return $result;
	}


	static function parseDiskUsage($cont)
	{
		$cont = preg_replace('/\n+/i' , "\n" , $cont);
		$arr = explode("\n", $cont);
		$i = 0;
		foreach($arr as $a) {
			if (!$i) {
				$i++;
				continue;
			}
			$a = preg_replace('/\s+/i' , " " , $a);
			$r = explode(' ', $a);

			if (!$r[0]) {
				continue;
			}
			$result[$i]['nname'] = $r[0];
			$result[$i]['kblock'] = round($r[1]/1000);
			$result[$i]['available'] = round($r[3]/1000);
			$result[$i]['used'] = $result[$i]['kblock'] - $result[$i]['available'];
			$result[$i]['pused'] = str_replace("%", "", $r[4]);
			$result[$i]['mountedon'] = $r[5];
			$i++;
		}
		return $result;
	}

}
