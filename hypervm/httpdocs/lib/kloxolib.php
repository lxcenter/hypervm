<?php 

class kloxo extends lxclass {

static $__desc = array("", "", "kloxo_tab");
static $__acdesc_show = array("", "", "kloxo_tab");
static $__acdesc_update_all_resource = array("", "", "all_resource");
static $__acdesc_update_list_client = array("", "", "list_client");
static $__acdesc_update_show_server = array("", "", "show_server");
static $__acdesc_update_update_home = array("", "", "update_home");
static $__acdesc_update_desktop = array("", "", "desktop");
static $__acdesc_update_home = array("", "", "home");
static $__acdesc_update_list_service = array("", "", "list_service");
static $__acdesc_update_update_kloxo_now = array("", "", "update_kloxo_now");

function get() {}
function write() {}

function createShowPropertyList(&$alist)
{
	$parent = $this->getParentO();
	$parent->createShowPropertyList($nalist);
	$alist = $nalist;
	foreach($alist['property'] as &$__a) {
		$__a = "goback=1&$__a";
	}
}

function createShowAlist(&$alist, $subaction = null)
{
	global $gbl, $sgbl, $login, $ghtml; 
	$alist['__title_misc'] = "actions";

	$alist[] = $this->createKloxoUrl("a=show", "home");
	$alist[] = $this->createKloxoUrl("a=list&c=service&k[class]=pserver&k[nname]=localhost", "list_service");
	//$alist[] = $this->createKloxoUrl("a=desktop", "desktop");
	$alist[] = $this->createKloxoUrl("a=list&c=all_domain", "all_resource");
	$alist[] = $this->createKloxoUrl("a=list&c=client", "list_client");
	$alist[] = $this->createKloxoUrl("a=updateform&sa=commandcenter&l[class]=pserver&l[nname]=localhost", "commandcenter");
	$alist[] = $this->createKloxoUrl("a=show&l[class]=pserver&l[nname]=localhost", "show_server");
	$alist[] = $this->createKloxoUrl("a=show&o=lxupdate&k[class]=pserver&k[nname]=localhost", "update_home");
	//$alist[] = $this->createKloxoUrl("a=update&sa=lxupdateinfo&o=lxupdate&k[class]=pserver&k[nname]=localhost", "update_kloxo_now");
	return $alist;
}


function createKloxoUrl($url, $action)
{
	global $gbl, $sgbl, $login, $ghtml; 
	$parent = $this->getParentO();
	$selfurl = $ghtml->get_get_from_current_post(array('frm_emessage'));
	$rurl = self::generateKloxoUrl($parent, $selfurl, $url);
	$url = create_simpleObject(array('url' => $rurl, 'purl' => "a=updateform&sa=$action&l[class]=kloxo&l[nname]=name", 'target' => null));
	return $url;
}

static function generateKloxoUrl($parentclname, $selfurl, $url)
{
	global $gbl, $sgbl, $login, $ghtml; 

	if (!$selfurl) { $selfurl = "frm_action=show"; }

	if (is_object($parentclname)) {
		$parent = $parentclname;
	} else {
		list($class, $name) = getParentNameAndClass($parentclname);
		$parent = new $class(null, null, $name);
		$parent->get();
	}

	$string = 'secret';
	$ssl['string'] = $string;
	$priv_key_res = openssl_get_privatekey($parent->text_private_key, "");
	openssl_private_encrypt($string, $encrypted_string, $priv_key_res);
	$ssl['encrypted_string'] = base64_encode($encrypted_string);
	$selfip = $_SERVER['SERVER_NAME'];
	list($host, $port) = explode(":", $selfip);
	if ($port === '8887') {
		$prot = "https";
		$lxport = "7777";
	} else {
		$prot = "http";
		$lxport = "7778";
	}
	$ssl_param['backurl'] = "$prot://$selfip/display.php?$selfurl";
	$ssl_param['backbase'] = "$prot://$selfip/display.php";
	$ssl_param['parent_clname'] = $parent->getClName();
	$ssl['ssl_param'] = $ssl_param;
	$ssl = base64_encode(serialize($ssl));
	if ($parent->isClass('pserver')) {
		$ip = getOneIPForServer($parent->nname);
	} else {
		if ($parent->mainipaddress) {
			$ip = $parent->mainipaddress;
		} else {
			$ip = getFirstFromList($parent->vmipaddress_a)->nname;
		}
		if (!$ip) {
			throw new lxexception("no_ipaddress_for_the_vps", '', $name);
		}
	}
	$url = @ $ghtml->getFullUrl($url, null);
	$url = "$prot://$ip:$lxport/$url&frm_ssl=$ssl";
	return $url;
}

static function initThisObjectRule($parent, $class, $name = null)
{ 
	return $parent->nname;

}
}

