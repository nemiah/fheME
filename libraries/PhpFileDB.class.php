<?php
/**
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
class PhpFileDB {

	private $separator = "&%%%&";
	private $endOfLine = "%%&&&";
	private $newLine = "%n%l%";

	private $fileWithPath = "";

	private $affectedRows = 0;

	#private $fieldsInQuery = array();
	private $table = "";
	private $folder = "";
	private $selectedRows = array();
	private $openFile = null;
	private $fieldsInDB = array();
	private $typesInDB = array();
	private $lengthInDB = array();

	private $maxLineLength = 0;
	private $rowsInDB = 0;

	private $selectField = array();

	private $updateField = array();
	private $updateValue = array();

	private $createField = array();
	private $createType = array();
	private $createLength = array();

	private $dbLength = array();
	private $dbType = array();

	private $insertField = array();
	private $insertValue = array();
	private $insertId = -1;

	private $whereField = array();
	private $whereOperator = array();
	private $whereValue = array();
	
	private $limit = array();
	private $line = 0;
	

	### -------------------------------------------- parsers -----------------------------------------------------------
	### -------------------------------------------- lengthAndTypeParser
	private function lengthAndTypeParser($line){
		$e = $this->ex($line);
		$this->typesInDB = array();
		$this->lengthInDB = array();
		$this->maxLineLength = 0;
		
		
		for($i=0;$i<count($e);$i++){
			$s = explode("(",str_replace(")","",$e[$i]));
			$this->typesInDB[] = $s[0];
			$this->lengthInDB[] = $s[1];
			$this->maxLineLength += $s[1];
		}
		
		$this->rowsInDB = $i;
		
		$this->typesInDB[] = "int";
		$this->lengthInDB[] = "10";
	}

	### -------------------------------------------- insertParser
	private function insertParser($sql){
		#$split = explode(" VALUES ",$sql);
		$this->insertField = array();
		$this->insertValue = array();
		$f = 0;
		$mode = "table";
		$table = "";
		
		for($i=0;$i<strlen($sql);$i++){
			if($sql{$i} == "(")
				$mode = "fields";
				
			if($mode == "table")
				$table .= $sql{$i};
				
			if($mode == "fields" AND preg_match("/[a-zA-Z0-9]/",$sql{$i})){
				if(!isset($this->insertField[$f])) $this->insertField[$f] = "";
				$this->insertField[$f] .= $sql{$i};
			}
			
			if($sql{$i} == "," AND $mode == "fields")
				$f++;
		}
		$this->tableParser($table);
	}

	### -------------------------------------------- selectFieldsParser
	private function selectFieldsParser($sql){
		$f = 0;
		$this->selectField = array();
		
		for($i=0;$i<strlen($sql);$i++){
			if(preg_match("/[a-zA-Z0-9\*]/",$sql{$i})){
				if(!isset($this->selectField[$f])) $this->selectField[$f] = "";
				$this->selectField[$f] .= $sql{$i};
			} else if($sql{$i} == ",") {
				$f++;
			} else if($sql{$i} == " ") true;
			  else if($sql{$i} == ".") {
			  	$this->selectField[$f] = "";
			  }
			  else {
				echo "Found unknown symbol '".$sql{$i}."' near $sql";
				exit();
			}
		}
	}

	### -------------------------------------------- valuesParser
	private function valuesParser($sql){
		$f = 0;
		$v = 0;
		$this->insertValue = array();
		$mode = "none";
		$submode = "none";
		for($i=0;$i<strlen($sql);$i++){
			if($mode == "none" AND $sql{$i} == "("){
				$mode = "values";
				continue;
			}
				
			if($mode == "values" AND $submode == "invalue" AND $sql{$i} == "'" AND $sql{$i-1} != "\\"){
				$submode = "none";
				continue;
			}
			
			if($mode == "values" AND $submode != "invalue" AND $sql{$i} == "'"){
				$submode = "invalue";
				if(!isset($this->insertValue[$f])) $this->insertValue[$f] = array();
				if(!isset($this->insertValue[$f][$v])) $this->insertValue[$f][$v] = "";
				continue;
			}
			
			if($mode == "values" AND $submode != "invalue" AND $sql{$i} == ","){
				$v++;
				continue;
			}
				
			if($mode == "values" AND $submode != "invalue" AND $sql{$i} == ")"){
				$mode = "none";
				$f++;
				$v = 0;
				continue;
			}
			
			if($mode == "none" OR $submode == "none") {
				continue;
			}
			
			if($submode == "invalue"){
				$this->insertValue[$f][$v] .= $sql{$i};
			}
			
		}
	}

	### -------------------------------------------- tableParser
	private function tableParser($sql){
		$this->table = "";
		$sql = trim($sql);
		
		for($i=0;$i<strlen($sql);$i++){
			if(preg_match("/[a-zA-Z0-9]/",$sql{$i}))
				$this->table .= $sql{$i};
			if($sql{$i} == " "){
				if(isset($sql{$i+2}) AND $sql{$i+1}.$sql{$i+2} == "AS"){
					echo "Aliases for tables or fields are not supported!";
					exit();
				}
				/*if(trim(str_replace($this->table,"",$sql)) != ""){
					echo "There is an error in your SQL-statement near $sql.";
					exit();
				}*/
				break;
			}
		}
	}

	### -------------------------------------------- createParser
	private function createParser($sql){
		$mode = "table";
		$submode = "none";
		$f = 0;
		$this->createField = array();
		$this->createType = array();
		$this->createLength = array();
		
		$table = "";
		
		for($i=0;$i<strlen($sql);$i++){
			/*if(ereg("'",$sql{$i}) AND $mode == "none"){
				$mode = "table";
				continue;	
			}*/
			/*
			if(ereg("'",$sql{$i}) AND $mode == "table"){
				$mode = "none";
				continue;
			}*/
			
			if($sql{$i} == "(" AND $mode == "table"){
				$mode = "creates";
				continue;
			}
			
			if($mode == "table")
				$table .= $sql{$i};
			
			if($mode == "creates"){
				if($sql{$i} == "'" AND $submode == "none"){
					$submode = "field";
					continue;
				}
				
				if($sql{$i} == "'" AND $submode == "field"){
					$submode = "type";
					continue;
				}
					
				if($sql{$i} == "(" AND $submode == "type"){
					$submode = "length";
					continue;
				}
					
				if($sql{$i} == ")" AND $submode == "length"){
					$submode = "rest";
					continue;
				}
					
				if($sql{$i} == "," AND $submode == "rest"){
					$f++;
					$submode = "none";
					continue;
				}
				
				
				if($submode == "field" AND preg_match("/[a-zA-Z0-9]/",$sql{$i})){
					if(!isset($this->createField[$f])) $this->createField[$f] = "";
					$this->createField[$f] .= $sql{$i};
				}
				
				if($submode == "type" AND preg_match("/[a-zA-Z]/",$sql{$i})){
					if(!isset($this->createType[$f])) $this->createType[$f] = "";
					$this->createType[$f] .= $sql{$i};
				}
				
				if($submode == "length" AND preg_match("/[0-9,]/",$sql{$i})){
					if(!isset($this->createLength[$f])) $this->createLength[$f] = "";
					$this->createLength[$f] .= $sql{$i};
				}
			}
			
		}
		$this->tableParser(trim($table));
	}

	### -------------------------------------------- setParser
	private function setParser($sql){
		$f = 0;
		$mode = "fields";
		$this->updateField = array();
		$this->updateValue = array();
		
		for($i=0;$i<strlen($sql);$i++){
			if($mode == "fields"){
				if(preg_match("/[a-zA-Z0-9]/",$sql{$i})) {
					if(!isset($this->updateField[$f])) $this->updateField[$f] = "";
					$this->updateField[$f] .= $sql{$i};
				}
				if($sql{$i} == "'"){
					$mode = "values";
					continue;
				}
			}
			if($mode == "values"){
				if(!isset($this->updateValue[$f])) $this->updateValue[$f] = "";
				if($sql{$i} != "'" OR $sql{$i-1} == "\\") $this->updateValue[$f] .= $sql{$i};
				
				if($sql{$i} == "'" AND $sql{$i-1} != "\\"){
					$mode = "fields";
					$f++;
				}
			}
		}
	}
	
	### -------------------------------------------- whereParser
	private function whereParser($sql){
		$f = 0;
		$mode = "fields";
		$this->whereField = array();
		$this->whereValue = array();
		$this->whereOperator = array();
		
		for($i=0;$i<strlen($sql);$i++){
			if($mode == "fields" AND isset($sql{$i+1}) AND ($sql{$i}.$sql{$i+1} == "OR" OR $sql{$i}.$sql{$i+1} == "||")){
				echo "Only 'AND' or '&&' is supported in WHERE-statements.";
				exit();
			}
				
			if($mode == "fields" AND isset($sql{$i+2}) AND $sql{$i}.$sql{$i+1}.$sql{$i+2} == "AND")
				$i+=3;
				
			if($mode == "fields" AND isset($sql{$i+1}) AND $sql{$i}.$sql{$i+1} == "&&")
				$i+=2;
			
			if($mode == "fields" AND ($sql{$i} == "(" OR $sql{$i} == ")")){
				echo "Brackets are not supported in the WHERE-statement.";
				exit();
			}
			
			if($mode == "fields"){
				if(preg_match("/[a-zA-Z0-9]/",$sql{$i})) {
					if(!isset($this->whereField[$f])) $this->whereField[$f] = "";
					$this->whereField[$f] .= $sql{$i};
				}
				if(preg_match("/[!=]/",$sql{$i})){
					if(!isset($this->whereOperator[$f])) $this->whereOperator[$f] = "";
					$this->whereOperator[$f] .= $sql{$i};
				}
				if($sql{$i} == "'"){
					$mode = "values";
					continue;
				}
			}
			if($mode == "values"){
				if(!isset($this->whereValue[$f])) $this->whereValue[$f] = "";
				if($sql{$i} != "'" OR $sql{$i-1} == "\\") $this->whereValue[$f] .= $sql{$i};
				
				if($sql{$i} == "'" AND $sql{$i-1} != "\\"){
					$mode = "fields";
					$f++;
				}
			}
		}
	}

	### -------------------------------------------- the mighty parser Version 2 -----------------------------------------------------------
	private function SQLParser2($sql){
		#echo $sql."\n";
		#$sql = str_replace("\t"," ",$sql);
		$sql = str_replace("\n"," ",$sql);
		#while(ereg("  ",$sql)) $sql = str_replace("  "," ",$sql);
		$sql = trim($sql);

		$mode = "";
		for($i=0;$i<6;$i++)
			if($sql{$i} != " ")
				$mode .= $sql{$i};
			else break;

		
		$next = "";
		while(isset($sql{$i}) AND $sql{$i} == " ") $i++;
		
		$collector = "";
		if(isset($sql{$i+2})) {
			while(
				(isset($sql{$i+4}) AND $sql{$i}.$sql{$i+1}.$sql{$i+2}.$sql{$i+3}.$sql{$i+4} != "TABLE" )
				AND (isset($sql{$i+3}) AND $sql{$i}.$sql{$i+1}.$sql{$i+2}.$sql{$i+3} != "INTO" )
				AND (isset($sql{$i+3}) AND $sql{$i}.$sql{$i+1}.$sql{$i+2}.$sql{$i+3} != "FROM" )
				AND (isset($sql{$i+2}) AND $sql{$i}.$sql{$i+1}.$sql{$i+2} != "SET")){

				$collector .= $sql{$i};
				$i++;
			}
		}
		else {
		 	echo "SQL-statement incomplete.";
		 	exit();
		}
		#echo $mode."\n";
		$collector = trim($collector);
		if($collector != ""){
			if($mode == "SELECT"){
				$this->selectFieldsParser($collector);
			}
			if($mode == "UPDATE")
				$this->tableParser($collector);
		} 
		
		$collector = "";
		
		while(isset($sql{$i}) AND $sql{$i} != " ") {
			$collector .= $sql{$i};
			$i++;
		}
		
		if($mode == "SELECT" AND trim($collector) != "FROM"){
		 	echo "$sql\nSQL-statement incomplete. There must be a 'FROM' after SELECT ...";
		 	exit();
		}
		if($mode == "UPDATE" AND trim($collector) != "SET"){
		 	echo "$sql\nSQL-statement incomplete. There must be a 'SET' after UPDATE tableName";
		 	exit();
		}
		if($mode == "INSERT" AND trim($collector) != "INTO"){
		 	echo "$sql\nSQL-statement incomplete. There must be a 'INTO' after INSERT";
		 	exit();
		}
		if($mode == "CREATE" AND trim($collector) != "TABLE"){
		 	echo "$sql\nSQL-statement incomplete. There must be a 'TABLE' after CREATE";
		 	exit();
		}
		if($mode == "DELETE" AND trim($collector) != "FROM"){
		 	echo "$sql\nSQL-statement incomplete. There must be a 'FROM' after DELETE";
		 	exit();
		}
		if($mode == "DROP" AND trim($collector) != "TABLE"){
		 	echo "$sql\nSQL-statement incomplete. There must be a 'TABLE' after DROP";
		 	exit();
		}
		
		while(isset($sql{$i}) AND $sql{$i} == " ") $i++;
		
		$collector = "";
		if(strpos($sql,"WHERE") OR strpos($sql,"VALUES"))
			while(
				(isset($sql{$i+4}) AND $sql{$i}.$sql{$i+1}.$sql{$i+2}.$sql{$i+3}.$sql{$i+4} != "WHERE" )
				AND (isset($sql{$i+5}) AND $sql{$i}.$sql{$i+1}.$sql{$i+2}.$sql{$i+3}.$sql{$i+4}.$sql{$i+5} != "VALUES" )){

				$collector .= $sql{$i};
				$i++;
			}
		else
			while(isset($sql{$i})) $collector .= $sql{$i++};
		
		$collector = trim($collector);
		
		if($mode == "SELECT" AND $collector == ""){
		 	echo "$sql\nSQL-statement incomplete. There must be a table to SELECT from.";
		 	exit();
		}
		
		$j = 0;
		if($mode == "SELECT" OR $mode == "DROP" OR $mode == "DELETE")
			$this->tableParser($collector);
		
		if($mode == "UPDATE")
			$this->setParser($collector);
		
		if($mode == "CREATE")
			$this->createParser($collector);
		
		if($mode == "INSERT")
			$this->insertParser($collector);
		
		
		$collector = "";
		while(isset($sql{$i}) AND $sql{$i} != " ") $next .= $sql{$i++};
		if($next != "")
			if($next == "WHERE"){
				if(strpos($sql,"LIMIT"))
					while(
						(isset($sql{$i+4}) AND $sql{$i}.$sql{$i+1}.$sql{$i+2}.$sql{$i+3}.$sql{$i+4} != "LIMIT")){

						$collector .= $sql{$i};
						$i++;
					} else
					while(isset($sql{$i})) $collector .= $sql{$i++};
				$this->whereParser($collector);
				
			} else if($next == "VALUES"){
				while(isset($sql{$i})) $collector .= $sql{$i++};
				$this->valuesParser($collector);
			} else {
				echo "$sql\nStatement '$next' unknown.";
				exit();
			}
		
		$function = "do".ucfirst(strtolower($mode));
		$this->$function();
	}

	
	### -------------------------------------------- action functions -----------------------------------------------------------
	### -------------------------------------------- doCreate
	private function doCreate(){
		if(file_exists("$this->folder".$this->table.".pfdb.php")){
			echo "The database $this->table already exists.";
			exit();
		}
		$this->openFile = fopen("$this->folder".$this->table.".pfdb.php", "w+");
		
		if(!fwrite($this->openFile, "<?php echo \"This is a database-file.\"; /*\n".$this->im($this->createField)."\n")){
			echo "Error while writing in the database";
			exit();
		}
		
		$header = array();
		for($i=0;$i<count($this->createType);$i++)
			$header[] = $this->createType[$i]."(".$this->createLength[$i].")";
			
		if(!fwrite($this->openFile, $this->im($header)."\n*/ ?>\n")){
			echo "Error while writing in the database";
			exit();
		}
	}
	
	### -------------------------------------------- doSelect
	private function doSelect(){
		$this->doFileChecksAndOpen(false);
		$this->selectedRows = array();
		
		if(isset($this->selectField[0]) AND $this->selectField[0] == "*" AND count($this->selectField) == 1) {
			$this->selectField = $this->fieldsInDB;
			unset($this->selectField[count($this->selectField)-1]); // removing the field ID again
		}
		else if(in_array("*",$this->selectField) AND count($this->selectField) >= 1){
			echo "You may only use 'SELECT *' <u>or</u> 'SELECT fieldName,...' but <u>never both</u>!";
			exit();
		}
		
		for($i=0;$i<count($this->selectField);$i++){
			if(!in_array($this->selectField[$i],$this->fieldsInDB)) {
				echo "Field '".$this->selectField[$i]."' from query does not exist in table!";
				exit();
			}
			$this->selectedRows[] = array_search($this->selectField[$i],$this->fieldsInDB);
		}
	}
	
	### -------------------------------------------- doDrop
	private function doDrop(){
		if(is_file("$this->folder".$this->table.".pfdb.php")) unlink("$this->folder".$this->table.".pfdb.php");
		else {
			echo "File $this->folder$this->table.pfdb.php does not exist!";
			exit();
		}
	}
	
	### -------------------------------------------- doDelete
	private function doDelete(){
		$this->doFileChecksAndOpen(true,true);
		$this->updateField = $this->fieldsInDB;
		$this->updateValue = array();
		for($i=0;$i<count($this->updateField);$i++)
			$this->updateValue[$i] = "";
		#print_r($this->updateValue);
		#$this->fieldsInDB[$i],$this->updateField
		#print_r($this->updateValue);
		$this->doUpdate(true);
	}
	
	### -------------------------------------------- doInsert
	private function doInsert(){
		$this->insertId = -1;
		$this->doFileChecksAndOpen(true,true);
		$this->insertFieldFlip = array_flip($this->insertField);
		for($l=0;$l<count($this->insertValue);$l++){
			$input = array();
			for($i=0;$i<count($this->fieldsInDB);$i++){
				if($this->fieldsInDB[$i] == "ID") continue;
				if(in_array($this->fieldsInDB[$i],$this->insertField)) 
					$input[] = str_pad(substr($this->insertValue[$l][$this->insertFieldFlip[$this->fieldsInDB[$i]]], 0, $this->lengthInDB[$i]), $this->lengthInDB[$i]);
				
				else 
					$input[] = str_pad("", $this->lengthInDB[$i]);
				
			}
			
			fwrite($this->openFile, $this->im($input).$this->endOfLine."\n");
		}
		fwrite($this->openFile, "*/ ?>\n");
		$this->pfdbCloseFile();
	}
	
	### -------------------------------------------- doUpdate
	private function doUpdate($delete = false){
		$this->doFileChecksAndOpen(true);
		$this->affectedRows = 0;
		$b = "";
		$pos = ftell($this->openFile);
		
		if($this->whereField[0] == "ID"){
			
			$this->line = 0;
			if($this->whereValue[0] <= 0) {
				echo "ID ".$this->whereValue[0]." does not exist. IDs start at 1.";
				exit();
			}
			do {
				++$this->line;
				$pos = ftell($this->openFile);
				$b = fgets($this->openFile)."\n";
				if(trim($b) == "*/ ?>") {
					$this->affectedRows = 0;
					return;
				}
			} while($this->line < $this->whereValue[0]);
			
			fseek($this->openFile,$pos);
			$this->writeUpdateLine($delete);
			$this->affectedRows = 1;
		} else {
			$key = array_search($this->whereField[0],$this->fieldsInDB);
			$pos = ftell($this->openFile);
			$b = fgets($this->openFile);
			while(trim($b) != "*/ ?>" AND !feof($this->openFile)){
				$ex = $this->ex($b);

				if($ex[$key] == $this->whereValue[0]){
					fseek($this->openFile,$pos);
					$this->writeUpdateLine($delete);
					$this->affectedRows++;
				}
				
				$pos = ftell($this->openFile);
				$b = fgets($this->openFile);
			}
		}
		$this->pfdbCloseFile();
	}


	### -------------------------------------------- public functions -----------------------------------------------------------
	### -------------------------------------------- setFolder
	public function setFolder($folder){
		if($folder{strlen($folder)-1} != "/") {
			echo "Please use a '/' at the end of the directory path.";
			exit();
		}
		$this->folder = $folder;
	}
	
	### -------------------------------------------- pfdbAffectedRows
	public function pfdbAffectedRows(){
		return $this->affectedRows;
	}
	
	### -------------------------------------------- pfdbCheckDB
	public function pfdbCheckDB(){
		$this->doFileChecksAndOpen(false);
		$b = fgets($this->openFile);
		$this->line = 1;
		
		while(trim($b) != "*/ ?>" AND !feof($this->openFile)){
			
			$e = $this->ex($b,true);
			
			for($i=0;$i<count($this->fieldsInDB);$i++){
				if($this->fieldsInDB[$i] == "ID") continue;
				
				if(strlen($e[$i]) != $this->lengthInDB[$i]){
					echo "The value ".$e[$i]." in line $this->line has not the specified length ".$this->lengthInDB[$i]."";
					exit();
				}
			}
			$this->line++;
			$b = fgets($this->openFile);
		}
		
		$this->pfdbCloseFile();
	}
	
	### -------------------------------------------- pfdbFetchAssoc
	public function pfdbFetchAssoc(){
	    $b = fgets($this->openFile);
	    $this->line++;
	    
	    while($b == "\n") {
	    	$b = fgets($this->openFile);
	    	$this->line++;
	    }
	    
	    while(trim($b) == "") {
	    	$b = fgets($this->openFile);
	    	$this->line++;
	    }
	    
	    $r = array();
	    
	    
		if(feof($this->openFile) OR trim($b) == "*/ ?>") {
			$this->pfdbCloseFile();
			return false;
		}
		
	    $fields = $this->ex($b);
	    $fields[] = $this->line;
	    
    	for($e=0;$e<count($this->whereValue);$e++){
			if(!in_array($this->whereField[$e],$this->fieldsInDB) AND $this->selectField[0] != "*"){
				echo "Field ".$this->whereField[$e]." in WHERE-statement not found in field-list of the table: ".implode(", ",$this->fieldsInDB);
				exit();
			}
    	
    		if($this->whereOperator[$e] == "="){
    			$index = array_search($this->whereField[$e],$this->fieldsInDB);
    			if($fields[$index] != $this->whereValue[$e]) return $this->pfdbFetchAssoc();
    		}
    		if($this->whereOperator[$e] == "!="){
    			$index = array_search($this->whereField[$e],$this->fieldsInDB);
    			if($fields[$index] == $this->whereValue[$e]) return $this->pfdbFetchAssoc();
    		}
    	}
		
		if(isset($this->limit[1]) AND $this->limit[1] == -1) {
			if($this->limit[0] == $this->limit[2]) return false;
			$this->limit[2]++;
		}
    
	    if(isset($this->limit[1]) AND $this->limit[1] != -1){
	    	if($this->limit[2] <= $this->limit[0]){
		    	$this->limit[2]++;
		    	return $this->pfdbFetchAssoc();
	    	}
	    	
	    	if($this->limit[3] < $this->limit[1])
	    		$this->limit[3]++;
	    	else return false;
	    }
    	
	    for($j=0;$j<count($this->selectedRows);$j++)
	    	$r[$this->fieldsInDB[$this->selectedRows[$j]]] = $this->unescapeString($fields[$this->selectedRows[$j]]);
	    
	    $r["ID"] = $this->line;
	    
		return $r;
	}
	
	### -------------------------------------------- pfdbCloseFile
	public function pfdbCloseFile(){
		fclose($this->openFile);
	}
	
	### -------------------------------------------- pfdbQuery
	public function pfdbQuery($sql){
		$this->SQLParser2($sql);
	}
	
	### -------------------------------------------- pfdbInsertId
	public function pfdbInsertId(){
		$this->doFileChecksAndOpen(false);
		$b = "";
		$this->line = 0;
		
		while(trim($b) != "*/ ?>" AND !feof($this->openFile)) {
			$b = fgets($this->openFile);
			$this->line++;
		}
		
		return $this->insertId = $this->line-1;
	}
	
	### -------------------------------------------- pfdbTableExists
	public function pfdbTableExists($table){
		$this->table = $table;
		if($this->doFileChecksAndOpen(false)){
			$this->pfdbCloseFile();
			return true;
		} else return false;
	
	}
	
	### -------------------------------------------- escapeString
	public function escapeString($string){
		$string = str_replace("\'", "%%&ESCSLASH%%&", $string);
		$string = str_replace("'","\'",$string);
		$string = str_replace("\n",$this->newLine,$string);
		return $string;
	}
	
	### -------------------------------------------- unescapeString
	public function unescapeString($string){
		$string = str_replace("\'","'",$string);
		#$string = str_replace("%%&ESCSLASH%%&","\'",$string);
		$string = str_replace($this->newLine,"\n",$string);
		return $string;
	}
	
	### -------------------------------------------- meta functions -----------------------------------------------------------
	### -------------------------------------------- writeUpdateLine
	private function writeUpdateLine($delete = false){
		$updateFieldFlip = array_flip($this->updateField);
		#print_r($this->fieldsInDB);
		#return;
		for($i=0;$i<count($this->fieldsInDB);$i++){
			if($this->fieldsInDB[$i] == "ID") continue;
			
			if(in_array($this->fieldsInDB[$i],$this->updateField)) {
				$string = str_pad(substr($this->updateValue[$updateFieldFlip[$this->fieldsInDB[$i]]], 0, $this->lengthInDB[$i]),$this->lengthInDB[$i]);
				fwrite($this->openFile,$string);
				
			} else
				for($j=0;$j<$this->lengthInDB[$i];$j++)
					fgetc($this->openFile);
			
			
			if($i < count($this->fieldsInDB)-2) fwrite($this->openFile,($delete ? str_pad("",strlen($this->separator)) : $this->separator));#fgets($this->openFile,strlen($this->separator)+1);
		}
		fwrite($this->openFile,($delete ? str_pad("",strlen($this->endOfLine)) : $this->endOfLine)."\n");
		#fgets($this->openFile,strlen($this->endOfLine)+1);
	}
	
	### -------------------------------------------- doFileChecksAndOpen
	private function doFileChecksAndOpen($write, $end = false){
		if($this->folder == "") {
			echo "Please use setFolder() to set the folder where your databases are!";
			exit();
		}
		
		if(!file_exists("$this->folder".$this->table.".pfdb.php")) {
			echo "File $this->folder".$this->table.".pfdb.php not found. You need to create the table first!";
			exit();
		}
		
		if(!is_readable("$this->folder".$this->table.".pfdb.php")){
			echo "Please make sure that the file $this->folder".$this->table.".pfdb.php is readable!";
			exit();
		}
		
		if($write AND !is_writable("$this->folder".$this->table.".pfdb.php")){
			echo "Please make sure that the file $this->folder".$this->table.".pfdb.php is writable!";
			exit();
		}
		
		$this->openFile = fopen("$this->folder".$this->table.".pfdb.php", "r".($write ? "+" : "")."b");
		$b = fgets($this->openFile);
		
		if(trim($b) != '<?php echo "This is a database-file."; /*'){
			echo htmlentities('Header of database-file wrong (should be "<?php echo \"This is a database-file.\"; /*")!');
			exit();
		}
		
		$b = fgets($this->openFile);
	    
	    
		$this->fieldsInDB = $this->ex($b);
		$this->fieldsInDB[] = "ID";
		
		$this->lengthAndTypeParser(fgets($this->openFile));
		$this->line = 0;
		if($end) fseek($this->openFile, -6, SEEK_END);
		return 1;
	}
	
	### -------------------------------------------- ex
	private function ex($line,$noTrim = false){
		$a = explode($this->separator,$line);
		$a[count($a) - 1] = str_replace($this->endOfLine,"",$a[count($a) - 1]);
		if($noTrim) $a[count($a) - 1] = str_replace("\n","",$a[count($a) - 1]);
		return ($noTrim ? $a : array_map("trim", $a));
	}
	
	### -------------------------------------------- im
	private function im($array){
		$a = implode($this->separator,$array);
		return $a;
	}
}
?>
