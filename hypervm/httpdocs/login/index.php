<?php 

chdir("..");
include_once "htmllib/lib/displayinclude.php";

initLanguage();
$cgi_clientname = $ghtml->frm_clientname;
$cgi_class = $ghtml->frm_class;
$cgi_password = $ghtml->frm_password;
$cgi_forgotpwd = $ghtml->frm_forgotpwd;
$cgi_email = $ghtml->frm_email;
$cgi_classname = 'client';

ob_start();
include_once "login/indexcontent.php";

