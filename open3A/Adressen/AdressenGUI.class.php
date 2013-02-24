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
 *  2007 - 2013, Rainer Furtmeier - Rainer@Furtmeier.IT
 */
class AdressenGUI extends Adressen implements iGUIHTMLMP2, iAutoCompleteHTML, icontextMenu, iSearchFilter {
	
	protected $gui;
	public $searchFields = array("nachname","vorname","firma","ort","strasse","kundennummer");
	public $inAC = false;
	public $searchLimit = 10;

	function __construct(){
		parent::__construct();
		$this->customize();
		$bps = $this->getMyBPSData();

		if(Session::isPluginLoaded("mAdressBuch")){
			$CAB = mAdressBuchGUI::getCurrent($this, false, true);
			
			if($CAB == "-2"){
				$this->addAssocV3("type","=","default", "AND", "1");
				
				$AC = anyC::get("AdressBuch");
				$AC->addAssocV3("AdressBuchUserID", "=", Session::currentUser()->getID());
				$AC->addAssocV3("AdressBuchTyp", "=", "2");

				while($AB = $AC->getNextEntry())
					$this->addAssocV3 ("type", "=", "AB".$AB->getID(), "OR", "1");
			}
			
			if($CAB == "0")
				$this->addAssocV3("type","=","default");
			
			if($CAB > 0){
				$AB = new AdressBuch($CAB);
				if($AB->A("AdressBuchTyp") == "1" OR $AB->A("AdressBuchTyp") == "3" OR
					($AB->A("AdressBuchTyp") == "2" AND $AB->A("AdressBuchUserID") == Session::currentUser()->getID()))
					$this->addAssocV3("type","=","AB".$bps["AdressBuch"]);
			}
		}
		
		/*if($bps == -1 OR !isset($bps["AdressBuch"]) OR $bps["AdressBuch"] == 0) $this->addAssocV3("type","=","default");
		else {
			$AB = new AdressBuch($bps["AdressBuch"]);
			if($AB->A("AdressBuchTyp") == "1" OR $AB->A("AdressBuchTyp") == "3" OR
				($AB->A("AdressBuchTyp") == "2" AND $AB->A("AdressBuchUserID") == Session::currentUser()->getID()))
				$this->addAssocV3("type","=","AB".$bps["AdressBuch"]);
		}*/

		$this->gui = new HTMLGUI();
		if($bps != -1 AND isset($bps["selectionMode"]) AND (strpos($bps["selectionMode"], "Vertrag") !== false OR strpos($bps["selectionMode"], "Bestellung") !== false OR strpos($bps["selectionMode"], "CloudKunde") !== false OR strpos($bps["selectionMode"], "Einsatzort") !== false)){
			$this->gui = new HTMLGUI2();
			$this->gui->setDisplaySide("right");
		}
	}
	
	function getHTML($id, $page){
		#$this->addJoinV3("Kappendix", "AdresseID", "=", "AdresseID"); //this is no good idea, unless with an index on Kappendix::AdresseID column
		try {
			new CRMHTMLGUI();
			$id = -1;
		} catch(ClassNotFoundException $e){ }
		$gui = $this->gui;
		
		$gui->VersionCheck("Adressen");
		
		$gui->setName("Adresse");
		$gui->setObject($this);
		$gui->tip();

		$gui->setParser("firma","AdressenGUI::firmaParser",array("\$sc->vorname","\$sc->nachname","\$aid", "\$type", "\$tel", "\$fax", "\$email", "\$mobil", "\$homepage", __CLASS__));

		$gui->customize($this->customizer);
		$gui->showFilteredCategoriesWarning($this->filterCategories(), "Adressen");
		$gesamt = $this->loadMultiPageMode($id, $page, 0);

		if(get_class($this) == "AdressenGUI"){
			$_SESSION["BPS"]->setActualClass("adressenMode");
			$_SESSION["BPS"]->unsetACProperty("adressenMode");
		}

		$tab = "";
		if(Session::isPluginLoaded("mAdressBuch") AND $id == -1)
			$tab = mAdressBuchGUI::getSelectionMenu($this, "contentRight", false, true);
		
		$gui->isQuickSearchable(str_replace("GUI","",get_class($this)));
		$gui->setMultiPageMode($gesamt, $page, 0, "contentRight", str_replace("GUI","",get_class($this)));
		if($this->collector != null) $gui->setAttributes($this->collector);
		
		$gui->setDisplayGroup("KategorieID");
		$gui->setDisplayGroupParser("AdressenGUI::DGParser");
		
		$gui->setShowAttributes(array("firma"));
		
		$gui->setJSEvent("onNew","function() { contentManager.reloadFrameRight(); }");
		

		$bps = $this->getMyBPSData();

		$gui->autoCheckSelectionMode(get_class($this));
		return ($id == -1 ? $tab : "").$gui->getBrowserHTML($id);
	}
	
