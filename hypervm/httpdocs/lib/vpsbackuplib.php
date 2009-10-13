<?php 



function getBackupVar($opt)
{
	if (!isset($opt['backuplocation'])) {
		print("Usage $argv[0] --backuplocation=\n");
		exit;
	} 

	$backuplocation = $opt['backuplocation'];

	list($backupserver, $snapshotdir) = explode(":", $backuplocation);

	if (!$snapshotdir) {
		print("Format for backuploaction: root@server:directory\n");
		exit;
	}
	return array($backupserver, $snapshotdir);
}

