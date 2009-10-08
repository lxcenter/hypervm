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

include_once "htmllib/lib/include.php";

$ghtml = new HtmlLib();

//$res["Super Admin"] = array('superadmin', 'superclient');
$res["Admin"] = array('admin', 'client');
//$res['Wholesale Reseller'] = array('wholesale', 'client');
//$res['Reseller'] = array('reseller', 'client');
//$res['Customer'] = array('customer', 'client');
$res['Openvz Owner'] = array('openvz.vm', 'vps');
//$res['Xen Owner'] = array('xen.vm', 'vps');

$color = "style='border:1px solid black'";
print("<table cellspacing=0 cellpadding=0> ");

foreach($res as $k => $v) {

	$formname = $v[0] . "_" . $v[1];
	$class = $v[1];
	$name = $v[0];

	$color = null;
	if ($class == 'superadmin') {
		$color = "style='border-bottom:1px solid black'";
	}
	$formname = str_replace(array('@', '.'), "", $formname);
	print("<tr > <td $color>");
	print("<form name=$formname method=$sgbl->method action='/htmllib/phplib/'>") ;

	print("<input type=hidden name=frm_clientname value={$v[0]}>");
	print("<input type=hidden name=frm_class value={$v[1]}>");
	print("<input type=hidden name=frm_password value=lxlabs>");
	print("</form>");


	if ($class == 'client') {
		$var = "cttype_v_$name";
	} else {
		$var = 'show';
	}

	$image = $ghtml->get_image("/img/image/collage/button/", $class, $var, ".gif");

	print(" <img width=20 height=20 src=$image> </td> <td $color ><a href=javascript:document.$formname.submit()> Click here to Login as $k ($v[0])</a>");
	print("</td></tr>");
}

print(" <tr> <td ><img width=20 height=20 src=/img/general/button/on.gif> </td> <td ><a href=http://lxlabs.com/forum/ target='_blank'> Visit our forums.</a> </td></tr>");
print(" <tr> <td ><img width=20 height=20 src=/img/general/button/on.gif> </td> <td ><a href=http://lxlabs.com/ target='_blank'> lxlabs.com</a> </td></tr>");
print(" <tr> <td ><img width=20 height=20 src=/img/general/button/on.gif> </td> <td ><a href=http://lxlabs.com/software/hypervm/full-feature/ target='_blank'> Full Feature List</a> </td></tr>");
print("</table>");

