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
 *  2007 - 2014, Rainer Furtmeier - Rainer@Furtmeier.IT
 */

class DBPDOStorage {
	private $parsers;
	protected $affectedRowsOnly = false;
	protected $c;
	public static $globalConnection = array();
	protected $data;
	public static $queryCounter = 0;

	function __construct(){
		if($this->data == null AND isset($_SESSION["DBPDOData"]))
			$this->data = $_SESSION["DBPDOData"];
		
		if(!isset(self::$globalConnection[get_class($this)]))
			self::$globalConnection[get_class($this)] = null;

		if(self::$globalConnection[get_class($this)] == null){
			if($this->data == null) throw new NoDBUserDataException();
			$this->renewConnection();
		} else $this->c = self::$globalConnection[get_class($this)];
		
	}

	/**
	 * @return PDO
	 */
	public function getConnection(){
		return $this->c;
	}
	
	
	public static function disconnect(){ //has to be static or new connection will be built in __construct()
		$status = self::$globalConnection[__CLASS__]->close();
		
		self::$globalConnection[__CLASS__] = null;

		return $status;
	}
	
	public function setGetAffectedRowsOnly($bool){
		$this->affectedRowsOnly = $bool;
	}
	
	public function renewConnection(){
		#pgsql
		$this->c = new PDO($this->data["driver"].':dbname='.$this->data["datab"].';host='.$this->data["host"], $this->data["user"], $this->data["password"]);
		#new mysqli($this->data["host"],$this->data["user"],$this->data["password"],$this->data["datab"]);
		#if(mysqli_connect_error() AND (mysqli_connect_errno() == 1045 OR mysqli_connect_errno() == 2002 OR mysqli_connect_errno() == 2003 OR mysqli_connect_errno() == 2005)) throw new NoDBUserDataException(mysqli_connect_errno().":".mysqli_connect_error());
		##if(mysqli_connect_error() AND mysqli_connect_errno() == 1049 OR mysqli_connect_errno() == 1044) throw new DatabaseNotFoundException();
		#echo $this->c->error;
		#$this->c->set_charset("utf8");
		#$this->c->query("SET SESSION sql_mode='';");
		
		self::$globalConnection[get_class($this)] = $this->c;
	}
	
	public function setParser($p){
		$this->parsers = $p;
	}
	
	function checkForTable($name){
		return false;
	}
	
	function checkMyTable($CIA){
		return false;
	}
	
	public function lockTable($table){
		
	}
	
	public function unlockTable($table){
		
	}
	
	public function alterTable($CIA){
		
	}
	
	private function dropTable($name){
		
	}
	
	private function dropView($name){
		
	}
	
	function loadSingle2($table, $id) {
		$sql = "SELECT * FROM $table WHERE ".$table."ID = '$id'";
		$q = $this->c->prepare($sql);
		$q->execute();
		DBPDOStorage::$queryCounter++;
		#$_SESSION["messages"]->addMessage("executing MySQL: $sql");
		#if($this->c->error AND $this->c->errno == 1146) throw new TableDoesNotExistException($table);
		#if($this->c->error AND ($this->c->errno == 1045 OR $this->c->errno == 2002)) throw new NoDBUserDataException();
		#if($this->c->error AND $this->c->errno == 1054) {
		#	preg_match("/[a-zA-Z0-9 ]*\'([a-zA-Z0-9\.]*)\'[a-zA-Z ]*\'([a-zA-Z ]*)\'.*/", $this->c->error(), $regs);
		#	throw new FieldDoesNotExistException($regs[1],$regs[2]);
		#}
		#if($this->c->error AND $this->c->errno == 1046) throw new DatabaseNotSelectedException();
		#echo $this->c->errorInfo();

		$t = $q->fetch(PDO::FETCH_OBJ);
		
		$fields = PMReflector::getAttributesArrayAnyObject($t);
		foreach($fields AS $key => $value){
			$colName = $value;
			$isIDCol = false;
			if($colName == strtolower($table."ID")){
				$colName = $table."ID";
				$isIDCol = true;
			}
			
			$t->$colName = stripslashes($t->$value);
			
			if($isIDCol)
				unset($t->$value);
		}

		return $t;
	}
	
