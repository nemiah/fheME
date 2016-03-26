<?php
/*
 *  This file is part of phynx.

 *  phynx is free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 3 of the License, or
 *  (at your option) any later version.

 *  phynx is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.

 *  You should have received a copy of the GNU General Public License
 *  along with this program.  If not, see <http://www.gnu.org/licenses/>.
 * 
 *  2007 - 2016, Rainer Furtmeier - Rainer@Furtmeier.IT
 */

class DBStorage {
	private $parsers;
	protected $affectedRowsOnly = false;
	protected $c;
	protected $cWrite;
	public static $globalConnection = array();
	public static $globalConnectionWrite = array();
	protected $data;
	protected $dataWrite;
	public static $queryCounter = 0;
	public static $lastQuery = array();
			
	function __construct(){
		if($this->data == null AND isset($_SESSION["DBData"]))
			$this->data = $_SESSION["DBData"];
		
		if($this->dataWrite == null AND isset($_SESSION["DBDataWrite"]))
			$this->dataWrite = $_SESSION["DBDataWrite"];

		if(!isset(self::$globalConnection[get_class($this)]))
			self::$globalConnection[get_class($this)] = null;

		if(!isset(self::$globalConnectionWrite[get_class($this)]))
			self::$globalConnectionWrite[get_class($this)] = null;

		
		if(self::$globalConnection[get_class($this)] == null){
			if($this->data == null) throw new NoDBUserDataException();
			$this->renewConnection();
		} else $this->c = self::$globalConnection[get_class($this)];
		
		
		$this->cWrite = $this->c;
			
		if(self::$globalConnectionWrite[get_class($this)] != null)
			$this->cWrite = self::$globalConnectionWrite[get_class($this)];
		
		if(self::$globalConnectionWrite[get_class($this)] == null AND $this->dataWrite != null)
			$this->renewConnectionWrite();
		
		
		#echo "<br /><br />".get_class($this).":";
		#print_r(self::$globalConnection[get_class($this)]);
		#echo mysqli_connect_errno();
	}

	/**
	 * @return mysqli
	 */
	public function getConnection(){
		return $this->c;
	}
	
	/**
	 * @return mysqli
	 */
	public function getConnectionWrite(){
		return $this->cWrite;
	}
	
	public static function disconnect(){ //has to be static or new connection will be built in __construct()
		$status = self::$globalConnection[__CLASS__]->close();
		if(isset(self::$globalConnectionWrite[__CLASS__]) AND self::$globalConnectionWrite[__CLASS__] != null)
			self::$globalConnectionWrite[__CLASS__]->close();
		
		self::$globalConnection[__CLASS__] = null;
		self::$globalConnectionWrite[__CLASS__] = null;

		return $status;
	}
	
	public function setGetAffectedRowsOnly($bool){
		$this->affectedRowsOnly = $bool;
	}
	/*
	public static function getSI() {
		if (!DBStorage::$instance)
			DBStorage::$instance = new DBStorage();
			
		return DBStorage::$instance;
	}*/
	
	public function renewConnection(){
		$this->c = new mysqli($this->data["host"],$this->data["user"],$this->data["password"],$this->data["datab"]);
		if(mysqli_connect_error() AND (mysqli_connect_errno() == 1045 OR mysqli_connect_errno() == 2002 OR mysqli_connect_errno() == 2003 OR mysqli_connect_errno() == 2005)) throw new NoDBUserDataException(mysqli_connect_errno().":".mysqli_connect_error());
		if(mysqli_connect_error() AND (mysqli_connect_errno() == 1049 OR mysqli_connect_errno() == 1044)) throw new DatabaseNotFoundException();
		echo $this->c->error;
		
		if($this->c->error AND PHYNX_USE_SYSLOG)
			syslog(LOG_ERR, "MySQL: ".$this->c->error."(".$this->c->errno.")");
		
		$this->c->set_charset("utf8");
		$this->c->query("SET SESSION sql_mode='';");
		
		self::$globalConnection[get_class($this)] = $this->c;
	}
	
