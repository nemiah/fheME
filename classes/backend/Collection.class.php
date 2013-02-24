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
abstract class Collection {
	protected $A = null;
	protected $Adapter = null;

	protected $collectionOf = "";
	protected $i = 0;
	protected $collector = null;
	protected $storage = PHYNX_MAIN_STORAGE;#"MySQL";

	protected $myAdapterClass;
	
	protected $VxMessenger = array();
	
	protected $lI = null;
	
	protected $customizer;

	protected $loadedTotal;
	protected $loadedPage;
	protected $loadedPerPage;
	protected $isFiltered = false;
	
	function __clone() {
		$this->Adapter = clone $this->Adapter;
	}
	
	/**
	 * If active, customizes this class.
	 *
	 * If updated, please also update UnpersistentClass::customizer and Environment::customizer
	 */
	public function customize(){
		if(defined("PHYNX_FORBID_CUSTOMIZERS"))
			return;
		
		try {
			$active = mUserdata::getGlobalSettingValue("activeCustomizer");
			if($active == null) return;

			$this->customizer = new $active();
			$this->customizer->customizeClass($this);
		} catch (Exception $e){ }
	}

	public function getMultiPageDetails(){
		return array("total" => $this->loadedTotal, "perPage" => $this->loadedPerPage, "page" => $this->loadedPage);
	}

	public function isFiltered(){
		return $this->isFiltered;
	}

	/**
	 * Returns BPS Data of this object.
	 * 
	 * @return Array BPS Data
	 */
	protected function getMyBPSData(){
		return BPS::getAllProperties(get_class($this));
	}
	
	/**
	 * Returns the collector of this object.
	 * 
	 * @return Object Collector
	 */
	public function getCollector(){
		return $this->collector;
	}
	
	/**
	 * Checks if the table associated to this object exists.
	 * 
	 * @return Boolean true, if the table exists, otherwise false
	 */
	function checkIfMyTableExists(){
		$this->loadAdapter();
		return $this->Adapter->checkIfTableExists($this->collectionOf);
	}
	
	/**
	 * Returns the database table associated to this object.
	 * 
	 * @return String Database table associated to this object
	 */
	function getCollectionOf(){
		return $this->collectionOf;
	}
	
	/**
	 * Checks if the DB File exists for the plugin currently in use.
	 * 
	 * @return Boolean true, if File exists, otherwise false
	 */
	function checkIfMyDBFileExists(){
		#$p = $this->getClearClass();#
		$p = str_replace("GUI","",get_class($this));
		return file_exists("../".$_SESSION["CurrentAppPlugins"]->getAppFolderOfPlugin($p)."/".$_SESSION["CurrentAppPlugins"]->getFolderOfPlugin($p)."/CI.pfdb.php");
	}
	
	/**
	 * Returns the relative path to the current DB File.
	 * 
	 * @return String Path to DB File
	 */
	function getMyDBFileName(){
		#$p = $this->getClearClass();
		$p = str_replace("GUI","",get_class($this));
		return "../".$_SESSION["CurrentAppPlugins"]->getAppFolderOfPlugin($p)."/".$_SESSION["CurrentAppPlugins"]->getFolderOfPlugin($p)."/CI.pfdb.php";
	}
	
	/**
	 * Returns the relative path to the folder where the current DB File resides.
	 * 
	 * @return String Path to DB File Folder
	 */
	function getMyDBFolder(){
		#$p = $this->getClearClass();
		$p = str_replace("GUI","",get_class($this));
		return "../".$_SESSION["CurrentAppPlugins"]->getAppFolderOfPlugin($p)."/".$_SESSION["CurrentAppPlugins"]->getFolderOfPlugin($p)."/";
	}
	
	/**
	 * Retrieves all rows from the associated Database File.
	 * 
	 * @return Array Rows of associated DB File, -1 on error
	 */
	function getMyTablesInfos(){
		
		$this->loadAdapter();
		
		if($this->checkIfMyDBFileExists()) {
			$creates = new CIs();
			$creates->setMyDBFolder($this->getMyDBFolder());
			$creates->lCV3();
			
			return $creates;
		} else $_SESSION["messages"]->addMessage("Database-information file for plugin ".str_replace("GUI","",get_class($this))." does not exist.");
		return -1;
	}
	
