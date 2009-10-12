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

include_once "lib/vpsbackuplib.php";
initProgram('admin');

$opt = parse_opt($argv);

list($backupserver, $snapshotdir) = getBackupVar($opt);

if (isset($opt['sshport'])) {
	$backup_ssh_port_string = "-p {$opt['sshport']}";
	$ssh_port = $opt['sshport'];
} else {
	print("Need --sshport=, which is the ssh port for the backup server... SSh ports for the slaves will be automatically gathered from the hyperVM database.\n");
	exit;
}

system("ssh $backup_ssh_port_string $backupserver mkdir -p $snapshotdir/template/openvz $snapshotdir/template/xen");
print("Uploading xen templates...\n");
exec("rsync -e \"ssh $backup_ssh_port_string\" /home/hypervm/xen/template/*.tar.gz $backupserver:$snapshotdir/template/xen");
print("Uploading Openvz Templates...\n");
exec("rsync -e \"ssh $backup_ssh_port_string\" /vz/template/cache/*.tar.gz $backupserver:$snapshotdir/template/openvz");

$var = 'for i in *.tar.gz ; do dirn=`basename $i .tar.gz` ; if ! [ -d $dirn ] ; then mkdir -p $dirn ; tar -C $dirn -xzf $i  ; fi; done';

print("Untarring the templates...\n");
passthru("ssh $backup_ssh_port_string $backupserver '(cd $snapshotdir/template/xen/ ; $var)'");
passthru("ssh $backup_ssh_port_string $backupserver '(cd $snapshotdir/template/openvz/ ; $var)'");
print("Done\n");
