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

function mssql_real_escape_string($s) {
	if(get_magic_quotes_gpc())
		$s = stripslashes($s);

	$s = str_replace("'","''",$s);
	
	return $s;
}

function mssql_insert_id() {
	$id = false;
	$res = mssql_query('SELECT @@identity AS id');
	if($row = mssql_fetch_row($res))
		$id = trim($row[0]);
	
	mssql_free_result($res);
	return $id;
}

function mssql_errno(){
	$errorCode = 0;
	$res = mssql_query('SELECT @@ERROR as ErrorCode');
	if ($row = mssql_fetch_row($res))
		$errorCode = trim($row[0]) * 1;

	mssql_free_result($res);
	return $errorCode;
}

class MSSQLStorage {
	protected $instance;
	protected $connection;
	private $parsers;
	protected $affectedRowsOnly = false;
	
	function __construct(){
		$this->renewConnection();
	}
	
	public function setGetAffectedRowsOnly($bool){
		$this->affectedRowsOnly = $bool;
	}
	
	public function renewConnection(){
		$this->connection = mssql_pconnect($_SESSION["DBData"]["host"],$_SESSION["DBData"]["user"],$_SESSION["DBData"]["password"]);
		if(!$this->connection) throw new NoDBUserDataException();
		if(!mssql_select_db($_SESSION["DBData"]["datab"], $this->connection))
			 throw new DatabaseNotFoundException();
	}

	public function getConnection(){
		return $this->connection;
	}
	
	public function setParser($p){
		$this->parsers = $p;
	}
	
	static function checkForTable($name){
		$sql = "SELECT * FROM INFORMATION_SCHEMA.TABLES";
		@$result = mssql_query($sql);
		$_SESSION["messages"]->addMessage("executing MSSQL: $sql");
		if($result) while ($row = mssql_fetch_row($result))
			if(strtolower($row[2]) == strtolower($name)) return true;
		
		if($result) mssql_free_result($result);
		
		return false;
	}
	
	function checkMyTable($CIA){
		if(strpos($CIA->MSSQL, "INSERT INTO") !== false) return;
		$CIAAlt = clone $CIA;
		
		$view = false;
		if(strpos($CIA->MSSQL, "CREATE VIEW") !== false) $view = true;
		
		if(!$view) preg_match("/CREATE TABLE \[([a-zA-Z0-9]*)\]/",$CIA->MSSQL,$regs);
		else preg_match("/CREATE VIEW \[([a-zA-Z0-9]*)\]/",$CIA->MSSQL,$regs);

		$rand = "RANDOM".rand(10000,100000);
		while($this->checkForTable($regs[1].$rand))
			$rand = "RANDOM".rand(10000,100000);
		
		if(!$view) $CIA->MSSQL = str_replace("CREATE TABLE [$regs[1]]","CREATE TABLE [".$regs[1].$rand."]",$CIA->MSSQL);
		else $CIA->MSSQL = str_replace("CREATE VIEW [$regs[1]]","CREATE VIEW [".$regs[1].$rand."]",$CIA->MSSQL);


		$this->createTable($CIA);
		$newTable = PMReflector::getAttributesArrayAnyObject($this->getTableColumns($regs[1].$rand));

		if(!$view) $this->dropTable($regs[1].$rand);
		else $this->dropView($regs[1].$rand);


		$oldTable = PMReflector::getAttributesArrayAnyObject($this->getTableColumns($regs[1]));

		$unterschied2 = array_diff($newTable,$oldTable);
		
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
			$newSQL = strstr($CIA->MSSQL,"[$value]");
			$ex = explode(",\n",$newSQL);
			$newSQL = $ex[0];
			mssql_query("ALTER TABLE [$regs[1]] ADD $newSQL");
			#echo mysql_error();
			$_SESSION["messages"]->addMessage("Added field $value in table $regs[1]");
			
			$changes++;
		}
		
