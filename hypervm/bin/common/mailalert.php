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


monitor_child();

function monitor_child()
{
	global $gbl, $sgbl, $login, $ghtml;
	global $global_reminder;

	initProgram('admin');
	$login->loadAllObjects('client');
	$login->loadAllObjects('vps');
	$cllist = $login->getList('client');
	$vpslist = $login->getList('vps');
	$clist = lx_array_merge(array($cllist, $vpslist));
	foreach($clist as $c) {
		$downlist = null;
		$mlist = $c->getList('monitorserver');
		if (!$mlist) {
			continue;
		}
		foreach($mlist as $ml) {
			$plist = $ml->getList('monitorport');
			$eidlist = $ml->getList('emailalert');
			$nidlist = $c->getList('emailalert');
			$rlist = lx_array_merge(array($nidlist, $eidlist));
			$portlist =  process_port($rlist, $plist);

			if ($portlist) {
				$text = file_get_contents("../file/mailalert.txt");
				$text = str_replace("%port%", implode(" ", $portlist), $text);
				$text = str_replace("%server%", $ml->servername, $text);
				foreach($rlist as $eid) {
					if ((time() - $eid->last_sent) > $eid->period * 60) {
						log_message("Sending mail to $eid->emailid about $ml->servername at " . time());
						$global_reminder[$eid->emailid][] = array("s", $text);
						$eid->last_sent = time();
						$eid->setUpdateSubaction();
						$eid->write();
					}
				}

			}
		}

	}
}


