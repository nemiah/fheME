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
class SQLiteStorage {
	private $parsers;
	private $file = "";
	protected $c;

	function __construct(){
	}
	
	function setDBFile($file){
		$this->file = $file;
	}

	public function getConnection(){
		if($this->c == null) $this->renewConnection();

		return $this->c;
	}

	/**
	 * @return SQLiteDatabase
	 */
	public function renewConnection(){
		/*$this->c = new mysqli($_SESSION["DBData"]["host"],$_SESSION["DBData"]["user"],$_SESSION["DBData"]["password"],'database');
		if(mysqli_connect_error() AND (mysqli_connect_errno() == 1045 OR mysqli_connect_errno() == 2002)) throw new NoDBUserDataException();
		if(mysqli_connect_error() AND mysqli_connect_errno() == 1049) throw new DatabaseNotFoundException();
		echo $this->c->error;*/
		$this->c = new SQLiteDatabase($this->file);
	}

	public function setParser($p){
		$this->parsers = $p;
	}
	
	static function checkForTable($name){
		
		return true;
	}
	
	function loadSingle2($table, $id, $typsicher = false) {
		/*$db = @sqlite_open($this->file) or die ("SQLite-DB konnte nicht geöffnet werden. DB-Datei: ".$this->file.(is_file($this->file) ? " existiert" : " existiert nicht" ));
		$sql = "SELECT ".implode(", ",$fields)." FROM $table WHERE $keyName = '$id'";
		$_SESSION["messages"]->addMessage("executing SQLite: $sql");
		$q = sqlite_query($db,$sql);

		$t = sqlite_fetch_array($q);
		if(sqlite_num_rows($q) == 0) throw new ZeroRowsException();
		sqlite_close($db);
		
		return $t;*/

		if($this->c == null) $this->renewConnection();

		$sql = "SELECT * FROM $table WHERE ".$table."ID = '$id'";
		$q = $this->c->query($sql);
		$_SESSION["messages"]->addMessage("executing SQLite: $sql");
		#echo sqlite_error_string($this->c->lastError());
		if($this->c->lastError() === 1) throw new TableDoesNotExistException();

		/*if($this->c->error AND $this->c->errno == 1146) throw new TableDoesNotExistException();
		if($this->c->error AND ($this->c->errno == 1045 OR $this->c->errno == 2002)) throw new NoDBUserDataException();
		if($this->c->error AND $this->c->errno == 1054) {
			ereg("[a-zA-Z0-9 ]*\'([a-zA-Z0-9\.]*)\'[a-zA-Z ]*\'([a-zA-Z ]*)\'.*", $this->c->error(), $regs);
			throw new FieldDoesNotExistException($regs[1],$regs[2]);
		}
		if($this->c->error AND $this->c->errno == 1046) throw new DatabaseNotSelectedException();*/
		#echo $this->c->error;

		$t = $q->fetchObject();

		$fields = PMReflector::getAttributesArrayAnyObject($t);

		#foreach($fields AS $key => $value)
		#	$t->$value = $this->fixUtf8(stripslashes($t->$value));

		return $t;

	}
	
	function saveSingle($table, $keyName, $id, $fields, Attributes $A) {
		$db = @sqlite_open($this->file) or die ("SQLite-DB konnte nicht geöffnet werden. DB-Datei: ".$this->file);
	    $sql = "UPDATE $table SET";
		for($i = 0;$i < count($fields);$i++) $sql .= ($i > 0 ? "," : "")." ".$fields[$i]."='".$A->$fields[$i]."'";
		$sql .= " WHERE $keyName = '$id'";
		$_SESSION["messages"]->addMessage("executing SQLite: $sql");
		$q = sqlite_query($db,$sql);
		sqlite_close($db);
	}
	
	function makeNewLine($table, $keyName, $fields, Attributes $A) {
		
		$db = @sqlite_open($this->file) or die ("SQLite-DB konnte nicht geöffnet werden. DB-Datei: ".$this->file);
	    $values = "''";
		for($i = 1;$i < count($fields);$i++){
		    $values .= ", '".$A->$fields[$i]."'\n";
			$fields[$i] = "'".$fields[$i]."'";
		}
	    $sql = "INSERT INTO\n $table\n (".implode(",\n",$fields).") VALUES ($values)";
		$_SESSION["messages"]->addMessage("executing SQLite: $sql");
		
		$q = sqlite_query($db,$sql);
		#if(sqlite_error_string($q)) $_SESSION["messages"]->addMessage(sqlite_error_string($q));
	    return sqlite_last_insert_rowid($db);
	}
	
	function deleteSingle($table,$keyName,$id){
		$db = @sqlite_open($this->file) or die ("SQLite-DB konnte nicht geöffnet werden. DB-Datei: ".$this->file);
		$sql = "DELETE FROM $table WHERE $keyName = '$id'";
		$_SESSION["messages"]->addMessage("executing SQLite: $sql");
		$q = sqlite_query($db,$sql);
		sqlite_close($db);
	}

	function loadMultipleV2($table, $fields, $where, $order = "", $limit = "", $join = array(), $joinConditions = array()) {
		$db = @sqlite_open($this->file) or die ("SQLite-DB konnte nicht geöffnet werden. DB-Datei: ".$this->file);
		$joins = "";
		$t = 2;
		#$jF = "";
		foreach($join as $key => $value) {
			$conditions = "";
			if(isset($joinConditions[$key]))
				for($i=0;$i<count($joinConditions[$key]);$i++){
					$conditions .= " AND ".$joinConditions[$key][$i][0]." = '".$joinConditions[$key][$i][1]."'";
			}
			$joins .= "LEFT JOIN $key t$t ON(t1.$value = t$t.$value$conditions) ";
			$t++;
		}
	
		$sql = "SELECT ".implode(", ",$fields)." FROM $table t1 $joins".($where != "" ? "WHERE $where" : "")." ".($order != "" ? "ORDER BY $order" : "")." ".($limit != "" ? "LIMIT $limit" : "");
		
		$collector = array();
		$ClassAttributes = $table.(count($join) > 0 ? "Join" : "")."Attributes";
		$AS = new $ClassAttributes();
		$_SESSION["messages"]->addMessage("executing SQLite: $sql");
		
		$q = sqlite_query($db,$sql);
		#if(mysql_error() AND mysql_errno() == 1146) throw new TableDoesNotExistException();
		#if(mysql_error() AND mysql_errno() == 1046) throw new DatabaseNotSelectedException();
		#if(mysql_error() AND mysql_errno() != 1146) echo mysql_error()." ".mysql_errno();
			
		#if(mysql_affected_rows() == 0) throw new ZeroRowsException();
		$_SESSION["messages"]->addMessage(sqlite_num_rows($q)." $table geladen");
		
		while($t = sqlite_fetch_array($q)){
			if(count($this->parsers) > 0) foreach($this->parsers as $key => $value)
				eval("\$t[\$key] = ".$value."(\"".$t[$key]."\",\"load\");");
			
			$newAttributes = $AS->newWithValues(PMReflector::getAttributesArray($ClassAttributes),$t);
			
			$newCOfClass = new $table($t["t1.".$table."ID"]);
			$newCOfClass->setA($newAttributes);
			$collector[] = $newCOfClass;
		}
		sqlite_close($db);
		return $collector;
	}
}
?>