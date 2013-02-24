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
class pfDBStorage {
	static $instance;
	private $file = "";
	private $parsers;
	private $folder = "";
	
	function __construct(){
	}
	
	function setDBFile($file){
		$this->file = $file;
	}
	
	function setDBFolder($folder){
		$this->folder = $folder;
	}
	
	public static function getSI() {
		if (!pfDBStorage::$instance)
			pfDBStorage::$instance = new pfDBStorage();
			
		return pfDBStorage::$instance;
	}
	
	public function renewConnection(){
		#if($this->connection) mysql_close($this->connection);
		#$this->connection = @mysql_pconnect($_SESSION["DBData"]["host"],$_SESSION["DBData"]["user"],$_SESSION["DBData"]["password"]);# or die ("MySQL-DB nicht erreichbar");
		#if(mysql_error() AND mysql_errno() == 1045) throw new NoDBUserDataException();
		#@mysql_select_db($_SESSION["DBData"]["datab"], $this->connection);# or die ("Datenbank nicht gefunden");
	}
	
	public static function newSI(){
        #$_SESSION["messages"]->addMessage("Updating database connection...");
		#$DB = DBStorage::getSI();
		#$DB->renewConnection();
	}
	
	public function setParser($p){
		$this->parsers = $p;
	}
	
	public function checkForTable($name){
		
		$mf = new PhpFileDB();
		$mf->setFolder($this->folder);
		
		return $mf->pfdbTableExists($name);
	}
	/*
	function loadSingle($table, $keyName, $id, $fields) {
		$mf = new PhpFileDB();
		$mf->setFolder($this->folder);
		$keyName = str_replace(trim($table),"",$keyName);
		if($this->file != "") $table = $this->file;
		$sql = "SELECT ".implode(", ",$fields)." FROM $table WHERE $keyName = '$id'";
		$q = $mf->pfdbQuery($sql);
		$t = $mf->pfdbFetchAssoc($q);
		foreach($t as $key => $value)
			$t[$key] = $mf->unescapeString($value);
		
		
		$t[$table."ID"] = $t["ID"];
		return $t;
	}*/
	
	function loadSingle2($table, $id, $typsicher = false) {
		$mf = new PhpFileDB();
		$mf->setFolder($this->folder);
		#$keyName = str_replace(trim($table),"",$keyName);
		if($this->file != "") $table = $this->file;
		$sql = "SELECT * FROM $table WHERE ID = '$id'";
		$q = $mf->pfdbQuery($sql);
		$t = $mf->pfdbFetchAssoc($q);
		$c = new stdClass();
		
		foreach($t as $key => $value)
			$c->$key = $mf->unescapeString($value);
		
		$n = $table."ID";
		$c->$n = $t["ID"];
		unset($c->ID);
		return $c;
	}
	
	function getTableColumns($forWhat){
		if($forWhat != "Installation" AND $forWhat != "CI") throw new StorageException();
		
		$a = new stdClass();
		switch($forWhat){
			case "Installation":
				$a->host = "";
				$a->user = "";
				$a->password = "";
				$a->httpHost = "";
				$a->datab = "";
			break;
			case "CI":
				$a->MySQL = "";
			break;
		}
		
		return $a;
	}
	
	function createTable(CIAttributes $CIA){
		$mf = new PhpFileDB();
		$mf->setFolder($this->folder);
		$_SESSION["messages"]->addMessage("executing phpFileDB: $CIA->MySQL");
		$mf->pfdbQuery($CIA->MySQL);
	}
/*
	function saveSingle($table, $keyName, $id, $fields,Attributes $A) {
		$mf = new PhpFileDB();
		$mf->setFolder($this->folder);
		$keyName = str_replace($table,"",$keyName);
		
		if($this->file != "") $table = $this->file;
	    $sql = "UPDATE $table SET";
		for($i = 0;$i < count($fields);$i++) $sql .= ($i > 0 ? "," : "")." ".$fields[$i]."='".$mf->escapeString($A->$fields[$i])."'";
		$sql .= " WHERE $keyName = '$id'";
		$_SESSION["messages"]->addMessage("executing phpFileDB: $sql");
		$mf->pfdbQuery($sql);
	}
	*/
	function saveSingle2($table, $id, $A) {
		$mf = new PhpFileDB();
		$mf->setFolder($this->folder);
		#$keyName = str_replace($table,"",$keyName);
		
		if($this->file != "") $table = $this->file;
	    $sql = "UPDATE $table SET";
	    $f = "";
		#for($i = 0;$i < count($fields);$i++) $sql .= ($i > 0 ? "," : "")." ".$fields[$i]."='".$mf->escapeString($A->$fields[$i])."'";
		foreach($A AS $k => $v) $f .= ($f != "" ? "," : "")." ".$k."='".$mf->escapeString($v)."'";
		$sql .= $f." WHERE ID = '$id'";
		$_SESSION["messages"]->addMessage("executing phpFileDB: $sql");
		$mf->pfdbQuery($sql);
	}
	
