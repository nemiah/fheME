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

class Adapter {
	protected $ID;
	protected $DBS = null;
	protected $parsers = array();
	protected $file = "";
	protected $affectedRowsOnly = false;
	private $storage = "";
	
	protected $selectStatement = null;
	
	protected $hasParsers = false;
	
	/**
	 * Creates a new instance of this class. ID and Tablename are set.
	 * 
	 * @param $ID(Integer) ID of the object
	 * @param $storage(String) Name of the table corresponding to the object
	 */
	function __construct($ID, $storage){
	    $this->ID = $ID;
	    $this->selectStatement = new SelectStatement();
	    $this->storage = $storage;
	}
	
	function __clone() {
		$this->selectStatement = clone $this->selectStatement;
	}
	
	/**
	 * This function returns an instance of the type of database
	 * specified when this class was created.
	 *
	 * @return DBStorage DBStorageU SQLiteStorage pfDBStorage UDStorage IMAPStorage FileStorage Database connection object
	 */
	function getConnection(){ 
	    if($this->storage == "MySQL") $this->DBS = new DBStorage();
	    if($this->storage == "MySQLo") $this->DBS = new DBStorageU();
	    if($this->storage == "SQLite") $this->DBS = new SQLiteStorage();
	    if($this->storage == "phpFileDB") $this->DBS = new pfDBStorage();
	    if($this->storage == "UDStorage") $this->DBS = new UDStorage();
	    if($this->storage == "IMAP") $this->DBS = new IMAPStorage();
	    if($this->storage == "File") $this->DBS = new FileStorage();
	    if($this->storage == "MSSQL") $this->DBS = new MSSQLStorage();
	    if($this->storage == "Cloud") $this->DBS = new CloudStorage();

	    return $this->DBS;
	}
	
	/**
	 * Specifies whether the next query should only return the number of affected rows.
	 * 
	 * @param $bool(Boolean) The boolean value to be set
	 */
	function setGetAffectedRowsOnly($bool){
		if($this->DBS == null) $this->getConnection();
		$this->affectedRowsOnly = $bool;
		$this->DBS->setGetAffectedRowsOnly($bool);
	}
	
	/**
	 * This function returns the SelectStatement parameter specified by $command.
	 * 
	 * @param $command(String) The desired parameter of the SelectStatement object
	 * 
	 * @return The value of the desired parameter
	 */
	function getSelectStatement($command){
		return($this->selectStatement->$command);
	}
	
	/**
	 * This function sets the specified SelectStatement parameter to a specified value.
	 * 
	 * @param $command(String) The name of the parameter to be set
	 * @param $value(String) The value to be set
	 */
	function setSelectStatement($command, $value){
		if($command == "AttributesClassName") $this->selectStatement->$command = $value;
		else $this->selectStatement->$command = (is_array($value) ? $value : array($value));
	}	
	
	/**
	 * This function creates a new SelectStatement object which is saved in this object.
	 */
	function newSelectStatement(){
		$this->selectStatement = new SelectStatement();
	}
	
	/**
	 * This function adds a value to the specified SelectStatement parameter.
	 * 
	 * @param $command(String) Name of the parameter
	 * @param $value(String) Value to be added
	 */
	function addSelectStatement($command, $value){
		$c = array();
		$c[] = $value;
		$this->selectStatement->$command = array_merge($this->selectStatement->$command, $c);
	}
	
	/**
	 * Sets the file used for the database.
	 * Only supported if one of the following database types is used:
	 *  - SQLiteStorage
	 *  - pfDBStorage
	 *  
	 * @param $file(String) The file to be set
	 */
	function setDBFile($file){
		if($this->DBS == null) $this->getConnection();
		$this->DBS->setDBFile($file);
	}
	
	/**
	 * Sets the folder used for the database.
	 * Only supported if one of the following database types is used:
	 *  - FileStorage
	 *  - pfDBStorage
	 *  
	 * @param $folder(String) The folder to be set
	 * @param $forceDir(Boolean) Force the dir
	 */
	function setDBFolder($folder, $forceDir = false){
		if($this->DBS == null) $this->getConnection();
		$this->DBS->setDBFolder($folder, $forceDir);
	}
	
	/**
	 * Checks the existance of the specified table.
	 * 
	 * @param $name(String) The name of the table to be checked
	 * 
	 * @return Boolean true if table exists, false otherwise
	 */
	function checkIfTableExists($name){
		if($this->DBS == null) $this->getConnection();
		return $this->DBS->checkForTable($name);
	}
	
