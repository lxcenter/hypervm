<?php
//
//    HyperVM, Server Virtualization GUI for OpenVZ and Xen
//
//    Copyright (C) 2000-2009     LxLabs
//    Copyright (C) 2009-2013     LxCenter
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
//
chdir("..");
include_once "htmllib/lib/displayinclude.php";

function header_main()
{
    global $gbl, $sgbl, $login, $ghtml;
    initProgram();
    initLanguage();
    initLanguageCharset();

    // Load default skin or feather skin
    if ($login->isDefaultSkin()) {
        include_once "lib/default_header.php";
        CreateDefaultHeaderMenu();
    } else {
        print_header();
    }
}


function print_one_link($name)
{
    global $gdata;
    $s = $gdata[$name];
    $desc = $s[0];
    $url = $s[1];
    $img = $s[2];
    $target = null;
    if (!csa($url, "javascript")) {
        $onclickstring = "onClick=\"top.mainframe.location='$url';\";";
    } else {
        $onclickstring = "onClick=\"$url\"";
    }
    print("<td ><span title='$desc' OnMouseOver=\"style.cursor='pointer'\" $onclickstring><img src=/img/skin/hypervm/feather/default/images/$img></span> </td>\n");
}

function print_logout()
{
    print("<td OnMouseOver=\"style.cursor='pointer'\" onClick=\"javascript:top.mainframe.logOut();\"> <span title=Logout> <img width=15 height=14 src=/img/skin/hypervm/feather/default/images/logout.png> Logout </span> </td>\n");
}

function print_header()
{
    global $gbl, $sgbl, $login, $ghtml;
    $lightskincolor = $login->getLightSkinColor();
    CreateHeaderData();
    print("<body topmargin=0 leftmargin=0>\n");
    print("\n<!-- httpdocs/lbin/header.php -->\n");
    print("<div id=statusbar  style='background:#$lightskincolor;scroll:auto;height:26;width:100%;border-bottom:4px solid #b1cfed;margin:2 2 2 2:vertical-align:top;text-align:top'>\n");

    $alist[] = "a=show";
    $alist = $login->createShowAlist($alist);
    $gbl->__c_object = $login;

    print("<table cellpadding=0 cellspacing=0 >\n<tr>\n");
    $count = 0;
    $icount = 0;
    foreach ($alist as $k => $v) {
        if (csa($k, "__title")) {
            $count++;
            continue;
        }
        $icount++;
        if ($icount > 8) {
            continue;
        }
        $v = $ghtml->getFullUrl($v);
        $ghtml->print_div_button_on_header(null, true, $k, $v);
    }

    print("<td nowrap style='width:40px'></td>\n");
    $v = "a=list&c=ndskshortcut";
    $v = $ghtml->getFullUrl($v);
    $ghtml->print_div_button_on_header(null, true, 0, $v);
    $ghtml->print_toolbar();

    print("<td width=100%> </td>\n");
    $v = $ghtml->getFullUrl("a=list&c=ssessionlist");
    $ghtml->print_div_button_on_header(null, true, $k, $v);
    $v = create_simpleObject(array('url' => "javascript:top.mainframe.logOut()", 'purl' => '&a=updateform&sa=logout', 'target' => null));
    $ghtml->print_div_button_on_header(null, true, $k, $v);
    print("</tr> </table>\n");
    print("</div> </body>\n");
    return;

    ?>
<body topmargin=0 bottommargin=0 leftmargin=0 rightmargin=0 class="bdy1" onload="foc()">
<!-- httpdocs/lbin/header.php -->
<table id="tab1" border="0" cellpadding="0" cellspacing="0">
    <tr>
        <td class="top2">
            <div class="menuover" style="margin-top:2px;margin-left:0%">


<?php 

	$list[] = "a=show";
	if ($login->isLte('reseller')) {
        $list[] = "a=list&c=all_vps";
        $list[] = "a=list&c=client";
    }
	$list[] = "k[class]=ffile&k[nname]=/&a=show";
	$list[] = "a=list&c=ticket";

	$list = null;
	$list[] = "home";
	$list[] = "ffile";
	$list[] = "ticket";

	foreach ($list as $k) {
        print_one_link($k);
    }

	print("<span style='margin-left:39%;'> </span> \n");

	foreach (array("ssession", "help", "logout") as $k) {
        print_one_link($k);
    }
	print("</div></td></tr>\n");
	print("</table>\n");

}

