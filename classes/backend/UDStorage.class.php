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

class UDStorage {
	public function loadSingle2($table, $id){

		/*$Ks = new anyC();
		$Ks->setCollectionOf("Kategorie");
		$Ks->setFieldsV3(array("t1.name"));
		$Ks->addAssocV3("type","=","2");*/
		
		$mU = new mUserdata();
		$A =  $mU->getAsObject($table);
		
		#while($t = $Ks->getNextEntry()){
		#	$n = $table."GUIKategorieID".$t->getID();
		#	if(!isset($A->$n)) $A->$n = -1;
		#}
		$idn = $table."ID";
		$A->$idn = 0;
		return $A;
	}
	
	/*function saveSingle($table,$keyName,$id,$fields,Attributes $A) {
	    $mU = new mUserdata();
	    $a = $mU->getAsArray($table);

	    foreach($A->values AS $key => $value){
	    	$mU = new mUserdata();
	    	$mU->setUserdata($key, $value, $table);
	    	if($value != "") $a[$key] = "doNotDeleteThisUserdata";
	    }
	    
	    foreach($a AS $key => $value){
	    	if($value != "doNotDeleteThisUserdata"){
		    	$mU = new mUserdata();
		    	$mU->delUserdata($key);
	    	}
	    }
	}*/
	
	function saveSingle2($forwhat, $thisID, $A){
		$a = $this->loadSingle2($forwhat, $thisID);

	    foreach($A AS $key => $value){
	    	$mU = new mUserdata();
	    	$mU->setUserdata($key, $value, $forwhat);
	    	if($value != "") $a->$key = "doNotDeleteThisUserdata";
	    }
	    
	    foreach($a AS $key => $value){
	    	if($value != "doNotDeleteThisUserdata"){
		    	$mU = new mUserdata();
		    	$mU->delUserdata($key);
	    	}
	    }
	}
}
 ?>