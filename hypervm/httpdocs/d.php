<?php 
include_once "htmllib/lib/include.php"; 

$ret = lxshell_unzip(".", "tmp.tar", null);

dprint("Return: $ret \n");