	/**
	 * Creates a new table corresponding to the supplied parameter. 
	 * 
	 * @param $CIA(Object) The object describing the table to be created
	 * 
	 * @return mysqli The database connection used in the process of table creation
	 */
	function createMyTable($CIA){
		if($this->DBS == null) $this->getConnection();
		return $this->DBS->createTable($CIA);
	}
	
	/**
	 * Grabs the columns of the specified table.
	 * 
	 * @return stdclass Object with table names as attributes
	 * 
	 * @param $forWhat(String) Name of the table from which columns are to be retrieved
	 */
	function getTableColumns($forWhat){
		if($this->DBS == null) $this->getConnection();
		return $this->DBS->getTableColumns($forWhat);
	}

	/**
	 * Checks whether table or view described in $CIA equals corresponding table in database.
	 * 
	 * @return Integer Number of differences or changes
	 * 
	 * @param $CIA(Object) table description to be checked
	 */
	function checkMyTable($CIA){
		if($this->DBS == null) $this->getConnection();
		return $this->DBS->checkMyTable($CIA);
	}
	
	/**
	 * Alters table as specified in $CIA.

	 * @return mysqli_error Error-object if error occured else no return value
	 * 
	 * @param $CIA(Object) Contains alter statement
	 */
	function alterTable($CIA){
		if($this->DBS == null) $this->getConnection();
		return $this->DBS->alterTable($CIA);
	}
	
	/**
	 * Adds a parser for the supplied attribute.
	 * 
	 * @param $attribute(String) Attribute to be parsed
	 * @param $function(String) Name of the function which will parse the attribute
	 */
	function addParser($attribute,$function) {
		$this->parsers[$attribute] = $function;
		$this->hasParsers = true;
	}

	function resetParsers(){
		$this->parsers = array();
		$this->hasParsers = false;
	}
	
	/**
	 * Loads a single row of the specified table. Extracts table name out of classname if none is supplied.
	 * Previously set parsers are called if necessary.
	 * 
	 * @return Object Content of the row after execution of parsers
	 * 
	 * @param $forWhat[optional](String) Tablename to be used
	 * @param $typsicher[optional](Boolean) Determines if execution is typesafe
	 */
	function loadSingle2($forWhat = ""/*, $typsicher = false*/){
		if($this->DBS == null) $this->getConnection();
		if($forWhat == "") $forWhat = str_replace("Adapter","",get_class($this));
		
		#if(!$typsicher) 
		$A = $this->DBS->loadSingle2($forWhat, $this->ID);
		#else $A = $this->DBS->loadSingleT($forWhat, $this->ID);

		foreach($this->parsers AS $key => $value)
			if(isset($A->$key)) {
				$s = explode("::",$value);
				$method = new ReflectionMethod($s[0], $s[1]);
				$A->$key = $method->invoke(null, $A->$key, "load");
			}
			#if(isset($A->$key)) eval("\$A->\$key = ".$value."(\"".$A->$key."\",\"load\");");

		return $A;
	}
	
	/**
	 * Applies defined parsers on specified object.
	 * 
	 * @return Object The supplied object after parsing
	 * 
	 * @param $A(Object) The object to be parsed
	 */
	function doParsing($A){
		if(count($this->parsers) > 0)
			foreach($this->parsers AS $key => $value)
				if(isset($A->$key)) {
					$s = explode("::",$value);
					$method = new ReflectionMethod($s[0], $s[1]);
					$A->$key = $method->invoke(null, $A->$key, "store", $A);
				}
		return $A;
	}
	
	/**
	 * Stores given data in specified table.
	 * 
	 * @param $forWhat(String) The name of the table
	 * @param $A(Object) Contains table data
	 */
	function saveSingle2($forWhat, $A){
		if($this->DBS == null) $this->getConnection();
		$_SESSION["messages"]->addMessage("Saving class $forWhat into DB...");
		$A = $this->doParsing($A);
		$this->DBS->saveSingle2($forWhat, $this->ID, $A);
	}
	
	/**
	 * Inserts a new row into the table.
	 * 
	 * @return The id of the new row
	 * 
	 * @param $forWhat(String) Name of the table
	 * @param $A(Object) Data to be inserted
	 */
	function makeNewLine2($forWhat, $A){
		if($this->DBS == null) $this->getConnection();
		$A = $this->doParsing($A);
		$this->ID = $this->DBS->makeNewLine2($forWhat, $A);
		return $this->ID;
	}
	
