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

include_once "../htmllib/lib/include.php";


function createHeaderData()
{
	global $gbl, $sgbl, $login, $ghtml;
	global $gdata;
	$homedesc = $login->getKeywordUc('home');
	$aboutdesc = $login->getKeywordUc('about');

	$vpsdesc = 'VMs';
	$clientdesc = get_plural(get_description('client'));
	$slavedesc = get_plural(get_description('pserver'));
	$ticketdesc = get_plural(get_description('ticket'));
	$ssessiondesc = get_description('ssession');
	$systemdesc = $login->getKeywordUc('system');
	$logoutdesc = $login->getKeywordUc('logout');
	$helpdesc = $login->getKeywordUc('help');


	$gdata = array(
		"home" => array($homedesc, "/display.php?frm_action=show", "client_list.gif"),
		"vps" => array($vpsdesc, "/display.php?frm_action=list&frm_o_cname=vps", "vps_list.gif"),
		"system" => array($systemdesc, "/display.php?frm_action=show&frm_o_o[0][class]=pserver&frm_o_o[0][nname]=localhost", "pserver_list.gif"),
		"client" => array($clientdesc, "/display.php?frm_action=list&frm_o_cname=client", "client_list.gif"),
		"pserver" => array($slavedesc, "/display.php?frm_action=list&frm_o_cname=pserver", "pserver_list.gif"),
		"ticket" => array($ticketdesc, "/display.php?frm_action=list&frm_o_cname=ticket", "ticket_list.gif"),
		"ssession" => array($ssessiondesc, "/display.php?frm_action=list&frm_o_cname=ssessionlist", "ssession_list.gif"),
		"about" => array($aboutdesc, "/display.php?frm_action=about", "ssession_list.gif"),
		"help" => array($helpdesc, "javascript:top.leftframe.program_help()", "ssession_list.gif"),
		"logout" => array("<font color=red>$logoutdesc<font >", "javascript:top.mainframe.logOut();", "delete.gif")
	);
}

header_main();

function header_main()
{

	print_header();
}

function print_a_right_button($something, $ttype, $id, $pos)
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

	$name = $gdata[$id][0];
	$url = $gdata[$id][1];
	$icon = $gdata[$id][2];
	// Menus are now in the left panel, and since there is no space for more than 1, all menus have to start at position 0; No longer done... Removed the properties from menus..
	$menupos = 0;

	$side = "right";
	if ($side === 'right') {
		$imgprop = "height=8 width=8";
		$menu = "rightmenu";
		$bgimg = "button.gif";
		$imgtdprop = "width=17";
		$tdstyle = "style='padding-top:10";
		$arg = "0, $menupos";
	} else {
		$bgimg = "button.gif";
		$imgprop = "height=15 width=13";
		$menu = "showMenuInFrame";
		$imgtdprop = "width=25";
		$tdstyle = "style='padding-top:1";
		$arg = "$menupos, 0";
	}

	$skindir = $login->getSkinDir();
	?>

<table width=65 cellspacing=0 cellpadding=0 border=0 style='font-size:11;color:#004466;height:20;margin:0 0 0 0;background:url(<?php echo $login->getSkinDir() ?>/<?php echo $bgimg ?>)' OnMouseOver="style.cursor='pointer'" ; <?php echo $onclickstring ?>>
	<tr>
		<td <?php echo $imgtdprop ?> align=center<?php echo $tdstyle ?>'><img
		<?php echo $imgprop ?>
			src='/img/image/<?php echo $login->getSpecialObject('sp_specialplay')->icon_name ?>/button/<?php echo $icon ?>'></td>
		<td <?php $tdstyle ?> valign=middle align=center><b><?php echo $name ?>&nbsp;</b></td>
	</tr>
</table>
		<?php
}

function print_left_side_bar()
{

	global $gbl, $sgbl, $login, $ghtml;
	$skindir = $login->getSkinDir();
	?>
<img src=<?php echo $skindir?>
	/side2.gif>
	<?php

}