	public static function DGParser($w, $l, $p = null){
		if($w == 0)
			return "-";
		
		$k = new Kategorie($w);
		return $k->getA() != null ? $k->getA()->name : "Kategorie unbekannt";
	}

	public static function getContactButton($kundennummer){
		$BKontakt = new Button("Kontaktdaten anzeigen", "./images/i2/telephone.png", "icon");
		$BKontakt->style("float:right;");
		$BKontakt->popup("name", "Kontaktdaten", "Adressen", -1, "getContactPopup", $kundennummer);

		return $BKontakt;
	}

	public function getContactPopup($kundennummer){
		$A = new Adresse(Kappendix::getAdresseIDToKundennummer($kundennummer));

		$T = new HTMLTable(2);
		$T->setColWidth(1, 120);

		$T->addRow(array($A->getHTMLFormattedAddress()));
		$T->addRowColspan(1, 2);

		if($A->getA() == null) die($T);

		if($A->A("tel") != "") $T->addLV("Telefon:", $A->A("tel"));
		if($A->A("fax") != "") $T->addLV("Fax:", $A->A("fax"));
		if($A->A("mobil") != "") $T->addLV("Mobil:", $A->A("mobil"));
		if($A->A("email") != "") $T->addLV("E-Mail:", $A->A("email"));

		if(!Session::isPluginLoaded("mAnsprechpartner")) die($T);

		$AC = Ansprechpartner::getAnsprechpartner("Adresse", $kundennummer);
		$TAP = new HTMLTable(2);
		$TAP->setColWidth(1, 120);
		while($AP = $AC->getNextEntry()){
			if(trim($AP->A("AnsprechpartnerVorname")." ".$AP->A("AnsprechpartnerNachname")) != ""){
				$TAP->insertSpaceAbove($AP->A("AnsprechpartnerPosition"));
				$TAP->addLV("Name:", $AP->A("AnsprechpartnerVorname")." ".$AP->A("AnsprechpartnerNachname"));
				if($AP->A("AnsprechpartnerTel") != "") $TAP->addLV("Telefon:", $AP->A("AnsprechpartnerTel"));
				if($AP->A("AnsprechpartnerEmail") != "") $TAP->addLV("E-Mail:", $AP->A("AnsprechpartnerEmail"));
			}
		}

		echo $T.$TAP;
	}

	public function getACData($attributeName, $query){
		$this->setSearchStringV3($query);
		$this->setSearchFieldsV3(array("firma", "nachname", "email"));

		$this->setFieldsV3(array("firma AS label", "AdresseID AS value", "vorname", "nachname", "CONCAT(strasse, ' ', nr, ', ', plz, ' ', ort) AS description","email", "firma"));
		$this->setLimitV3("10");
		$this->setParser("label", "AdressenGUI::parserACLabel");
		if($attributeName == "SendMailTo")
			$this->addAssocV3 ("email", "!=", "");
		#$this->setParser("email", "AdressenGUI::parserACEmail");
		
		Aspect::joinPoint("query", $this, __METHOD__, $this);
		
		echo $this->asJSON();
	}
	
	public static function parserACEmail($w, $m, $E){
		$name = trim($E->A("firma"));
		if($name == "")
			$name = trim(trim($E->A("vorname"))." ".trim($E->A("nachname")));
		
		if($name == "")
			return $E->A("email");
		
		$name = str_replace("<", "", $name);
		
		return trim($name." <".$E->A("email").">");
	}
	
	public static function parserACLabel($w, $m, $E){
		return $w != "" ? $w : trim($E->A("vorname")." ".$E->A("nachname"));
	}
	
