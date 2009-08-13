<?php 

include_once "htmllib/lib/displayinclude.php";

redirect_to_https();
main_main();

function domainshow()
{   

	global $gbl, $sgbl, $login, $ghtml; 


	$url = $login->getUrlFromLoginTo();
	$url = $ghtml->getFullUrl($url);


	$sp = $login->getSpecialObject('sp_specialplay');

	if ($sp->isOn('lpanel_scrollbar')) {
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

	if (lxfile_exists("lbin/header_vendor.php")) {
		$file = "/lbin/header_vendor.php";
	} else if (lxfile_exists("bin/header_vendor.php")) {
		$file = "/bin/header_vendor.php";
	} else {
		$file = "/lbin/header.php";
	}

	print_meta_lan();
    $title = get_title();
	?> 
	<title> <?php echo $title ?> </title>
		



	<?php

	print("<FRAMESET frameborder=0 rows=\"93,*\"  border=0>\n");

	print("<FRAME name=topframe src=$file scrolling=no>\n");

	if (!$sp->isOn('split_frame')) { 
		print("<FRAMESET frameborder=0 cols=\"$width,*\" border=0>\n");
		print("<FRAME name=leftframe src='/htmllib/lbin/lpanel.php?lpanel_type=tree' $scrollstring border=0>\n");
	}
	if ($sp->isOn('split_frame')) {
		print("<FRAMESET frameborder=0 cols=\"50%,*\" border=0>\n");
	}
	print("<FRAME name=mainframe src=\"$url\">\n");

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


