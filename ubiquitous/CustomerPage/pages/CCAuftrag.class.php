<?php
/**
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
 *  2007 - 2017, Furtmeier Hard- und Software - Support@Furtmeier.IT
 */

ini_set('session.gc_maxlifetime', 24 * 60 * 60);

class CCAuftrag extends CCPage implements iCustomContent {
	
	protected $showZahlungsart = false;
	protected $showButtonEditAddress = false;
	protected $showButtonCheckWithGoogle = false;
	protected $showPrices = true;
	protected $showPosten = true;
	function __construct() {
		$this->customize();
		
		if(get_class($this) != "CCAuftrag"){
			parent::__construct();
			return;
		}
		
		/*if(!isset($_POST["benutzer"])){
			$_POST["benutzer"] = "Max";
			$_POST["password"] = sha1("Max");
			$_POST["belegart"] = "R";
			$_POST["lead_id"] = "Vicidial-001";
			$_POST["firma"] = "Furtmeier Hard- und Software";
			$_POST["strasse"] = "Neuteile";
			$_POST["nr"] = "8";
			$_POST["plz"] = "86682";
			$_POST["ort"] = "Genderkingen";
		}*/
		$this->loggedIn = true;
		if(Session::currentUser() == null AND !Users::login($_POST["benutzer"], $_POST["password"], "open3A"))
			$this->loggedIn = false;
		
		$this->showZahlungsart = true;
		$this->showButtonEditAddress = true;
		$this->showButtonCheckWithGoogle = true;
	}
	
	function getTitle(){
		return $this->getLabel();
	}
	
	function getLabel(){
		return "Auftragserfassung";
	}
	