	/**
	 * Removes row from the table.
	 * 
	 * @param $forWhat(String) Name of the table
	 */
	function deleteSingle($forWhat){
		if($this->DBS == null) $this->getConnection();
		#$forWhat = ereg_replace("Adapter","",get_class($this));
		$_SESSION["messages"]->addMessage("deleting ID $this->ID from $forWhat");
		$this->DBS->deleteSingle($forWhat,$forWhat."ID",$this->ID);		
	}
	
	/**
	 * Converts given searchString to SQL statements.
	 */
	function parseSearchString(){
		if(count($this->selectStatement->searchString) == 0) return;
		$string = $this->selectStatement->searchString[0];
		$fields = $this->selectStatement->searchFields;

		$s = explode(" UND ",$string);
		for($i = 0; $i< count($s);$i++){
			foreach($fields AS $j => $name){
			#for($j = 0;$j<count($fields);$j++){
				
				$this->addSelectStatement("whereFields",$fields[$j]);
				$this->addSelectStatement("whereOperators", "LIKE");
				$this->addSelectStatement("whereValues","%".$s[$i]."%");
				$this->addSelectStatement("whereLogOp",($j == 0 ? "AND" : "OR"));
				$this->addSelectStatement("whereBracketGroup","BG".$i);
				
				//Fix by mysqli and utf8 broken umlauts
				$v = DBStorage::findNonUft8($s[$i]);
				if($v != $s[$i]){
					$this->addSelectStatement("whereFields",$fields[$j]);
					$this->addSelectStatement("whereOperators", "LIKE");
					$this->addSelectStatement("whereValues","%".$v."%");
					$this->addSelectStatement("whereLogOp","OR");
					$this->addSelectStatement("whereBracketGroup","BG".$i);
				}
			}
				
		}
	}

	/**
	 * This function will return all entries from database matching previously set selectStatement.
	 * 
	 * @return Object All objects matching selectStatement
	 * 
	 * @param $typsicher[optional](Boolean) Determines if execution is typesafe
	 */
	function lCV4($typsicher = false){
		$this->parseSearchString();
		if($this->DBS == null) $this->getConnection();

		if(count($this->selectStatement->fields) == 0)
			$this->selectStatement->fields[0] = "*";

		#if(!in_array($this->selectStatement->table[0]."ID",$this->selectStatement->fields) AND !in_array("*",$this->selectStatement->fields))
		$this->selectStatement->fields[] = "t1.".$this->selectStatement->table[0]."ID"; //This is REQUIRED or else objects with values from a joined table may not get an id (if same colname appears in more than one table)
		
		$this->DBS->setParser($this->parsers);
		if($this->affectedRowsOnly) $this->affectedRowsOnly = false;

		#if(!$typsicher)
		$return = $this->DBS->loadMultipleV4($this->selectStatement);
		#else return $this->DBS->loadMultipleT($this->selectStatement);
		if($return != null AND is_array($return))
			foreach($return AS $k => $v)
				$v->parsers = $this->hasParsers;
		
		return $return;
	}
	
	/**
	 * Same as lCV4, but without typesafe capabilities.
	 * 
	 * @return Object All objects matching selectStatement
	 */
	function lCV3(){
		$this->parseSearchString();
		if($this->DBS == null) $this->getConnection();
		
		if($this->selectStatement->AttributesClassName == "") 
			$this->selectStatement->AttributesClassName = $this->selectStatement->table[0].(count($this->selectStatement->joinTables) > 0 ? "Join" : "")."Attributes";
			
		if(count($this->selectStatement->fields) == 0)
			$this->selectStatement->fields = PMReflector::getAttributesArray($this->selectStatement->AttributesClassName);#$this->selectStatement->table[0].(count($this->selectStatement->joinTables) > 0 ? "Join" : "")."Attributes");
		
		if(!in_array($this->selectStatement->table[0]."ID",$this->selectStatement->fields)) $this->selectStatement->fields[] = (count($this->selectStatement->joinTables) > 0 ? "t1." : "").$this->selectStatement->table[0]."ID";
		
		$this->DBS->setParser($this->parsers);
		if($this->affectedRowsOnly) $this->affectedRowsOnly = false;
		
		$return = $this->DBS->loadMultipleV3($this->selectStatement);
		if($return != null AND is_array($return))
			foreach($return AS $k => $v)
				$v->parsers = $this->hasParsers;
		
		return $return;
	}
}
?>