	/**
	 * Creates a Database Table using the information of the associated database file.
	 */
	function createMyTable($quiet = false) {
		$_SESSION["messages"]->addMessage("Creating table for ".get_class($this).". Using file ".$this->getMyDBFolder()."CI.pfdb.php...");
		#if(!$this->checkIfMyTableExists()) {

			$creates = $this->getMyTablesInfos();
			$message = "Führe SQL aus...<br />";
			
			$CI = $creates->getNextEntry();
			while($CI != null){
				$CIA = $CI->getA();
				$CIA->MySQL = str_replace("%%&ESCSLASH%%&","\'",$CIA->MySQL);
				$CIA->MSSQL = str_replace("%%&ESCSLASH%%&","\'",$CIA->MSSQL);
				$message .= htmlentities($CIA->MySQL);

				$connection = $this->Adapter->createMyTable($CIA);
				if($connection == null and PHYNX_MAIN_STORAGE == "MySQLo"){
					$connection = new stdClass();
					$connection->error = mysql_error();
					$connection->affected_rows = mysql_affected_rows();
				}
				$CI = $creates->getNextEntry();
				$message .= "<br /><br />Anzahl betroffener Datensätze: ".$connection->affected_rows."<br />";
				if($connection->error) $message .= "<span style=\"color:red;\">Es ist ein SQL-Fehler aufgetreten: ".$connection->error."</span><br />";
				else $message .= "<span style=\"color:green;\">Es ist kein MySQL-Fehler aufgetreten</span><br />";
				#$message .= "<br /><br />";
			}
		#} else $message = "Diese Tabelle wurde bereits angelegt";
		$html = $message;/*"
		<div class=\"backgroundColor1 Tab\"><p>Installations-Informationen</p></div>
		<table>
			<colgroup>
				<col class=\"backgroundColor3\" />
			</colgroup>
			<tr>
				<td>$message</td>
			</tr>
		</table>";*/
		
		if(!$quiet)
			echo $html;
		
		return $html;
	}

	/**
	 * Prints out the number of changes between the data in the Database, accessed by Adapter,
	 * and the data in the Database File.
	 */
	function checkMyTables($quiet = false){
		$_SESSION["messages"]->addMessage("Checking tables of ".get_class($this).".");
		
		$creates = $this->getMyTablesInfos();

		$changes = 0;

		if(is_numeric($creates) AND $creates == -1) {
			echo -2;
			return;
		}
		try {
			while(($CI = $creates->getNextEntry())){
				$_SESSION["messages"]->addMessage("checking entry ".$CI->getID());
				#$CIA = $CI->getA();
				$c = $this->Adapter->checkMyTable($CI->getA());
				
				if($c >= 0) $changes+=$c;
				#else return -1;
			}
		} catch(TableDoesNotExistException $e){
			$this->createMyTable($quiet);
		}
		
		if(!$quiet)
			echo $changes;
		
		return $changes;
	}
	
	/**
	 * Selects the parser to be used with the associated adapter.

	 * @param $a(String) The name of the parser
	 * @param $f(String) The function to be executed for parsing
	 */
	function setParser($a,$f){
		if($this->Adapter == null) $this->loadAdapter();
		$this->Adapter->addParser($a,$f);
	}
	
	#   V3 functions ---------------------------------------------------------------------------------------------------
	
	/**
	 * Adds the specified data type to the SelectStatement of the associated Adapter object.
	 * 
	 * @param $field(String) The name of the field
	 * @param $type(String) The type of the field
	 */
	function addDataType($field, $type){
		if($this->Adapter == null) $this->loadAdapter();
		
		$this->Adapter->addSelectStatement("dataTypes",array($field => $type));
	}
	
