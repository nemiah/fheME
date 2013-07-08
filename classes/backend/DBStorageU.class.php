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
 *  2007 - 2013, Rainer Furtmeier - Rainer@Furtmeier.IT
 */
class DBStorageU {
	protected $instance;
	protected $connection;
	private $parsers;
	protected $affectedRowsOnly = false;
	
	public static $globalConnection = array();
	
	function __construct(){
		if(!isset(self::$globalConnection[get_class($this)]))
			self::$globalConnection[get_class($this)] = null;
		
		if(self::$globalConnection[get_class($this)] == null)
			$this->renewConnection();
		else
			$this->connection = self::$globalConnection[get_class($this)];
			
		/*
		$this->connection = mysql_pconnect($_SESSION["DBData"]["host"],$_SESSION["DBData"]["user"],$_SESSION["DBData"]["password"]);# or die ("MySQL-DB nicht erreichbar");
		if(mysql_error() AND (mysql_errno() == 1045 OR mysql_errno() == 2002 OR mysql_errno() == 2003 OR mysql_errno() == 2005)) throw new NoDBUserDataException();
		echo mysql_error();
		mysql_select_db($_SESSION["DBData"]["datab"], $this->connection);# or die ("Datenbank nicht gefunden");
		#mysql_set_charset("utf8");
		if(mysql_error() AND (mysql_errno() == 1049 OR mysql_errno() == 1044)) throw new DatabaseNotFoundException();*/
	}
	
	public function setGetAffectedRowsOnly($bool){
		$this->affectedRowsOnly = $bool;
	}
	
	
	public function renewConnection(){
		$this->connection = @mysql_pconnect($_SESSION["DBData"]["host"],$_SESSION["DBData"]["user"],$_SESSION["DBData"]["password"]);# or die ("MySQL-DB nicht erreichbar");
		if(mysql_error() AND (mysql_errno() == 1045 OR mysql_errno() == 2002 OR mysql_errno() == 2003 OR mysql_errno() == 2005)) throw new NoDBUserDataException();
		if(mysql_error() AND mysql_errno() == 1049) throw new DatabaseNotFoundException();
		#echo mysql_error();
		@mysql_select_db($_SESSION["DBData"]["datab"], $this->connection);
		if(mysql_error() AND (mysql_errno() == 1049 OR mysql_errno() == 1044)) throw new DatabaseNotFoundException();
		
		mysql_query("SET SESSION sql_mode = ''");
		mysql_set_charset("utf8");
		
		self::$globalConnection[get_class($this)] = $this->connection;
	}
	/*
	public function renewConnection(){
		#if($this->connection) mysql_close($this->connection);
		$this->connection = @mysql_pconnect($_SESSION["DBData"]["host"],$_SESSION["DBData"]["user"],$_SESSION["DBData"]["password"]);# or die ("MySQL-DB nicht erreichbar");
		if(mysql_error() AND (mysql_errno() == 1045 OR mysql_errno() == 2002)) throw new NoDBUserDataException();
		if(mysql_error() AND mysql_errno() == 1049) throw new DatabaseNotFoundException();
		#echo mysql_error();
		@mysql_select_db($_SESSION["DBData"]["datab"], $this->connection);# or die ("Datenbank nicht gefunden");
	}*/

	public function getConnection(){
		return $this->connection;
	}
	
	public function setParser($p){
		$this->parsers = $p;
	}
	
	static function checkForTable($name){
		$sql = "SHOW TABLES FROM `".$_SESSION["DBData"]["datab"]."`";
		@$result = mysql_query($sql);
		$_SESSION["messages"]->addMessage("executing MySQL: $sql");
		if($result) while ($row = mysql_fetch_row($result))
			if(strtolower($row[0]) == strtolower($name)) return true;
		
		if($result) mysql_free_result($result);
		
		return false;
	}
	