	public function getACHTML($attributeName, $query){
		$this->inAC = true;
		$gui = $this->gui;
		
		switch($attributeName){
			case "plz":
				$this->addAssocV3("plz","LIKE", $query."%");
				$this->addGroupV3("plz");
				$this->setFieldsV3(array("plz","ort"));
				$this->setLimitV3("10");
				$this->lCV3();
				
				#$gui->setAttributes($this->collector);
				$gui->setObject($this);
				$gui->setShowAttributes(array("plz","ort"));
				echo $gui->getACHTMLBrowser();
			break;
			case "quickSearchAdressen":
			case "quickSearchmAdresse":
				$mode = "quickSearchLoadFrame";
				$hasNr = false;
				if(is_numeric($query)){
					#$this->addAssocV3("kundennummer", "LIKE", "%".$query."%");
					$this->addAssocV3("kundennummer", "=", $query);
					$this->addJoinV3("Kappendix","AdresseID","=","AdresseID");
					$hasNr = true;
				} else {
					unset($this->searchFields[array_search("kundennummer", $this->searchFields)]);
					$this->setSearchStringV3($query);
					$this->setSearchFieldsV3($this->searchFields);
				}
				
				$this->setLimitV3($this->searchLimit);
				
				Aspect::joinPoint("query", $this, __METHOD__, array($attributeName, $query));
				
				$this->lCV3();

				if(!$hasNr AND $this->numLoaded() > 0){
					$AC = anyC::get("Kappendix");
					while($A = $this->getNextEntry())
						$AC->addAssocV3("AdresseID", "=", $A->getID(), "OR");
					
					$this->resetPointer();
					
					$kundennummern = array();
					while($K = $AC->getNextEntry())
						$kundennummern[$K->A("AdresseID")] = $K->A("kundennummer");
					
					
					while($A = $this->getNextEntry()){
						if(!isset($kundennummern[$A->getID()]))
							continue;
						
						$A->changeA("kundennummer", $kundennummern[$A->getID()]);
					}
					
					$this->resetPointer();
				}
				
				$gui->setObject($this);
				#$gui->setAttributes($this->collector);
				$gui->setShowAttributes(array("kundennummer","firma"));
				
				#$gui->setParser("firma","AdressenGUI::ACFirmaParser",array("\$sc->nachname","\$sc->vorname"));
				$gui->setParser("firma","AdressenGUI::ACFirmaParser",array("\$sc->nachname","\$sc->vorname","\$sc->plz","\$sc->ort","\$sc->strasse"));
				
				$_SESSION["BPS"]->registerClass(get_class($gui));
				$_SESSION["BPS"]->setACProperty("targetFrame","contentLeft");
				$_SESSION["BPS"]->setACProperty("targetPlugin","Adresse");
				$gui->autoCheckSelectionMode(get_class($this));
				$gui->customize($this->customizer);
				echo $gui->getACHTMLBrowser($mode);
			break;

		}
	}
	
	public static function ACFirmaParser($w,$l,$p){
		$s = HTMLGUI::getArrayFromParametersString($p);
		return ($w != "" ? $w.($s[0] != "" ? "<br /><small>$s[1] $s[0]</small>" : "") : "$s[1] $s[0]")."<br /><small style=\"color:grey;\">$s[2] $s[3]".($s[4] != "" ? ", $s[4]" : "")."</small>";
	}
	
	public function getContextMenuHTML($identifier){
		echo "<p style=\"padding:5px;\">Es werden folgende Felder durchsucht:<br /><br />Kundennummer<br />Firma<br />Vorname<br />Nachname<br />Straße<br />Ort</p><p>Sie können Ihre Suchanfrage mit UND verknüpfen.<br />Also z.B. \"Firmenname UND Ort\"</p>
			<p><img src=\"./images/i2/searchFilter.png\" style=\"float:left;margin-right:5px;\" /> Bei der Filterung nach einem Suchbegriff wird die Kundennummer nicht berücksichtigt.";

	}
	
	public function saveContextMenu($identifier, $key){}
	