	function createTable($CIA){
		
	}

	function saveSingle2($table, $id, $A) {
		$fields = PMReflector::getAttributesArray($A);
	    $sql = "UPDATE $table SET";
	    
		$done = array();
		for($i = 0;$i < count($fields);$i++){
			if(isset($done[strtolower($fields[$i])]))
				continue;
			
			$sql .= ($i > 0 ? "," : "")." ".$fields[$i]." = ".$this->c->quote($A->$fields[$i])."";
			$done[strtolower($fields[$i])] = true;
		}
		
		$sql .= " WHERE ".$table."ID = '$id'";
		$_SESSION["messages"]->addMessage("executing PDO: $sql");
		#echo $sql;
		$stmt = $this->c->exec($sql);
		if($stmt->errorCode !== '00000')
			print_r($stmt->errorInfo);
		
		DBPDOStorage::$queryCounter++;
		#if($this->cWrite->error AND $this->cWrite->errno == 1062) throw new DuplicateEntryException($this->cWrite->error);
		#echo $this->cWrite->error;
	}

	function getTableColumns($forWhat){
		$q = $this->c->prepare("SHOW COLUMNS FROM $forWhat");
		$q->execute();
		DBPDOStorage::$queryCounter++;
		#if($this->c->error AND $this->c->errno == 1146) throw new TableDoesNotExistException($forWhat);
		
		$a = new stdClass();
		while ($row = $q->fetch(PDO::FETCH_ASSOC))
			$a->$row["Field"] = "";
		
		return $a;
	}
	
	
	function loadMultipleV4(SelectStatement $statement, $typsicher = false){
		#file_put_contents(Util::getRootPath()."debug.txt", print_r(debug_backtrace(), true));
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
				AND substr($currentWhereValue, 0, 3) != "t1."
				AND substr($currentWhereValue, 0, 3) != "t2.") 
				$currentWhereValue = "".$this->c->quote($currentWhereValue)."";
				
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
			
