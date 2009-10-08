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

include("common.php");
echo <<<EOF
<HTML>
<HEAD>
<TITLE>Nicklist</TITLE>
$css
</HEAD>
<BODY bgcolor="$chan_bg" text="$chan_fg" link="$chan_fg">

EOF;

if (isset($_REQUEST['list'])) {
	$list = $_REQUEST['list'];
} else {
	$list = "";
}
if ($list) {
	$nicknames = split(":", $list);
	foreach($nicknames as $n) {
		if (strlen($n) > 0) {
			echo "$n<BR />";
		}
	}
} else {
	echo "<br> Please wait..";
}

echo "</BODY>\n</HTML>\n";

