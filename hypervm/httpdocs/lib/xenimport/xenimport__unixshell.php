<?php 
include_once "htmllib/lib/include.php"; 

__xenimport_get_data();


function isXencfgSkip($l)
{
	if (is_dir("/etc/xen/$l")) {
		return true;
	}
	if (csb($l, "xend-")) {
		return true;
	}
	if (csb($l, "xmexample")) {
		return true;
	}
	if (csb($l, "qemu-")) {
		return true;
	}
	if (csa($l, "windows.hvm")) {
		return true;
	}
}


function __xenimport_get_data()
{
	lxfile_mkdir("/home/oldxenconfig-hypervm");
	$list = lscandir_without_dot("/etc/xen");
	foreach($list as $l) {
		if (isXencfgSkip($l)) {
			continue;
		}
		dprint("$l\n");
		$vm[] = __xenimport_parse_config("/etc/xen/$l");
		//lxfile_mv("/etc/xen/$l", "/home/oldxenconfig-hypervm");
		lunlink("/etc/xen/auto/$l");
	}
	dprintr($vm);
	return $vm;
}


function __xenimport_parse_config($file)
{
	$list = lfile_trim($file);

	foreach($list as $l) {
		if (!csa($l, "=")) {
			continue;
		}
		list($var, $val) = explode("=", $l);

		$var = trim($var);
		$val = trim($val);

		switch($var) {
			case "memory":
				$ret['memory'] = $val;
				break;

			case "name":
				$ret['name'] = strtolower(trim($val, '"'));
				break;

			case "ipaddr":
				$val = trim($val, '"');
				$ret['ipaddress'] = trim($val);
				break;

			case "hostname":
				$val = trim($val, '"');
				$ret['hostname'] = trim($val);
				break;

			case "disk":
				__xenimport_parsedisk($ret, $val);
				break;

		}
	}
	return $ret;


}

function __xenimport_parsedisk(&$ret, $val)
{
	preg_match("/\[\s*'phy:([^']*)'.*'phy:([^']*)'\s*\]/i", $val, $matches);

	if (!isset($matches[0])) {
		throw new lxException("could_not_parse_disk_string");
	}

	$diskstring = $matches[1];
	$disk = explode(",", $matches[1]);
	list($location, $maindiskname) = explode("/", $disk[0]);
	$ret['location'] = $location;
	$ret['maindiskname'] = $maindiskname;

	$swap = explode(",", $matches[2]);
	list($location, $swapdiskname) = explode("/", $swap[0]);

	if ($location !== $ret['location']) {
		throw new lxException("swap_disk_location_not_same", 'nname', "{$ret['name']}: {$ret['location']}");
	}

	$ret['swapdiskname'] = $swapdiskname;

}