	/**
	 * Adds an order statement to the SelectStatement of the associated Adapter object.
	 * 
	 * @param $order(String) The column to be ordered by
	 * @param $o[optional](String) The direction (ASC/DESC)
	 */
	function addOrderV3($order,$o = "ASC"){
		#$this->V3Used = true;
		if($this->Adapter == null) $this->loadAdapter();
		$this->Adapter->addSelectStatement("order",$order);
		$this->Adapter->addSelectStatement("orderAscDesc", $o);
	}
	
	/**
	 * Sets the order of the SelectStatement of the associated
	 * Adapter object overwriting previously set ones.
	 * 
	 * @param $order(String) The column to be ordered by
	 * @param $o[optional](String) The direction (ASC/DESC)
	 */
	function setOrderV3($order,$o = "ASC"){
		#$this->V3Used = true;
		if($this->Adapter == null) $this->loadAdapter();
		$this->Adapter->setSelectStatement("order",$order);
		$this->Adapter->setSelectStatement("orderAscDesc", $o);
	}

	/**
	 * Sets the table from which data is to be loaded.
	 *
	 * @param $table(String) The name of the table
	 */
	function setTableV3($table){
		#$this->V3Used = true;
		if($this->Adapter == null) $this->loadAdapter();
		$this->Adapter->setSelectStatement("table",$table);
	}

	/**
	 * Sets the class name which will be created.
	 *
	 * @param $table(String) The name of the table
	 */
	function setClassNameV3($className){
		#$this->V3Used = true;
		if($this->Adapter == null) $this->loadAdapter();
		$this->Adapter->setSelectStatement("className",$className);
	}
	
	/**
	 * Sets the fiels to be selected from the table.
	 * 
	 * @param $fields(Array) Names of table fields
	 */
	function setFieldsV3($fields){
		#$this->V3Used = true;
		if($this->Adapter == null) $this->loadAdapter();
		$this->Adapter->setSelectStatement("fields",$fields);
	}	
	
	/**
	 * Adds a field to be selected from the table.
	 * 
	 * @param $field(String) Name of the field
	 */
	function addFieldV3($field){
		#$this->V3Used = true;
		if($this->Adapter == null) $this->loadAdapter();
		$this->Adapter->addSelectStatement("fields",$field);
	}
	
	/**
	 * Sets the limit for the SelectStatement.
	 * 
	 * @param $limit(String) Limit for the table data
	 */
	function setLimitV3($limit){
		if($this->Adapter == null) $this->loadAdapter();
		#$this->V3Used = true;
		$this->limit = $limit;
		$this->Adapter->setSelectStatement("limit",$limit);
	}
	
	/**
	 * Sets the restrictions for the SelectStatement.
	 * 
	 * @param $field(Array) The names of the fields
	 * @param $operator(Array) Operators for the fields
	 * @param $value(Array) Values for the fiels
	 * @param $logOperator[optional](Array) Logical operator for linking multiple restrictions
	 * @param $bracketGroup[optional](Array) Creation of aggregations
	 */
	function setAssocV3($field, $operator, $value, $logOperator = "AND", $bracketGroup = ""){
		#$this->V3Used = true;
		if($this->Adapter == null) $this->loadAdapter();
		$this->Adapter->setSelectStatement("whereFields",$field);
		$this->Adapter->setSelectStatement("whereOperators", $operator);
		$this->Adapter->setSelectStatement("whereValues",$value);
		$this->Adapter->setSelectStatement("whereLogOp",$logOperator);
		$this->Adapter->setSelectStatement("whereBracketGroup",$bracketGroup);
	}
	
	/**
	 * Adds additional restrictions. See setAssocV3.
	 * 
	 * @param $field(Array) The names of the fields
	 * @param $operator(Array) Operators for the fields
	 * @param $value(Array) Values for the fiels
	 * @param $logOperator[optional](Array) Logical operator for linking multiple restrictions
	 * @param $bracketGroup[optional](Array) Creation of aggregations
	 */
	function addAssocV3($field, $operator, $value, $logOperator = "AND", $bracketGroup = ""){
		#$this->V3Used = true;
		if($this->Adapter == null) $this->loadAdapter();
		$this->Adapter->addSelectStatement("whereFields",$field);
		$this->Adapter->addSelectStatement("whereOperators", $operator);
		$this->Adapter->addSelectStatement("whereValues",$value);
		$this->Adapter->addSelectStatement("whereLogOp",$logOperator);
		$this->Adapter->addSelectStatement("whereBracketGroup",$bracketGroup);
	}
	
