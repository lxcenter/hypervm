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
function CreateDefaultHeaderMenu()
{
    global $gbl, $login, $ghtml;

    CreateHeaderData();

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
    <body style='margin: 0;'>
    <!-- httpdocs/lib/default_header.php -->
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

    CreateMenuItem("left", "home");
    CreateMenuLeft();

    print("<td width='100%'></td>\n<td>\n");

    if (!$login->is__table('mailaccount')) {

        if (!$login->is__table('ticket')) {
            CreateMenuItemRightSide("ticket");
            print("</td>\n<td>\n");
        }

        CreateMenuItemRightSide("ssession");

        print("</td>\n<td>\n");

        CreateMenuItemRightSide("help");

        print("</td>\n<td>\n");
    }

    CreateMenuItemRightSide("logout");

    print("</td>\n");
}

function CreateMenuItemRightSide($id)
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


function CreateMenuItem($side, $id)
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

function CreateMenuLeft()
{
	global $gbl, $login, $ghtml; 

	if($login->isLte('reseller')) {
		print("</td>\n<td>\n");
		CreateMenuItem("left", "client");
	}

	print("</td>\n<td>\n");

	if($login->isLte('reseller')) {
		CreateMenuItem("left", "all");
	} 

	print("</td>\n<td>\n");

	if($login->isAdmin()) {
		CreateMenuItem("left", "pserver");
	} 

	print("</td>\n<td>\n");

	if ($login->isLte('customer') && $login->priv->isOn('webhosting_flag')) {
		CreateMenuItem("left", "ffile");
	}

}