	public function renewConnectionWrite(){
		$this->cWrite = new mysqli($this->dataWrite["host"],$this->dataWrite["user"],$this->dataWrite["password"],$this->dataWrite["datab"]);
		if(mysqli_connect_error() AND (mysqli_connect_errno() == 1045 OR mysqli_connect_errno() == 2002 OR mysqli_connect_errno() == 2003 OR mysqli_connect_errno() == 2005)) throw new NoDBUserDataException(mysqli_connect_errno().":".mysqli_connect_error());
		if(mysqli_connect_error() AND (mysqli_connect_errno() == 1049 OR mysqli_connect_errno() == 1044)) throw new DatabaseNotFoundException();
		echo $this->cWrite->error;
		
		if($this->cWrite->error AND PHYNX_USE_SYSLOG)
			syslog(LOG_ERR, "MySQL: ".$this->cWrite->error."(".$this->cWrite->errno.")");
		
		$this->cWrite->set_charset("utf8");
		$this->cWrite->query("SET SESSION sql_mode='';");
		
		self::$globalConnectionWrite[get_class($this)] = $this->cWrite;
	}
	/*
	public function renewConnection(){
		
		$this->c = new mysqli($this->data["host"],$this->data["user"],$this->data["password"],$this->data["datab"]);
		if(mysqli_connect_error() AND (mysqli_connect_errno() == 1045 OR mysqli_connect_errno() == 2002)) throw new NoDBUserDataException();
		if(mysqli_connect_error() AND mysqli_connect_errno() == 1049) throw new DatabaseNotFoundException();
		echo $this->c->error;
		$this->c->set_charset("utf8");
		$this->c->query("SET SESSION sql_mode='';");
		DBStorage::$queryCounter++;
		self::$globalConnection[get_class($this)] = $this->c;
	}*/
	
	public function setParser($p){
		$this->parsers = $p;
	}
	
	function checkForTable($name){
		$sql = "SHOW TABLES FROM `".$this->data["datab"]."`";
		$result = $this->c->query($sql);
		DBStorage::$queryCounter++;
		$_SESSION["messages"]->addMessage("executing MySQL: $sql");
		if($result) while ($row = $result->fetch_row())
			if(strtolower($row[0]) == strtolower($name)) return true;

		#if($result) mysql_free_result($result);
		
		return false;
	}
	
	function checkMyTable($CIA){
		if(strpos($CIA->MySQL, "INSERT INTO") !== false)
			return;
		
		$CIAAlt = clone $CIA;
		
		$view = false;
		if(strpos($CIA->MySQL, "CREATE VIEW") !== false)
			$view = true;
		
		if(!$view)
			preg_match("/CREATE TABLE `([a-zA-Z0-9]*)`/", $CIA->MySQL, $regs);
		else
			preg_match("/CREATE VIEW `([a-zA-Z0-9]*)`/", $CIA->MySQL, $regs);
		
		$rand = "RANDOM".rand(10000,100000);
		while($this->checkForTable($regs[1].$rand))
			$rand = "RANDOM".rand(10000,100000);
		
		if(!$view)
			$CIA->MySQL = str_replace("CREATE TABLE `$regs[1]`","CREATE TABLE `".$regs[1].$rand."`",$CIA->MySQL);
		else
			$CIA->MySQL = str_replace("CREATE VIEW `$regs[1]`","CREATE VIEW `".$regs[1].$rand."`",$CIA->MySQL);
		
		$this->createTable($CIA);
		$newTable = PMReflector::getAttributesArrayAnyObject($this->getTableColumns($regs[1].$rand));

		if(!$view)
			$this->dropTable($regs[1].$rand);
		else
			$this->dropView($regs[1].$rand);
		
		$oldTable = PMReflector::getAttributesArrayAnyObject($this->getTableColumns($regs[1]));
		$unterschied2 = array_diff($newTable,$oldTable);
		
		$this->cWrite->query("ALTER TABLE `$regs[1]` COMMENT = '".$_SESSION["applications"]->getActiveApplication()."_".$_SESSION["applications"]->getRunningVersion().";'");
		DBStorage::$queryCounter++;

		
		if(stripos(php_uname("s"), "windows") === 0)
			$this->cWrite->query("ALTER TABLE `$regs[1]` ENGINE = 'MyISAM';");
		
		
		if(count($unterschied2) == 0){
			$_SESSION["messages"]->addMessage("No differences found! (Only different field-names can be found!)");
			return -1;
		}
		
		if($view){
			$this->dropView($regs[1]);
			$this->createTable($CIAAlt);
			return count($unterschied2);
		}
		
		$_SESSION["messages"]->addMessage("Please be aware that this function only works on properly formatted SQL-code. The fieldname must be enclosed by ` and a newline \\n must follow the ,.");
		$changes = 0;
		foreach($unterschied2 as $key => $value){
			$newSQL = strstr($CIA->MySQL,"`$value`");
			#$ex = explode(",", $newSQL);
			$ex = preg_split("/,\s/", $newSQL);
			#echo "<pre>";
			#print_r($ex);
			#echo "</pre>";
			$newSQL = $ex[0];
			
			$sql = "ALTER TABLE `$regs[1]` ADD $newSQL";
			$this->cWrite->query($sql);
			self::$lastQuery[] = $sql;
			if(count(self::$lastQuery) > 5)
				array_shift(self::$lastQuery);
			
			if($this->cWrite->error)
				throw new Exception($this->cWrite->error);
			
			#ALTER command denied to user
			
			DBStorage::$queryCounter++;
			$_SESSION["messages"]->addMessage("Added field $value in table $regs[1]");
			
			$changes++;
		}
		
		return $changes;
	}

