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

function windowsOs() { if (getOs() == "windows") { return true; } return false; }
function getOs() { return (substr(php_uname(), 0, 7) == "Windows")? "windows": "linux"; }

function download_file($url, $localfile = null)
{
	$ch = curl_init($url);
	if (!$localfile) {
		$localfile = basename($url);
	}
	$fp = fopen($localfile, "w");
	curl_setopt($ch, CURLOPT_FILE, $fp);
	curl_setopt($ch, CURLOPT_HEADER, 0);
	curl_exec($ch);
	curl_close($ch);
	fclose($fp);
}
chdir("..");
unlink("hypervm-current.zip");
print("Downloading.... \n");
download_file("http://download.lxlabs.com/download/hypervm/production/hypervm/hypervm-current.zip");
print("download done...\n");
if (WindowsOs()) {
	system("c:/Progra~1/7-zip/7z.exe x -y hypervm-current.zip");
} else {
	system("unzip -oq hypervm-current.zip");
	print("Chowning...\n");
	system("chown -R lxlabs ..");
}


