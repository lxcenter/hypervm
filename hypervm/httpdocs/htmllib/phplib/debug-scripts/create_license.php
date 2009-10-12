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

include "htmllib/lib/include.php";

create_license();


function create_license()
{
	global $gbl, $sgbl, $login, $ghtml, $argc, $argv; 

	$elements = array(
		'client' => 'On',
		'client_num' => 'Unlimited',
		'pserver_num' => '4',
		'domain_num' => 'Unlimited',
		'live_support' => 'On',
	);

	$opt = parse_opt($argv);

	if (!isset($opt['expiry_date']) || !isset($opt['ipaddress'])) {
		print("need expiry_date and IPaddress\n");
		print("Usage: $argv[0] --ipaddress= --expiry_date= [--client=] [--live_support=] [--pserver_num=] [--client_num=] [--domain_num]\n");
		exit;
	}


	$now = time();

	$timear = array('y' => 24* 3600 * 365, 'm' => 24 * 3600 * 30, 'd' => 24 * 3600, 'h' => 3600, 's' => 1);

	if (isset($timear[$opt['expiry_date'][strlen($opt['expiry_date']) - 1]])) {
		$val = $timear[$opt['expiry_date'][strlen($opt['expiry_date']) - 1]];
	} else {
		print("time is either y,m,d,h,s");
	}

	$time = substr($opt['expiry_date'], 0, strlen($opt['expiry_date']) - 1);
	print($time . "\n");
	$expiry_date = $now +  $time * $val;
	$opt['expiry_date'] = $expiry_date;
	$elements['expiry_date'] = $expiry_date;
	$elements['ipaddress'] = $opt['ipaddress'];
	$string = null;

	foreach($opt as $k => $v) {
		if (isset($elements[$k])) {
			$elements[$k] = $v;
		}
	}

	foreach($elements as $k => $v) {
		$string .= "$k=$v&";
	}

	dprint($string. "\n");

	$encrypted_string = licenseEncrypt($string);
	lfile_put_contents("license.txt", $encrypted_string);
}