	private static $lockAliases = array();
	public function lockTable($tables){#, $readLock = false){
		if(!is_array($tables))
			$tables = array($tables);
		
		self::$lockAliases["Userdata"] = "Userdata_locked_read";
		
		$query = "LOCK TABLES";
		foreach($tables AS $k => $table){
			self::$lockAliases[$table] = $table."_locked_read";
			
			$query .= ($k != 0 ? ", " : "")."`$table` WRITE, `$table` AS `{$table}_locked_read` READ";
		}
		
		
		$this->cWrite->query("$query, `Userdata` WRITE, `Userdata` AS `Userdata_locked_read` READ;");# AS `{$table}_locked`
		
		
		if($this->cWrite->error)
			echo $this->cWrite->error;
	}
	
	public function unlockTables(){
		$this->cWrite->query('UNLOCK TABLES;');
		self::$lockAliases = array();
		
		if($this->cWrite->error)
			echo $this->cWrite->error;
	}
	
	public function alterTable($CIA){
		if(strpos($CIA->MySQL, "ALTER TABLE") != 0) return;
		
		$_SESSION["messages"]->addMessage("executing MySQL: $CIA->MySQL");

		$this->cWrite->query($CIA->MySQL);
		DBStorage::$queryCounter++;
		if($this->cWrite->error)
			echo $this->cWrite->error;
		
	}
	
	private function dropTable($name){
		$sql = "DROP TABLE `".$name."`";
		$_SESSION["messages"]->addMessage("executing MySQL: $sql");
		$this->cWrite->query($sql);
		DBStorage::$queryCounter++;
	}
	
	private function dropView($name){
		$sql = "DROP VIEW `".$name."`";
		$_SESSION["messages"]->addMessage("executing MySQL: $sql");
		$this->cWrite->query($sql);
		DBStorage::$queryCounter++;
	}
	
	function loadSingle2($table, $id/*, $typsicher = false*/) {
		$sql = "SELECT * FROM $table WHERE ".$table."ID = '$id'";
		$q = $this->c->query($sql);
		DBStorage::$queryCounter++;
		$_SESSION["messages"]->addMessage("executing MySQL: $sql");
		
		if($this->c->error AND $this->c->errno == 1146)
			throw new TableDoesNotExistException($table);
		
		if($this->c->error AND ($this->c->errno == 1045 OR $this->c->errno == 2002))
			throw new NoDBUserDataException();
		
		if($this->c->error AND $this->c->errno == 1054) {
			preg_match("/[a-zA-Z0-9 ]*\'([a-zA-Z0-9\.]*)\'[a-zA-Z ]*\'([a-zA-Z ]*)\'.*/", $this->c->error(), $regs);
			throw new FieldDoesNotExistException($regs[1],$regs[2]);
		}
		
		if($this->c->error AND $this->c->errno == 1046)
			throw new DatabaseNotSelectedException();
		
		echo $this->c->error;
		
		if($this->c->error AND PHYNX_USE_SYSLOG)
			syslog(LOG_ERR, "MySQL: ".$this->c->error."(".$this->c->errno.") in $sql");
		
		$t = $q->fetch_object();
		
		$fields = PMReflector::getAttributesArrayAnyObject($t);
		
		/*if($typsicher){
			$types = array();
			$qc = $this->c->query("SHOW COLUMNS FROM $table");
			DBStorage::$queryCounter++;
			while($tc = $qc->fetch_object())
				$types[$tc->Field] = $this->mysql2Object($tc->Type);
		}*/
		
		foreach($fields AS $key => $value){
			$t->$value = $this->fixUtf8(stripslashes($t->$value));
						
			/*if($typsicher){
				if(isset($types[$value])) $typObj = $types[$value];
				else throw new DataTypeNotDefinedException($value);
				
				$t->$value = new $typObj($t->$value);
			}*/
		}
		
		return $t;
	}
	
