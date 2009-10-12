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

class sslipaddress__sync extends lxDriverClass {


function dbactionUpdate($subaction)
{
	$name = sslcert::getSslCertnameFromIP($this->main->nname);

	$path = "__path_ssl_root";

	$contentscer = $this->main->text_crt_content;
	$contentskey = $this->main->text_key_content;
	$contentsca = trim($this->main->text_ca_content);

	if (!$contentscer || !$contentskey) {
		throw new lxException("certificate_key_file_empty", '');
	}
	sslcert::checkAndThrow($contentscer, $contentskey, $name);

	lfile_put_contents("$path/$name.crt", $contentscer);
	lfile_put_contents("$path/$name.key", $contentskey);
	$contentpem = "$contentscer\n$contentskey";
	lfile_put_contents("$path/$name.pem", $contentpem);

	if ($contentsca) {
		lfile_put_contents("$path/$name.ca", $contentsca);
	} else {
		lxfile_cp("htmllib/filecore/program.ca", "$path/$name.ca");
	}

	createRestartFile($this->main->__var_webdriver);
}


}