		return $changes;
	}
	
	public function alterTable($CIA){
		if(strpos($CIA->MSSQL, "ALTER TABLE") != 0) return;
		
		$_SESSION["messages"]->addMessage("executing MSSQL: $CIA->MSSQL");
		mssql_query($CIA->MSSQL);
	}
	
	private function dropTable($name){
		$sql = "DROP TABLE [".$name."]";
		$_SESSION["messages"]->addMessage("executing MSSQL: $sql");
		mssql_query($sql);
	}
	
	private function dropView($name){
		$sql = "DROP VIEW [".$name."]";
		$_SESSION["messages"]->addMessage("executing MSSQL: $sql");
		mssql_query($sql);
	}
	
	function loadSingle($table, $keyName, $id, $fields) {
		throw new FunctionDeprecatedException("DBStorage", "loadSingle");
	}
	
	function loadSingle2($table, $id, $typsicher = false) {
		$sql = "SELECT * FROM [$table] WHERE ".$table."ID = '$id'";
		$q = mssql_query($sql);
		$_SESSION["messages"]->addMessage("executing MSSQL: $sql");
		$t = mssql_fetch_object($q);

		if(mssql_get_last_message() AND mssql_errno() == 208) throw new TableDoesNotExistException();
		#if(mysql_error() AND (mysql_errno() == 1045 OR mysql_errno() == 2002)) throw new NoDBUserDataException();
		#if(mysql_error() AND mysql_errno() == 1054) {
		#	preg_match("/[a-zA-Z0-9 ]*\'([a-zA-Z0-9\.]*)\'[a-zA-Z ]*\'([a-zA-Z ]*)\'.*/", mysql_error(), $regs);
		#	throw new FieldDoesNotExistException($regs[1],$regs[2]);
		#}
		#if(mysql_error() AND mysql_errno() == 1046) throw new DatabaseNotSelectedException();
		#echo mysql_error();

		$fields = PMReflector::getAttributesArrayAnyObject($t);
		
		if($typsicher){
			$types = array();
			$qc = mssql_query("SHOW COLUMNS FROM $table");
			while($tc = mssql_fetch_object($qc))
				$types[$tc->Field] = $this->mssql2Object($tc->Type);
		}
		
		foreach($fields AS $key => $value){
			$t->$value = $this->fixUtf8(stripslashes($t->$value));
						
			if($typsicher){
				if(isset($types[$value])) $typObj = $types[$value];
				else throw new DataTypeNotDefinedException($value);
				
				$t->$value = new $typObj($t->$value);
			}
		}
		
		return $t;
	}
	
	function loadSingleT($table, $id) {
		return $this->loadSingle2($table, $id, true);
	}
	
	function createTable($CIA){
		$view = false;
		if(strpos($CIA->MSSQL, "CREATE VIEW") !== false) $view = true;
		
		if(!$view) preg_match("/CREATE TABLE \[([a-zA-Z0-9]*)\]/",$CIA->MSSQL,$regs);
		else preg_match("/CREATE VIEW \[([a-zA-Z0-9]*)\]/",$CIA->MSSQL,$regs);

		if(strpos($CIA->MSSQL, "INSERT INTO ") !== false){
			preg_match("/INSERT INTO \[([a-zA-Z0-9]*)\]/",$CIA->MSSQL,$regs);
			mssql_query("SET IDENTITY_INSERT [$regs[1]] ON;");
		}
		$_SESSION["messages"]->addMessage("executing MSSQL: $CIA->MSSQL");

		mssql_query($CIA->MSSQL);

		if(strpos($CIA->MSSQL, "INSERT INTO ") !== false){
			mssql_query("SET IDENTITY_INSERT [$regs[1]] OFF;");
		}
		#if(mysql_error() AND mysql_errno() == 1046) throw new DatabaseNotSelectedException();
		
		#if(strpos($CIA->MSSQL, "INSERT INTO") === false){
			#$sql = "ALTER TABLE `$regs[1]` COMMENT = '".$_SESSION["applications"]->getActiveApplication()."_".$_SESSION["applications"]->getRunningVersion().";'";
			#$_SESSION["messages"]->addMessage("executing MSSQL: $sql");
			#mssql_query($sql);
		#}
		
		#if(mysql_error()) echo mysql_error()."<br /><pre style=\"font-size:8px;\">".($CIA->MSSQL != "" ? $CIA->MSSQL : "leeres MySQL-Statement!")."</pre>";


		$data = new stdClass();
		$data->error = mssql_get_last_message();
		$data->affected_rows = mssql_rows_affected($this->connection);

		return $data;
	}

	function saveSingle($table,$keyName,$id,$fields,Attributes $A) {
		throw new FunctionDeprecatedException("DBStorage", "saveSingle");
	}

	function saveSingle2($table, $id, $A) {
		$fields = PMReflector::getAttributesArray($A);
	    $sql = "UPDATE [$table] SET";
	    
		for($i = 0;$i < count($fields);$i++)
			$sql .= ($i > 0 ? "," : "")." [".$fields[$i]."]='".addslashes($A->$fields[$i])."'";
			
		$sql .= " WHERE [".$table."ID] = '$id'";
		$_SESSION["messages"]->addMessage("executing MSSQL: $sql");
		mssql_query($sql);
		if(mysql_error() AND mysql_errno() == 1062) throw new DuplicateEntryException(mysql_error());
		echo mysql_error();
	}

	function getTableColumns($forWhat){
		$result = mssql_query("SELECT * FROM INFORMATION_SCHEMA.Columns WHERE TABLE_NAME = '$forWhat'");
		
		$a = new stdClass();
		while ($row = mssql_fetch_assoc($result))
			$a->$row["COLUMN_NAME"] = "";
		
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

		#echo array_search("t1.".$statement->table[0], $statement->fields);
		unset($statement->fields[array_search("t1.".$statement->table[0]."ID", $statement->fields)]);
		if(array_search("t1.".$statement->table[0]."ID AS currentObjectID", $statement->fields) === false)
			$statement->fields[] = "t1.".$statement->table[0]."ID AS currentObjectID";
		#print_r($statement->fields);
		#return ;
		if(count($statement->order) == 0){
			$statement->order[] = "currentObjectID";
			$statement->orderAscDesc[] = "ASC";
		}
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
				AND $currentWhereValue != "NOT NULL") 
				$currentWhereValue = "'".mssql_real_escape_string($currentWhereValue)."'";
				
			$where .= ($where != "(" ? " ".$statement->whereLogOp[$key]." ".($addOpenBracket ? "(" : "") : "")./*(in_array($statement->whereFields[$key], $nJAs) ? "t1." : "").*/"[".$statement->whereFields[$key]."] ".$statement->whereOperators[$key]." ".$currentWhereValue."";
			$lastKey = $key;
		}
		$where .= ")";
		$order = "";
		foreach($statement->order as $key => $value)
			$order .=  ($order != "" ? ", ": "")."[".$statement->order[$key]."] ".$statement->orderAscDesc[$key];
		
		
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
			
			$joinAdd .= "\n LEFT JOIN [".$table."] t$t ON($ons)";

			$t++;
		}



		$sql = "WITH MSOrdered AS (\nSELECT\n (ROW_NUMBER() OVER(ORDER BY $order)) - 1 AS MSZeilennummer, ".implode(",\n ",$statement->fields)."\n FROM [".$statement->table[0]."] t1$joinAdd ".($where != "()" ? "\n WHERE $where" : "").(count($statement->group) > 0 ? "\n GROUP BY ".implode(", ",$statement->group) : "")."\n)\n";
		#.(count($statement->limit) > 0 ? "\n LIMIT ".implode(", ",$statement->limit) : "")

		$sql .= "SELECT * FROM MSOrdered";

		$limit = array();
		if(isset($statement->limit[0]))
			$limit = explode(",", $statement->limit[0]);

		if(count($limit) == 1)
			$sql .= " WHERE MSZeilennummer < ".$limit[0];

		if(count($limit) == 2)
			$sql .= " WHERE MSZeilennummer BETWEEN ".$limit[0]." AND ".$limit[1];

		$sql .= ($order != "" ? "\n ORDER BY $order" : "");

		$collector = array();
		
		#if($statement->table[0] != "Userdata")
			$_SESSION["messages"]->startMessage("executing MSSQL: $sql");
		#echo nl2br($sql)."<br /><br />";

		$q = mssql_query($sql);
		#echo mssql_get_last_message().": ".(mssql_errno() == 208)."<br />";

		#if(mysql_error() AND (mysql_errno() == 1045 OR mysql_errno() == 2002)) throw new NoDBUserDataException();
		if(mssql_get_last_message() AND mssql_errno() == 208) throw new TableDoesNotExistException();
		#if(mysql_error() AND mysql_errno() == 1046) throw new DatabaseNotSelectedException();
		#if(mysql_error() AND mysql_errno() == 1054) {
		#	preg_match("/[a-zA-Z0-9 ]*\'([a-zA-Z0-9\.]*)\'[a-zA-Z ]*\'([a-zA-Z ]*)\'.*/", mysql_error(), $regs);
		#	throw new FieldDoesNotExistException($regs[1],$regs[2]);
		#}
		#if(mysql_error()) echo "MySQL-Fehler: ".mysql_error()."<br />Fehlernummer: ".mysql_errno();
			
		if($statement->table[0] != "Userdata") $_SESSION["messages"]->endMessage(": ".mssql_rows_affected($this->connection)." ".$statement->table[0]." geladen");
		
		if($this->affectedRowsOnly) {
			$this->affectedRowsOnly = false;
			return mssql_rows_affected($this->connection);
		}

		if($typsicher){
			$types = array();
			$qc = mssql_query("SHOW COLUMNS FROM ".$statement->table[0]);
			while($tc = mssql_fetch_object($qc))
				$types[$tc->Field] = $this->mysql2Object($tc->Type);
				
			foreach($statement->joinTables AS $kc => $vc){
				$qc = mssql_query("SHOW COLUMNS FROM ".$vc);
				while($tc = mssql_fetch_object($qc))
					$types[$tc->Field] = $this->mysql2Object($tc->Type);
			}
			
			foreach($statement->dataTypes AS $kc => $vc)
				$types = array_merge($types, $vc);
		}

		$fields = null;
		while(@$t = mssql_fetch_object($q)){
			$A = new Attributes();
			
			if($fields == null) $fields = PMReflector::getAttributesArrayAnyObject($t);
			
			foreach($fields AS $key => $value){
				$A->$value = $this->fixUtf8(stripslashes($t->$value));
				
				if($typsicher){
					if(isset($types[$value])) $typObj = $types[$value];
					else throw new DataTypeNotDefinedException($value);
					
					$A->$value = new $typObj($A->$value);
					#echo "<pre>";
					#print_r($A);
					#echo "</pre>";
				}
			}
			
			if(count($this->parsers) > 0) foreach($this->parsers as $key => $value)
				if(isset($A->$key)) eval("\$A->\$key = ".$value."(\"".$A->$key."\",\"load\");");
			
			$oID = "currentObjectID";
			
			$cName = $statement->table[0];

			$newCOfClass = new $cName($t->$oID);
			$newCOfClass->setA($A);
			$collector[] = $newCOfClass;
		}
		
		return $collector;
	}
	
	function loadMultipleT(SelectStatement $statement){
		return $this->loadMultipleV4($statement, true);
	}
	
	
	function makeNewLine2($table, $A) {
		$fields = PMReflector::getAttributesArray($A);
		
	    $values = "";#"''";
	    $sets = "";#"[".$table."ID]";
		for($i = 0;$i < count($fields);$i++){
			if($fields[$i] == $table."ID") continue;
		    $values .= ($values != "" ? ", " : "")." '".mssql_real_escape_string($A->$fields[$i])."'\n";
			$sets .= ($sets != "" ? ", " : "")."\n[".$fields[$i]."]";
		}
	    $sql = "INSERT INTO\n [$table]\n ($sets) VALUES ($values)";
		$_SESSION["messages"]->addMessage("executing MSSQL: $sql");
	    mssql_query($sql);
	
		if(mysql_error() AND mysql_errno() == 1054) {
			preg_match("/[a-zA-Z0-9 ]*\'([a-zA-Z0-9\.]*)\'[a-zA-Z ]*\'([a-zA-Z ]*)\'.*/", $this->c->error, $regs);
			throw new FieldDoesNotExistException($regs[1],$regs[2]);
		}
		if(mysql_error() AND mysql_errno() == 1062) throw new DuplicateEntryException($this->c->error);
		
		if(mysql_error()) throw new StorageException();
		
	    return mssql_insert_id();
	}
	
	function deleteSingle($table,$keyName,$id){
		$sql = "DELETE FROM [$table] WHERE [$keyName] = '$id'";
		mssql_query($sql);
		$_SESSION["messages"]->addMessage("executing MSSQL: $sql");
	}
	
	private function mssql2Object($type){
		$k = strpos($type, "(");
		if($k !== false) $type = substr($type, 0, $k);

		$values = array();
		$values["int"] = "I";
		$values["varchar"] = "S";
		$values["text"] = "S";
		$values["decimal"] = "D";
		$values["tinyint"] = "B";
		
		return isset($values[$type]) ? $values[$type] : "S";
	}
}

?>
