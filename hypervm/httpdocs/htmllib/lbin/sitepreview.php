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
chdir("../../");
include_once "htmllib/lib/include.php";


function parse_etc_mime()
{
	$list = lfile_trim("/etc/mime.types");
	foreach($list as $s) {
		if (!$s) {
			continue;
		}
		if ($s[0] === '#') {
			continue;
		}
		$s = trimSpaces($s);
		$s = explode(" ", $s);
		$type = array_shift($s);
		foreach($s as $ss) {
			$res[$ss] = $type;
		}
	}
	return $res;
}

$res = parse_etc_mime();


$request = $_SERVER['REQUEST_URI'];

if (!csa($request, "sitepreview/")) {
	header("HTTP/1.0 404 Not Found");
	print("404--- <br> ");
	exit;
}

$request = strfrom($request, "sitepreview/");

$domain = strtilfirst($request, "/");

dprint($domain);
$sq = new Sqlite(null, 'web');
$res = $sq->getRowsWhere("nname = '$domain'");

if (!$res) {
	print("Domain Doesn't exist\n");
	exit;
}

$server = $res[0]['syncserver'];
$ip = getOneIPForServer($server);

rl_exec_get(null, 'localhost', 'addtoEtcHost', array($domain, $ip));
$file = curl_general_get("http://$request");

$pinfo = pathinfo($request);
$ext = $pinfo['extension'];
if (isset($res[$ext]) && $res[$ext] !== 'text/html' && $res[$ext] !== 'text/css') {
	header("Content-Type  $res[$ext]");
	print($file);
	exit;
}




rl_exec_get(null, 'localhost', 'removeFromEtcHost', array($domain));

include "/usr/local/lxlabs/kloxo/httpdocs/lib/hn_urlrewrite_example/hn_urlrewrite.class.php";

$rewrite = new hn_urlrewrite();

$page = $rewrite->_rewrite_page($domain, $file);
print($page);



