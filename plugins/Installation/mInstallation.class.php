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
 *  2007 - 2012, Rainer Furtmeier - Rainer@Furtmeier.de
 */
class mInstallation extends anyC {
	private $folder = "./system/DBData/";
	
	function __construct() {
		$this->collectionOf = "Installation";
		$this->storage = "phpFileDB";
	}
	
	public function switchDBToMySQLo(){
		file_put_contents(Util::getRootPath()."system/connect.php", str_replace("\n#define(\"PHYNX_MAIN_STORAGE\",\"MySQLo\");\n", "\ndefine(\"PHYNX_MAIN_STORAGE\",\"MySQLo\");\n", file_get_contents(Util::getRootPath()."system/connect.php")));
	
		#$DB = new DBStorageU();
	}

	public function updateAllTables(){
			$p = array_flip($_SESSION["CurrentAppPlugins"]->getAllPlugins());
			
			echo "
			<div class=\"backgroundColor1 Tab\"><p>Tabellen-Updates:</p></div>
			<table>
				<colgroup>
					<col style=\"width:120px;\" class=\"backgroundColor2\">
					<col class=\"backgroundColor3\">
				</colgroup>";
				foreach($p as $key => $value){
				try {
					if(method_exists($_SESSION["CurrentAppPlugins"], "isPluginGeneric") AND $_SESSION["CurrentAppPlugins"]->isPluginGeneric($key)){
						$c = new mGenericGUI('', $key);
					} else {
						$c = new $key();
						#$className = $key;
					}
				} catch (ClassNotFoundException $e){
					$key2 = $key."GUI";
					
					try {
						$c = new $key2();
						#$className = $key2;
					} catch (ClassNotFoundException $e2){
						#echo $key." nicht gefunden";
						continue;
					}
				}

				if($key == "CIs") continue;

				#$c = new $className();
				if($c->checkIfMyTableExists() AND $c->checkIfMyDBFileExists()){
				echo "
				<tr>
					<td style=\"text-align:right;\">".$value.":</td><td>";
					$c->checkMyTables();
				echo "
					</td>
				</tr>";
				} #else echo "keine Tabelle(n) / keine DB-Info-Datei";
			}
			echo "
			</table>";
	}
	
	function changeFolder($newFolder){
		$this->folder = $newFolder;
	}
	
	function loadAdapter(){
		parent::loadAdapter();
		if(is_file($this->folder."Installation.pfdb.php")) $this->Adapter->setDBFolder($this->folder);
		else $this->Adapter->setDBFolder(".".$this->folder);
	}
}
?>