	/*function loadSingleT($table, $id) {
		return $this->loadSingle2($table, $id, true);
	}*/
	
	function createTable($CIA){
		$view = false;
		if(strpos($CIA->MySQL, "CREATE VIEW") !== false)
			$view = true;
		
		if(!$view)
			preg_match("/CREATE TABLE `([a-zA-Z0-9]*)`/",$CIA->MySQL,$regs);
		else
			preg_match("/CREATE VIEW `([a-zA-Z0-9]*)`/",$CIA->MySQL,$regs);

		$_SESSION["messages"]->addMessage("executing MySQL: $CIA->MySQL");
		$this->cWrite->query($CIA->MySQL);
		DBStorage::$queryCounter++;
		if($this->cWrite->error AND $this->cWrite->errno == 1046) throw new DatabaseNotSelectedException();
		
		if(strpos($CIA->MySQL, "INSERT INTO") === false AND !$view){
			$sql = "ALTER TABLE `$regs[1]` COMMENT = '".$_SESSION["applications"]->getActiveApplication()."_".$_SESSION["applications"]->getRunningVersion().";'";
			$_SESSION["messages"]->addMessage("executing MySQL: $sql");
			$this->cWrite->query($sql);
			DBStorage::$queryCounter++;
			
			if(stripos(php_uname("s"), "windows") === 0)
				$this->cWrite->query("ALTER TABLE `$regs[1]` ENGINE = 'MyISAM';");
		}
		return $this->c;
	}

	function saveSingle2($table, $id, $A) {
		#if(PHYNX_MYSQL_STRICT)
		#	$this->fixTypes($table, $A);
		
		$fields = PMReflector::getAttributesArray($A);
	    $sql = "UPDATE $table SET";
		for($i = 0;$i < count($fields);$i++){
			$f = $fields[$i];
			#print_r($A->$f);
			#if(!is_numeric($A->$fields[$i]))
				$sql .= ($i > 0 ? "," : "")." ".$fields[$i]." = '".$this->cWrite->real_escape_string($A->$f)."'";
			#else $sql .= ($i > 0 ? "," : "")." ".$fields[$i]." = ".$A->$fields[$i]."";
		}
		$sql .= " WHERE ".$table."ID = '$id'";
		$_SESSION["messages"]->addMessage("executing MySQL: $sql");
		$this->cWrite->query($sql);
		DBStorage::$queryCounter++;
		if($this->cWrite->error AND $this->cWrite->errno == 1062)
			throw new DuplicateEntryException($this->cWrite->error);
		
		if($this->cWrite->error)
			Red::errorD ($this->cWrite->error);
		
		if($this->cWrite->error AND PHYNX_USE_SYSLOG)
			syslog(LOG_ERR, "MySQL: ".$this->cWrite->error."(".$this->cWrite->errno.") in $sql");
	}

	function getTableColumns($forWhat){
		$result = $this->c->query("SHOW COLUMNS FROM $forWhat");
		DBStorage::$queryCounter++;
		if($this->c->error AND $this->c->errno == 1146) throw new TableDoesNotExistException($forWhat);
		
		$a = new stdClass();
		while ($row = $result->fetch_assoc()){
			$f = $row["Field"];
			$a->$f = "";
		}
		return $a;
	}
	
	public function fixUtf8($value){
		$value = str_replace("Ã„", "Ä", $value);
		$value = str_replace("Ã–", "Ö", $value);
		$value = str_replace("Ãœ", "Ü", $value);
		
		$value = str_replace("Ã¤", "ä", $value);
		$value = str_replace("Ã¶", "ö", $value);
		$value = str_replace("Ã¼", "ü", $value);
		
		$value = str_replace("ÃŸ", "ß", $value);
		return $value;
	}
	
	/**
	 * @deprecated since version 31.10.2012
	 * @param string $value
	 * @return string
	 */
	public static function findNonUft8($value){
		$value = str_replace("Ä", "Ã„", $value);
		$value = str_replace("Ö", "Ã–", $value);
		$value = str_replace("Ü", "Ãœ", $value);
		
		$value = str_replace("ä", "Ã¤", $value);
		$value = str_replace("ö", "Ã¶", $value);
		$value = str_replace("ü", "Ã¼", $value);
		
		$value = str_replace("ß", "ÃŸ", $value);
		return $value;
	}
	
