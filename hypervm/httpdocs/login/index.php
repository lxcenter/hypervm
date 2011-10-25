<?php 

chdir("..");
include_once "htmllib/lib/displayinclude.php";

init_language();
$cgi_clientname = $ghtml->frm_clientname;
$cgi_class = $ghtml->frm_class;
$cgi_password = $ghtml->frm_password;
$cgi_forgotpwd = $ghtml->frm_forgotpwd;
$cgi_email = $ghtml->frm_email;

$cgi_classname = 'client';
if ($cgi_class) {
    $cgi_classname = $cgi_classname;
}
ob_start();
include_once "htmllib/lib/indexcontent.php";


function index_print_header()
{
    ?>
<table width=100% height=" 64" border="0" valign="top" align="center" cellpadding="0" cellspacing="0">

    <tr>
        <td width=100% style='background:url(/img/skin/hypervm/default/default/background.gif)'></td>
        <td width=326 style='background:url(/img/skin/hypervm/default/default/background.gif);background-repeat:repeat'>
            <table width=326>
                <tr align=right>
                    <td width=200> &nbsp; &nbsp; </td>
                    <td align=right><img id=main_logo width=84 height=23 src="/img/hypervm-logo.gif"></td>
                    <td width=10%> &nbsp; &nbsp; </td>
                </tr>
            </table>
        </td>
    </tr>
    <tr>
        <td width="100%" colspan=5 bgcolor="#003366" width="10" height="2"></td>
    </tr>
</table>

<?php 

}



