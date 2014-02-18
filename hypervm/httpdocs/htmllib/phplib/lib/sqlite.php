<?php


class Sqlite {

private $__sqtable;
static $__database  = NULL;

private $__column_type;


function __construct($readserver, $table, $force = false)
{
	global $gbl, $sgbl, $ghtml;

	$name = $sgbl->__var_program_name;

	$this->__sqtable = $table;
	$this->__readserver = 'localhost';
	$readserver = $this->__readserver;

	$fdbvar = "__fdb_{$this->__readserver}";

	if (!isset($gbl->$fdbvar) || $force) {
		if (is_running_secondary()) {
			throw new lxexception("this_is_a_running_secondary_master", '', "");
		}

		$user = $sgbl->__var_admin_user;
		$db = $sgbl->__var_dbf;
		$pass = getAdminDbPass();

	        // TODO: REPLACE MYSQL_CONNECT
		if ($sgbl->__var_database_type === 'mysql') {

            	$gbl->$fdbvar = mysqli_connect($readserver, $user, $pass,$db);

        	if(!$gbl->$fdbvar)
	            {
	                print("\nMySQL-ERROR: Can not connect to the MySQL server at ($readserver): ".mysqli_connect_error().".\n");
	                exit;
	            }

		mysqli_query($gbl->$fdbvar,"SET CHARACTER SET 'utf8'");
	        mysqli_query($gbl->$fdbvar,"SET character_set_connection= 'utf8'");
		mysqli_select_db($gbl->$fdbvar,$db);

		self::$__database = 'mysql';

		} else if ($sgbl->__var_database_type === 'mssql')
                {
			        $gbl->$fdbvar = mssql_pconnect("$readserver,$sgbl->__var_mssqlport");
			        mssql_select_db($db);
			        self::$__database = 'mssql';
		        } else {
			        $gbl->$fdbvar = new PDO("sqlite:$db");
			        self::$__database = 'sqlite';
		        }

	    }

}


function reconnect()
{

	global $gbl, $sgbl, $login, $ghtml; 

	$this->__readserver = 'localhost';
	$user = $sgbl->__var_admin_user;
	$db = $sgbl->__var_dbf;
	$pass = getAdminDbPass();

	$readserver = $this->__readserver;

	$fdbvar = "__fdb_" . $this->__readserver;

	log_log("database_reconnect", "Reconnecting again");

    // TODO: REPLACE MYSQL_CONNECT
	if ($sgbl->__var_database_type === 'mysql') {
		$gbl->$fdbvar = mysqli_connect($readserver, $user, $pass, $db);
		mysqli_select_db($gbl->$fdbvar,$db);
		self::$__database = 'mysql';
	} else if ($sgbl->__var_database_type === 'mssql') {
		//print("$user, $pass <br> \n");
		//$gbl->$fdbvar = mssql_connect('\\.\pipe\MSSQL$LXLABS\sql\query');
		$gbl->$fdbvar = mssql_pconnect("$readserver,$sgbl->__var_mssqlport");
		mssql_select_db($db);
		self::$__database = 'mssql';
	} else {
		$gbl->$fdbvar = new PDO("sqlite:$db");
		self::$__database = 'sqlite';
	}
}


final function isLocalhost($var = "__readserver")
{

	global $gbl, $sgbl, $login, $ghtml; 
	if (isset($this->$var) && $this->$var && $this->$var != "localhost") {
		return false;
	}
	return true;
}


function rawQuery($string)
{

	$ret = $this->rl_query($string);
	return $ret;
}

function setPassword($newp)
{
	return $this->rawQuery("set password=Password('$newp');");
}

function real_escape_string($string)
{
     global $gbl, $sgbl, $login, $ghtml;
     $fdbvar = "__fdb_" . $this->__readserver;
     return mysqli_real_escape_string($gbl->$fdbvar,$string);

}

function database_query($res, $string)
{
    global $gbl, $sgbl, $login, $ghtml;
    $fdbvar = "__fdb_" . $this->__readserver;

    if (self::$__database == 'mysql') {
		$res = mysqli_query($gbl->$fdbvar,$string);
		if (!$res) {
			dprint("MySQL connection is broken. Reconnecting..\n");
			debugBacktrace();
			$this->reconnect();
			$res = mysqli_query($gbl->$fdbvar,$string);
		}
		dprint(mysqli_error($gbl->$fdbvar));
		return $res;
	} else if (self::$__database == "mssql") {
		return mssql_query($string, $res);
	} else {
		//return $res->query($string);
		$st = $res->prepare($string);
		if ($st) {
			$v = $st->execute();
		} else {
			dprint($string);
			dprintr($res->errorInfo());
		}
		return $st;
	}

}

function database_fetch_array($query)
{
	if (self::$__database == 'mysql') {
		return mysqli_fetch_array($query);
	} else if (self::$__database === 'mssql') {
		return mssql_fetch_array($query, MSSQL_ASSOC);
	} else {
		return $query->fetch(PDO::FETCH_ASSOC);
	}


}

static function close()
{
	global $gbl, $sgbl, $login, $ghtml; 
	$fdbvar = "__fdb_" . $this->__readserver;
	if (self::$__database == 'mysql') {
		//mysql_close($gbl->$fdbvar);
	} else 	if (self::$__database == 'mssql') {
		//mssql_close($gbl->$fdbvar);
	} else {
	}

	$gbl->$fdbvar = NULL;
}

function rl_query($string)
{
	global $gbl, $sgbl, $login, $ghtml; 

	$fdbvar = "__fdb_{$this->__readserver}";

	$query = $this->database_query($gbl->$fdbvar, $string);

	if (!$query) {
		return 0;
	}

	if (!(is_resource($query) || is_object($query))) {
		return 0;
	}

	//dprintr($select . ' ' . $string);
	$fulresult = null;
	while ($result = $this->database_fetch_array($query)) {
		if (isset($result['nname']) && $result['nname'] === '__dummy__dummy__') {
			continue;
		}

		if (isset($result['realpass'])) {
			$value = $result['realpass'];
			if (csb($value, '__lxen:')) {
				$value = base64_decode(strfrom($value, "__lxen:"));
			}
			$result['realpass'] = $value;
		}

		$fulresult[] = $result;
	}
	return $fulresult;



}

function getRowsGeneric($string, $list = null)
{
	$ret = null;

	if ($list) {
		$select = implode(",", $list);
	} else {
		$select = "*";
	}
	$query = "SELECT $select FROM $this->__sqtable $string";
	$fulresult = $this->rl_query($query);

	return $fulresult;
}



function getClass()
{
	return 'sqlite';
}


function existInTable($var, $value)
{
	$result = $this->getRowsWhere("$var = '$value'");
	if ($result) {
		return true;
	}
	return false;
}



function getRowsWhere($string, $list = null)
{

	return $this->getRowsGeneric("where " . $string, $list);
}

function getRowsOr($field1, $value1, $field2, $value2)
{
	return $this->getRowsWhere("$field1 = '$value1' or $field2 = '$value2'");
}


function getRowAnd($field1, $value1,$field2,$value2)
{
  return $this->getRowsWhere("$field1 = '$value1' and  $field2='$value2'");
}


function getRowsNot($field, $notval) 
{
	return $this->getRowsWhere("$field != '$notval'");
}


function getRows($field, $value)
{
	return $this->getRowsWhere("$field = '$value'");
}


function getTable($list = null)
{

	return $this->getRowsGeneric("", $list); 

}


function getColumnTypes()
{

	global $gbl, $sgbl, $login, $ghtml; 
	$fdbvar = "__fdb_" . $this->__readserver;
    $res = null;

	if (!$this->__column_type) {
		if ($sgbl->__var_database_type === 'mysql') {
            $query = "SHOW COLUMNS FROM $this->__sqtable";
			$result = mysqli_query($gbl->$fdbvar,$query);
			if (!$result) {
				dprint("MySQL connection is broken. Reconnecting.\n");
				$this->reconnect();
                $result = mysqli_query($gbl->$fdbvar,$query);
			}
		} else if ($sgbl->__var_database_type === 'mssql') {
			$result = mssql_query("sp_columns $this->__sqtable", $gbl->$fdbvar);
		} else {
			$f = $gbl->$fdbvar;
			$result = $f->prepare("select * from $this->__sqtable where nname = '__dummy__dummy__' ");
			if ($result) {
				$result->execute();
			}
		}

		if (!$result) {
			return null;
		}
		
		if ($sgbl->__var_database_type === 'mysql') {
			while(($row = mysqli_fetch_assoc($result))) {
				$res[$row['Field']] = $row['Field'];
			}
		} else if ($sgbl->__var_database_type === 'mssql') {
			while(($row = mssql_fetch_assoc($result))) {
				$res[$row['COLUMN_NAME']] = $row['COLUMN_NAME'];
			}
		} else {
			$row = $result->fetch(PDO::FETCH_ASSOC);
			if (!$row) {
				return null;
			}
			foreach($row as $k => $v) {
				$res[$k] = $k;
			}
		}


		$this->__column_type = $res;

	}

	return $this->__column_type;
}


function escapeBack($key, $string)
{
	if (!csb($key, "text_")) {
		return $string;
	}

	return $string;
}

function createQueryStringAdd($array)
{
	$string = " ( ";
	$result = $this->getColumnTypes();

	foreach($result as $key => $val) {
		$string = $string . " $key,";
	}
	$string = preg_replace("/,$/i", "", $string);
	$string = $string . ") values(" ;

	foreach($result as $key => $val) {

		if ($key === 'realpass') {
			$rp = $array[$key];
			$rp = base64_encode($rp);
			$rp = "__lxen:$rp";
			$string = "$string '$rp',";
			continue;
		}

		$string = "$string '{$this->escapeBack($key, $array[$key])}',";

	}
	$string = preg_replace("/,$/i", "", $string);
	$string = $string . " )";

	return $string;
}

function createQueryStringUpdate($array)
{
	global $gbl, $sgbl, $login, $ghtml; 

	$string = "";
	$result = $this->getColumnTypes();

	foreach($result as $key => $val) {
		if (isset($sgbl->__var_collectquota_run) && $sgbl->__var_collectquota_run) {
			if (!csb($key, "priv_") && !csb($key,'used_') && !csb($key, "nname") && !csb($key, "status") && !csb($key, "state") && !csb($key, "cpstatus")) {
				continue;
			}
		}

		if ($key === 'realpass') {
			$rp = $array[$key];
			if (!csb($rp, "__lxen:")) {
				$rp = base64_encode($rp);
				$rp = "__lxen:$rp";
			}
			$string[] = "$key = '$rp'";
			continue;
		}

		$string[] = "$key = '{$this->escapeBack($key, $array[$key])}'";
	}


	$string = implode(",", $string);

	return $string;
}

function getCountWhere($query)
{
	global $gbl, $sgbl, $login, $ghtml; 
	$countres = $this->rawquery("SELECT COUNT(*) FROM $this->__sqtable WHERE $query");
	if ($sgbl->__var_database_type === 'mysql') {
		$countres = $countres[0]['count(*)'];
	} else if ($sgbl->__var_database_type === 'mssql') {
		$countres = $countres[0]['computed'];
	} else {
		$countres = $countres[0]['count(*)'];
	}

	return $countres;
}


function getToArray($object) 
{
	$col = $this->getColumnTypes();

	foreach($col as $key => $val) {
		if (csb($key, "coma_")) {
			$cvar = substr($key, 5);
			$value = $object->$cvar;
			if (cse($key, "_list")) {
				$namelist = $value;
			} else {
				$namelist = get_namelist_from_objectlist($value);
			}
			$ret[$key] = implode(",", $namelist);
			dprint("in COma $key {$ret[$key]}<br> ");
			$ret[$key] = ",$ret[$key],";
		} else if (csb($key, "ser_")) {
			$cvar = substr($key, 4);
			$value = $object->$cvar;
			if ($value && isset($value->driverApp)) {
				unset($value->driverApp);
			}

			if (cse($key, "_a")) {
				if ($value) foreach($value as $kk => $vv) {
					unset($value[$kk]->__parent_o);
				}
			}

			$ret[$key] = base64_encode(serialize($object->$cvar));

		} else if (csb($key, "priv_q_") || csb($key, "used_q_")) {
			$qob = strtil($key, "_q_");
			$qkey = strfrom($key, "_q_");
			if ($object->get__table() === 'uuser') {
			}
			$ret[$key] = $object->$qob->$qkey;
		} else {
			if (!isset($object->$key)) {
				$object->$key = null;
			}
			if (csb($key, "text_")) {
				$string = str_replace("\\", '\\\\', $object->$key);
			} else {
				$string = $object->$key;
			}
			$ret[$key] = str_replace("'", "\'", $string);
		}
	}
	return $ret;
}


function setRowObject($nname, $value, $object)
{

	$array = $this->getToArray($object);

	$this->setRow($nname, $value, $array);

}


function addRowObject($object)
{

	$array = $this->getToArray($object);

	$this->addRow($array);

}


function setRow($nname, $value, $array)
{

	global $gbl, $sgbl, $login, $ghtml; 

	$fdbvar = "__fdb_" . $this->__readserver;

	if (!$this->isLocalhost()) {
		print("Major error occured! This is not localhost?\n");
		exit;
	}

	$string = $this->createQueryStringUpdate($array);

	$update = "UPDATE $this->__sqtable SET $string WHERE $nname= '$value'";

	if (!($upd = $this->database_query($gbl->$fdbvar, $update)))
		log_database("DB-Error: Update Failed for $update");
	else  {
		if ($this->__sqtable !== 'utmp') {
			dprint("DB-Success: Updated " .$this->__sqtable . " for " .  $array['nname'] . "\n", 1);
		}
	}

}



function addRow($array)
{
	global $gbl, $sgbl, $login, $ghtml; 
	$fdbvar = "__fdb_" . $this->__readserver;

	if (!$this->isLocalhost()) {
        print("Major error occured! This is not localhost?\n");
		exit;
	}

	$string = $this->createQueryStringAdd($array);
	$insert = "INSERT INTO $this->__sqtable $string ;";

	if ($ins = $this->database_query($gbl->$fdbvar, $insert)) {
		dprint("DB-Record: Inserted in $this->__sqtable for {$array['nname']}\n", 1);
	} else {
		log_database("DB-Error: Insert Failed for {$this->__sqtable}:{$array['nname']}");
		log_bdatabase("DB-Error: Insert Failed for {$this->__sqtable}:{$array['nname']} $insert");
		return true;
	}
    return false;
}

function delRow($nname, $value)
{

	global $gbl, $sgbl, $login, $ghtml; 

	$fdbvar = "__fdb_" . $this->__readserver;

	$delete = "DELETE FROM $this->__sqtable WHERE $nname = '$value'";

	$delresult = $this->database_query($gbl->$fdbvar, $delete);


	 if(!$delresult) {
		 log_database("DB-Error: Delete Failed for $delete");
	 } else {
		 dprint("Record deleted from $this->__sqtable for $nname <br>.");
	 }
 
}


}