function print_a_button($side, $ttype, $id, $pos, $menupos = 0)
{
	global $gbl, $login, $ghtml, $gdata;
	$name = $gdata[$id][0];
	$url = $gdata[$id][1];
	$icon = $gdata[$id][2];
	// Menus are now in the left panel, and since there is no space for more than 1, all menus have to start at position 0; No longer done... Removed the properties from menus..

	if (csa($url, "javascript")) {
		$onclickstring = "onClick=\"$url\"";
	} else {
		$onclickstring = "onClick=\"top.mainframe.location='$url';\"";
	}



	if ($side === 'right') {
		$imgprop = "height=8 width=8";
		$menu = "rightmenu";
		$bgimg = "button.gif";
		$imgtdprop = "width=17";
		$tdstyle = "style='padding-top:10";
		$arg = "0, $menupos";
	} else {
		$bgimg = "button.gif";
		$imgprop = "height=15 width=13";
		$menu = "showMenuInFrame";
		$imgtdprop = "width=25";
		$tdstyle = "style='padding-top:1";
		$arg = "$menupos, 0";
	}



	//	$pos = $gdata[$id][3];
	//	$pos = 1;

	?>
<table width=85 cellspacing=0 cellpadding=0 border=0 style='font-size:11;color:#004466;height:20;margin:0 0 0 0;background:url(<?php echo $login->getSkinDir() ?>/<?php echo $bgimg ?>)' OnMouseOver="style.cursor='pointer'" <?php echo $onclickstring ?>>
	<tr>
		<td <?php echo $imgtdprop ?> align=center<?php echo $tdstyle ?>'><img
		<?php echo $imgprop ?>
			src='/img/image/<?php echo $login->getSpecialObject('sp_specialplay')->icon_name ?>/button/<?php echo $icon ?>'></td>
		<td <?php $tdstyle ?> valign=middle align=center><b><?php echo $name ?>&nbsp;</b></td>
	</tr>
</table>
		<?php
}


function print_header()

{
	global $gbl, $login;
	global $gbl, $ghtml;

	initProgram();
	init_language();

	//check_if_disabled_and_exit();

	$ttype = $login->cttype;

	createHeaderData();

	$ghtml->print_include_jscript("header");
	$skin = $login->getSkinDir();
	$logo = $login->getSpecialObject('sp_specialplay')->logo_image;
	$logo_loading = $login->getSpecialObject('sp_specialplay')->logo_image_loading;
	?>
<script>
if (document.captureEvents) {
	document.captureEvents(Event.MOUSEUP);
}

function changeLogo(flag)
{
	imgob = document.getElementById('main_logo');
	if (!imgob) {
		return;
	}
	if (flag) {
		imgob.src = '<?php echo $logo_loading ?>';
	} else {
		imgob.src = '<?php echo $logo ?>';
	}

}
</script>
<body topmargin=0 bottommargin=0 leftmargin=0 rightmargin=0 border=0>
<table width=100% height="59" border="0" valign=top align="center"
	cellpadding="0" cellspacing="0">
	<tr>
		<td width=100% style='background:url(<?php echo $login->getSkinDir() ?>/background.gif)'>
		</td>
		<td width=326 style='background:url(<?php echo $login->getSkinDir() ?>/background.gif);background-repeat:repeat'>
		<table width=326>
			<tr align=right>
				<td width=200>&nbsp; &nbsp;</td>
				<td align=right><img id=main_logo width=84 height=23
					src="<?php echo $logo_loading?>"></td>
				<td width=10%>&nbsp; &nbsp;</td>
			</tr>
		</table>
		</td>
	</tr>
</table>
<TABLE border="0" width=100% cellspacing=0 cellpadding=0
	background="<?php echo $login->getSkinDir() ?>/background.gif">
	<TBODY>
		<TR>
		<?php
		if ($gbl->isOn('show_lpanel')) {
			/*
			 ?>
			 <td width=218><table width=218> <tr> <td > </td> </tr></table></td>
			 <?php
			 */
		}
		?>
			<td><?php 

			$count = 1;
			$button_width = 85;
			print("<td >");
			//print_left_side_bar();
			print("</td> <td > ");
			print_a_button("left", $ttype, "home", $count, 1);
			$count+= 83;

			if (!$login->isSuperClient()) {
				print_left_panel($ttype, $count);
			}




			print("<td width=100%></td> <td >");
			//print_left_side_bar();
			//print("</td> <td >");
			print_a_right_button("right", $ttype, "ssession", 150);
			print("</td> <td >");
			print_a_right_button("right", $ttype, "help", 150);
			print("</td> <td >");
			print_a_right_button("right", $ttype, "logout", 190);
			print("</td>");


}



function print_left_panel($ttype, $count)
{
	global $gbl, $login, $ghtml;

	$button_width = 85;

	if($login->isLte('customer')) {
		print("</td> <td >");
		print_a_button("left", $ttype, "vps",$count);
		$count += $button_width;
	}


	if($login->isLte('reseller')) {

		print("</td> <td >");
		print_a_button("left", $ttype, "client", $count);
		$count += $button_width;

	}

	print("</td> <td >");
	if($login->isAdmin()) {
		print_a_button("left", $ttype, "pserver", $count);
		$count += $button_width;
	} 
}
