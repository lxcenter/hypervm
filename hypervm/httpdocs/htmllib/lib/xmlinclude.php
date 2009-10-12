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


function parse_category($cat)
{
	foreach($cat->category as $v) {
		dprint("Category: $v\n");
	}
}
function parse_entry($entry)
{
	if (!$entry) { return ; }
	foreach($entry->entry as  $v) {
		dprintr("$v->label $v->path \n");
	}

}
function parse_requirement($requirement)
{
	$db = $requirement->children('http://apstandard.com/ns/1/db');
}

function aps_check_if_db($s)
{
	$rq = $s->requirements;

	$db = $rq->children('http://apstandard.com/ns/1/db');

	if ($db) { return true; }
	return false;
}



function parse_mapping($root, $m, $parent_path)
{
	$a = $m->children('http://apstandard.com/ns/1/php')->attributes();
	$spath = $m->attributes()->url;
	$parent_path = "$parent_path/$spath";

	if ($a) {
		dprintr($a);
		if ((string)$a->writable === 'true') {
			lxfile_generic_chmod("$root/$parent_path", "0775");
			dprint("$parent_path Is writable\n");
		}
	} else {
		dprint("$parent_path Not writable\n");
	}


	foreach($m->mapping as $mp) {
		parse_mapping($root, $mp, $parent_path);
	}

}