	function loadMultipleV4(SelectStatement $statement){
		$mf = new PhpFileDB();
		$mf->setFolder($this->folder);
		$table = $statement->table[0];
		$where = "";
		foreach($statement->whereFields as $key => $value)
			$where .= ($where != "" ? " ".$statement->whereLogOp[$key]." " : "").($statement->whereFields[$key] == $table."ID" ? "ID" : $statement->whereFields[$key])." ".$statement->whereOperators[$key]." '".$statement->whereValues[$key]."'";
		
		$order = "";
		foreach($statement->order as $key => $value)
			$order .=  ($order != "" ? ", ": "").$statement->order[$key]." ".$statement->orderAscDesc[$key];
		
		unset($statement->fields[array_search($statement->table[0]."ID",$statement->fields)]);
		#".implode(", ",$statement->fields)."
		$sql = "SELECT * FROM ".$statement->table[0]."".($where != "" ? " WHERE $where" : "").(count($statement->group) > 0 ? " GROUP BY ".implode(", ",$statement->group) : "").($order != "" ? " ORDER BY $order" : "").(count($statement->limit) > 0 ? " LIMIT ".implode(", ",$statement->limit) : "");

		$collector = array();
		$Class = $statement->table[0];
		$Class = new $Class(-1);
		$AS = $Class->newAttributes();
		$_SESSION["messages"]->addMessage("executing phpFileDB: $sql");
		
		$q = $mf->pfdbQuery($sql);
		#echo $sql;
		#if(mysql_error() AND mysql_errno() == 1146) throw new TableDoesNotExistException();
		#if(mysql_error() AND mysql_errno() == 1046) throw new DatabaseNotSelectedException();
		#if(mysql_error() AND mysql_errno() != 1146) echo mysql_error()." ".mysql_errno();
			
		#if(mysql_affected_rows() == 0) throw new ZeroRowsException();
		#$_SESSION["messages"]->addMessage(mysql_affected_rows()." $table geladen");
		
		while(($t = $mf->pfdbFetchAssoc($q))){
			$t[$table."ID"] = $t["ID"];
			unset($t["ID"]);
			foreach($t as $key => $value)
				$t[$key] = $mf->unescapeString($value);
			
			if(count($this->parsers) > 0) foreach($this->parsers as $key => $value)
				eval("\$t[\$key] = ".$value."(\"".$t[$key]."\",\"load\");");
			
			$newAttributes = new stdClass();
			foreach($t as $key => $value)
				$newAttributes->$key = $value;
			#$newAttributes = $AS->newWithValues(PMReflector::getAttributesArray($ClassAttributes),$t);

			$newCOfClass = new $table($t[$table."ID"]);
			$newCOfClass->setA($newAttributes);
			$collector[] = $newCOfClass;
		}
		
		return $collector;
	}
	
	/*
	function makeNewLine($table,$keyName,$fields,Attributes $A) {
		$mf = new PhpFileDB();
		$mf->setFolder($this->folder);
		#print_r($fields);
	    $values = "";
		for($i = 0;$i < count($fields);$i++){
		    $values .= ($values != "" ? ", " : "")."'".$A->$fields[$i]."'\n";
			$fields[$i] = "".$fields[$i]."";
		}
		if($this->file != "") $table = $this->file;
	    $sql = "INSERT INTO\n $table\n (".implode(",\n",$fields).") VALUES ($values)";
		$_SESSION["messages"]->addMessage("executing phpFileDB: $sql");
	    $mf->pfdbQuery($sql);
	    return $mf->pfdbInsertId();
	}*/
	function makeNewLine2($table, $A) {
		$mf = new PhpFileDB();
		$mf->setFolder($this->folder);
		#print_r($fields);
	    $values = "";
	    $fields = PMReflector::getAttributesArray($A);
		for($i = 0;$i < count($fields);$i++){
		    $values .= ($values != "" ? ", " : "")."'".$A->$fields[$i]."'\n";
			$fields[$i] = "".$fields[$i]."";
		}
		if($this->file != "") $table = $this->file;
	    $sql = "INSERT INTO\n $table\n (".implode(",\n",$fields).") VALUES ($values)";
		$_SESSION["messages"]->addMessage("executing phpFileDB: $sql");
	    $mf->pfdbQuery($sql);
	    return $mf->pfdbInsertId();
	}
	
	function deleteSingle($table,$keyName,$id){
		$mf = new PhpFileDB();
		$mf->setFolder($this->folder);
		$keyName = str_replace($table,"",$keyName);
		if($this->file != "") $table = $this->file;
		$sql = "DELETE FROM $table WHERE $keyName = '$id'";
		$mf->pfdbQuery($sql);
		$_SESSION["messages"]->addMessage("executing phpFileDB: $sql");
	}
}

?>
