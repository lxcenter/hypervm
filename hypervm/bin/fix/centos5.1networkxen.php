<?php 

include_once "htmllib/lib/displayinclude.php";
lxfile_cp("../file/sysfile/xen/xend-config.sxp", "/etc/xen/xend-config.sxp");
system("chkconfig libvirtd off");