	function getCMSHTML() {
		switch($_GET["page"]){
			case "1":
				return "Der Auftrag wurde erfolgreich erfasst!";
			break;
		
			case "2":
				return "Der Auftrag wurde abgebrochen!";
			break;
		
			case "start":
				if(!$this->loggedIn)
					return "Die Anmeldung ist fehlgeschlagen!";
				
				$GRLBMID = $this->createAuftrag($this->checkAdresse(), isset($_POST["belegart"]) ? $_POST["belegart"] : "R");

				return "<script type=\"text/javascript\" src=\"https://maps.google.com/maps/api/js?sensor=false\"></script>
				<div style=\"max-width:1200px;\">
					<div style=\"display:inline-block;width:48%;vertical-align:top;margin-right:3%;\" id=\"contentLeft\">
						".$this->getAuftrag(array("GRLBMID" => $GRLBMID))."
					</div>
					<div style=\"display:inline-block;width:48%;vertical-align:top;\" id=\"contentRight\">
						".$this->getArtikel(array("KategorieID" => "", "query" => "", "GRLBMID" => $GRLBMID))."
					</div>
				</div>".OnEvent::script("$(window).bind('beforeunload', function(){
	$('#contentLeft').html('<p>Die Auftragserfassung wurde abgebrochen.</p>');
	$('#contentRight').html('');
	$(window).unbind('beforeunload');
	CustomerPage.rme('cancelAuftrag', {GRLBMID: $GRLBMID});
    return \"Bitte verwenden Sie die Knöpfe 'Abbrechen' oder 'Abschließen'\";});");
			break;
			
			default:
				return "Nichts zu tun!";
		}
	}
	
	private function checkAdresse(){
		if(!isset($_POST["lead_id"]))
			die("Keine Lead-ID übergeben!");
		
		$S = anyC::get("Sync", "SyncGUID", $_POST["lead_id"]);
		$S->addAssocV3("SyncOwnerClass", "=", "Adresse");
		
		$Sync = $S->getNextEntry();
		
		#if($Sync == null){
		$Adresse = new Adresse(-1);
		$A = $Adresse->newAttributes();

		$F = new Factory("Adresse", $Sync == null ? -1 : $Sync->A("SyncOwnerClassID"));
		foreach($_POST AS $k => $v){
			if(!isset($A->$k))
				continue;

			$F->sA($k, $v);
		}

		$F->store();

		$Adresse = $F->gO();
		
		if($Sync == null){
			$K = new Kunden();
			$K->createKundeToAdresse($Adresse->getID());
			mSync::updateGUID("Adresse", $Adresse->getID(), $_POST["lead_id"]);
		}
		
		return $Adresse;
	}
	
	private function createAuftrag(Adresse $Adresse, $type){
		$Auftrag = new Auftrag(-1);
		$ID = $Auftrag->newWithDefaultValues($Adresse->getID());
		
		return $Auftrag->createGRLBM($type, true);
	}
	
	public function getAuftrag($data){
		$Beleg = new GRLBM($data["GRLBMID"]);#$this->createAuftrag(new Adresse(1), "W");

		
		
		$TSumme = new HTMLTable(3);
		$TSumme->setTableStyle("width:100%;border-top:1px solid #AAA;margin-top:30px;");
		$TSumme->setColWidth(1, 26);
		$TSumme->addColStyle(3, "text-align:right;");
		$TSumme->addRow(array("", "Netto: <b>".Util::CLFormatCurrency($Beleg->A("nettobetrag")*1, true)."</b>", "Brutto: <b>".Util::CLFormatCurrency($Beleg->A("bruttobetrag")*1, true)."</b>"));
		
		
		
		$IZahlungsart = new HTMLInput("zahlungsart", "select", $Beleg->A("GRLBMpayedVia"), GRLBM::getPaymentVia(null, array("transfer", "debit")));
		$IZahlungsart->onchange("if(this.value == 'debit') $('#rowZahlungsart').show(); else $('#rowZahlungsart').hide(); CustomerPage.timeout = window.setTimeout(CustomerPage.saveKontodaten, 300);");
		
		$IBankleitzahl = new HTMLInput("bankleitzahl", "text", $Beleg->A("GRLBMBankleitzahl"));
		$IBankleitzahl->placeholder("Bankleitzahl");
		$IBankleitzahl->style("margin-top:10px;text-align:right;width:130px;");
		$IBankleitzahl->onkeyup("if(CustomerPage.timeout != null) window.clearTimeout(CustomerPage.timeout); CustomerPage.timeout = window.setTimeout(CustomerPage.saveKontodaten, 300);");
		
		$IKontonummer = new HTMLInput("kontonummer", "text", $Beleg->A("GRLBMKontonummer"));
		$IKontonummer->placeholder("Kontonummer");
		$IKontonummer->style("margin-top:10px;text-align:right;margin-left:10px;width:130px;");
		$IKontonummer->onkeyup("if(CustomerPage.timeout != null) window.clearTimeout(CustomerPage.timeout); CustomerPage.timeout = window.setTimeout(CustomerPage.saveKontodaten, 300);");
		#$IBIC = new HTMLInput("bic");
		#$IIBAN = new HTMLInput("iban");
		
		$BCheck = new Button("Prüfung", "question_mark", "iconic");
		$BCheck->id("ktoCheck");
		$BCheck->style("margin-left:8px;");
		
		$TZahlungsart = new HTMLTable(2, "Zahlungsart");
		$TZahlungsart->setTableStyle("width:100%;");
		$TZahlungsart->setColWidth(1, 26);
		
		$TZahlungsart->addRow(array(new Button("Zahlungsart", "pin", "iconic"), $IZahlungsart));
		$TZahlungsart->addRowColspan(2, 2);
		$TZahlungsart->addCellStyle(1, "vertical-align:top;");
		
		$TZahlungsart->addRow(array("", $IBankleitzahl." ".$IKontonummer."$BCheck <div id=\"bankMessage\" style=\"margin-top:6px;\">&nbsp;</div>"));
		$TZahlungsart->setRowID("rowZahlungsart");
		if($Beleg->A("GRLBMpayedVia") != "debit")
			$TZahlungsart->addRowStyle("display:none;");
		else
			echo OnEvent::script("CustomerPage.saveKontodaten();");
		
		$IBemerkung = new HTMLInput("bemerkung", "textarea", $Beleg->A("textbausteinUnten"));
		$IBemerkung->style("width:100%;height:100px;margin-top:40px;");
		$IBemerkung->placeholder("Bemerkungen");
		#$IBemerkung->onblur("");
		$IBemerkung->onkeyup("if(CustomerPage.timeout != null) window.clearTimeout(CustomerPage.timeout); CustomerPage.timeout = window.setTimeout(CustomerPage.saveBemerkung, 300);");
		
		$TZahlungsart->addRow(array(new Button("Bemerkung", "document_alt_stroke", "iconic"), $IBemerkung));
		$TZahlungsart->addCellStyle(1, "vertical-align:top;padding-top:40px;");
		
		
		
		#$TZahlungsart->addCellStyle(3, "padding-top:50px;");
		
		$TFinish = new HTMLTable(1);
		$TFinish->setTableStyle("width:100%;");
		
		$TFinish->addRow(array($this->buttonCancel($data).$this->buttonDone($data)));
		$TFinish->addCellStyle(1, "padding-top:50px;");
		
		$html = "<h1>Auftrag ".$Beleg->A("prefix").$Beleg->A("nummer")."</h1>";
		
		$html .= $this->getAdresse($Beleg);
		
		if($this->showPosten)
			$html .= $this->getPosten($Beleg);
		
		if($this->showPrices)
			$html .= $TSumme;
		
		if($this->showZahlungsart)
			$html .= $TZahlungsart;
		
		$html .= $this->getBottom($Beleg);
		
		$html .= $TFinish;
		
		return $html.OnEvent::script("
			CustomerPage.timeout = null;
			CustomerPage.saveBemerkung  = function(){ CustomerPage.rme('setBemerkung', {GRLBMID: $data[GRLBMID], bemerkung: $('textarea[name=bemerkung]').val() }); };
			CustomerPage.saveKontodaten = function(){ CustomerPage.rme('setKontodaten', {
				GRLBMID: $data[GRLBMID],
				zahlungsart: $('select[name=zahlungsart]').val(),
				kontonummer: $('input[name=kontonummer]').val(),
				bankleitzahl: $('input[name=bankleitzahl]').val()
			}, function(t){ $('#bankMessage').html(t); }); };");
	}
	
	public function getBottom($Beleg){
		return "";
	}
	
	public function getAdresse($Beleg){
		$Auftrag = new Auftrag($Beleg->A("AuftragID"));
		$Adresse = new Adresse($Auftrag->A("AdresseID"));
		
		$BCheckK = "";
		if(Session::isPluginLoaded("mklickTel")){
			$BCheckK = new Button("Adresse mit klickTel prüfen", "compass", "iconic");
			$BCheckK->style("float:right;font-size:30px;margin-right:15px;");
			$BCheckK->onclick("CustomerPage.popup('Adressprüfung', 'checkAddressKlickTel', {AdresseID: {$Adresse->getID()}, GRLBMID: $data[GRLBMID]}, {modal: true, width: 500, resizable: false, position: ['center', 40]});");
			$BCheckK->id("BCheckKT");
			Aspect::joinPoint("modButtonKlickTel", $this, __METHOD__, array($BCheckK, $Auftrag));
		}
		
		$BUpdate = new Button("Adresse ändern", "pen_alt2", "iconic");
		$BUpdate->style("float:right;font-size:30px;margin-right:15px;");
		$BUpdate->onclick("CustomerPage.popup('Adresse ändern', 'alterAddress', {AdresseID: {$Adresse->getID()}, GRLBMID: $data[GRLBMID]}, {modal: true, width: 500, resizable: false, position: ['center', 40]});");
		if(!$this->showButtonEditAddress)
			$BUpdate = "";
		
		$BCheckG = new Button("Adresse mit Google prüfen", "compass", "iconic");
		$BCheckG->style("float:right;font-size:30px;");
		$BCheckG->onclick("CustomerPage.popup('Adressprüfung', 'checkAddressGoogle', {AdresseID: {$Adresse->getID()}, GRLBMID: $data[GRLBMID]}, {modal: true, width: 500, resizable: false, position: ['center', 40]});");
		$BCheckG->id("BCheckG");
		if(!$this->showButtonCheckWithGoogle)
			$BCheckG = "";
		
		Aspect::joinPoint("modButtonGoogle", $this, __METHOD__, array($BCheckG, $Auftrag));
		
		$TAdresse = new HTMLTable(2, "Kundenadresse");
		$TAdresse->setColWidth(1, 26);
		$TAdresse->setTableStyle("width:100%;");
		$TAdresse->addRow(array(new Button("Adresse", "home", "iconic"), $BCheckG.$BCheckK.$BUpdate.$Adresse->getHTMLFormattedAddress()));
		$TAdresse->setColStyle(1, "vertical-align:top;");
		
		return $TAdresse;
	}
	
	public function getScript(){
		return "var CCAuftrag = {
			lastValue: null,
			allowSave: false,
			lastTextbausteinUnten: null
		};";
	}
	
	public function getPosten(GRLBM $Beleg){
		$TPosten = new HTMLTable(8, "Auftragspositionen");
		$TPosten->setTableStyle("width:100%;");
		#$TPosten->addColStyle(3, "text-align:right;");
		$TPosten->addColStyle(5, "text-align:right;");
		$TPosten->addColStyle(6, "text-align:right;");
		$TPosten->addColStyle(7, "text-align:right;color:grey;");
		$TPosten->addColStyle(8, "text-align:right;");
		$TPosten->setColWidth(1, 26);
		$TPosten->setColWidth(2, 80);
		$TPosten->setColWidth(5, 80);
		$TPosten->setColWidth(6, 80);
		$TPosten->setColWidth(7, 80);
		$TPosten->setColWidth(8, 20);
		
		Aspect::joinPoint("alterTable", $this, __METHOD__, array($TPosten));
		
		$AC = anyC::get("Posten", "GRLBMID", $Beleg->getID());
		$AC->addOrderV3("PostenID");
		$i = 0;
		$O = new Button("Positionen", "list", "iconic");
		while($P = $AC->getNextEntry()){
			$B = new Button("Position löschen", "trash_stroke", "iconic");
			$B->onclick("CustomerPage.rme('delPosten', {PostenID: '".$P->getID()."'}, function(){ CustomerPage.rme('getAuftrag', {GRLBMID: ".$Beleg->getID()."}, function(transport){ $('#contentLeft').html(transport); }); });");
			
			$I = new HTMLInput("mwst", "text", Util::CLNumberParserZ($P->A("menge")));
			$I->style("text-align:right;width:80px;");
			$I->onEnter("\$j(this).trigger('blur');");
			$I->onfocus("CCAuftrag.lastValue = this.value; CCAuftrag.allowSave = true;");
			$I->onblur("if(CCAuftrag.lastValue != this.value && CCAuftrag.allowSave) CustomerPage.rme('setMenge', {PostenID: '".$P->getID()."', menge: this.value}, function(){ CustomerPage.rme('getAuftrag', {GRLBMID: ".$Beleg->getID()."}, function(transport){ $('#contentLeft').html(transport); }); }); CCAuftrag.allowSave = false;");
			
			$name = Aspect::joinPoint("alterName", $this, __METHOD__, array($P, $P->A("name")), $P->A("name"));
			$buttons = Aspect::joinPoint("alterButtons", $this, __METHOD__, array($P, $B), $B);
			
			$TPosten->addRow(array(
				$i == 0 ? $O : "",
				$I, 
				$P->A("gebinde"),
				$name,
				$this->showPrices ? Util::CLNumberParserZ($P->A("preis")) : "",
				$this->showPrices ? Util::CLNumberParserZ($P->A("menge") * $P->A("preis")) : "", 
				$this->showPrices ? Util::CLNumberParserZ($P->A("mwst"))."%" : "",
				$buttons));
			
			$i++;
		}
		if($AC->numLoaded() == 0){
			$TPosten->addRow(array($O, "Bitte fügen Sie einen Artikel hinzu."));
			$TPosten->addRowColspan(2, 6);
			$TPosten->setColWidth(2, "100%");
			$TPosten->setColStyle(2, "text-align:left;");
		}
		
		return $TPosten;
	}
	
	public function buttonCancel($data){
		$IC = new Button("Abbrechen");
		$IC->className("submitFormButton");
		$IC->style("background-color:#DDD;color:grey;float:none;");
		$IC->onclick("$(window).unbind('beforeunload'); CustomerPage.rme('cancelAuftrag', {GRLBMID: $data[GRLBMID]}, function(){ document.location.href='?CC=Auftrag&page=2'; });");
		
		return $IC;
	}
	
	public function buttonDone($data){
		$IOK = new Button("Abschließen");
		$IOK->className("submitFormButton");
		$IOK->onclick("$(window).unbind('beforeunload'); document.location.href='?CC=Auftrag&page=1';");
		
		return $IOK;
	}
	
	public function getArtikel($data){
		$TKategorien = new HTMLTable(2, "Kategorien");
		$TKategorien->setTableStyle("width:100%;");
		$TKategorien->setTableID("tableKategorien");
		$TKategorien->setColWidth(1, 20);
		
		$B = new Button("Nach Kategorie filtern", "arrow_down", "iconic");
		$B->className("reverse");
			
		$AC = anyC::get("Kategorie", "type", "2");
		$AC->addOrderV3("name");
		
		$TKategorien->addRow(array($B, "Alle Kategorien"));
		$TKategorien->addRowClass("selectable");
		$TKategorien->addRowEvent("click", "CustomerPage.rme('getArtikel', {KategorieID: '', query : '$data[query]', GRLBMID: $data[GRLBMID]}, function(transport){ $('#contentRight').html(transport); });");
		
		if($data["KategorieID"] == "")
			$TKategorien->addRowStyle ("text-decoration:underline;");
			
		while($K = $AC->getNextEntry()){
			$B = new Button("Nach Kategorie filtern", "arrow_down", "iconic");
			if($data["KategorieID"] != $K->getID())
				$B->className("reverse");
		
			$TKategorien->addRow(array($B, $K->A("name")));
			$TKategorien->addRowClass("selectable");
			$TKategorien->addRowEvent("click", "CustomerPage.rme('getArtikel', {KategorieID: '".$K->getID()."', query : '$data[query]', GRLBMID: $data[GRLBMID]}, function(transport){ $('#contentRight').html(transport); });");
			
			#if($data["KategorieID"] == $K->getID())
			#	$TKategorien->addRowStyle ("text-decoration:underline;");
		}
		
		
		$TArtikel = new HTMLTable(4, "Artikel");
		$TArtikel->setTableStyle("width:100%;");
		$TArtikel->setColWidth(1, 26);
		$TArtikel->setColWidth(2, 100);
		$TArtikel->setColStyle(4, "text-align:right;");
		
		$BQ = "";
		if($data["query"] != ""){
			$BQ = new Button("Suche löschen", "x_alt", "iconic");
			$BQ->onclick("CustomerPage.rme('getArtikel', {KategorieID: '$data[KategorieID]', query : '', GRLBMID: $data[GRLBMID]}, function(transport){ $('#contentRight').html(transport); });");
			$BQ->style("color:darkred;float:left;");
		} else
			$BQ = new Button("Suche", "question_mark", "iconic");
		
		
		$I = new HTMLInput("query", "text", $data["query"]);
		$I->placeholder("Suche nach Name, Nummer oder Beschreibung");
		$I->style("width:90%;");
		$I->onEnter("CustomerPage.rme('getArtikel', {KategorieID: '$data[KategorieID]', query : this.value, GRLBMID: $data[GRLBMID]}, function(transport){ $('#contentRight').html(transport); });");
		
		$BS = new Button("Los", "arrow_right", "iconic");
		$BS->onclick("CustomerPage.rme('getArtikel', {KategorieID: '$data[KategorieID]', query : \$j('[name=query]').val(), GRLBMID: $data[GRLBMID]}, function(transport){ $('#contentRight').html(transport); });");
		
		$TArtikel->addRow(array($BQ, $I." ".$BS));
		$TArtikel->addRowColspan(2, 3);
		
		$AC = anyC::get("Artikel");
		if($data["KategorieID"] != "")
			$AC->addAssocV3 ("KategorieID", "=", $data["KategorieID"], "AND", "1");
		if($data["query"] != ""){
			$AC->addAssocV3("artikelnummer", "LIKE", "%$data[query]%", "AND", "2");
			$AC->addAssocV3("name", "LIKE", "%$data[query]%", "OR", "2");
			$AC->addAssocV3("beschreibung", "LIKE", "%$data[query]%", "OR", "2");
		}
		$AC->addOrderV3("artikelnummer");
		$AC->addOrderV3("name");
		$AC->setLimitV3(100);
		while($A = $AC->getNextEntry()){
			$B = new Button("Artikel hinzufügen", "arrow_left", "iconic");
			$B->className("reverse");
			#$B->onclick("CustomerPage.rme('delPosten', {PostenID: '".$P->getID()."'}, function(){ CustomerPage.rme('getAuftrag', {GRLBMID: $data[GRLBMID]}, function(transport){ $('#contentLeft').html(transport); }); });");
			$A->resetParsers();
			$TArtikel->addRow(array(
				$B,
				$A->A("artikelnummer"), 
				$A->A("name").($A->A("bemerkung") != "" ? "<br /><small style=\"color:grey;\">".$A->A("bemerkung")."</small>" : ""),
				$this->showPrices ? Util::CLFormatCurrency($A->getGesamtBruttoVK() * 1, true)."<br /><small style=\"color:grey;\">".Util::CLFormatCurrency($A->getGesamtNettoVK() * 1, true)."</small>" : ""
			));
			$TArtikel->addRowClass("selectable");
			$TArtikel->addRowEvent("click", "CCAuftrag.lastTextbausteinUnten = \$('[name=textbausteinUnten]').val(); CustomerPage.rme('addArtikel', {ArtikelID: '".$A->getID()."', GRLBMID: $data[GRLBMID]}, function(transport){ CustomerPage.rme('getAuftrag', {GRLBMID: $data[GRLBMID]}, function(transport){ $('#contentLeft').html(transport); }); });");
			
		}
		
		if($AC->numLoaded() == 0){
			$TArtikel->addRow(array("", "Keine Artikel gefunden"));
			$TArtikel->setColWidth(2, 200);
		}
		
		$html = "<h1>Artikel</h1>
				$TKategorien
				$TArtikel";
		
		return $html;
	}
	
	public function alterAddress($data){
		$Adresse = new Adresse($data["AdresseID"]);
		
		$F = new HTMLForm("alterAddress", array("firma", "vorname", "nachname", "strasse", "nr", "plz", "ort", "action", "AdresseID"));
		
		$F->setValues($Adresse);
		$F->setValue("action", "alterAddress");
		$F->setValue("AdresseID", $data["AdresseID"]);
		
		$F->setType("action", "hidden");
		$F->setType("AdresseID", "hidden");
		
		$F->setLabel("strasse", "Straße");
		
		$F->setSaveCustomerPage("Speichern", "", false, "function(){ CustomerPage.closePopup(); CustomerPage.rme('getAuftrag', {GRLBMID: $data[GRLBMID]}, function(transport){ $('#contentLeft').html(transport); }); }");
		
		echo $F;
	}
	
	public function cancelAuftrag($data){
		
		$Beleg = new GRLBM($data["GRLBMID"]);
		$Auftrag = new Auftrag($Beleg->A("AuftragID"));
		
		$Beleg->deleteMe();
		
		$Auftrag->deleteMe();
	}
	
	public function checkAddressGoogle($data){
		$Adresse = new Adresse($data["AdresseID"]);
		
		$B = new Button("Auf Karte anzeigen", "map_pin_stroke", "iconic");
		$B->style("float:right;font-size:30px;margin-top:-5px;");
		$B->onclick("showMap();");
		
		return "<p>".$Adresse->getHTMLFormattedAddress()."</p>
		<div class=\"backgroundColor1 Tab\">
			<p>Google-Antwort</p>
		</div>
		<p id=\"gAnswer\"></p>
		<div id=\"map_canvas\" style=\"width:100%; height:400px;display:none;\"></div>".OnEvent::script("
			
		var map;
		var geocoder = new google.maps.Geocoder();
		var r;
		function showMap(){
			$('#map_canvas').show();
			map = new google.maps.Map(document.getElementById('map_canvas'), {
			  zoom: 16,
			  center: latlng = new google.maps.LatLng(-34.397, 150.644),
			  mapTypeId: google.maps.MapTypeId.ROADMAP
			});

			map.setCenter(r[0].geometry.location);
			var marker = new google.maps.Marker({
				map: map,
				position: r[0].geometry.location
			});
		}
		setTimeout(function(){
			geocoder.geocode( { 'address': '".str_replace("\n", ", ", $Adresse->getFormattedAddress())."'}, function(results, status) {
				if (status == google.maps.GeocoderStatus.OK) {
					var foundLoc = results[0].geometry.location_type;
					if(foundLoc == 'APPROXIMATE' || foundLoc == 'RANGE_INTERPOLATED')
						foundLoc = 'Ungefähr gefunden';

					if(foundLoc == 'ROOFTOP' || foundLoc == 'GEOMETRIC_CENTER')
						foundLoc = 'Genau gefunden';
						
					r = results;
					$('#gAnswer').html('$B'+foundLoc);
					
					CustomerPage.rme('reportCheckAddressGoogleOK', {AdresseID: $data[AdresseID]});
					$('#BCheckG').css('color', 'green');
				} else {
					$('#gAnswer').html(\"<span style='color:red;'>Die Adresse wurde nicht gefunden!</span>\");
					CustomerPage.rme('reportCheckAddressGoogleError', {AdresseID: $data[AdresseID]});
					
					$('#BCheckG').css('color', 'darkred');
				}

			});
		}, 300);");
	}
	
	public function reportCheckAddressGoogleError($data){
		$Adresse = new Adresse(-1);
		
		Aspect::joinPoint("onError", $this, __METHOD__, array($data));
	}
	
	public function reportCheckAddressGoogleOK($data){
		$Adresse = new Adresse(-1);
		
		Aspect::joinPoint("onSuccess", $this, __METHOD__, array($data));
	}
	
	public function checkAddressKlickTel($data){
		
		$KT = new klickTel(-1);
		
		$Adresse = new Adresse($data["AdresseID"]);
		
		Aspect::joinPoint("before", $this, __METHOD__, $data); //call AFTER new Adresse!
		
		try {
			$result = $KT->checkAddress($data["AdresseID"]);
		} catch (Exception $e){
			Aspect::joinPoint("onError", $this, __METHOD__, array($data, $e)); //call AFTER new Adresse!
			die("<p>".$e->getMessage()."</p>");
		}
		
		$T = new HTMLTable(2, "Gefundene Adressen");
		$T->useForSelection(false);
		$T->setTableStyle("width:100%;");
		
		$i = 0;
		$found = null;
		foreach($result AS $k => $A){
			$T->addRow(array($A->street." ".$A->streetnumber, $A->zipcode." ".$A->city));
			$T->addRowEvent("click", "CustomerPage.rme('changeAddressKlickTel', {AdresseID: $data[AdresseID], useNr: $k, GRLBMID:$data[GRLBMID]}, function(){ CustomerPage.closePopup(); CustomerPage.rme('getAuftrag', {GRLBMID: $data[GRLBMID]}, function(transport){ $('#contentLeft').html(transport); $('#BCheckKT').css('color', 'green'); }); });");
			$T->addRowClass("selectable");
			$found = $A;
			$i++;
		}
		
		echo "<p>".$Adresse->getHTMLFormattedAddress()."</p>";
		
		if($i == 1 AND
			$Adresse->A("strasse") == $found->street AND
			$Adresse->A("nr") == $found->streetnumber AND
			$Adresse->A("plz") == $found->zipcode AND
			$Adresse->A("ort") == $found->city){
			
			Aspect::joinPoint("onSuccess", $this, __METHOD__, array($data, $found)); //call AFTER new Adresse!
			die("<p style=\"color:green;\">Diese Adresse wurde genau so in der klickTel Datenbank gefunden.</p>".OnEvent::script("$('#BCheckKT').css('color', 'green');"));
		}
		
		echo $T;
	}
	
	public function changeAddressKlickTel($data){
		$KT = new klickTel(-1);
		
		try {
			$result = $KT->checkAddress($data["AdresseID"]);
		} catch (Exception $e){
			die("<p>".$e->getMessage()."</p>");
		}
		
		#$Adresse = new Adresse($data["AdresseID"]);
			
		$useLocation = null;
		foreach($result AS $k => $A)
			if($k == $data["useNr"])
				$useLocation = $A;


		$AdresseNew = new Adresse($data["AdresseID"]);

		$AdresseNew->changeA("strasse", $useLocation->street);
		if(trim($useLocation->streetnumber) != "")
			$AdresseNew->changeA("nr", $useLocation->streetnumber);
		$AdresseNew->changeA("plz", $useLocation->zipcode);
		$AdresseNew->changeA("ort", $useLocation->city);

		$AdresseNew->saveMe(true, false);

		Aspect::joinPoint("after", $this, __METHOD__, array($data)); //call AFTER new Adresse!
		#$reload = OnEvent::script("");
		#echo $reload;
		#die("<p>Die Adresse wurde geändert und lautet nun:</p><p>".$AdresseNew->getHTMLFormattedAddress()."</p>".$reload);
	}
	
	public function addArtikel($data){
		$Beleg = new GRLBM($data["GRLBMID"]);
		$Beleg->addArtikel($data["ArtikelID"]);
	}
	
	public function delPosten($data){
		$Posten = new Posten($data["PostenID"]);
		$Posten->deleteMe();
	}
	
	public function setMenge($data){
		$Posten = new Posten($data["PostenID"]);
		$Posten->recalcNetto = false;
		$Posten->changeA("menge", Util::CLNumberParser($data["menge"], "store"));
		$Posten->saveMe();
	}
	
	public function setBemerkung($data){
		$GRLBM = new GRLBM($data["GRLBMID"]);
		$GRLBM->changeA("textbausteinUnten", $data["bemerkung"]);
		$GRLBM->changeA("textbausteinUntenID", "0");
		$GRLBM->saveMe();
	}
	
	public function removeOptional($data){
		$P = new Posten($data["PostenID"]);
		$P->changeA("PostenIsAlternative", "0");
		$P->saveMe();
	}
	
	public function setKontodaten($data){
		if($data["zahlungsart"] != "debit"){
			$data["kontonummer"] = "";
			$data["bankleitzahl"] = "";
		}
		
		$GRLBM = new GRLBM($data["GRLBMID"]);
		$GRLBM->changeA("GRLBMpayedVia", $data["zahlungsart"]);
		$GRLBM->changeA("GRLBMKontonummer", $data["kontonummer"]);
		$GRLBM->changeA("GRLBMBankleitzahl", $data["bankleitzahl"]);
		$GRLBM->saveMe();
		
		if($data["bankleitzahl"] == "")
			die("&nbsp;");
		
		$uri = "http://soapi.io/soap/blz";
		
		$Soap = new SoapClient(null, array(
			"location" => $uri,
			"uri" => $uri));
		
		$R = $Soap->requestBank($data["bankleitzahl"], $data["kontonummer"]);
		
		echo $data["bankleitzahl"].": ".$R["bankname"];
		
		if($R["isValidKontonummer"])
			echo OnEvent::script("$('#ktoCheck').removeClass('question_mark').removeClass('x_alt').addClass('check').css('color', 'green');");
		else
			echo OnEvent::script("$('#ktoCheck').removeClass('question_mark').removeClass('check').addClass('x_alt').css('color', 'red');");
	}
	
	function handleForm($valuesAssocArray){
		switch($valuesAssocArray["action"]){
			case "alterAddress":
				$F = new Factory("Adresse", $valuesAssocArray["AdresseID"]);
				$F->fill($valuesAssocArray);
				$F->store();
			break;
		}
		
		parent::handleForm($valuesAssocArray);
	}
	
	public function getPDFViewer($data){
		if(!$this->loggedIn)
			return "TIMEOUT";
		
		return "<iframe src=\"index.php?CC=Lieferschein&M=getPDF&GRLBMID=$data[GRLBMID]&_=".rand(0, 99999999)."\" style=\"border:0px;height:500px;width:100%;\"></iframe>";
	}
	
	public function getPDF($data){
		if(!$this->loggedIn)
			return "TIMEOUT";
		
		$G = new GRLBM($data["GRLBMID"]);
		$Auftrag = new Auftrag($G->A("AuftragID"));
		
		$brief = $Auftrag->getLetter("", false, $data["GRLBMID"]);
		
		$brief->generate(false, null);
	}
}
?>
