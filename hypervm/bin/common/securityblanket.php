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

include "htmllib/lib/include.php";

security_blanket_main();

function security_blanket_main()
{

	global $argv;
	//sleep(100);
	$rem = unserialize(lfile_get_contents($argv[1]));
	unlink($argv[1]);
	if (!$rem) { exit; }

	if (is_array($rem->func)) {
		dprintr($rem);
		$object = new $rem->func[0](null, null, 'hello');
	}
	call_user_func_array($rem->func, $rem->arglist);

	$sq = new Sqlite(null, $rem->table);
	$res = $sq->getRowsWhere("nname = '$rem->nname'", array($rem->flagvariable));

	if ($res[0][$rem->flagvariable] === 'doing') {
		$sq->rawQuery("update $rem->table set $rem->flagvariable = 'Program Got aborted in the midst. Please try again.' where nname = '$rem->nname'");
	}


}
