<?php 

include_once "htmllib/lib/include.php";

$ghtml = new HtmlLib();

$res["Admin"] = array('admin', 'client');
$res['Openvz Owner'] = array('openvz.vm', 'vps');

$color = "style='border:1px solid black'";
print("<table cellspacing=0 cellpadding=0>\n");

foreach ($res as $k => $v) {

    $formname = $v[0] . "_" . $v[1];
    $class = $v[1];
    $name = $v[0];

    $color = null;
    if ($class == 'superadmin') {
        $color = "style='border-bottom:1px solid black'";
    }
    $formname = str_replace(array('@', '.'), "", $formname);
    print("<tr>\n<td $color>\n");
    print("<form name=$formname method=$sgbl->method action='/htmllib/phplib/'>\n");

    print("<input type=hidden name=frm_clientname value={$v[0]}>\n");
    print("<input type=hidden name=frm_class value={$v[1]}>\n");
    print("<input type=hidden name=frm_password value=lxlabs>\n");
    print("</form>\n");


    if ($class == 'client') {
        $var = "cttype_v_$name";
    } else {
        $var = 'show';
    }

    $image = $ghtml->get_image("/img/image/collage/button/", $class, $var, ".gif");

    print("<img width=20 height=20 src=$image></td>\n<td $color ><a href=javascript:document.$formname.submit()> Click here to Login as $k ($v[0])</a>\n");
    print("</td>\n</tr>\n");
}

print("<tr>\n<td><img width=20 height=20 src=/img/general/button/on.gif></td>\n<td><a href=http://forum.lxcenter.org/ target='_blank'> Visit our forums.</a></td>\n</tr>\n");
print("<tr>\n<td><img width=20 height=20 src=/img/general/button/on.gif></td>\n<td><a href=http://lxcenter.org/ target='_blank'> Visit lxcenter.org</a></td>\n</tr>\n");
print("<tr>\n<td><img width=20 height=20 src=/img/general/button/on.gif></td>\n<td><a href=http://wiki.lxcenter.org/ target='_blank'> Visit the Wiki</a></td>\n</tr>\n");
print("</table>\n\n");