function CreateHeaderData()
{
    global $gbl, $sgbl, $login, $ghtml, $gdata;

    $homedesc = $login->getKeywordUc('home');
    $deskdesc = $login->getKeywordUc('desktop');
    $aboutdesc = $login->getKeywordUc('about');
    $domaindesc = get_plural(get_description('vps'));
    $clientdesc = get_plural(get_description('client'));
    $slavedesc = get_description('pserver');
    $ticketdesc = get_plural(get_description('ticket'));
    $ssessiondesc = get_description('ssession');
    $systemdesc = $login->getKeywordUc('system');
    $logoutdesc = $login->getKeywordUc('logout');
    $helpdesc = $login->getKeywordUc('help');
    $ffiledesc = get_plural(get_description("ffile"));
    $alldesc = $login->getKeywordUc('all');

    if ($login->isAdmin()) {
        $domainclass = "vps";
    } else  {
        $domainclass = "vps";
    }

    if (check_if_many_server()) {
        $serverurl = $ghtml->getFullUrl('a=list&c=pserver');
        $slavedesc = get_plural($slavedesc);
    } else {
        $serverurl = $ghtml->getFullUrl('k[class]=pserver&k[nname]=localhost&a=show');
    }

    if ($login->is__table('client')) {
        $ffileurl = $ghtml->getFullUrl('k[class]=ffile&k[nname]=/&a=show');
    } else {
        $ffileurl = $ghtml->getFullUrl('n=web&k[class]=ffile&k[nname]=/&a=show');
    }

    $gob = $login->getObject('general')->generalmisc_b;

    if (isset($gob->ticket_url) && $gob->ticket_url) {
        $url = $gob->ticket_url;
        $url = add_http_if_not_exist($url);
        $ticket_url = "javascript:window.open('$url')";
    } else {
        $ticket_url = "/display.php?frm_action=list&frm_o_cname=ticket";
    }

    $helpurl = $sgbl->__url_help;

    $gdata = array(
        "desktop" => array($deskdesc, "/display.php?frm_action=desktop", "client_list.gif"),
        "home" => array($homedesc, "/display.php?frm_action=show", "client_list.gif"),
        "all" => array($alldesc, "/display.php?frm_action=list&frm_o_cname=all_vps", "client_list.gif"),
        "domain" => array($domaindesc, "/display.php?frm_action=list&frm_o_cname=$domainclass", "domain_list.gif"),
        "system" => array($systemdesc, "/display.php?frm_action=show&frm_o_o[0][class]=pserver&frm_o_o[0][nname]=localhost", "pserver_list.gif"),
        "client" => array($clientdesc, "/display.php?frm_action=list&frm_o_cname=client", "client_list.gif"),
        "ffile" => array($ffiledesc, $ffileurl, "client_list.gif"),
        "pserver" => array($slavedesc, $serverurl, "pserver_list.gif"),
        "ticket" => array($ticketdesc, $ticket_url, "ticket_list.gif"),
        "ssession" => array($ssessiondesc, "/display.php?frm_action=list&frm_o_cname=ssessionlist", "ssession_list.gif"),
        "about" => array($aboutdesc, "/display.php?frm_action=about", "ssession_list.gif"),
        "help" => array($helpdesc, "javascript:window.open('$helpurl')", "ssession_list.gif"),
        "logout" => array($logoutdesc, "javascript:top.mainframe.logOut();", "delete.gif")
    );
}

header_main();

