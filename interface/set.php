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
require "../system/connect.php";
if($_SESSION["S"]->checkIfUserLoggedIn() == true) die("-1");

$build = Phynx::build();
if($build)
	header("X-Build: ".$build);

$data = $_GET;
if(isset($_POST) AND count($_POST) > 0) $data = $_POST;

if(isset($data["class"]) AND !$_SESSION["S"]->checkIfUserIsAllowed($data["class"])) 
	Red::errorD("Sie haben keine Berechtigung, um diese Aktion auszuführen!");

try {
	$className = $data["class"].(!strstr($data["class"],"GUI") ? "GUI" : "");

	if($data["id"] != -1){
		$oldClass = new $className($data["id"]);
		$oldClass->loadMe();
		$A = $oldClass->getA();

		if($A == null) Red::errorD ("Dieser Datensatz existiert nicht (mehr). Eventuell wurde er gelöscht.");

		if(get_class($A) != "stdClass") $A->fillWithAssociativeArray($data);
		else $A = Util::fillStdClassWithAssocArray($A, $data);

	} else {
		$oldClass = new $className(-1);
		
		$A = $oldClass->newAttributes();
		if(get_class($A) != "stdClass") $A->newWithAssociativeArray($data);
		else $A = Util::fillStdClassWithAssocArray($A, $data);
	}

	$unusedData = $data;
	foreach($A AS $k => $v)
		unset($unusedData[$k]);
	
	#$AddA = new Attributes();
	#$AddA->newWithAssociativeArray($unusedData, true);
	#print_r($AddA);
	if(isset($data["emptyAttribute"])) {
		$fi = $data["emptyAttribute"];
		$A->$fi = "";
	}
	
	$C = new $className($data["id"]);
	$C->setA($A);
	
	foreach($unusedData AS $k => $v)
		$C->AA($k, $v);
	
	if($className == "FileGUI")
		$C->makeUpload($A);
	
	if($className == "TempFileGUI")
		$C->makeUpload($A);
	
	
	if($data["id"] != -1) $C->saveMe(true, true);
	else $C->newMe(true, true);
	
} catch (TableDoesNotExistException $e) {

} catch (DatabaseNotSelectedException $e) {
	#echo "Database does not exist<br />";
} catch (NoDBUserDataException $e) {
	#echo "Database authentication failed.<br />";
} catch (DatabaseNotFoundException $e) {
	#echo "Specified database not found.<br />";
} catch (DuplicateEntryException $e) {
	Red::errorDuplicate($e->getDuplicateFieldValue());
} catch (ClassNotFoundException $e){
	Red::errorClass($e->getClassName());
}
?>