			$statement->fields = array("COUNT(*) AS \"anzahlTotal\"");
			$order = "";
		}
		
		
		if(count($statement->limit) > 0 AND strpos($statement->limit[0], ",") > 0)
			$statement->limit = explode (",", $statement->limit[0]);
		
		foreach($statement->fields AS $k => $field){
			
			if($field == "t1.".$statement->table[0]."ID"){
				
				$statement->fields[$k] = "t1.".$statement->table[0]."ID AS \"".$statement->table[0]."ID\"";
					if(count($statement->group) > 0)
						$statement->fields[$k] = "MAX(t1.".$statement->table[0]."ID) AS \"".$statement->table[0]."ID\"";
				break;
			}
		}
		
		$sql = "SELECT\n ".implode(",\n ",$statement->fields)."\n FROM ".$statement->table[0]." t1$joinAdd ".($where != "()" ? "\n WHERE $where" : "").(count($statement->group) > 0 ? "\n GROUP BY ".implode(", ",$statement->group) : "").($order != "" ? "\n ORDER BY $order" : "").(count($statement->limit) == 1 ? "\n LIMIT ".$statement->limit[0] : "").(count($statement->limit) > 1 ? "\n LIMIT ".$statement->limit[1]." OFFSET ".$statement->limit[0] : "");
		#echo $sql;
		$collector = array();
		
		if($statement->table[0] != "Userdata")
			$_SESSION["messages"]->startMessage("executing PDO: $sql");
		#echo nl2br($sql)."<br /><br />";
		#$q = $this->c->query($sql);
		$q = $this->c->prepare($sql);
		#echo $sql;
		#var_dump($q);
		$q->execute();
		#print_r($this->c->errorInfo());
		DBPDOStorage::$queryCounter++;

		#if($this->c->error AND ($this->c->errno == 1045 OR $this->c->errno == 2002)) throw new NoDBUserDataException();
		#if($this->c->error AND $this->c->errno == 1146) throw new TableDoesNotExistException($statement->table[0]);
		#if($this->c->error AND $this->c->errno == 1046) throw new DatabaseNotSelectedException();
		#if($this->c->error AND $this->c->errno == 1054) {
		#	preg_match("/[a-zA-Z0-9 ]*\'([a-zA-Z0-9\.]*)\'[a-zA-Z ]*\'([a-zA-Z ]*)\'.*/", $this->c->error, $regs);
		#	throw new FieldDoesNotExistException($regs[1],$regs[2]);
		#}
		
		#if($this->c->error) echo "MySQL-Fehler: ".$this->c->error."<br />Fehlernummer: ".$this->c->errno;
		#echo $sql."<br /><br />";
		#if($statement->table[0] != "Userdata") $_SESSION["messages"]->endMessage(": ".$this->c->affected_rows." ".$statement->table[0]." geladen");
		
		if($this->affectedRowsOnly) {
			$this->affectedRowsOnly = false;
			
			if(count($statement->group) === 0){
				$statement->fields = $tempFields;
				
				$t = $q->fetch(PDO::FETCH_OBJ);
				return $t->anzahlTotal;
			}
			
			return $s->rowCount();
		}
		
		$fields = null;
		$cName = $statement->table[0];
		if($statement->className != "") $cName = $statement->className[0];
		

		while($t = $q->fetch(PDO::FETCH_OBJ)){
			$A = new Attributes();

			if($fields == null) $fields = PMReflector::getAttributesArrayAnyObject($t);
			
			foreach($fields AS $key => $value)
				$A->$value = stripslashes($t->$value);
			
			if(count($this->parsers) > 0) foreach($this->parsers as $key => $value)
				if(isset($A->$key)) eval("\$A->\$key = ".$value."(\"".$A->$key."\",\"load\", \$A);");
			
			$oID = $statement->table[0]."ID";
			
			$newCOfClass = new $cName($t->$oID);
			$newCOfClass->setA($A);
			$collector[] = $newCOfClass;
		}
		
		return $collector;
	}
	

	public static $useAsNextID = null;
	function makeNewLine2($table, $A) {
		$fields = PMReflector::getAttributesArray($A);

	    $values = "NULL";
		if(self::$useAsNextID != null){
			$values = self::$useAsNextID;
			self::$useAsNextID = null;
		}
		
	    $sets = "`".$table."ID`";
		for($i = 0;$i < count($fields);$i++){
			if($fields[$i] == $table."ID") continue;

			$values .= ", ".$this->c->quote($A->$fields[$i])."\n";

			$sets .= ",\n`".$fields[$i]."`";
		}
	    $sql = "INSERT INTO\n $table\n ($sets) VALUES ($values)";
		$_SESSION["messages"]->addMessage("executing PDO: $sql");
		
	    $this->c->exec($sql);
		print_r($this->c->errorInfo());
		DBPDOStorage::$queryCounter++;
	
		#if($this->cWrite->error AND $this->cWrite->errno == 1054) {
		#	preg_match("/[a-zA-Z0-9 ]*\'([a-zA-Z0-9\.]*)\'[a-zA-Z ]*\'([a-zA-Z ]*)\'.*/", $this->cWrite->error, $regs);
		#	throw new FieldDoesNotExistException($regs[1],$regs[2]);
		#}
		#if($this->cWrite->error AND $this->cWrite->errno == 1062) throw new DuplicateEntryException($this->cWrite->error);
		
		#if($this->cWrite->error) throw new StorageException($this->cWrite->error);
		
	    return $this->c->lastInsertId();
	}
	
	function deleteSingle($table, $keyName, $id){
		#$sql = "DELETE FROM $table WHERE $keyName = '$id'";
		#$this->cWrite->query($sql);
		#DBStorage::$queryCounter++;
		#$_SESSION["messages"]->addMessage("executing MySQL: $sql");
	}
}

?>