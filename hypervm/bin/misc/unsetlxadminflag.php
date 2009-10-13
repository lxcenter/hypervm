<?php 

include_once "htmllib/lib/include.php"; 

$sq = new Sqlite(null, 'vps');

$sq->rawQuery("update vps set lxadmin_flag = 'off'");