	private $lazyRequest = null;
	function loadMultipleV4(SelectStatement $statement, $lazyload = false){
		if($lazyload AND $this->lazyRequest !== null){
			$t = $this->lazyRequest->fetch_object();
			if(!$t){
				$this->lazyRequest = null;
				return null;
			}
			
			return $this->process($t, $statement);
		}
		
		if(!$this->c)
			throw new StorageException();
		
		#file_put_contents(Util::getRootPath()."debug.txt", print_r(debug_backtrace(), true));
		$where = "(";
		$lastKey = "";
		#$closeBrackets = "";
		foreach($statement->whereFields as $key => $value){
			$addOpenBracket = false;
			if($where != "(" AND $statement->whereBracketGroup[$lastKey] != $statement->whereBracketGroup[$key]){
				$addOpenBracket = true;
				$where .= ")";
			}
			$currentWhereValue = $statement->whereValues[$key];
			if($currentWhereValue != "NULL" 
				AND $currentWhereValue != "NOT NULL"
				AND $statement->whereOperators[$key] != "IN"
				AND $statement->whereOperators[$key] != "NOT IN"
				AND substr($currentWhereValue, 0, 3) != "t1."
				AND substr($currentWhereValue, 0, 3) != "t2.") 
				$currentWhereValue = "'".$this->c->real_escape_string($currentWhereValue)."'";
				
			$where .= ($where != "(" ? " ".$statement->whereLogOp[$key]." ".($addOpenBracket ? "(" : "") : "")./*(in_array($statement->whereFields[$key], $nJAs) ? "t1." : "").*/"".$statement->whereFields[$key]." ".$statement->whereOperators[$key]." ".$currentWhereValue."";
			$lastKey = $key;
		}
		$where .= ")";
		$order = "";
		foreach($statement->order as $key => $value)
			$order .=  ($order != "" ? ", ": "").$statement->order[$key]." ".$statement->orderAscDesc[$key];
		
		
		$tables = array();
		
		for($i=0;$i<count($statement->joinTables);$i++){
			if(!isset($tables[$statement->joinTables[$i]]))
				$tables[$statement->joinTables[$i]] = array();
			
			$tables[$statement->joinTables[$i]][] = array(
				$statement->joinConditions[$i][0],
				$statement->joinConditionOperators[$i],
				$statement->joinConditions[$i][1],
				$statement->joinTypes[$i]);
		}
		
		$t = 2;
		$joinAdd = "";
		foreach($tables as $table => $conditions){
			$type = "LEFT";
			$ons = "";
			for($i=0;$i<count($conditions);$i++){
				if($i == 0)
					$ons .= ((!strpos($conditions[$i][0],".") AND $conditions[$i][0]{0} != " ") ? "t1." : "")."".$conditions[$i][0]." ".$conditions[$i][1]." t$t.".$conditions[$i][2];
				else {
					if($conditions[$i][2] != "NOT NULL" AND $conditions[$i][2] != "NULL") $conditions[$i][2] = "'".$conditions[$i][2]."'";
					$ons .= " AND t$t.".$conditions[$i][0]." ".$conditions[$i][1]." ".$conditions[$i][2]."";
				}
				
				if(isset($conditions[$i][3]) AND $conditions[$i][3] != "")
					$type = $conditions[$i][3];
			}
			
			$joinAdd .= "\n $type JOIN ".$table." t$t ON($ons)";

			$t++;
		}

		if($this->affectedRowsOnly AND count($statement->group) === 0) {
			$tempFields = $statement->fields;
			
			$statement->fields = array("COUNT(*) AS anzahlTotal");
			$order = "";
		}
		
		$sql = "SELECT\n ".implode(",\n ",$statement->fields)."\n FROM `".$statement->table[0]."` t1$joinAdd ".($where != "()" ? "\n WHERE $where" : "").(count($statement->group) > 0 ? "\n GROUP BY ".implode(", ",$statement->group) : "").($order != "" ? "\n ORDER BY $order" : "").(count($statement->limit) > 0 ? "\n LIMIT ".implode(", ",$statement->limit) : "");
		
		if(isset(self::$lockAliases[$statement->table[0]])){
			$sql = str_replace (" t1 ", " ".self::$lockAliases[$statement->table[0]]." ", $sql);
			$sql = str_replace (" t1.", " ".self::$lockAliases[$statement->table[0]].".", $sql);
		}
		
		
		if($statement->table[0] != "Userdata") $_SESSION["messages"]->startMessage("executing MySQL: $sql");
		#echo nl2br($sql)."<br /><br />";
		$q = $this->c->query($sql);
		self::$lastQuery[] = $sql;
		if(count(self::$lastQuery) > 5)
			array_shift(self::$lastQuery);
		
		DBStorage::$queryCounter++;

		if($q === null OR ($this->c->error AND ($this->c->errno == 1045 OR $this->c->errno == 2002))) throw new NoDBUserDataException();
		if($this->c->error AND $this->c->errno == 1146) throw new TableDoesNotExistException($statement->table[0]);
		if($this->c->error AND $this->c->errno == 1046) throw new DatabaseNotSelectedException();
		if($this->c->error AND $this->c->errno == 1047)
			throw new BrainSplitException($this->c->error);
		
		if($this->c->error AND $this->c->errno == 1054) {
			preg_match("/[a-zA-Z0-9 ]*\'([a-zA-Z0-9\.]*)\'[a-zA-Z ]*\'([a-zA-Z ]*)\'.*/", $this->c->error, $regs);
			throw new FieldDoesNotExistException($regs[1],$regs[2]);
		}
		#if($this->c->error AND $this->c->errno == 1028) //aborted query
		#	die($sql);
		if($this->c->error AND $this->c->errno == 2006) throw new StorageException($this->c->error);
		
		
		
		if($this->c->error)
			echo "MySQL-Fehler: ".$this->c->error." (".$this->c->errno.")<br>\nQuery:$sql";
		

		if($this->c->error AND PHYNX_USE_SYSLOG)
			syslog(LOG_ERR, "MySQL: ".$this->c->error."(".$this->c->errno.") in $sql");
		
		#echo $sql."<br /><br />";
		if($statement->table[0] != "Userdata") 
			$_SESSION["messages"]->endMessage(": ".$this->c->affected_rows." ".$statement->table[0]." geladen");
		
		if($this->affectedRowsOnly) {
			$this->affectedRowsOnly = false;
			
			if(count($statement->group) === 0){
				$statement->fields = $tempFields;
				
				$t = $q->fetch_object();
				return $t->anzahlTotal;
			}
			
			return $this->c->affected_rows;
		}

		if($lazyload){
			$this->lazyRequest = $q;
			$t = $q->fetch_object();
			return $this->process($t, $statement);
		}
		
		$collector = array();
		while($t = $q->fetch_object())
			$collector[] = $this->process($t, $statement);
		
		
		return $collector;
	}
	
