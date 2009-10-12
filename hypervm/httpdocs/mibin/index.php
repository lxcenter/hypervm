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

chdir("../");
include_once "htmllib/lib/displayinclude.php";

main_main();

function domainshow()
{   

	global $gbl, $sgbl, $login, $ghtml; 


	$url = $login->getUrlFromLoginTo();
	$url = $ghtml->getFullUrl($url);



	if ($login->getSpecialObject('sp_specialplay')->isOn('lpanel_scrollbar')) {
		$lpscroll = 'auto';
	} else {
		$lpscroll = 'no';
	}

	if ($gbl->isOn('show_help')) {
		$scrollstring = 'scrolling=no';
	} else {
		$scrollstring = "scrolling=$lpscroll";
	}

	$width = $sgbl->__var_lpanelwidth;

	if (lxfile_exists("bin/header_vendor.php")) {
		$file = "/bin/header_vendor.php";
	} else {
		$file = "/mibin/header.php";
	}

    $title = get_title();
	?> 
	<head>
	<title> <?php echo $title ?> </title>
		

	<FRAMESET frameborder=0 rows="93,*"  border=0>

	<FRAME name=topframe src=<?php echo $file ?> scrolling=no>
	<?php 
	if ($gbl->isOn('show_lpanel')) {
		?> 
		<FRAMESET frameborder=0 cols="<?php echo $width?>,*" border=0>
		<FRAME name=leftframe src='/htmllib/mibin/lpanel.php' <?php echo $scrollstring ?>  border=0>
		<?php 
	}
	?> 

	<FRAME name=mainframe src="<?php echo $url ?>">
	</FRAMESET>
	</FRAMESET>
	</head>
	<?php
	//<FRAME name=bottomframe src="/bin/bottom.php">
}

function generalshow()
{  
	global $gbl, $login, $ghtml; 

    $title = get_title();

	$gbl->setSessionV("redirect_to", "/display.php?frm_action=show");

	?>
	<head>
	<title> <?php echo $title ?> </title>
	<FRAMESET frameborder=0 rows="98,*" border=0>
	<FRAME name=top src="/header.php" scrolling=no border=0> 
	<FRAME name=mainframe src="/display.php?frm_action=update&frm_subaction=general&frm_ev_list=frm_emailid&frm_emessage=set_emailid">
	</FRAMESET>
	</head>
	<?php 
}

function main_main()
{
	global $gbl, $login, $ghtml; 

   	initProgram();

	domainshow();

}