	function checkMyTable($CIA){
		if(strpos($CIA->MySQL, "INSERT INTO") !== false) return;
		$CIAAlt = clone $CIA;
		
		$view = false;
		if(strpos($CIA->MySQL, "CREATE VIEW") !== false) $view = true;
		
		if(!$view) preg_match("/CREATE TABLE `([a-zA-Z0-9]*)`/",$CIA->MySQL,$regs);
		else preg_match("/CREATE VIEW `([a-zA-Z0-9]*)`/",$CIA->MySQL,$regs);

		$rand = "RANDOM".rand(10000,100000);
		while($this->checkForTable($regs[1].$rand))
			$rand = "RANDOM".rand(10000,100000);
		
		if(!$view) $CIA->MySQL = str_replace("CREATE TABLE `$regs[1]`","CREATE TABLE `".$regs[1].$rand."`",$CIA->MySQL);
		else $CIA->MySQL = str_replace("CREATE VIEW `$regs[1]`","CREATE VIEW `".$regs[1].$rand."`",$CIA->MySQL);
		
		$this->createTable($CIA);
		$newTable = array();
		$sql = "SHOW FIELDS FROM ".$regs[1].$rand;
		$result = mysql_query($sql);
		if(mysql_error()) echo $sql;
		$_SESSION["messages"]->addMessage("executing MySQL: $sql");
		if($result) while ($row = mysql_fetch_row($result))
			$newTable[] = $row[0];
		if($result) mysql_free_result($result);
		if(!$view) $this->dropTable($regs[1].$rand);
		else $this->dropView($regs[1].$rand);
		
		$oldTable = array();
		$sql = "SHOW FIELDS FROM ".$regs[1];
		$result = mysql_query($sql);
		if(mysql_error() AND mysql_errno() == 1146) throw new TableDoesNotExistException();

		$_SESSION["messages"]->addMessage("executing MySQL: $sql");
		if($result) while ($row = mysql_fetch_row($result))
			$oldTable[] = $row[0];
		if($result) mysql_free_result($result);
		
		#$unterschied1 = array_diff($oldTable,$newTable);
		$unterschied2 = array_diff($newTable,$oldTable);
		
		mysql_query("ALTER TABLE `$regs[1]` COMMENT = '".$_SESSION["applications"]->getActiveApplication()."_".$_SESSION["applications"]->getRunningVersion().";'");
		
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
			$ex = explode(",\n",$newSQL);
			$newSQL = $ex[0];
			mysql_query("ALTER TABLE `$regs[1]` ADD $newSQL");
			echo mysql_error();
			$_SESSION["messages"]->addMessage("Added field $value in table $regs[1]");
			
			$changes++;
		}
		
