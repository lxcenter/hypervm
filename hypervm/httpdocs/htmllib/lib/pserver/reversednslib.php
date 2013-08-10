<?php 


class reversedns extends Lxdb {

static $__table = "reversedns";
static $__desc =  array("", "",  "reverse_dns");
static $__desc_nname =  array("", "",  "ipaddress");
static $__desc_reversename =  array("n", "",  "reversename");
static $__acdesc_list =  array("", "",  "reverse_dns");




static function createListAddForm($parent, $class) { return true;}

static function createListAlist($parent, $class)
{
	global $gbl, $sgbl, $login, $ghtml; 
	$alist[] = "a=list&c=reversedns";

	if ($login->isAdmin()) {
		$alist[] = "o=general&a=updateform&sa=reversedns";
		$alist[] = "a=list&c=rdnsrange";
	}
	if ($login->isAdmin() && $sgbl->isHyperVm()) {
		$alist[] = "a=list&c=all_dns";
		$alist[] = "a=list&c=all_reversedns";
	}
	return $alist;
}

static function createListNlist($parent, $view)
{
	$nlist['nname'] = '40%';
	$nlist['reversename'] = '100%';
	return $nlist;
}

static function add($parent, $class, $param)
{
	global $gbl, $sgbl, $login, $ghtml; 
	$revc = $login->getObject('general')->reversedns_b;
	$param['syncserver'] = implode(",", $revc->dns_slave_list);
	return $param;
}

static function getBaseEnd($ip, $rangelist = null)
{
        if(isIPV6($ip))
        {
                $comp = expandIP6ToArray($ip);
                $last = array_pop($comp);
                
                $base  = self::createDottedRevedIPV6($comp[6])."." ;
                $base .= self::createDottedRevedIPV6($comp[5])."." ;
                $base .= self::createDottedRevedIPV6($comp[4])."." ;
                $base .= self::createDottedRevedIPV6($comp[3])."." ;
                $base .= self::createDottedRevedIPV6($comp[2])."." ;
                $base .= self::createDottedRevedIPV6($comp[1])."." ;
                $base .= self::createDottedRevedIPV6($comp[0])."." ;
                
                if ($rangelist && false){
                        // FIXME: OA: The whole rangelist must be reworked
                        //              I dont care it now, clients cannot declare rangelists
                        foreach($rangelist as $v) {
                                list($rb, $rf, $rl) = $v;
                                if ($rb === $base && $last >= $rf && $last <= $rl) {
                                        $base = "$rf-$rl.$base";
                                        break;
                                }
                        }
                }
        }
        else{

		$comp = explode(".", $ip);
		$last = array_pop($comp);
		$base = "$comp[2].$comp[1].$comp[0]";

		if ($rangelist) {
			foreach($rangelist as $v) {
				list($rb, $rf, $rl) = $v;
				if ($rb === $base && $last >= $rf && $last <= $rl) {
					$base = "$rf-$rl.$base";
					break;
				}
			}
		}
	}
	return array($base, $last);
}

function expandIP6($ip){
	$ip = strtoupper($ip);
        //Make sure we have 8 parts
        while(count(explode(":",$ip)) < 8){
                $ip = str_replace("::",":::",$ip);
        } 
        return $ip;
}


function expandIP6ToArray($ip){
	$ip = strtoupper($ip);

        //Make sure we have 8 parts
        while(count(explode(":",$ip)) < 8){
                $ip = str_replace("::",":::",$ip);
        } 
          
        $ipa = explode(":",$ip);
        for($i=0;$i<8;$i++){    
                $ipa[$i]=str_pad($ipa[$i],4,"0",STRPADLEFT);
        } 
        return $ipa;
}


// !! we only check for . and : !! 
// NO validation done!
function isIPV6($ip)
{
  if(strchr($ip, ':') && !strchr($ip, '.')) return true;
  if(strchr($ip, '.') && !strchr($ip, ':')) return false;

  throw new lxException('Invalid IP address: ' . $ip . ' Contains both dot and colon!', $variable);
    return false;
}
 
static function createDottedIPV6($ippart)
{
        $chars = str_split($ippart); 
        $ret = "";

        foreach($chars as $c)
        {
        	if($c== '.') continue;
                $ret .= $c ."." ;
        }
        return $ret;
}

static function createDottedRevedIPV6($ippart)
{
        $chars = str_split($ippart); 
        $ret = "";

        for($i=count($chars)-1; $i> 0;$i--)
        {
        	if($chars[$i]== '.') continue;
                $ret .= $chars[$i] .".";
        }
        $ret .= $chars[0];
        
        return $ret;
}



function createExtraVariables()
{
	global $gbl, $sgbl, $login, $ghtml; 

	$revc = $login->getObject('general')->reversedns_b;

	if (!$revc->dns_slave_list) {
		throw new lxexception("dns_params_not_configured", '', "");
	}
	$this->syncserver = implode(",", $revc->dns_slave_list);

	$rdrlist = $this->getList('rdnsrange');
	$rdrange = null;
	foreach($rdrlist as $k => $v) {
		list($base, $first) = self::getBaseEnd($v->firstip);
		list($base, $last) = self::getBaseEnd($v->lastip);
		$rdrange[] = array($base, $first, $last);
	}

	$this->__var_rdnsrange = $rdrange;

	dprintr($this->__var_rdnsrange);

	$sq = new Sqlite(null, 'reversedns');
	$res = $sq->getTable();
	foreach($res as $r) {
		$r['nname'] = strtoupper($r['nname']);
		
		list($base, $last) = self::getBaseEnd($r['nname'], $this->__var_rdnsrange);
		
		$total[$base][] = array('nname' => $r['nname'], 'end' => $last, 'reversename' => $r['reversename']);
	}

	$this->__var_revdns1 = $revc->primarydns;
	$this->__var_revdns2 = $revc->secondarydns;

	$this->__var_reverse_list = $total;
}




static function addform($parent, $class, $typetd = null)
{
	$vlist = null;
	if ($parent->isClass('client')) {
		$vlist['nname'] = null;
		$vlist['reversename'] = null;
	} else {
		$vv =  $parent->getNotExistingList($vlist, "nname", 'reversedns', 'vmipaddress_a');
		if ($vv) {
			$vlist['reversename'] = null;
		}
	}
	$ret['variable'] = $vlist;
	$ret['action'] = 'add';
	return $ret;
}

function updateform($subaction, $param)
{
	$vlist['nname'] = array('M', $this->nname);
	$vlist['reversename'] = null;
	return $vlist;
}

}

class all_reversedns extends reversedns {

static $__desc =  array("n", "",  "all_reversedns");
static $__desc_parent_name_f =  array("n", "",  "vps");
static $__desc_parent_clname =  array("n", "",  "vps");
static $__acdesc_list =  array("", "",  "all_reverse_dns");

function isSelect() { return false ; }

static function initThisListRule($parent, $class)
{
	if (!$parent->isAdmin()) {
		throw new lxexception("only_admin_can_access", '', "");
	}

	return "__v_table";
}

static function createListSlist($parent)
{
	$nlist['nname'] = null;
	$nlist['parent_clname'] = null;
	return $nlist;
}
static function createListAddForm($parent, $class) { return false;}

static function createListAlist($parent, $class)
{
	return reversedns::createListAlist($parent, $class);
}

}
