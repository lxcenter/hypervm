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
		if(self::isIPV6($l)) $cmd= "ip6tables";
		else $cmd="iptables";
		
		$count = 0;
		while (true) {
			$count++;
			exec("$cmd -nv -L FORWARD", $output);
			$output = implode("\n", $output);
			if ($count > 20) {
				break;
			}
			if (!preg_match("/$l/", $output)) {
				break;
			}
			dprint($output);
			dprint("\n $cmd -D FORWARD -s $l -j ACCEPT\n");
			exec("$cmd -D FORWARD -s $l -j ACCEPT > /dev/null");
			exec("$cmd -D FORWARD -d $l -j ACCEPT> /dev/null");
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
		if(self::isIPV6($l)){
			exec("ip6tables -A FORWARD -s $l -j ACCEPT");
			exec("ip6tables -A FORWARD -d $l -j ACCEPT");
		}
		else{
			exec("iptables -A FORWARD -s $l -j ACCEPT");
			exec("iptables -A FORWARD -d $l -j ACCEPT");
		}
	}
}

static function isIPV6($ip)   
{
  if(strchr($ip, ':') && !strchr($ip, '.')) return true;
    if(strchr($ip, '.') && !strchr($ip, ':')) return false;
    
      throw new lxException('Invalid IP address: ' . $ip . ' Contains both dot and colon!', $variable);
          return false;  
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
