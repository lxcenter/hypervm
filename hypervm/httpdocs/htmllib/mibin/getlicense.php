<?php

chdir("../..");
include_once "htmllib/lib/include.php";

if (!os_isSelfSystemOrLxlabsUser()) {
	exit;
}
initProgram('admin');
license::doupdateLicense();
print("License Successfully updated\n");
