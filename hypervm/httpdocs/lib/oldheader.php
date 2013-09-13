<?php

function print_header_old_default()
{
    global $gbl, $login, $ghtml;

    createOldHeaderData();

    $ghtml->print_include_jscript("header");
    $logo = $login->getSpecialObject('sp_specialplay')->logo_image;
    $logo_loading = $login->getSpecialObject('sp_specialplay')->logo_image_loading;
    ?>
    <script>

        function changeLogo(flag)
        {
            imgob = document.getElementById('main_logo');
            if (!imgob) {
                return;
            }
            if (flag) {
                imgob.src = '<?php echo $logo_loading; ?>';
            } else {
                imgob.src = '<?php echo $logo; ?>';
            }

        }
    </script>
    <body style='body: margin: 0;'>
    <!-- httpdocs/lib/oldheader.php -->
    <table width='100%' height='59' border='0' valign='top' align='center' cellpadding='0' cellspacing='0'>
        <tr>
            <td width='100%' style='background: url(<?php echo $login->getSkinDir(); ?>header_top_bg.gif)'></td>
            <td width='326' style='background: url(<?php echo $login->getSkinDir(); ?>header_top_rt.gif); background-repeat: no-repeat'>
                <table width='326'>
                    <tr align='right'>
                        <td width='200'>&nbsp;</td>
                        <td align='right'><img id='main_logo' width='136' height='33' src='<?php echo $logo_loading; ?>'></td>
                        <td width='10%'>&nbsp;</td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
    <table border='0' width='100%' cellspacing='0' cellpadding='0' background='<?php echo $login->getSkinDir(); ?>header_panel_bg.gif'>
    <tbody>
    <tr>
    <td>
    <?php

    print( "</td>\n<td>\n");

    print_a_button("left", "home");
    print_left_panel();

    print("<td width='100%'></td>\n<td>\n");

    if (!$login->is__table('mailaccount')) {

        if (!$login->is__table('ticket')) {
            print_a_right_button("ticket");
            print("</td>\n<td>\n");
        }

        print_a_right_button("ssession");

        print("</td>\n<td>\n");

        print_a_right_button("help");

        print("</td>\n<td>\n");
    }

    print_a_right_button("logout");

    print("</td>\n");
}

function createOldHeaderData()
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

function print_a_right_button($id)
{
	global $gbl, $login, $ghtml, $gdata; 

	$name = $gdata[$id][0];
	$url = $gdata[$id][1];
	$icon = $gdata[$id][2];

	if (csa($url, "javascript")) {
		$onclickstring = "onClick=\"$url\"";
	} else {
		$onclickstring = "onClick=\"top.mainframe.location='$url';\"";
	}
	$skindir = $login->getSkinDir();

    $fontStyle = "style='font-weight:bold;'";
    if ($id === 'logout')
    {
        $fontStyle = "style='font-weight:bold;color:red;'";
    }

	?>

    <table border='0' cellspacing='0' cellpadding='0'
           style='font-size:8pt;color:#004466;height:34px;width:73px;background:url(<?php echo $skindir; ?>right_btn.gif);'
           OnMouseOver="style.cursor='pointer'; top.mainframe.changeContent('help','<?php echo $name; ?>');"<?php echo $onclickstring ?>
           OnMouseOut="changeContent('help','helparea')">
        <tr>
            <td valign='bottom' width='17' height='34' align='left' style='padding-bottom:2px;padding-left:4px'>
                <img height='12' width='12'
                     src='/img/image/<?php echo $login->getSpecialObject('sp_specialplay')->icon_name ?>/button/<?php echo $icon; ?>'>
            </td>
            <td valign='bottom' width='53' style='padding-left:3px;padding-bottom:3px;' align='left'>
                <p <?php echo $fontStyle; ?>><?php echo $name; ?></p></td>
        </tr>
    </table>

	<?php
}


function print_a_button($side, $id)
{
	global $gbl, $login, $ghtml, $gdata; 
	$name = $gdata[$id][0];
	$url = $gdata[$id][1];
	$icon = $gdata[$id][2];

    // Right is not used, still uses print_a_right_button()
	if ($side === 'right') {
		$imgprop = "height='10' width='10'";
		$bgimg = "right_btn.gif";
		$imgtdprop = "width='17'";
		$tdstyle = "style='padding-top:10px;'";
        $fontstyle = "style='font-weight:bold;'";
	} else {
		$bgimg = "left_btn.gif";
		$imgprop = "style='padding-left:7px;' height='15' width='15'";
		$imgtdprop = "width='25'";
		$tdstyle = "style='padding-top:1px;'";
        $fontstyle = "style='font-weight:bold;'";
	}
	?>
			<table width='85' cellspacing='0' cellpadding='0' border='0' style='font-size:8pt;color:#004466;height:34px;margin:0;background:url(<?php echo $login->getSkinDir(); echo $bgimg; ?>)' OnMouseOver="style.cursor='pointer' ;  top.mainframe.changeContent('help','<?php echo $name; ?>');" onClick="top.mainframe.location='<?php echo $url; ?>';" OnMouseOut="top.mainframe.changeContent('help','helparea')">
                <tr>
                    <td <?php echo $imgtdprop; ?> align='center' <?php echo $tdstyle; ?>>
                    <img <?php echo $imgprop; ?> src='/img/image/<?php echo $login->getSpecialObject('sp_specialplay')->icon_name; ?>/button/<?php echo $icon; ?>'></td>
                    <td <?php $tdstyle; ?> valign='middle' align='center'>
                    <p <?php echo $fontstyle; ?>><?php echo $name; ?></p></td>
                </tr>
            </table>
			<?php 
}

function print_left_panel()
{
	global $gbl, $login, $ghtml; 

	if($login->isLte('reseller')) {
		print("</td>\n<td>\n");
		print_a_button("left", "client");
	}

	print("</td>\n<td>\n");

	if($login->isLte('reseller')) {
		print_a_button("left", "all");
	} 

	print("</td>\n<td>\n");

	if($login->isAdmin()) {
		print_a_button("left", "pserver");
	} 

	print("</td>\n<td>\n");

	if ($login->isLte('customer') && $login->priv->isOn('webhosting_flag')) {
		print_a_button("left", "ffile");
	}

}