	/**
	 * Adds a join with another table.
	 * 
	 * @param $table(String) Name of the table
	 * @param $field1(String) Name of the field in the first table
	 * @param $operator[optional](String) Operator for the join
	 * @param $field2[optional](String) Name of the field in the second table
	 * @param $ACN[optional](String) The class name for the attributes
	 */
	function addJoinV3($table, $field1, $operator = "", $field2 = "", $ACN = ""){
		#$this->V3Used = true;
		if($this->Adapter == null) $this->loadAdapter();
		$this->Adapter->addSelectStatement("joinTables",$table);
		$this->Adapter->addSelectStatement("joinConditions",array($field1,$field2));
		$this->Adapter->addSelectStatement("joinConditionOperators",$operator);
		
		if($ACN != "") $this->setACNV3($ACN);
	}
	
	/**
	 * Sets the class name for the attributes.
	 * 
	 * @param $name(String) Name for the attribute
	 */
	function setACNV3($name){
		if($this->Adapter == null) $this->loadAdapter();
		$this->Adapter->setSelectStatement("AttributesClassName",$name);
	}
	
	/**
	 * Sets the search fields.
	 * 
	 * @param $fields(Array) Search fields
	 */
	function setSearchFieldsV3($fields){
		if($this->Adapter == null) $this->loadAdapter();
		$this->Adapter->setSelectStatement("searchFields",$fields);
	}
	
	/**
	 * Sets the string to search for.
	 * 
	 * @param $string(String) The string to be searched for
	 */
	function setSearchStringV3($string){
		if($this->Adapter == null) $this->loadAdapter();
		$this->Adapter->setSelectStatement("searchString",$string);
	}

	/**
	 * Adds the specified GROUP BY-parameter to the SelectStatement.
	 * 
	 * @param $by(String) The name of the column to be grouped by
	 */
	function addGroupV3($by){
		if($this->Adapter == null) $this->loadAdapter();
		#$this->V3Used = true;
		$this->Adapter->addSelectStatement("group",$by);
	}
	
	/**
	 * Sets the specified GROUP BY-parameter to the SelectStatement.
	 * 
	 * @param $by(String) The name of the column to be grouped by
	 */
	function setGroupV3($by){
		if($this->Adapter == null) $this->loadAdapter();
		#$this->V3Used = true;
		$this->Adapter->setSelectStatement("group",$by);
	}
	# / V3 functions ---------------------------------------------------------------------------------------------------
	
	/**
	 * Returns the next entry of the associated Collector.
	 * 
	 * @return persistentObject Next entry of Collector
	 */
	function getNextEntry(){
		if($this->collector === null) $this->lCV3();
		if(isset($this->collector[$this->i])) return $this->collector[$this->i++];
		else return null;
	}
	
	/**
	 * Resets the pointer of the current Collector entry.
	 */
	function resetPointer(){
		$this->i = 0;
	}
	
	/**
	 * Returns the adjusted classname of this instance.
	 * 
	 * @return String Classname
	 */
	function getClearClass(){
		if(isset($this->collectionOf) AND (get_class($this) == "mGenericGUI" OR get_class($this) == "anyC" OR get_parent_class($this) == "anyC"))
			return "m".$this->collectionOf;
			
		$n = get_class($this);
		if(strstr($n,"GUI")) $n = get_parent_class($this);
		return $n;
	}

	/**
	 * If Adapter is not yet loaded, creates an new instance of the specified
	 * Adapter class and associates the specified storage with it.
	 */
	function loadAdapter(){
		if($this->Adapter == null) {
			
			$n = $this->myAdapterClass;
			if($this->myAdapterClass != null){
				$this->Adapter = new $n(-1,$this->storage);
			}
			else {
				$this->Adapter = new Adapter(-1,$this->storage);
			}
		}
	}
	
