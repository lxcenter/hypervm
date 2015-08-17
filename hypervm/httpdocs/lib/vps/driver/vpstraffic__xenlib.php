<?php 

class vpstraffic__xen extends lxDriverClass {


static function findTotaltrafficUsage($list, $oldtime, $newtime)
{
	global $gbl, $sgbl, $login, $ghtml; 

	if(!isset($oldtime)) {
		return null;
	}

	$file =  '/var/log/lxinterfacetraffic.log';
	$processedir = "/var/log/";
	$processfile = $file;

	lxshell_return("__path_php_path", "../bin/common/iptraffic.php");

	$globaliplist = null;
	foreach($list as $d) {
		foreach($d->viflist as $iface) {
			$tlist[$d->nname] = self::get_usage($processfile, $iface, $oldtime, $newtime);
			$globalifacelist[] = $iface;
		}
	}

	lfile_put_contents("__path_program_etc/xeninterface.list", implode("\n", $globalifacelist));

	$stat = stat($file);
	if ($stat['size'] >= 10 * 1024 * 1024) {
		lxfile_mv($file, getNotexistingFile($processedir, basename($file)));
	}

	return $tlist;
}


static function get_usage($file, $iface, $oldtime, $newtime)
{ 
	global $gbl, $sgbl, $login, $ghtml; 
	$total =  self::getEachfileqouta($file, $iface, $oldtime, $newtime);

	return $total;
}

static function LogConvertString($line)
{
	$line = trimSpaces($line);
	$list = explode(" ", $line);
	return $list[3];
}


static function getFromString($line, $num)
{
	$line = trimSpaces($line);
	$list = explode(" ", $line);
	return $list[$num];
}

static function getTimeFromString($line)
{
	
	///2006-03-10 07:00:01
	$line = trimSpaces($line);
	$list = explode(" ", $line);
	return $list[0];
}


static function  getEachfileqouta($file, $iface, $oldtime, $newtime) 
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

	$ret = FindRightPosition($fp, $fsize, $oldtime, $newtime, array("vpstraffic__xen", "getTimeFromString"));

	if ($ret < 0) {
		dprint("Could not find position\n");
		return null;
	}


	$total = 0;
	while(!feof($fp)) {
		$string = fgets($fp);
		if (csa($string, $iface)) {
			//$total += self::LogConvertString($string);
			$incoming += self::getFromString($string, 4);
			$outgoing += self::getFromString($string, 5);
			$total += self::getFromString($string, 5);
			$total += self::getFromString($string, 4);
		}
		if (self::getTimeFromString($string) > $newtime) {
			break;
		}
	}

	$incoming = self::roundupValue($incoming);
	$outgoing = self::roundupValue($outgoing);
	$total = self::roundupValue($total);
	fclose($fp);
	dprint("Returning Total From OUT SIDE This File: for $iface $total \n");
	return array('total' => $total, 'incoming' => $incoming, 'outgoing' => $outgoing);
}

static function roundupValue($total)
{

	$total = $total / (1024 * 1024);
	$total = round($total, 2);
	return $total;
}

}
