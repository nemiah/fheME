<?php
/*
 *  This file is part of open3A.

 *  open3A is free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 3 of the License, or
 *  (at your option) any later version.

 *  open3A is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.

 *  You should have received a copy of the GNU General Public License
 *  along with this program.  If not, see <http://www.gnu.org/licenses/>.
 * 
 *  2007 - 2024, open3A GmbH - Support@open3A.de
 */
#namespace open3A;
class KategorienGUI extends Kategorien implements iGUIHTMLMP2, iCategoryFilter {
	function getHTML($id,$page){
		T::load(__DIR__, "Kategorien");
		
		$merge = $this->getAvailableCategories(false);

		foreach($merge as $value)
			$this->addAssocV3("type", "=", $value, "OR");
			
		$this->addAssocV3("type", "=", "3", "OR");
		$this->addOrderV3("type", "ASC");
		$this->addOrderV3("name", "ASC");
		
		$this->filterCategories();
		$this->loadMultiPageMode($id, $page, 0);
		
		$gui = new HTMLGUIX($this, "Kategorien");
		$gui->version("Kategorien");
		#$gui->setObject($this);
		$gui->screenHeight();
		$gui->tip();
		#$gui->showFilteredCategoriesWarning($this->filterCategories(),$this->getClearClass());
		
		#$gui->setMultiPageMode($gesamt, $page, 0, "contentRight", str_replace("GUI","",get_class($this)));
		#if($this->collector != null) $gui->setAttributes($this->collector);
		$gui->name("Kategorie");
		#$gui->setCollectionOf("Kategorie","Kategorie");

		$gui->attributes(array("isDefault", "name"));
		
		$gui->displayGroup("type", "KategorienGUI::parserDG");
		
		$gui->colWidth("isDefault", "20px");
		
		$gui->parser("isDefault","Util::catchParser");
		$gui->parser("type","KategorienGUI::typeParser");
		$gui->parser("name","KategorienGUI::nameParser", array("\$type"));
		
		return $gui->getBrowserHTML($id);
	}
	
	public static function parserDG($w){
		$K = new KategorienGUI();
		$KS = $K->getAvailableCategories(true);
		return T::_($KS[$w]);
	}


	public static function IDParser($w, $l, $p){
		$s = HTMLGUI::getArrayFromParametersString($p);
		
		$B = new Button("", "./images/i2/".($s[0] != "" ? "" : "not")."ok.gif");
		$B->type("icon");
		if($s[0] == "") $B->rme($s[1], "-1", "createRemote", array("'$s[2]'", "'$s[3]'"), "if(checkResponse(transport)) contentManager.reloadFrameLeft();");
		else $B->rme($s[1], $s[0], "deleteRemote", '', "if(checkResponse(transport)) contentManager.reloadFrameLeft();");
		
		return $B;
	}
	
	public function getAvailableCategories($flip = true){
		if(isset($_SESSION["nil"]["kategorien"]) AND isset($_SESSION[$_SESSION["applications"]->getActiveApplication()]["kategorien"])) $merge = array_merge($_SESSION[$_SESSION["applications"]->getActiveApplication()]["kategorien"], $_SESSION["nil"]["kategorien"]);
		elseif(isset($_SESSION[$_SESSION["applications"]->getActiveApplication()]["kategorien"])) $merge = $_SESSION[$_SESSION["applications"]->getActiveApplication()]["kategorien"];
		elseif(isset($_SESSION["nil"]["kategorien"])) $merge = $_SESSION["nil"]["kategorien"];
		else $merge = array();

		while($K = Registry::callNext("Kategorien", "cat")){
			$merge = array_merge($merge, $K);
		}
		Registry::reset("Kategorien");
		return $flip ? array_flip($merge) : $merge;
	}
	
	public function getCategoryFieldName(){
		return "type";
	}
	
	public static function typeParser($w){
		$ks = array_flip($_SESSION[$_SESSION["applications"]->getActiveApplication()]["kategorien"]);
		return (isset($ks[$w]) ? $ks[$w] : "Typ nicht registriert");
	}
	
	public static function nameParser($w, $l, $p){
		if($p == "mwst")
			return Util::CLNumberParserZ(Util::parseFloat("de_DE",str_replace("%","",$w)),"load")."%";
		
		else return $w;
	}

	public static function doSomethingElse(){
		if(!isset($_SESSION[$_SESSION["applications"]->getActiveApplication()]["kategorien"]))
			$_SESSION[$_SESSION["applications"]->getActiveApplication()]["kategorien"] = array();

		$_SESSION[$_SESSION["applications"]->getActiveApplication()]["kategorien"]["bitte auswählen"] = "0";
	}
	
	public function manageCostCategories(){
		$AC = anyC::get("Kategorie", "type", "costs");
		$AC->addOrderV3("KategorieID", "DESC");
		
		$GUI = new HTMLGUIX($AC);
		$GUI->attributes(array("name"));
		$GUI->options(true, false);
		
		$GUI->displayMode("popup");
		$GUI->parser("name", "KategorienGUI::parserCostsEntry");
		$GUI->addToEvent("onDelete", OnEvent::reload("Left").OnEvent::reloadPopup("Kategorien"));
		
		$B = new Button("Neue Umsatz-\nkategorie", "new");
		$B->style("margin:10px;");
		$B->rmePCR("Kategorien", -1, "costsNew", array(), OnEvent::reloadPopup("Kategorien"));
		
		$TC = new HTMLTable(2);
		$TC->setColWidth(1, 32);
		
		echo $B.$GUI->getBrowserHTML();
	}
	
	public static function parserCostsEntry($w, $E){
		$I = new HTMLInput("name", "text", $w);
		$I->activateMultiEdit("Kategorie", $E->getID(), OnEvent::reload("Left"));
		
		return $I;
	}
	
	public function costsNew(){
		$F = new Factory("Kategorie");
		$F->sA("type", "costs");
		$F->sA("name", "");
		$F->store();
	}
}
?>
