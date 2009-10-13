<?php 

class ClientTemplate extends ClienttemplateBase {

static $__desc_vps_num = array("q", "", "number_of_vpses");
static $__desc___v_priv_used_vps_num    = array("S","",  "vpses"); 





function createShowPropertyList(&$alist)
{
	$alist['property'][] = 'a=show';
	$alist['property'][] = "a=updateForm&sa=description";
	$alist['property'][] = "a=updateForm&sa=disable_per";
}

function createShowAlist(&$alist, $subaction = null)
{
	return $alist;

}

}