	/**
	 * Executes the previously defined SelectStatement and returns the result.
	 * 
	 * @return Array Result of the SelectStatement
	 * 
	 * @param $id[optional](Integer) Only select object with specified ID
	 * @param $returnCollector[optional](Boolean) If true, result is saved in collector-variable, otherwise the result is returned
	 */
	/*public function lCT($id = -1, $returnCollector = true){
		
		if($this->Adapter == null) $this->loadAdapter();
		
		$gT = $this->Adapter->getSelectStatement("table");
		if(count($gT) == 0) $this->Adapter->setSelectStatement("table",$this->collectionOf);
		
		if($id != -1)
			$this->setAssocV3((count($gT) == 0 ? $this->collectionOf : $gT[0])."ID","=",$id);
		
		if($returnCollector) $this->collector = $this->Adapter->lCT();
		else return $this->Adapter->lCT();
	}*/
	
	/**
	 * Executes the previously defined SelectStatement and returns the result.
	 * 
	 * @return Array Result of the SelectStatement
	 * 
	 * @param $id[optional](Integer) Only select object with specified ID
	 * @param $returnCollector[optional](Boolean) If true, result is saved in collector-variable, otherwise the result is returned
	 */
	public function lCV3($id = -1, $returnCollector = true){
		
		if($this->Adapter == null) $this->loadAdapter();
		
		$gT = $this->Adapter->getSelectStatement("table");
		if(count($gT) == 0) $this->Adapter->setSelectStatement("table",$this->collectionOf);
		
		if($id != -1)
			$this->setAssocV3((count($gT) == 0 ? $this->collectionOf : $gT[0])."ID","=",$id);
		
		if($returnCollector) $this->collector = $this->Adapter->lCV3();
		else return $this->Adapter->lCV3();
	}
	
	/**
	 * Sets the needed parameters for pagination of output.
	 * 
	 * @return Integer Number of affected rows
	 * 
	 * @param $id[optional](Integer) Specifies ID of object
	 * @param $page[optional](Integer) Number of the current page
	 * @param $entriesPerPage[optional](Integer) Number of entries per page
	 */
	public function loadMultiPageMode($id = -1, $page = 0, $entriesPerPage = 20){
		if($entriesPerPage == 0){
			$c = $this->getClearClass();#str_replace("GUI", "", get_class($this));
			$mU = new mUserdata();
			$entriesPerPage = $mU->getUDValue("entriesPerPage$c");
			if($entriesPerPage == null) $entriesPerPage = 20;
		}
		$num = $this->getAffectedRows($id);
		$this->setLimitV3($page * $entriesPerPage.",".$entriesPerPage);
		
		if(PMReflector::implementsInterface(get_class($this),"iOrderByField")){
			$sort = new mUserdata();
			$sort = $sort->getUDValue("OrderByFieldInHTMLGUI".$this->getClearClass());
			if($sort != null) $this->setOrderV3(substr($sort,0,strpos($sort,";")),substr($sort,strpos($sort,";")+1));
		}
			
		$this->lCV3($id);
		
		$this->loadedTotal = $num;
		$this->loadedPage = $page;
		$this->loadedPerPage = $entriesPerPage;

		return $num;
	}

	/**
	 * Outputs the attributes for this class.
	 */
	public function tellMe(){
		if($this->A == null) $this->lCV3();
		$c = PMReflector::getAttributesArray($this->getClearClass()."Attributes");
		for($i=0;$i<count($c);$i++) echo count($this->A->$c[$i])." ".$this->getClearClass()." geladen";
	}
	
