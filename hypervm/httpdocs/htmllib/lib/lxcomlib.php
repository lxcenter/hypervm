<?PHP
//
//    HyperVM, Server Virtualization GUI for OpenVZ and Xen
//
//    Copyright (C) 2000-2009     LxLabs
//    Copyright (C) 2009          LxCenter
//
//    This program is free software: you can redistribute it and/or modify
//    it under the terms of the GNU Affero General Public License as
//    published by the Free Software Foundation, either version 3 of the
//    License, or (at your option) any later version.
//
//    This program is distributed in the hope that it will be useful,
//    but WITHOUT ANY WARRANTY; without even the implied warranty of
//    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
//    GNU Affero General Public License for more details.
//
//    You should have received a copy of the GNU Affero General Public License
//    along with this program.  If not, see <http://www.gnu.org/licenses/>.
?>

<?php

class Lxcom {

	public $__notreal;
	public $__com;
	public $__name;
	public $__varlist;

	function __construct($name, $throwflag = false)
	{
		$this->__notreal = false;
		dprint("Lxcom construct with throw $name\n");
		$this->__name = $name;
		try {
			$this->__com = new COM($name);
		} catch (exception $e) {
			if ($throwflag) {
				throw $e;
			}
			$this->__notreal = true;
		}

	}

	function lxcom_getSection($sec)
	{
		$osec = new Variant(NULL);
		$this->__com->getSection($sec, $osec);
		return create_lxcom($osec);
	}

	function object_set($obj, $var, $val)
	{
		$app = $this->__com->$obj;
		$app->$var = $val;
		$this->__com->$obj = $app;
		$this->__varlist["$obj.$var"] = $val;
	}

	function __set($var, $val)
	{
		if ($this->__notreal) {
			return;
		}
		$this->__varlist[$var] = $val;
		$this->__com->$var = $val;
	}

	function __get($var)
	{
		if ($this->__notreal) {
			return null;
		}
		return $this->__com->$var;
	}

	function __call($m, $arg)
	{

		$strarg = var_export($arg, true);
		$strarg = str_replace("\n", " ", $strarg);
		$existing = var_export($this->__varlist, true);
		$existing = str_replace("\n", " ", $existing);
		$call =  "$m $strarg on $this->__name (existing $existing)";
		if ($this->__notreal) {
			log_log("com_error", "unreal $call");
			return;
		}

		$comerr = false;
		$retcom = false;
		if (csb($m, "com_")) {
			$m = strfrom($m, "com_");
			$retcom = true;
		}
		try {
			//$ret = call_user_func_array(array($this->__com, $m), $arg);
			$string = null;
			for($i = 0; $i < count($arg); $i++) {
				$string[] = "\$arg[$i]";
			}
			if ($string) {
				$string = implode(", ", $string);
			}
			$func = "return \$this->__com->$m($string);";
			dprint("$func \n");
			$ret = eval($func);
		} catch (exception $e) {
			log_log("com_error", "Exception: {$e->getMessage()}: $call");
			$ret = null;
			$call = "Exception: $call";
			$comerr = true;
		}
		if (!$comerr) {
			$call = "Success..: $call";
		}
		log_log("com_call", $call);
		if ($retcom) {
			return create_lxcom($ret);
		}

		return $ret;
	}

}


function create_lxcom($ob)
{
	$lxc = new Lxcom('newcom');
	$lxc->__com = $ob;
	//if (is_object($ob) && get_class($ob) === 'com') {
	if (is_object($ob)) {
		$lxc->__notreal = false;
	} else {
		$lxc->__notreal = true;
	}
	return $lxc;
}
