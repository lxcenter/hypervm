<?php 

chdir("../..");
include_once "htmllib/lib/displayinclude.php";



$name = $ghtml->frm_redirectname;


$action = base64_decode($ghtml->frm_redirectaction);

$url = str_replace("__tmp_lx_name__", $name, $action);

header("Location: $url");
