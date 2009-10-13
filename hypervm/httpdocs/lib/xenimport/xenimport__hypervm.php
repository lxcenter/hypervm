<?php 
//include_once "lib/include.php";

//__xenimport_get_data();

function __xenimport_get_data()
{
	lxfile_mkdir("/home/oldxenconfig-hypervm");
	$list = lscandir_without_dot("/home/xen");
	foreach($list as $l) {
		if (!cse($l, ".vm")) {
			continue;
		}
		$vm[] = __xenimport_parse_config("/home/xen/$l/$l.cfg");
		//lxfile_mv("/etc/xen/$l", "/home/oldxenconfig-hypervm");
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

		$val = trim($val, "'");

		switch($var) {
			case "memory":
				$ret['memory'] = $val;
				break;

			case "name":
				$ret['name'] = strtolower(strtil($val, '.vm'));
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
	preg_match("/\['phy:([^']*)'.*'phy:([^']*)'\]/i", $val, $matches);
	$ret['type'] = 'lvm';

	if (!isset($matches[0])) {
		$ret['type'] = 'file';
		preg_match("/\['file:([^']*)'.*'file:([^']*)'\]/i", $val, $matches);
	}

	if (!isset($matches[0])) {
		throw new lxException("could_not_parse_disk_string");
	}
	$diskstring = $matches[1];
	$disk = explode(",", $matches[1]);

	if (csb($disk[0], "/dev/")) { $disk[0] = strfrom($disk[0], "/dev/"); }

	if ($ret['type'] === 'file') {
		$location = "/home/xen";
		$mdn = explode("/", $disk[0]);
		$maindiskname = array_pop($mdn);
	} else {
		list($location, $maindiskname) = explode("/", $disk[0]);
	}

	$ret['location'] = $location;
	$ret['maindiskname'] = $maindiskname;

	$swap = explode(",", $matches[2]);
	if (csb($swap[0], "/dev/")) { $swap[0] = strfrom($swap[0], "/dev/"); }

	if ($ret['type'] === 'file') {
		$location = "/home/xen";
		$swp = explode("/", $swap[0]);
		$swapdiskname = array_pop($swp);
	} else {
		list($location, $swapdiskname) = explode("/", $swap[0]);
	}

	if ($location !== $ret['location']) {
		throw new lxException("swap_disk_location_not_same", 'nname', "{$ret['name']}: {$ret['location']}");
	}

	$ret['swapdiskname'] = $swapdiskname;

}


