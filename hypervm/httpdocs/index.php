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

include_once "htmllib/lib/displayinclude.php";

redirect_to_https();
main_main();

function domainshow()
{   
	global $gbl, $sgbl, $login, $ghtml; 

	if ($login->isAdmin()) {
		$doctype = "admin";
		$domainclass = "all_domaina";
	} else  {
		$doctype = "client";
		$domainclass = "domaina";
	}


	//$url = $login->getUrlFromLoginTo();
	$url = "a=show";
	$url = $ghtml->getFullUrl($url);


	if (lxfile_exists("lbin/header_vendor.php")) {
		$file = "/lbin/header_vendor.php";
	} else {
		$file = "/lbin/header.php";
	}
	$file = "/lbin/header.php";


	if ($login->isAdmin()) {
		//$url = '/display.php?frm_action=list&frm_o_cname=client';
	}
	$sp = $login->getSpecialObject('sp_specialplay');

	if ($sp->isOn('lpanel_scrollbar')) {
		$lpscroll = 'auto';
	} else {
		$lpscroll = 'no';
	}

	if ($gbl->isOn('show_help')) {
		$scrollstring = 'scrolling=no';
		$width = $sgbl->__var_lpanelwidth;
	} else {
		$scrollstring = "scrolling=$lpscroll";
		$width = $sgbl->__var_lpanelwidth;
	}


    $title = get_title();
	?> 
	<head>
	<title> <?php echo $title ?> </title>
		
	<?php $ghtml->print_refresh_key();


	if ($login->getSpecialObject('sp_specialplay')->isOn('simple_skin')) {
		print("<FRAMESET frameborder=0 rows=\"96%,*\"  border=0>\n");
		print("<FRAME name=mainframe src=\"$url\" >\n");
		print("<FRAME name=bottomframe src='htmllib/lbin/bottom.php'>\n");
		return;
	}

	if ($login->isDefaultSkin()) {
		$headerheight = 93;
	} else  {
		if ($login->getSpecialObject('sp_specialplay')->isOn('show_thin_header')) {
			$headerheight = 29;
		} else {
			$headerheight = 132;
			$headerheight = 29;
		}
	}

	print("<FRAMESET frameborder=0 rows=\"$headerheight,*\"  border=0>\n");

	print("<FRAME name=topframe src=$file scrolling=no>\n");

	if (!$sp->isOn('split_frame')) { 
		print("<FRAMESET frameborder=0 cols=\"$width,*\" border=0>\n");
		print("<FRAME name=leftframe src='/htmllib/lbin/lpanel.php?lpanel_type=tree' $scrollstring border=0>\n");
	}

	if ($sp->isOn('split_frame')) {
		print("<FRAMESET frameborder=0 cols=\"50%,*\" border=0>\n");
	}
	print("<FRAMESET frameborder=0 rows=\"96%,*\"  border=0>\n");
	// style='overflow-x:hidden;'
	print("<FRAME name=mainframe  src=\"$url\">\n");
	print("<FRAME name=bottomframe src='htmllib/lbin/bottom.php'>\n");

	if ($sp->isOn('split_frame')) {
		print("<FRAME name=rightframe src=\"$url\">\n");
	}
	print("</FRAMESET>\n");
	print("</FRAMESET>\n");

	?> 
	</head>
	<?php
	//<FRAME name=bottomframe src="/bin/bottom.php">
}


function main_main()
{
	global $gbl, $login, $ghtml; 

   	initProgram();

	domainshow();
	/*
	if ($gbl->isOn('split_frame')) {
		$gbl->setSessionV('split_frame', 'off');
	} else {
		$gbl->setSessionV('split_frame', 'on');
	}
	$gbl->c_session->write();
	*/

}


