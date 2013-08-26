<?php 

class reversedns__bind extends Lxdriverclass {


function doReverseDns()
{
	global $sgbl;
	$this->main->nname = strtoupper($this->main->nname);
	addLineIfNotExistInside("__path_named_chroot/etc/named.conf", "include \"/etc/lxreverse.conf\";", "//added by hypervm");
	list($base, $end) = reversedns::getBaseEnd($this->main->nname, $this->main->__var_rdnsrange);

        if(reversedns::isIPV6($this->main->nname))
        	$isv6=true;
	else $isv6=false;
	
	
	
	$ddate = date("Ymd");

	// We read the last serial from the zone file
	// default to 0 on error
	if($isv6) $version =$this->getNewSerial("$sgbl->__path_named_realpath/".$base."ip6.arpa", $ddate);
	else $version = $this->getNewSerial("$sgbl->__path_named_realpath/".$base."in-addr.arpa", $ddate);
	
	if($version<10) $version="0". $version;
	
	$ddate = "$ddate$version";
	

	$string = null;
	$string .= "\$TTL 3600\n";
        if($isv6) $string .="\$ORIGIN ".$base."IP6.ARPA.\n";
        else $string .= "\$ORIGIN $base.IN-ADDR.ARPA.\n";
	$string .= "@ IN SOA {$this->main->__var_revdns1}. root.{$this->main->__var_revdns1}. (\n";
	$string .= "$ddate ; serial\n";
	$string .= "18800 ; refresh\n";
	$string .= "14400 ; retry\n";
	$string .= "1814400 ; expire\n";
	$string .= "3600 ; default_tt\n";
	$string .= ")\n";
	$string .= "   IN NS {$this->main->__var_revdns1}.\n";

	if ($this->main->__var_revdns2) {
		$string .= "   IN NS {$this->main->__var_revdns2}.\n";
	}

	$result = $this->main->__var_reverse_list ;
	$result = $result[$base];

	$this->main->end = $end;
	$result = merge_array_object_not_deleted($result, $this->main);

	foreach($result as $key => $val) {
		$val['reversename'] = trim($val['reversename']);

		if (!cse($val['reversename'], ".")) {
			$val['reversename'] .= ".";
		}
                if($isv6) $string .= reversedns::createDottedRevedIPV6($val['end']) . "\t IN PTR {$val['reversename']}\n";
		else $string .= "{$val['end']}\tIN PTR {$val['reversename']}\n";
      	}


        if($isv6) lfile_put_contents("__path_named_realpath/".$base."ip6.arpa", $string);
        else lfile_put_contents("__path_named_realpath/$base.in-addr.arpa", $string); 
	$this->createMainFile();
}

function createMainFile()
{
	global $gbl, $sgbl, $login, $ghtml; 
	list($base, $end) = reversedns::getBaseEnd($this->main->nname, $this->main->__var_rdnsrange);
	$string = null;

	if(reversedns::isIPV6($this->main->nname)) $isIPV6 =true;
	else $isIPV6=false;

	$transferstring = null;
	if ($this->main->__var_transferip) {
		$transferstring = "allow-transfer { {$this->main->__var_transferip}; };";
	}
	foreach($this->main->__var_reverse_list as $revBase_ => $addr) {
		
		$revBase = strtoupper($revBase_);

		if ($revBase === strtoupper($base)) {
			continue;
		}
		
		
                if( reversedns::isIPV6($addr[0]['nname']))       
  		     $string .= "zone \"".reversedns::createDottedIPV6($revBase)."ip6.arpa\" {type master; file \"$sgbl->__path_named_path/".reversedns::createDottedIPV6($revBase)."ip6.arpa\"; $transferstring};\n\n";
		else $string .= "zone \"$revBase.in-addr.arpa\" {type master; file \"$sgbl->__path_named_path/$revBase.in-addr.arpa\"; $transferstring};\n\n";
	}
	if($isIPV6 === true) $string .= "zone \"".$base."ip6.arpa\" {type master; file \"$sgbl->__path_named_path/".$base."ip6.arpa\"; $transferstring};\n\n";
	else $string .= "zone \"$base.in-addr.arpa\" {type master; file \"$sgbl->__path_named_path/$base.in-addr.arpa\"; $transferstring};\n\n";

	lfile_put_contents("__path_named_chroot/etc/lxreverse.conf", $string);
}


function getNewSerial($fname, $ddate)
{
	if(!lfile_exists($fname))
		return 0;
	$fd = @fopen($fname, "r");
	
	if($fd === false) return 0;
	
	while($line = fgets($fd))
	{
		if(preg_match("/(\d*).*serial/", $line, $match))
		{
		  // If dates do not math, we reset serial
		  $date = substr($match[1], 0,8);
		  if($ddate == $date) 
		  {
		  	// else we increment it
		  	$ser = substr($match[1], 8,2);
		  	$ser  += 1;
		  }
		  else $ser = 0;
		  
		  fclose($fd);
		  return $ser;
		}
	}
	
	fclose($fd);
	return -0;
	
}

function dbactionAdd()
{

	$this->doReverseDns();
}

function dbactionUpdate($subaction)
{
	$this->doReverseDns();
}

function dbactionDelete()
{
	//$this->doReverseDns();
}

function dosyncToSystemPost()
{
	global $sgbl;
	$ret =  lxshell_return("rndc", "reload");
	if ($ret) {
		createRestartFile("bind");
	}
}


}
