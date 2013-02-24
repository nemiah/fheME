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
class AdresseGUI extends Adresse implements /*iFPDF, */iGUIHTML2 {
	
	protected $gui;
	
	public function __construct($ID){
		parent::__construct($ID);

		$this->gui = new HTMLGUI2();
		#$this->setParser("geb", "Util::CLDateParserE");
	}
	
	function getHTML($id){
		$forReload = "";
		$displayMode = null;
		$AuftragID = -1;
		
		$bps = $this->getMyBPSData();
		if($bps != -1 AND isset($bps["AuftragID"]))
			$AuftragID = $bps["AuftragID"];

		if($bps != -1 AND isset($bps["displayMode"]))
			$displayMode = $bps["displayMode"];

		$_SESSION["BPS"]->unsetACProperty("AuftragID");
		$_SESSION["BPS"]->unsetACProperty("displayMode");
		
		#if($this->A == null AND $id != -1) $this->loadMe();
		$this->loadMeOrEmpty();
		
		if($id * 1 == -1) {
			$this->A = $this->newAttributes();
			$this->A->AuftragID = $AuftragID;
			if($displayMode != null) $this->A->type = $displayMode; //Has to stay or lieferAdresse will also overwrite a normal Auftrags-Adresse

			if(Session::isPluginLoaded("mAdressBuch")){
				$AB = BPS::getProperty("AdressenGUI", "AdressBuch", null);
				if($AB)
					$this->A->type = "AB$AB";
			}
			
			$id = $this->newMe(true, false);
			$this->forceReload();

			try {
				$K = new Kunden();
				if($displayMode == null AND $this->A("type") == "default") //Or else a lieferAdresse will get a Kundennummer
					$K->createKundeToAdresse($id,false);
			} catch(ClassNotFoundException $e) {}

			$forReload = "<script type=\"text/javascript\">lastLoadedLeft = $id; lastLoadedLeftPlugin = 'Adresse';</script>";

		}
		
		$OptTab = new HTMLSideTable("right");

		if(Session::isPluginLoaded("Kunden") AND $this->A->AuftragID == -1){
			$B = new Button("Kundendaten","kunden");
			$B->loadFrame("contentLeft", "Kunde", "-1", "0", "KundeGUI;AdresseID:{$this->getID()};action:Kappendix");
			$OptTab->addRow($B);
		}
		
		if($_SESSION["applications"]->getActiveApplication() == "open3A"){
			if($displayMode != null)
				$OptTab->setTableStyle("width:160px;margin:0px;margin-left:-170px;float:left;");

			if(($id == -1 OR $forReload != "") AND Session::isPluginLoaded("mImport")) {
				$OTBV = new Button("Schnell-\nImport","import");
				#$OTBV->rmePCR("importAdresse", "", "getFastImportWindow", "", "Popup.display('Adresse importieren:',transport);");
				$OTBV->onclick("Import.openSchnellImportAdresse('Adresse importieren:');");
				$OTBV->id("ButtonAdresseSchnellImport");

				$OptTab->addRow($OTBV);

			}

			if($id != -1 AND Session::isPluginLoaded("Kundenpreise") AND $this->A->AuftragID == -1 AND $this->A->type == "default"){
				$ButtonKundenpreise = new Button("Kundenpreise","package");
				$ButtonKundenpreise->onclick("contentManager.loadFrame('contentLeft','Kunde', -1, 0, 'KundeGUI;AdresseID:$this->ID;action:Kundenpreise');");

				$OptTab->addRow($ButtonKundenpreise);
			}
		
			if($this->A->AuftragID == -1 AND (Session::isPluginLoaded("mAnsprechpartner") OR Session::isPluginLoaded("mOSM"))){
				$OptTab->addRow("");
				$OptTab->addCellStyle(1, "height:30px;");
			}
			
			if($id != -1 AND Session::isPluginLoaded("mAnsprechpartner") AND $this->A->AuftragID == -1)
				$OptTab->addRow(Ansprechpartner::getButton("Adresse", $this->getID()));
			
			if($id != -1 AND Session::isPluginLoaded("mOSM") AND $this->A("AuftragID") == -1)
				$OptTab->addRow(OpenLayers::getButton("Adresse", $this->getID()));
			
		}
		
		if(Session::isPluginLoaded("mklickTel") AND $this->A->AuftragID == -1)
			$OptTab->addRow(klickTel::getButton($this->getID()));
		

		$this->loadMeOrEmpty();

		$gui = $this->gui;
		$gui->insertSpaceAbove("tel", "Kontakt");
		$gui->insertSpaceAbove("strasse", "Adresse");
		$gui->insertSpaceAbove("homepage", "Sonstiges");
		$gui->setFormID("AdresseForm");

		$fields = array(
			"firma",
			"position",
			"anrede",
			"vorname",
			"nachname",
			"AdresseSpracheID",
			"strasse",
			"bezirk",
			/*"nr",*/
			"zusatz1",
			#"plz",
			"ort",
			"land",
			"tel",
			"fax",
			"mobil",
			"email",
			"homepage",
			"gebRem",
			"gebRemMail",
			"AuftragID",
			"KategorieID",
			"type",
			"geb",
			"bemerkung");
		
		if(Session::isPluginLoaded("mLDAP"))
			$fields[] = "exportToLDAP";

		$gui->setShowAttributes($fields);
		
		$gui->setLabel("bemerkung", "Notizen");
		
		if(Session::isPluginLoaded("mSprache")) {
			$gui->setLabel("AdresseSpracheID","Sprache");
			$gui->setLabelDescription("AdresseSpracheID", "und Währung");
			#$ACS = anyC::get("Sprache");
			#$S = array();
			#while($SLW = $ACS->getNextEntry())
			#	$S[$SLW->getID()] = $SLW->A("SpracheSprache")." "." ".$SLW->A("SpracheWaehrung");
			$gui->selectWithCollection("AdresseSpracheID", new mSprache(), "SpracheName");
			#$gui->setType("AdresseSpracheID", "select");
			#$gui->setOptions("AdresseSpracheID", array_keys($S), array_values($S));
			#$gui->insertSpaceAbove("firma");
		} else $gui->setType("AdresseSpracheID","hidden");
		
		#$gui->setAttributes($this->A);
		$gui->setObject($this);
		$gui->setName("Adresse");
		
		$gui->setOptions("anrede", array_keys(self::getAnreden()), array_values(self::getAnreden()));
		$gui->setType("anrede","select");

		$gui->setType("geb","hidden");
		$gui->setType("gebRemMail","hidden");
		$gui->setType("gebRem","hidden");
		$gui->setType("exportToLDAP","checkbox");

		$gui->insertSpaceAbove("exportToLDAP");
		$gui->setFieldDescription("exportToLDAP", "Soll die Adresse auf einen LDAP-Server exportiert werden?");
		
		$gui->setType("AuftragID","hidden");
		$gui->setType("type","hidden");

		#$gui->setLabel("geb","Jahrestag");
		$gui->setLabel("ort","PLZ/Ort");
		$gui->setLabel("strasse","Straße/Hausnr.");
		$gui->setLabel("tel","Telefon");
		$gui->setLabel("email","E-Mail");
		$gui->setLabel("exportToLDAP","LDAP-Export?");
		
		$gui->setParser("strasse", "AdresseGUI::parserStrasse", array($this->A("nr")));
		$gui->setParser("ort", "AdresseGUI::parserOrt", array($this->A("plz")));
		
		#$gui->useAutoCompletion("plz", (Session::isPluginLoaded("Postleitzahlen") ? "Postleitzahlen" : "Adressen"));
		if(Session::isPluginLoaded("mStammdaten") OR Applications::activeApplication() == "MMDB"){
			/*if($this->A("land") == ""){
				$S = Stammdaten::getActiveStammdaten();
				if($S->A("land") == "D") $S->changeA("land","DE");
				$this->changeA("land", ISO3166::getCountryToCode($S->A("land")));
			}*/
			
			$gui->setType("land","select");

			$countries = ISO3166::getCountries();
			$labels = array_merge(array("" => "keine Angabe"), $countries);
			$values = array_merge(array("" => ""), $countries);
			$gui->setOptions("land", array_values($values), array_values($labels));

			if($this->A("land") != ISO3166::getCountryToCode("GB") AND $this->A("land") != ISO3166::getCountryToCode("US") AND $this->A("land") != ISO3166::getCountryToCode("CH")) {
				$gui->setLineStyle("zusatz1", "display:none;");
				$gui->setLineStyle("position", "display:none;");
			}
			
			if($this->A("land") != ISO3166::getCountryToCode("DK") AND $this->A("land") != ISO3166::getCountryToCode("ES"))
				$gui->setLineStyle("bezirk", "display:none;");
			

			$gui->setLabel("zusatz1", "Zusatz 1");
			
			$gui->setInputJSEvent("land", "onchange", "contentManager.toggleFormFields((this.value == '".ISO3166::getCountryToCode("GB")."' || this.value == '".ISO3166::getCountryToCode("US")."' || this.value == '".ISO3166::getCountryToCode("CH")."') ? 'show' : 'hide', ['zusatz1', 'position']); contentManager.toggleFormFields((this.value == '".ISO3166::getCountryToCode("DK")."' || this.value == '".ISO3166::getCountryToCode("ES")."') ? 'show' : 'hide', ['bezirk']);");
		}

		if(Session::isPluginLoaded("mGemeinschaft")){
			$gui->activateFeature("addCustomButton", $this, "tel", Gemeinschaft::getCallButton($this->A("tel")));
			$gui->activateFeature("addCustomButton", $this, "mobil", Gemeinschaft::getCallButton($this->A("mobil"), "mobile"));
		}

		
		$kat = new Kategorien();
		$kat->addAssocV3("type","=",($displayMode != "" ? $displayMode : "1" ));
		$keys = $kat->getArrayWithKeys();
		$keys[] = "0";
		
		$values = $kat->getArrayWithValues();
		$values[] = "bitte auswählen";
		
		$gui->setOptions("KategorieID", $keys, $values);
		$gui->setType("bemerkung","textarea");
		
		$gui->setLabel("KategorieID","Kategorie");
		
		if($AuftragID == -1) $gui->setType("KategorieID","select");
		else {
			$gui->setType("KategorieID","hidden");
			$gui->setType("bemerkung","hidden");
			$gui->setType("tel","hidden");
			$gui->setType("fax","hidden");
			$gui->insertSpaceAbove("email");
			$gui->setType("homepage","hidden");
			$gui->setType("exportToLDAP","hidden");
			$gui->setType("mobil","hidden");
		}

		switch($displayMode){
			case "auftragsAdresse":
				$gui->setJSEvent("onSave","function() {
					contentManager.loadFrame('contentLeft','Auftrag',{$this->A->AuftragID});
					contentManager.loadFrame('contentRight','Auftraege');
				}");
			break;

			case "lieferAdresse":
				$this->A->type = "lieferAdresse";
				$gui->setJSEvent("onSave","function() {
					contentManager.loadFrame('contentRight','Auftraege');
					contentManager.loadFrame('subframe','GRLBM',{$this->A->AuftragID});
				}");
			break;
		}

		Aspect::joinPoint("buttons", $this, __METHOD__, $OptTab);
		
		$gui->setStandardSaveButton($this, "Adressen");
		
		$gui->customize($this->customizer);

		return $forReload.$OptTab.$gui->getEditHTML();
	}

