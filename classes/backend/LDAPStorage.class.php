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

class LDAPStorage {
	protected $affectedRowsOnly = false;
	protected $c;
	protected $LD = null;

	function __construct(){
		$this->LD = LoginData::get("LDAPServerUserPass");
		if($this->LD == null) throw new NoDBUserDataException();
		
		$this->c = ldap_connect($this->LD->A("server"));
		ldap_set_option($this->c, LDAP_OPT_PROTOCOL_VERSION, 3);
		$r = ldap_bind($this->c,$this->LD->A("benutzername"), $this->LD->A("passwort"));
		
		#if(!$r) throw new StorageException("Could not authenticate to LDAP server: ".ldap_error($this->c));
	}

	function  __destruct() {
		ldap_close($this->c);
	}
	
	public function getConnection(){
		return $this->c;
	}
	
	public function setGetAffectedRowsOnly($bool){
		$this->affectedRowsOnly = $bool;
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
	
	private function dropTable($name){

	}
		
	function loadSingle2($table, $id, $typsicher = false) {

	}
	
	function loadSingleT($table, $id) {
		return $this->loadSingle2($table, $id, true);
	}

	private function searchID($id, $dir){
		#if($dir == "") $dir = $this->LD->A("optionen");
		#array("cn")
		$sr = ldap_search($this->c, $dir, "(uid=$id)");
		return ldap_get_entries($this->c, $sr);
	}

	private function translate($table, $A){
		$t = new $table(-1);
		$schema = $t->getLDAPSchema();
		#print_r($schema);
		#if($as == null)
		$as = PMReflector::getAttributesArray($A);

		foreach($schema AS $k => $v){
			if($k == "objectclass") continue;
			foreach($as AS $m)
				$v = str_replace("{".$m."}",$A->$m,$v);

			if($v == ""){
				unset($schema[$k]);
				continue;
			}
			$schema[$k] = $v;
		}

		return $schema;
	}

	function saveSingle2($table, $id, $A, $dir = "") {
		if($dir == "") $dir = $this->LD->A("optionen");
		#echo $dir;
		$new = $table."ID";
		$A->$new = $id;

		$A = $this->translate($table, $A);

		$info = $this->searchID($A["uid"], $dir);

		if($info["count"] == 0) throw new StorageException("Entry with uid $A[uid] not found!");
		$newCn = null;
		if($info[0]["cn"][0] != $A["cn"]) {
			$newCn = $A["cn"];
			unset($A["cn"]);
		}

		foreach($A AS $k => $v){
			if($v == "" AND $info[0][$k][0] != ""){
				echo $k.": ".$v."\n";
				$attrs = array();
				$attrs[$k] = array();
				if(ldap_mod_del($this->c, $info[0]["dn"],$attrs))
					unset($A[$k]);
				else
					throw new StorageException("Error when deleting an attribute: ".ldap_error($this->c));
			}
		}
		$r = ldap_modify($this->c, $info[0]["dn"], $A);

		if(!$r) throw new StorageException("Error when updating an existing entry: ".ldap_error($this->c));

		if($newCn != null){
			$o = ldap_rename($this->c, $info[0]["dn"], "cn=$newCn", $dir, true);
			if(!$o) throw new StorageException("Error when trying to rename the entry: ".ldap_error($this->c));
		}
	}

	function getTableColumns($forWhat){

	}
	
	function loadMultipleV4(SelectStatement $statement, $typsicher = false){

	}
	
	function loadMultipleT(SelectStatement $statement){
		return $this->loadMultipleV4($statement, true);
	}
	
	function loadMultipleV3(SelectStatement $statement){

		
	}
	function makeNewLine2($table, $id, $A, $dir = "") {
		if($dir == "") $dir = $this->LD->A("optionen");

		$new = $table."ID";
		$A->$new = $id;
		#echo "<pre style=\"font-size:9px;\">";
		#print_r($A);
		$A = $this->translate($table, $A);
		#
		$info = $this->searchID($A["uid"], $dir);

		if($info["count"] > 0) throw new DuplicateEntryException("Entry with uid $A[uid] already exists!");

		if(trim($A["cn"]) == "") throw new LDAPNoCNException();
		#print_r($A);
		$r = ldap_add($this->c, "cn=$A[cn],".$dir, $A);
		#echo "</pre>";
		
		if(ldap_error($this->c) AND ldap_errno($this->c) == 68) throw new DuplicateEntryException(ldap_error($this->c));
		if(ldap_error($this->c) AND ldap_errno($this->c) == 32) throw new LDAPDirDoesNotExistException(ldap_error($this->c));
		if(!$r) throw new StorageException("Error executing LDAP statement: ".ldap_errno($this->c).": ".ldap_error($this->c));
	}
	
	function deleteSingle($id, $dir = ""){
		if($dir == "") $dir = $this->LD->A("optionen");
		$info = $this->searchID($id, $dir);

		if($info["count"] == 0) return;
		if($info["count"] > 1) throw new StorageException("Multiple entries with the same uid found!");

		return ldap_delete($this->c, $info[0]["dn"]);
	}
	
}

?>