	private function process($t, $statement){
		$cName = $statement->table[0];
		if($statement->className != "") 
			$cName = $statement->className[0];
		
		$A = new Attributes();
		
		#if($fields == null) 
		#	$fields = PMReflector::getAttributesArrayAnyObject($t);

		foreach($t AS $key => $value)
			$A->$key = $this->fixUtf8(stripslashes($value));


		if(count($this->parsers) > 0)
			foreach($this->parsers as $key => $value)
				if(isset($A->$key))
					$A->$key = $this->invokeStaticMethod($value, array($A->$key, "load", $A));

		$oID = $statement->table[0]."ID";

		$newCOfClass = new $cName($t->$oID);
		$newCOfClass->setA($A);
		
		return $newCOfClass;
	}
	
	function loadMultipleV3(SelectStatement $statement){

		$noJoinAs = str_replace("Join","",$statement->AttributesClassName);
		$nJAs = PMReflector::getAttributesArray($noJoinAs);
		
		$where = "(";
		$lastKey = "";
		$closeBrackets = "";
		foreach($statement->whereFields as $key => $value){
			$addOpenBracket = false;
			if($where != "(" AND $statement->whereBracketGroup[$lastKey] != $statement->whereBracketGroup[$key]){
				$addOpenBracket = true;
				$where .= ")";#$closeBrackets .= ")";
			}
			$currentWhereValue = $statement->whereValues[$key];
			if($currentWhereValue != "NULL" 
				AND $currentWhereValue != "NOT NULL" 
				AND substr($currentWhereValue, 0, 3) != "t1.") 
				$currentWhereValue = "'".$this->c->real_escape_string($currentWhereValue)."'";
			
			$where .= ($where != "(" ? " ".$statement->whereLogOp[$key]." ".($addOpenBracket ? "(" : "") : "").(in_array($statement->whereFields[$key],$nJAs) ? "t1." : "")."".$statement->whereFields[$key]." ".$statement->whereOperators[$key]." ".$currentWhereValue."";
			$lastKey = $key;
		}
		$where .= ")";#$closeBrackets;
		
		$order = "";
		foreach($statement->order as $key => $value)
			$order .=  ($order != "" ? ", ": "").$statement->order[$key]." ".$statement->orderAscDesc[$key];
		
			
		$tables = array();
		
		for($i=0;$i<count($statement->joinTables);$i++){
			if(!isset($tables[$statement->joinTables[$i]])) $tables[$statement->joinTables[$i]] = array();
			$tables[$statement->joinTables[$i]][] = array($statement->joinConditions[$i][0],$statement->joinConditionOperators[$i],$statement->joinConditions[$i][1]);
		}
		
		if($i > 0){
			for($i=0;$i<count($nJAs);$i++){
				$w = array_search($nJAs[$i],$statement->fields);
				if($statement->fields[$w] == $nJAs[$i]) $statement->fields[$w] = "t1.".$statement->fields[$w];
			}
		}
		
		$t = 2;
		$joinAdd = "";
		foreach($tables as $table => $conditions){
			$ons = "";
			for($i=0;$i<count($conditions);$i++){
				#$ons .= ($i != 0 ? " AND " : "")."t1.".$conditions[$i][0]." ".$conditions[$i][1];#.((in_array("t1.".$conditions[$i][2],$statement->fields) OR in_array($conditions[$i][2],$statement->fields)) ? " t1.".$conditions[$i][2] : " '".$conditions[$i][2]."'");
				if($i == 0) $ons .= ((!strpos($conditions[$i][0],".") AND $conditions[$i][0]{0} != " ") ? "t1." : "")."".$conditions[$i][0]." ".$conditions[$i][1]." t$t.".$conditions[$i][2];
				else {
					if($conditions[$i][2] != "NOT NULL" AND $conditions[$i][2] != "NULL") $conditions[$i][2] = "'".$conditions[$i][2]."'";
					$ons .= " AND t$t.".$conditions[$i][0]." ".$conditions[$i][1]." ".$conditions[$i][2]."";
				}
			}
			
			$joinAdd .= "\n LEFT JOIN ".$table." t$t ON($ons)";

			$t++;
		}
		
		if($this->affectedRowsOnly AND count($statement->group) === 0) {
			$tempFields = $statement->fields;
			
			$statement->fields = array("COUNT(*) AS anzahlTotal");
			$order = "";
		}
		
		$sql = "SELECT\n ".implode(",\n ",$statement->fields)."\n FROM `".$statement->table[0]."` t1$joinAdd ".($where != "()" ? "\n WHERE $where" : "").(count($statement->group) > 0 ? "\n GROUP BY ".implode(", ",$statement->group) : "").($order != "" ? "\n ORDER BY $order" : "").(count($statement->limit) > 0 ? "\n LIMIT ".implode(", ",$statement->limit) : "");

		$collector = array();

		$AS = new $statement->AttributesClassName();

		if($statement->table[0] != "Userdata") $_SESSION["messages"]->startMessage("executing MySQL: $sql");
		#echo nl2br($sql)."<br /><br />";
		$q = $this->c->query($sql);
		DBStorage::$queryCounter++;

		if($this->c->error AND ($this->c->errno == 1045 OR $this->c->errno == 2002)) throw new NoDBUserDataException();
		if($this->c->error AND $this->c->errno == 1146) throw new TableDoesNotExistException($statement->table[0]);
		if($this->c->error AND $this->c->errno == 1046) throw new DatabaseNotSelectedException();
		if($this->c->error AND $this->c->errno == 1054) {
			preg_match("/[a-zA-Z0-9 ]*\'([a-zA-Z0-9\.]*)\'[a-zA-Z ]*\'([a-zA-Z ]*)\'.*/", $this->c->error, $regs);
			throw new FieldDoesNotExistException($regs[1],$regs[2]);
		}
		if($this->c->error) echo "MySQL-Fehler: ".$this->c->error."<br />Fehlernummer: ".$this->c->errno;
			
		if($this->c->error AND PHYNX_USE_SYSLOG)
			syslog(LOG_ERR, "MySQL: ".$this->c->error."(".$this->c->errno.") in $sql");
		
		if($statement->table[0] != "Userdata") $_SESSION["messages"]->endMessage(": ".$this->c->affected_rows." ".$statement->table[0]." geladen");
		
		if($this->affectedRowsOnly) {
			$this->affectedRowsOnly = false;
			
			if(count($statement->group) === 0){
				$statement->fields = $tempFields;
				
				$t = $q->fetch_object();
				return $t->anzahlTotal;
			}
			
			return $this->c->affected_rows;
		}

		$object = $statement->table[0];
		if($statement->className != "") $object = $statement->className;

		$fields = null;
		while($t = $q->fetch_assoc()){
			$t = array_map("stripslashes",$t);
			if(count($this->parsers) > 0) 
				foreach($this->parsers AS $key => $value)
					if(isset($t[$key])) #eval("\$t[\$key] = ".$value."(\"".$t[$key]."\",\"load\");");
						$t[$key] = $this->invokeStaticMethod($value, array($t[$key], "load"));
			
			if($fields == null) $fields = PMReflector::getAttributesArray($statement->AttributesClassName);
			$newAttributes = $AS->newWithValues($fields,$t);
			
			$newCOfClass = new $object($t[$statement->table[0]."ID"]);
			$newCOfClass->setA($newAttributes);
			$collector[] = $newCOfClass;
		}
		
		return $collector;
		
	}