	public static function parserStrasse($w, $l, $p){
		
		$I1 = new HTMLInput("strasse", "text", $w);
		$I1->style("width:185px;margin-right:10px;");
		$I1->id("strasse");
		
		if(is_object($p)){
			if($p instanceof AkquiseGUI)
				$I1->style("width:170px;margin-right:10px;");
			
			$p = $p->A("nr");
		}
		
		$I2 = new HTMLInput("nr", "text", $p);
		$I2->style("width:50px;text-align:right;");
		$I2->id("nr");
		
		return $I1.$I2;
	}

	public static function parserOrt($w, $l, $p){
		
		$I1 = new HTMLInput("ort", "text", $w);
		$I1->style("width:185px;");
		$I1->id("ort");
		
		if(is_object($p)){
			if($p instanceof AkquiseGUI)
				$I1->style("width:170px;");
			
			$p = $p->A("plz");
		}
		
		$I2 = new HTMLInput("plz", "text", $p);
		$I2->style("width:50px;text-align:right;margin-right:10px;");
		$I2->id("plz");
		
		return $I2.$I1;
	}
	
	public function saveMe($checkUserData = true, $output = false, $deleteBPS = true){
		if($deleteBPS){
			$_SESSION["BPS"]->setActualClass(get_class($this));
			$_SESSION["BPS"]->unsetACProperty("edit");
		}
			
		parent::saveMe($checkUserData, $output);
	}
	
	public function getXML(){
		$xml = parent::getXML();
		$lines = explode("\n",$xml);
		foreach($lines as $k => $v)
			$lines[$k] = str_pad(($k + 1),5, " ", STR_PAD_LEFT).": ";
			
		echo Util::getBasicHTML("<pre class=\"backgroundColor2\" style=\"font-size:9px;float:left;\">".implode("\n",$lines)."</pre><pre class=\"backgroundColor0\" style=\"font-size:9px;margin-left:40px;\">".htmlentities(utf8_decode($xml))."</pre>","XML-Export");
	}

        #EE ab hier
    public static function testAusgabe($p1, $p2){
		Red::alertD("IDNr1: $p1; IDNr2: $p2");
	}
	
	public function getHTMLFormattedAddress($echo = false) {
		$A = parent::getHTMLFormattedAddress();
		
		if($echo)
			echo $A;
		
		return $A;
	}
	
	public function ACLabel(){
		return $this->getShortAddress();
	}
}
?>