	public static function firmaParser($w,$a,$p){
		if(!is_array($p))
			$s = HTMLGUI::getArrayFromParametersString($p);
		else
			$s = $p;
		
		$SM = BPS::getProperty("AdressenGUI", "selectionMode", false);
		if($s[9] == "mAdresseGUI")
			$SM = BPS::getProperty("mAdresseGUI", "selectionMode", false);
		
		$symbols = "";
		if($s[8] != "") $symbols .= "<a href=\"$s[8]\" target=\"_blank\"><img class=\"mouseoverFade\" style=\"float:right;margin-left:4px;\" src=\"./images/i2/flowers.gif\" title=\"$s[8]\" /></a>";
		if($s[7] != "") {
			if(Session::isPluginLoaded("mGemeinschaft")){
				$B = Gemeinschaft::getCallButton($s[7], "mobile");
				$B->style("float:right;margin-left:4px;");
				$symbols .= $B;
			} else
				$symbols .= "<img class=\"mouseoverFade\" style=\"float:right;margin-left:4px;\" src=\"./images/i2/mobile.png\" title=\"$s[7]\" />";
		}
		if($s[6] != "") $symbols .= "<a href=\"mailto:$s[6]\"><img class=\"mouseoverFade\" style=\"float:right;margin-left:4px;\" src=\"./images/i2/email.png\" title=\"$s[6]\" /></a>";
		if($s[5] != "") $symbols .= "<img class=\"mouseoverFade\" style=\"float:right;margin-left:4px;\" src=\"./images/i2/fax.png\" title=\"$s[5]\" />";
		if($s[4] != "") {
			if(Session::isPluginLoaded("mGemeinschaft")){
				$B = Gemeinschaft::getCallButton($s[7]);
				$B->style("float:right;margin-left:4px;");
				$symbols .= $B;
			} else
				$symbols .= "<img class=\"mouseoverFade\" style=\"float:right;margin-left:4px;\" src=\"./images/i2/telephone.png\" title=\"$s[4]\" />";
		}
		return $symbols.(($_SESSION["S"]->checkForPlugin("Kunden") AND $s[3] == "default" AND !$SM) ? 
		"<img src=\"./images/i2/kunde.png\" title=\"Kundendaten anzeigen/erstellen\" onclick=\"contentManager.selectRow(this); contentManager.loadFrame('contentLeft', 'Kunde', -1, 0, 'KundeGUI;AdresseID:$s[2];action:Kappendix');\" style=\"float:left;margin-right:4px;\" class=\"mouseoverFade\" />" : "")
		.(($_SESSION["S"]->checkForPlugin("Kundenpreise") AND $s[3] == "default" AND !$SM) ? "<img src=\"./images/i2/kundenpreis.png\" title=\"Kundenpreise festlegen\" onclick=\"contentManager.selectRow(this); contentManager.loadFrame('contentLeft','Kunde', -1, 0,'KundeGUI;AdresseID:$s[2];action:Kundenpreise');\" style=\"float:left;margin-right:4px;\" class=\"mouseoverFade\" />" : "")
		.(($_SESSION["S"]->checkForPlugin("labelPrinter") AND $s[3] == "default") ? "<img src=\"./images/i2/printer.png\" title=\"Etikette mit Adresse drucken\" onclick=\"rme('labelPrinter','','printEtikette','$s[2]');\" style=\"float:left;margin-right:4px;\" class=\"mouseoverFade\" />" : "")
		.($w != "" ? stripslashes($w).(($s[1] != "" OR $s[0] != "") ? "<br /><small>$s[0] $s[1]</small>" : "") : $s[0]." ".$s[1]);
	}

	public static function doSomethingElse(){
		if(!isset($_SESSION[$_SESSION["applications"]->getActiveApplication()]["kategorien"]))
			$_SESSION[$_SESSION["applications"]->getActiveApplication()]["kategorien"] = array();

		$_SESSION[$_SESSION["applications"]->getActiveApplication()]["kategorien"]["Adressen"] = "1";
	}

	public function openHP($url){
		header("Location: ".(substr($url, 0, 4) != "http" ? "http://" : "")."$url");
		exit();
	}

	public function getSearchedFields() {
		$SF = $this->searchFields;
		unset($SF[array_search("kundennummer", $SF)]);
		return $SF;
	}
}
?>
