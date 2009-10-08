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
/*
 * FCKeditor - The text editor for Internet - http://www.fckeditor.net
 * Copyright (C) 2003-2007 Frederico Caldeira Knabben
 *
 * == BEGIN LICENSE ==
 *
 * Licensed under the terms of any of the following licenses at your
 * choice:
 *
 *  - GNU General Public License Version 2 or later (the "GPL")
 *    http://www.gnu.org/licenses/gpl.html
 *
 *  - GNU Lesser General Public License Version 2.1 or later (the "LGPL")
 *    http://www.gnu.org/licenses/lgpl.html
 *
 *  - Mozilla Public License Version 1.1 or later (the "MPL")
 *    http://www.mozilla.org/MPL/MPL-1.1.html
 *
 * == END LICENSE ==
 *
 * This page lists the data posted by a form.
 */
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<html>
<head>
<title>FCKeditor - Samples - Posted Data</title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<meta name="robots" content="noindex, nofollow">
<link href="../sample.css" rel="stylesheet" type="text/css" />
</head>
<body>
<h1>FCKeditor - Samples - Posted Data</h1>
This page lists all data posted by the form.
<hr>
<table width="100%" border="1" cellspacing="0" bordercolor="#999999">
	<tr
		style="FONT-WEIGHT: bold; COLOR: #dddddd; BACKGROUND-COLOR: #999999">
		<td nowrap>Field Name&nbsp;&nbsp;</td>
		<td>Value</td>
	</tr>
	<?php

	if ( isset( $_POST ) )
	$postArray = &$_POST ;			// 4.1.0 or later, use $_POST
	else
	$postArray = &$HTTP_POST_VARS ;	// prior to 4.1.0, use HTTP_POST_VARS

	foreach ( $postArray as $sForm => $value )
	{
		if ( get_magic_quotes_gpc() )
		$postedValue = htmlspecialchars( stripslashes( $value ) ) ;
		else
		$postedValue = htmlspecialchars( $value ) ;

		?>
	<tr>
		<td valign="top" nowrap><b><?=$sForm?></b></td>
		<td width="100%"><?=$postedValue?></td>
	</tr>
	<?php
	}
	?>
</table>
</body>
</html>
