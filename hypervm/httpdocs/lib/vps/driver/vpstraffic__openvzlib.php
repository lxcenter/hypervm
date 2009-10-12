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

class vpstraffic__openvz extends lxDriverClass {

static function get_vps_ipadress()
{
	$out = lxshell_output("vzlist", "-a", '-H', '-o', 'ip');
	$list = explode("\n", $out);
	$total = null;
	foreach($list as $l) {
		$l = trim($l);
		$l = trim($l, "-");
		if (csa($l, "Warning")) { continue; }
		if (!$l) {
			continue;
		}
		$res = explode(" ", $l);
		$total = lx_merge_good($total, $res);
	}
	return $total;
}

static function iptables_delete()
{
	global $gbl, $sgbl, $login, $ghtml; 

	$list = self::get_vps_ipadress();

	if (!$list) {
		return;
	}

	foreach($list as $l) {
		if (!$l) {
			continue;
		}
		$count = 0;
		while (true) {
			$count++;
			exec("iptables -nv -L FORWARD", $output);
			$output = implode("\n", $output);
			if ($count > 10) {
				break;
			}
			if (!preg_match("/$l/", $output)) {
				break;
			}
			dprint($output);
			dprint("iptables -D FORWARD -s $l\n");
			exec("iptables -D FORWARD -s $l");
			exec("iptables -D FORWARD -d $l");
		}
	}

}

static function iptables_create()
{
	$list = self::get_vps_ipadress();
	if (!$list) {
		return;
	}
	foreach($list as $l) {
		if (!$l) {
			continue;
		}
		exec("iptables -A FORWARD -s $l");
		exec("iptables -A FORWARD -d $l");
	}
}


static function findTotaltrafficUsage($list, $oldtime, $newtime)
{
	global $gbl, $sgbl, $login, $ghtml; 

	if(!isset($oldtime)) {
		return null;
	}

	$file =  '/var/log/lxiptraffic.log';
	$processedir = "/var/log/";
	$processfile = $file;

	lxshell_return("__path_php_path", "../bin/sisinfoc.php");

	foreach($list as $d) {
		$tlist[$d->nname] = self::get_usage($processfile, $d->vpsid, $oldtime, $newtime);
	}

	self::iptables_delete();
	self::iptables_delete();
	self::iptables_create();

	$stat = stat($file);
	if ($stat['size'] >= 10 * 1024 * 1024) {
		lxfile_mv($file, getNotexistingFile($processedir, basename($file)));
	}

	return $tlist;
}


static function get_usage($file, $ip, $oldtime, $newtime)
{ 
	global $gbl, $sgbl, $login, $ghtml; 
	$total =  self::getEachfileqouta($file, $ip, $oldtime, $newtime);

	return $total;
}

static function getFromString($line, $num)
{
	$line = trimSpaces($line);
	$list = explode(" ", $line);
	return $list[$num];
}

static function LogConvertString($line)
{
	$line = trimSpaces($line);
	$list = explode(" ", $line);
	return $list[3];
}


static function getTimeFromString($line)
{
	
	///2006-03-10 07:00:01
	$line = trimSpaces($line);
	$list = explode(" ", $line);
	return $list[0];
}


static function getEachfileqouta($file, $vpsid, $oldtime, $newtime) 
{
	$fp = @fopen($file, "r");

	print("Opening File name is :$file\n");

	error_reporting(0);

	if(!$fp){
		return 0;
	}
	$fsize = filesize($file);


	print("Here U are in Mail log file Size is:$fsize\n");

	if($fsize <= 5){
		return 0;
	}

	$total = 0;

	$ret = FindRightPosition($fp, $fsize, $oldtime, $newtime, array("vpstraffic__openvz", "getTimeFromString"));

	if ($ret < 0) {
		dprint("Could not find position\n");
		return;
	}


	$total = 0;
	while(!feof($fp)) {
		$string = fgets($fp);
		//The space at the end is important. Otherwise 200 will match 2000.
		if (csa($string, "openvz-$vpsid ")) {
			$t = self::LogConvertString($string);

			if ($t > 10000014985) {
				continue;
			}

			$total += self::LogConvertString($string);
			$incoming += self::getFromString($string, 4);
			$outgoing += self::getFromString($string, 5);
		}
		if (self::getTimeFromString($string) > $newtime) {
			break;
		}
	}

	$incoming = self::roundupValue($incoming);
	$outgoing = self::roundupValue($outgoing);
	$total = self::roundupValue($total);
	fclose($fp);
	dprint("Returning Total From OUT SIDE This File: for $vpsid $total $incoming $outgoing \n");
	return array('total' => $total, 'incoming' => $incoming, 'outgoing' => $outgoing);
}

static function roundupValue($total)
{

	$total = $total / (1024 * 1024);
	$total = round($total, 2);
	return $total;
}

}