	/**
	 * Returns the total number of rows.
	 * 
	 * @return Integer Total number of rows
	 */
	public function getTotalNum(){
		if($this->Adapter == null) $this->loadAdapter();
		$this->setFieldsV3(array("0 AS anyField","COUNT(*) AS totalNum"));
		$this->setGroupV3("anyField");
		$this->addDataType("anyField","I");
		$this->addDataType("totalNum","I");
		$this->lCV3();
		$e = $this->getNextEntry();
		$this->resetPointer();
		$this->collector = null;
		$this->Adapter->newSelectStatement();
		if($e == null) return 0;
		return $e->getA()->totalNum;
	}
	
	/**
	 * Returns the number of entries loaded in the last Database request stored in the Collector.
	 * 
	 * @return Integer Number of entries in Collector
	 */
	public function numLoaded(){
		return count($this->collector);
	}
	
	/**
	 * Get affected rows for specified SelectStatement.
	 * 
	 * @return Integer Number of affected rows
	 * 
	 * @param $id[optional](Integer) ID of specific row
	 */
	protected function getAffectedRows($id = -1){
		if($this->Adapter == null) $this->loadAdapter();
		$this->Adapter->setGetAffectedRowsOnly(true);
		return $this->lCV3($id, false);
		#return $this->loadCollectionV2($id, false);
	}
	
	/**
	 * Filters table data by specified categories.
	 * 
	 * @return Boolean True if filters are applied
	 */
	protected function filterCategories(){
		
		$fC = false;
		if(PMReflector::implementsInterface(get_class($this),"iCategoryFilter")){
			$mU = new mUserdata();
			$K = $mU->getUDValue("filteredCategoriesInHTMLGUI".$this->getClearClass());
			$F = $this->getCategoryFieldName();
			if($K != null AND $K != "") {
				$Ks = explode(";",$K);
				foreach($Ks as $k => $v)
					$this->addAssocV3("$F","=",$v,($k == "0" ? "AND" : "OR"),"fCs");
	
				$fC = true;
			}
		}
		$this->isFiltered = $fC;

		if(!PMReflector::implementsInterface(get_class($this),"iSearchFilter"))
			return $fC;
		
		$mU = new mUserdata();

		$K = mUserdata::getUDValueS("searchFilterInHTMLGUI".$this->getClearClass());
		$F = $this->getSearchedFields();
		
		if($K == null)
			return $fC;
		 
		foreach($F as $k => $v)
			$this->addAssocV3("$v","LIKE",'%'.$K.'%',($k == 0 ? "AND" : "OR"),"sfs");
			
		$this->isFiltered = true;
		return true;
	}
	
	/**
	 * Creates new instance of the specified class for the currently selected user language.
	 * 
	 * @return Object The instance of the specified class
	 * 
	 * @param $class(String) The name of the class
	 */
	function loadLanguageClass($class){
		try {
			$n = $class."_".$_SESSION["S"]->getUserLanguage();
			$c = new $n();
		} catch(ClassNotFoundException $e){
			try {
				$n = $class."_de_DE";
				$c = new $n();
			} catch(ClassNotFoundException $e){
				return null;
			}
		}
		
		return $c;
	}
	
	/**
	 * Loads the next field from the database and returns the number of the next field.
	 * 
	 * @return Integer Number of the next field
	 * 
	 * @param $field(String) Name of the field
	 */
	public function getIncrementedField($field){

		$this->addOrderV3("CAST($field AS SIGNED)","DESC");
		$this->setLimitV3("1");
		$this->lCV3();
		
		if($this->numLoaded() == 0) return 1;
		
		$C = $this->getNextEntry();
		$CA = $C->getA();
		
		return $CA->$field + 1;
	}
	
	public function asXML(){
		$xml = new XML();
		$xml->setCollection($this);
		
		return $xml->getXML();
	}
	
	public function asJSON(){
		#$this->lCV3();
		
		$array = array();
		while($A = $this->getNextEntry()){
			$subArray = array();
			foreach($A->getA() as $key => $value)
				$subArray[$key] = $value;
			
			$array[] = $subArray;
		}
		
		#$array[] = array("label" => "Test", "value" => "1");
		#$array[] = array("label" => "Test2", "value" => "2");
		return json_encode($array);
	}
}
?>
