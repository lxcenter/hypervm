<?php 

include_once "htmllib/lib/displayinclude.php";

function domainshow()
{
    global $gbl, $sgbl, $login, $ghtml;

    if ($login->isAdmin()) {
        $doctype = "admin";
        $domainclass = "all_domaina";
    } else {
        $doctype = "client";
        $domainclass = "domaina";
    }
    

    $url = "a=show";
    $url = $ghtml->getFullUrl($url);


    if (lxfile_exists("lbin/header_vendor.php")) {
        $file = "/lbin/header_vendor.php";
    } else {
        $file = "/lbin/header.php";
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
    <title><?php echo $title ?></title>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
                
    <?php
    
    print("<link rel=\"icon\" href=\"favicon.ico\" type=\"image/x-icon\">\n");
    print("<link rel=\"shortcut icon\" href=\"favicon.ico\" type=\"image/x-icon\">\n");

    $ghtml->print_refresh_key();


    if ($login->getSpecialObject('sp_specialplay')->isOn('simple_skin')) {
        print("<FRAMESET frameborder=0 rows=\"96%,*\"  border=0>\n");
        print("<FRAME name=mainframe src=\"$url\" >\n");
        print("<FRAME name=bottomframe src='htmllib/lbin/bottom.php'>\n");
        return;
    }
    
    if ($login->isDefaultSkin()) {
        $headerheight = 93;
    } else {
        if ($login->getSpecialObject('sp_specialplay')->isOn('show_thin_header')) {
            $headerheight = 29;
        } else {
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

}


redirect_to_https();

initProgram();
domainshow();