		return $changes;
	}
	
	public function alterTable($CIA){
		if(strpos($CIA->MySQL, "ALTER TABLE") != 0) return;
		
		$_SESSION["messages"]->addMessage("executing MySQL: $CIA->MySQL");
		mysql_query($CIA->MySQL);
	}
	
	private function dropTable($name){
		$sql = "DROP TABLE `".$name."`";
		$_SESSION["messages"]->addMessage("executing MySQL: $sql");
		mysql_query($sql);
	}
	
	private function dropView($name){
		$sql = "DROP VIEW `".$name."`";
		$_SESSION["messages"]->addMessage("executing MySQL: $sql");
		mysql_query($sql);
	}
	
	function loadSingle($table, $keyName, $id, $fields) {
		throw new FunctionDeprecatedException("DBStorage", "loadSingle");
	}
	
	function loadSingle2($table, $id, $typsicher = false) {
		$sql = "SELECT * FROM $table WHERE ".$table."ID = '$id'";
		$q = mysql_query($sql);
		$_SESSION["messages"]->addMessage("executing MySQL: $sql");
		$t = mysql_fetch_object($q);
		if(mysql_error() AND mysql_errno() == 1146) throw new TableDoesNotExistException();
		if(mysql_error() AND (mysql_errno() == 1045 OR mysql_errno() == 2002)) throw new NoDBUserDataException();
		if(mysql_error() AND mysql_errno() == 1054) {
			preg_match("/[a-zA-Z0-9 ]*\'([a-zA-Z0-9\.]*)\'[a-zA-Z ]*\'([a-zA-Z ]*)\'.*/", mysql_error(), $regs);
			throw new FieldDoesNotExistException($regs[1],$regs[2]);
		}
		if(mysql_error() AND mysql_errno() == 1046) throw new DatabaseNotSelectedException();
		echo mysql_error();

		$fields = PMReflector::getAttributesArrayAnyObject($t);
		
		/*if($typsicher){
			$types = array();
			$qc = mysql_query("SHOW COLUMNS FROM $table");
			while($tc = mysql_fetch_object($qc))
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
	
	static function createTable($CIA){
		$view = false;
		if(strpos($CIA->MySQL, "CREATE VIEW") !== false) $view = true;
		
		if(!$view) preg_match("/CREATE TABLE `([a-zA-Z0-9]*)`/",$CIA->MySQL,$regs);
		else preg_match("/CREATE VIEW `([a-zA-Z0-9]*)`/",$CIA->MySQL,$regs);
		
		$_SESSION["messages"]->addMessage("executing MySQL: $CIA->MySQL");
		mysql_query($CIA->MySQL);
		if(mysql_error() AND mysql_errno() == 1046) throw new DatabaseNotSelectedException();
		
		if(strpos($CIA->MySQL, "INSERT INTO") === false){
			$sql = "ALTER TABLE `$regs[1]` COMMENT = '".$_SESSION["applications"]->getActiveApplication()."_".$_SESSION["applications"]->getRunningVersion().";'";
			$_SESSION["messages"]->addMessage("executing MySQL: $sql");
			mysql_query($sql);
		}
		
		#if(mysql_error()) echo mysql_error()."<br /><pre style=\"font-size:8px;\">".($CIA->MySQL != "" ? $CIA->MySQL : "leeres MySQL-Statement!")."</pre>";
		return null;
	}

	function saveSingle($table,$keyName,$id,$fields,Attributes $A) {
		throw new FunctionDeprecatedException("DBStorage", "saveSingle");
	}

	function saveSingle2($table, $id, $A) {
		$fields = PMReflector::getAttributesArray($A);
	    $sql = "UPDATE $table SET";
	    
		for($i = 0;$i < count($fields);$i++)
			$sql .= ($i > 0 ? "," : "")." ".$fields[$i]."='".addslashes($A->$fields[$i])."'";
			
		$sql .= " WHERE ".$table."ID = '$id'";
		$_SESSION["messages"]->addMessage("executing MySQL: $sql");
		mysql_query($sql);
		if(mysql_error() AND mysql_errno() == 1062) throw new DuplicateEntryException(mysql_error());
		echo mysql_error();
	}

	function getTableColumns($forWhat){
		$result = mysql_query("SHOW COLUMNS FROM $forWhat");
		
		$a = new stdClass();
		while ($row = mysql_fetch_assoc($result))
			$a->$row["Field"] = "";
		
		return $a;
	}
	
	private function fixUtf8($value){
		return $value;
		
		$value = str_replace("Ã„", "Ä", $value);
		$value = str_replace("Ã–", "Ö", $value);
		$value = str_replace("Ãœ", "Ü", $value);
		
		$value = str_replace("Ã¤", "ä", $value);
		$value = str_replace("Ã¶", "ö", $value);
		$value = str_replace("Ã¼", "ü", $value);
		
		$value = str_replace("ÃŸ", "ß", $value);
		return $value;
	}
	
	function loadMultipleV4(SelectStatement $statement, $typsicher = false){
		$where = "(";
		$lastKey = "";
		$closeBrackets = "";
		foreach($statement->whereFields as $key => $value){
			$addOpenBracket = false;
			if($where != "(" AND $statement->whereBracketGroup[$lastKey] != $statement->whereBracketGroup[$key]){
				$addOpenBracket = true;
				$where .= ")";
			}
			$currentWhereValue = $statement->whereValues[$key];
			if($currentWhereValue != "NULL" 
				AND $currentWhereValue != "NOT NULL"
				AND substr($currentWhereValue, 0, 3) != "t1.") 
				$currentWhereValue = "'".mysql_real_escape_string($currentWhereValue)."'";
				
			$where .= ($where != "(" ? " ".$statement->whereLogOp[$key]." ".($addOpenBracket ? "(" : "") : "")./*(in_array($statement->whereFields[$key], $nJAs) ? "t1." : "").*/"".$statement->whereFields[$key]." ".$statement->whereOperators[$key]." ".$currentWhereValue."";
			$lastKey = $key;
		}
		$where .= ")";
		$order = "";
		foreach($statement->order as $key => $value)
			$order .=  ($order != "" ? ", ": "").$statement->order[$key]." ".$statement->orderAscDesc[$key];
		
		
		$tables = array();
		
		for($i=0;$i<count($statement->joinTables);$i++){
			if(!isset($tables[$statement->joinTables[$i]])) $tables[$statement->joinTables[$i]] = array();
			$tables[$statement->joinTables[$i]][] = array($statement->joinConditions[$i][0],$statement->joinConditionOperators[$i],$statement->joinConditions[$i][1]);
		}
		
		$t = 2;
		$joinAdd = "";
		foreach($tables as $table => $conditions){
			$ons = "";
			for($i=0;$i<count($conditions);$i++){
				if($i == 0) $ons .= ((!strpos($conditions[$i][0],".") AND $conditions[$i][0]{0} != " ") ? "t1." : "")."".$conditions[$i][0]." ".$conditions[$i][1]." t$t.".$conditions[$i][2];
				else {
					if($conditions[$i][2] != "NOT NULL" AND $conditions[$i][2] != "NULL") $conditions[$i][2] = "'".$conditions[$i][2]."'";
					$ons .= " AND t$t.".$conditions[$i][0]." ".$conditions[$i][1]." ".$conditions[$i][2]."";
				}
			}
			
			$joinAdd .= "\n LEFT JOIN ".$table." t$t ON($ons)";

			$t++;
		}

		$sql = "SELECT\n ".implode(",\n ",$statement->fields)."\n FROM `".$statement->table[0]."` t1$joinAdd ".($where != "()" ? "\n WHERE $where" : "").(count($statement->group) > 0 ? "\n GROUP BY ".implode(", ",$statement->group) : "").($order != "" ? "\n ORDER BY $order" : "").(count($statement->limit) > 0 ? "\n LIMIT ".implode(", ",$statement->limit) : "");
		
		$collector = array();
		
		if($statement->table[0] != "Userdata") $_SESSION["messages"]->startMessage("executing MySQL: $sql");
		#echo nl2br($sql)."<br /><br />";

		$q = mysql_query($sql);
		
		if(mysql_error() AND (mysql_errno() == 1045 OR mysql_errno() == 2002)) throw new NoDBUserDataException();
		if(mysql_error() AND mysql_errno() == 1146) throw new TableDoesNotExistException();
		if(mysql_error() AND mysql_errno() == 1046) throw new DatabaseNotSelectedException();
		if(mysql_error() AND mysql_errno() == 1054) {
			preg_match("/[a-zA-Z0-9 ]*\'([a-zA-Z0-9\.]*)\'[a-zA-Z ]*\'([a-zA-Z ]*)\'.*/", mysql_error(), $regs);
			throw new FieldDoesNotExistException($regs[1],$regs[2]);
		}
		if(mysql_error()) echo "MySQL-Fehler: ".mysql_error()."<br />Fehlernummer: ".mysql_errno();
			
		if($statement->table[0] != "Userdata") $_SESSION["messages"]->endMessage(": ".mysql_affected_rows()." ".$statement->table[0]." geladen");
		
		if($this->affectedRowsOnly) {
			$this->affectedRowsOnly = false;
			return mysql_affected_rows();
		}

		/*if($typsicher){
			$types = array();
			$qc = mysql_query("SHOW COLUMNS FROM ".$statement->table[0]);
			while($tc = mysql_fetch_object($qc))
				$types[$tc->Field] = $this->mysql2Object($tc->Type);
				
			foreach($statement->joinTables AS $kc => $vc){
				$qc = mysql_query("SHOW COLUMNS FROM ".$vc);
				while($tc = mysql_fetch_object($qc))
					$types[$tc->Field] = $this->mysql2Object($tc->Type);
			}
			
			foreach($statement->dataTypes AS $kc => $vc)
				$types = array_merge($types, $vc);
		}*/

		$fields = null;
		while(@$t = mysql_fetch_object($q)){
			$A = new Attributes();
			
			if($fields == null) $fields = PMReflector::getAttributesArrayAnyObject($t);
			
			foreach($fields AS $key => $value){
				$A->$value = $this->fixUtf8(stripslashes($t->$value));
				
				/*if($typsicher){
					if(isset($types[$value])) $typObj = $types[$value];
					else throw new DataTypeNotDefinedException($value);
					
					$A->$value = new $typObj($A->$value);
					#echo "<pre>";
					#print_r($A);
					#echo "</pre>";
				}*/
			}
			
			if(count($this->parsers) > 0) foreach($this->parsers as $key => $value)
				if(isset($A->$key)) eval("\$A->\$key = ".$value."(\"".$A->$key."\",\"load\");");
			
			$oID = $statement->table[0]."ID";
			
			$cName = $statement->table[0];
			if(isset($_SESSION["CurrentAppPlugins"]) AND $_SESSION["CurrentAppPlugins"]->isPluginGeneric($cName))
				$cName = "Generic";

			$newCOfClass = new $cName($t->$oID, $statement->table[0]);
			$newCOfClass->setA($A);
			$collector[] = $newCOfClass;
		}
		
		return $collector;
	}
	
	/*function loadMultipleT(SelectStatement $statement){
		return $this->loadMultipleV4($statement, true);
	}*/
	
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
				$currentWhereValue = "'".mysql_real_escape_string($currentWhereValue)."'";
			
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
		
		$sql = "SELECT\n ".implode(",\n ",$statement->fields)."\n FROM `".$statement->table[0]."` t1$joinAdd ".($where != "()" ? "\n WHERE $where" : "").(count($statement->group) > 0 ? "\n GROUP BY ".implode(", ",$statement->group) : "").($order != "" ? "\n ORDER BY $order" : "").(count($statement->limit) > 0 ? "\n LIMIT ".implode(", ",$statement->limit) : "");

		$collector = array();

		$AS = new $statement->AttributesClassName();

		if($statement->table[0] != "Userdata") $_SESSION["messages"]->startMessage("executing MySQL: $sql");
		#echo nl2br($sql)."<br /><br />";
		$q = mysql_query($sql);

		if(mysql_error() AND (mysql_errno() == 1045 OR mysql_errno() == 2002)) throw new NoDBUserDataException();
		if(mysql_error() AND mysql_errno() == 1146) throw new TableDoesNotExistException();
		if(mysql_error() AND mysql_errno() == 1046) throw new DatabaseNotSelectedException();
		if(mysql_error() AND mysql_errno() == 1054) {
			preg_match("/[a-zA-Z0-9 ]*\'([a-zA-Z0-9\.]*)\'[a-zA-Z ]*\'([a-zA-Z ]*)\'.*/", mysql_error(), $regs);
			#print_r($regs);
			throw new FieldDoesNotExistException($regs[1],$regs[2]);
		}
		if(mysql_error()) echo "MySQL-Fehler: ".mysql_error()."<br />Fehlernummer: ".mysql_errno();
			
		if($statement->table[0] != "Userdata") $_SESSION["messages"]->endMessage(": ".mysql_affected_rows()." ".$statement->table[0]." geladen");
		
		if($this->affectedRowsOnly) {
			$this->affectedRowsOnly = false;
			return mysql_affected_rows();
		}
		$fields = null;
		while(@$t = mysql_fetch_assoc($q)){
			$t = array_map("stripslashes",$t);
			if(count($this->parsers) > 0) foreach($this->parsers as $key => $value)
				if(isset($t[$key])) eval("\$t[\$key] = ".$value."(\"".$t[$key]."\",\"load\");");
			
			if($fields == null) $fields = PMReflector::getAttributesArray($statement->AttributesClassName);
			$newAttributes = $AS->newWithValues($fields,$t);
			
			$newCOfClass = new $statement->table[0]($t[$statement->table[0]."ID"]);
			$newCOfClass->setA($newAttributes);
			$collector[] = $newCOfClass;
		}
		
		return $collector;
		
	}
	
	function makeNewLine2($table, $A) {
		$fields = PMReflector::getAttributesArray($A);
		
	    $values = "''";
	    $sets = "`".$table."ID`";
		for($i = 0;$i < count($fields);$i++){
			if($fields[$i] == $table."ID") continue;
		    $values .= ", '".mysql_real_escape_string($A->$fields[$i])."'\n";
			$sets .= ",\n`".$fields[$i]."`";
		}
	    $sql = "INSERT INTO\n $table\n ($sets) VALUES ($values)";
		$_SESSION["messages"]->addMessage("executing MySQL: $sql");
	    mysql_query($sql);
	
		if(mysql_error() AND mysql_errno() == 1054) {
			preg_match("/[a-zA-Z0-9 ]*\'([a-zA-Z0-9\.]*)\'[a-zA-Z ]*\'([a-zA-Z ]*)\'.*/", $this->c->error, $regs);
			throw new FieldDoesNotExistException($regs[1],$regs[2]);
		}
		if(mysql_error() AND mysql_errno() == 1062) throw new DuplicateEntryException($this->c->error);
		
		if(mysql_error()) throw new StorageException();
		
	    return mysql_insert_id();
	}
	
	function makeNewLine($table, $keyName, $fields, Attributes $A) {
		throw new FunctionDeprecatedException("DBStorage", "makeNewLine");
	}
	
	function deleteSingle($table,$keyName,$id){
		$sql = "DELETE FROM $table WHERE $keyName = '$id'";
		mysql_query($sql);
		$_SESSION["messages"]->addMessage("executing MySQL: $sql");
	}
	/*
	private function mysql2Object($type){
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
}

?>