	function makeNewLines($table, $columns, $values){
		$query = "INSERT INTO `$table` (`".implode("`, `", $columns)."`) VALUES ";
		foreach ($values AS $k => $r){
			$query .= ($k != 0 ? ", " : "")."(";
			
			foreach($r AS $kc => $c)
				$query .= ($kc != 0 ? ", " : "")."'".$this->cWrite->real_escape_string($c)."'";
			
			$query .= ")";
		}
		
		$this->cWrite->query($query);
		
		if($this->cWrite->error)
			echo $this->cWrite->error;
		
		if($this->cWrite->error AND PHYNX_USE_SYSLOG)
			syslog(LOG_ERR, "MySQL: ".$this->cWrite->error."(".$this->cWrite->errno.") in $query");
	}
	
	public static $useAsNextID = null;
	function makeNewLine2($table, $A) {
		$fields = PMReflector::getAttributesArray($A);

		#if(PHYNX_MYSQL_STRICT)
		#	$this->fixTypes($table, $A);

	    $values = "NULL";
		if(self::$useAsNextID != null){
			$values = self::$useAsNextID;
			self::$useAsNextID = null;
		}
		
	    $sets = "`".$table."ID`";
		for($i = 0;$i < count($fields);$i++){
			if($fields[$i] == $table."ID") continue;

			#if(is_numeric($A->$fields[$i])) $values .= ", ".$A->$fields[$i]."\n";
			#else
			$cf = $fields[$i];
			$values .= ", '".$this->cWrite->real_escape_string($A->$cf)."'\n";

			$sets .= ",\n`".$cf."`";
		}
	    $sql = "INSERT INTO\n $table\n ($sets) VALUES ($values)";
		$_SESSION["messages"]->addMessage("executing MySQL: $sql");
		Timer::now("sql start", __FILE__, __LINE__);
	    $this->cWrite->query($sql);
		Timer::now("sql end", __FILE__, __LINE__);
		DBStorage::$queryCounter++;
	
		if($this->cWrite->error AND $this->cWrite->errno == 1054) {
			preg_match("/[a-zA-Z0-9 ]*\'([a-zA-Z0-9\.]*)\'[a-zA-Z ]*\'([a-zA-Z ]*)\'.*/", $this->cWrite->error, $regs);
			throw new FieldDoesNotExistException($regs[1],$regs[2]);
		}
		if($this->cWrite->error AND $this->cWrite->errno == 1062)
			throw new DuplicateEntryException($this->cWrite->error);
		
		if($this->cWrite->error)
			throw new StorageException($this->cWrite->error);
		
		if($this->cWrite->error AND PHYNX_USE_SYSLOG)
			syslog(LOG_ERR, "MySQL: ".$this->cWrite->error."(".$this->cWrite->errno.") in $sql");
		
	    return $this->cWrite->insert_id;
	}
	
	function deleteSingle($table, $keyName, $id){
		$sql = "DELETE FROM $table WHERE $keyName = '$id'";
		$this->cWrite->query($sql);
		DBStorage::$queryCounter++;
		$_SESSION["messages"]->addMessage("executing MySQL: $sql");
		
		if($this->cWrite->error AND PHYNX_USE_SYSLOG)
			syslog(LOG_ERR, "MySQL: ".$this->cWrite->error."(".$this->cWrite->errno.") in $sql");
	}
	
	/*private function mysql2Object($type){
		$k = strpos($type, "(");
		if($k !== false) $type = substr($type, 0, $k);

		$values = array();
		$values["int"] = "I";
		$values["varchar"] = "S";
		$values["text"] = "S";
		$values["decimal"] = "D";
		$values["tinyint"] = "B";
		
		return isset($values[$type]) ? $values[$type] : "S";
	}*/
	
	private function invokeStaticMethod($method, $parameters){
		$e = explode("::", $method);
		$R = new ReflectionMethod($e[0], $e[1]);
		if(!is_array($parameters))
			$parameters = array($parameters);

		return $R->invokeArgs(null, $parameters);
	}
}

?>