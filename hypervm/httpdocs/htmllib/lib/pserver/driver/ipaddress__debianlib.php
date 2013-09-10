<?php 

class ipaddress__debian extends lxlclass {

function createipconf()
{

	global $gbl, $sgbl, $login, $ghtml; 

	
	$fstring =$this->getlostring();

	$pserver = $login->getFromList("pserver", "localhost");
	
	$ipl = $pserver->getList("ipaddress");

	$list  = array_reverse($ipl);
 
	$count = count($list);

	foreach($list as $ip) {
		$dev  = explode("-" , $ip->devname);
		if(count($dev) >= 2) {
			$actualname  = implode( ":", $dev);
		} else 
			$actualname = $ip->devname;

		$ip->network = $this->findnetworkaddress($ip->ipaddr , $ip->netmask);

		$fstring .= $this->getstring($actualname, $ip->ipaddr, $ip->network,$ip->broadcast , $ip->gateway , $ip->netmask);
 }		 
return $fstring;
}


function getlostring() 
{
	$fstring =	"auto lo\n";
	
	$fstring .= "iface lo inet loopback\n";

	return "$fstring\n\n\n";
}

function getstring($devname, $ipaddress, $network, $broadcast, $gateway , $netmask) 
{

	$fstring = "\n\n\n\n";

	$fstring  = "auto $devname\n";

	$fstring .= "iface $devname  inet static\n";

	$fstring .=  "\t\t address $ipaddress\n";

	$fstring .= "\t\t netmask $netmask\n"; 

	$fstring .= "\t\t network $network\n"; 

	$fstring .= "\t\t broadcast $broadcast\n";

	$fstring .= "\t\t gateway $gateway\n";

	return "$fstring\n\n\n";

}

static function listSystemIps($machinename)
{
	$result = self::getCurrentIps();
	$res = ipaddress::listSystemIps($result);
	foreach($res as $r) {
		ipaddress::copyCertificate($r['devname'], $machinename);
	}
	return $res;
}

function findnetworkaddress($ipaddr , $netmask) 
{

	$temp_ipaddr=explode(".",$ipaddr);
	$temp_netmask=explode(".",$netmask);
	$i=0;
	foreach($temp_ipaddr as $row)  { 
		$ipaddr_binary[$i]=str_pad(base_convert($row,10,2),8,'0',STR_PAD_LEFT);
		$i++;
	}
	$i=0;

	foreach($temp_netmask as $row) {
		$netmask_binary[$i]=str_pad(base_convert($row,10,2),8,'0',STR_PAD_LEFT);
		$networkip[$i]=($netmask_binary[$i] & $ipaddr_binary[$i]);
		$converted[$i]=base_convert($networkip[$i],2,10);
		$i++;
	}

	$networkaddress = implode(".",$converted);
	return $networkaddress;
}


function dosyncToSystem()
{
	return null;
}

static function getCurrentIps()
{

	global $gbl, $sgbl, $login, $ghtml; 

	$contents = lfile_get_contents("/etc/network/interfaces");

	$string = preg_replace('/[\n]+/' , "\n",  $contents);
	
	$array = explode("auto" , $string);

	foreach($array as $a12) {
		$a[]   =  self::getArrayFromString($a12); 
	}	
	foreach($a as $single ) {

		if(count($single) >= 3) {
			$ret[] = $single;
		}
	}

	return $ret;

}

static function getArrayFromString($t) 
{
	$array = explode("\n" , $t);

	error_reporting(0);

	foreach($array as $a ) 
	{
		$t = $a;

		$a=ltrim($t);

		list($a1, $a2, $a3, $a4) = explode(" " , $a); 
	
		$b['parent_clname'] = createParentName("pserver", "localhost");

		switch($a1) 
		{
		case 'iface':

            list($name,$id) = explode(':' , $a2);
		    if($id === " " || is_null($id)) 
				$b['devname'] = $a2 ;
			 else 
                $b['devname'] = $name . "-" . $id;  
			
			$b['bootproto'] = $a4;
			break;

		case 'address':
				$b['ipaddr'] = $a2;
				break;

	     case 'network':
				$b['network'] = $a2;
				break;

		case 'netmask':
				$b['netmask'] = $a2;
				break;

			case 'broadcast':
				$b['broadcast'] = $a2;
				break;

		case 'gateway': 
				$b['gateway'] = $a2;
				break;
	  }
  }
	return $b;

}

}

