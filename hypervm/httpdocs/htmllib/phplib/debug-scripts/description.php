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

// Dynamically create language files.

description_main();

function description_main()
{
	global $gbl, $sgbl, $login, $ghtml; 

	global $gl_class_array;

	foreach($gl_class_array as $k => $g) {
		if (csa($k, "__")) {
			continue;
		} 

		if (cse($k, "base") || cse($k, "core")) {
			continue;
		} 

		try {
			$r = new ReflectionClass($k);
		} catch (exception $e) {
			continue;
		}
		if ($r->isAbstract()) {
			continue;
		}
		$ob = new $k(null, null, "name", null, null);
	}

	system("mkdir -p lang/en");
	system("rm lang/en/*");

	$list = get_declared_classes();

	foreach($list as $k => $v) {

		$class = $v;
		try {
			$r = new ReflectionClass($class);
		} catch (exception $e) {
			continue;
		}
		if ($r->isAbstract()) {
			continue;
		}
		// First pass to isolate teh _v_ variable
		foreach($r->getProperties() as $s) {
			if (!csb($s->name, "__desc") && !csb($s->name, "__acdesc")) continue;
			$descr = get_real_class_variable($class, $s->name);
			$name = $s->name;
			$v = strtolower($v);
			$name = strtolower($name);
			$ret[$v][$name]  = $descr;

		}
	}

	$str = "<?php \n";
	foreach($ret as $k => $v) {
		foreach($v as $nk => $nv) {
			/* Let the definitions be made multiple times, but it is better to have them rather than not have them... So the line below is not needed.
			if ($k != 'lxclass' && isset($ret['lxclass'][$nk])) {
				continue;
			}
		*/
			if (cse($nk, "_o") || cse($nk, "_l")) {
				continue;
			}
			if (!isset($nv[2])) {
				dprint("2: $k  $nk \n");
				continue;
			}
			if (csb($nv[2], "__k_")) {
				continue;
			}
			$k = trim($nv[2], "_\n ");
			$description[$k] = change_underscore($nv[2]);

		}
	}

	foreach($description as $k => $v) {
		$str .= "\$__description[\"$k\"] = array(\"$v\");\n";
	}
	
	$str .= "\n";
	file_put_contents("lang/en/desclib.php", $str);

	include_once "htmllib/lib/messagelib.php";
	include_once "lib/messagelib.php";

	$string = "<?php\n";
	foreach($__information as $k => $v) { $string .= "\$__information['$k'] = \"$v\";\n"; }
	foreach($__emessage as $k => $v) { $string .= "\$__emessage['$k'] = \"$v\";\n"; }
	foreach($__smessage as $k => $v) { $string .= "\$__smessage['$k'] = \"$v\";\n"; }

	lfile_put_contents("lang/en/messagelib.php", $string);

	copy("htmllib/lib/langfunctionlib.php", "lang/en/langfunctionlib.php");
	copy("htmllib/lib/langkeywordlib.php", "lang/en/langkeywordlib.php");
	system("mkdir -p help/");
	system("cp htmllib/help-core/* help/");
	system("cp help-base/* help/");

	

}

