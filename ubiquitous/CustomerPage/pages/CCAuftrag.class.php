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
 *  2007 - 2020, open3A GmbH - Support@open3A.de
 */

ini_set('session.gc_maxlifetime', 24 * 60 * 60);

class CCAuftrag extends CCPage implements iCustomContent {
	
	protected $showZahlungsart = false;
	protected $showButtonEditAddress = false;
	protected $showButtonCheckWithGoogle = false;
	protected $showPrices = true;
	protected $showPosten = true;
	protected $showSignature = false;
	protected $increaseCount = false;
	
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
		if(Session::currentUser() == null AND !Users::login($_POST["benutzer"], $_POST["password"], "open3A", "default", false, false))
			$this->loggedIn = false;
		
		$this->showZahlungsart = true;
		$this->showButtonEditAddress = true;
		$this->showButtonCheckWithGoogle = true;
	}
	
	public function searchCustomer($query){
		$A = new AdressenGUI();
		$A->getACData("", $query["P0"]);
	}
	
	public function newDocument($data, $type = "R"){
		$A = new Auftrag(-1);
		if(isset($data["P0"]))
			$AID = $A->newWithDefaultValues($data["P0"]);
		else {
			$AID = $A->newWithDefaultValues();

			$F = new Factory("Adresse");
			$F->sA("AuftragID", $AID);
			$F->sA("firma", "Barrechnung");
			$AdresseID = $F->store();
		}
		
		$GRLBMID = $A->createGRLBM($type, true);
		
		if(!isset($data["P0"])){
			$Auftrag = new Auftrag($AID);
			$Auftrag->changeA("AdresseID", $AdresseID);
			$Auftrag->saveMe();
		}
		
		return $GRLBMID;
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
	
	public function getCustomers(){

		$html = "";
		
		
		$T = new HTMLTable(1);
		$T->setTableStyle("width:100%;margin-top:10px;");
		$T->setColWidth(1, 130);
		$T->useForSelection(false);
		$T->maxHeight(400);
		
		$AC = anyC::get("Adresse", "AuftragID", "-1");
		$AC->addOrderV3("CONCAT(firma, nachname, vorname)", "ASC");
		$i = 0;
		while($Adresse = $AC->n()){
			#$Adresse = new Adresse($B->A("AdresseID"));
			$T->addRow(array($Adresse->getHTMLFormattedAddress()));
			$T->addCellStyle(1, "vertical-align:top;");
			
			$T->addRowStyle("cursor:pointer;border-bottom:1px solid #ccc;");
			
			
			/*$T->addRowEvent("click", "
				$(this).addClass('selected');
				
				CustomerPage.rme('getAuftrag', {GRLBMID: ".$B->getID()."}, function(transport){ 
						if(transport == 'TIMEOUT') { document.location.reload(); return; } 
						$('#contentLeft').html(transport); 
					}, 
					function(){},
					'POST');
					
				CustomerPage.rme('getArtikel', {GRLBMID: ".$B->getID().", query : '', KategorieID: ''}, function(transport){ 
						if(transport == 'TIMEOUT') { document.location.reload(); return; } 
						$('#contentRight').html(transport); 
						$('.selected').removeClass('selected');
						$('#frameSelect').hide(); $('#frameEdit').show();
					}, 
					function(){},
					'POST');");*/
			
			$i++;
		}
		
		$html .= $T;
		
		return $html;
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

		$Auftrag = new Auftrag($Beleg->A("AuftragID"));
		$K = Kappendix::getKappendixToKundennummer($Auftrag->A("kundennummer"));
		
		$js = "";
		
		$TSumme = new HTMLTable(3);
		$TSumme->setTableStyle("width:100%;border-top:1px solid #AAA;margin-top:30px;");
		$TSumme->setColWidth(1, 26);
		$TSumme->addColStyle(3, "text-align:right;");
		$TSumme->addRow(array("", "Netto: <b>".Util::CLFormatCurrency($Beleg->A("nettobetrag")*1, true)."</b>", "Brutto: <b>".Util::CLFormatCurrency($Beleg->A("bruttobetrag")*1, true)."</b>"));
		
		
		
		$IZahlungsart = new HTMLInput("zahlungsart", "select", $Beleg->A("GRLBMpayedVia"), GRLBM::getPaymentVia(null, array("transfer", "debit")));
		$IZahlungsart->onchange("if(this.value == 'debit') $('.rowZahlungsart').show(); else $('.rowZahlungsart').hide(); CustomerPage.timeout = window.setTimeout(CustomerPage.saveKontodaten, 300);");
		
		$IBemerkung = new HTMLInput("bemerkung", "textarea");#, $Beleg->A("textbausteinUnten"));
		$IBemerkung->style("width:100%;height:100px;max-width:100%;");
		$IBemerkung->placeholder("Bemerkungen");
		#$IBemerkung->onblur("");
		#$IBemerkung->onkeyup("if(CustomerPage.timeout != null) window.clearTimeout(CustomerPage.timeout); CustomerPage.timeout = window.setTimeout(CustomerPage.saveBemerkung, 300);");
		
		$TBemerkung = new HTMLTable(2, "Bemerkungen");
		$TBemerkung->setTableStyle("width:100%;");
		$TBemerkung->setColWidth(1, 26);
		
		$TBemerkung->addRow(array(new Button("Bemerkung", "document_alt_stroke", "iconic"), $IBemerkung));
		$TBemerkung->addCellStyle(1, "vertical-align:top;");
		
		$sepa = json_decode($Beleg->A("GRLBMSEPAData"));
		
		
		$IBankleitzahl = new HTMLInput("BIC", "text", $sepa->BIC);
		$IBankleitzahl->placeholder("BIC");
		$IBankleitzahl->style("");
		$IBankleitzahl->onkeyup("if(CustomerPage.timeout != null) window.clearTimeout(CustomerPage.timeout); CustomerPage.timeout = window.setTimeout(CustomerPage.saveKontodaten, 300);");
		
		$IKontonummer = new HTMLInput("IBAN", "text", $sepa->IBAN);
		$IKontonummer->placeholder("IBAN");
		$IKontonummer->style("");
		$IKontonummer->onkeyup("if(CustomerPage.timeout != null) window.clearTimeout(CustomerPage.timeout); CustomerPage.timeout = window.setTimeout(CustomerPage.getBIC, 300);");
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
		
		$TZahlungsart->addRow(array("", $IKontonummer." $BCheck <div id=\"bankMessage\" style=\"display:none;\">&nbsp;</div>"));
		$TZahlungsart->addRowClass("rowZahlungsart");
		
		$TZahlungsart->addRow(array("", $IBankleitzahl));
		$TZahlungsart->addRowClass("rowZahlungsart");
		
		$IMandat = new HTMLInput("mandat", "checkbox");
		$IMandat->style("vertical-align:middle;");
		
		$TZahlungsart->addRow(array("", $IMandat." SEPA-Mandat erteilt"));
		$TZahlungsart->addRowClass("rowZahlungsart");
		
		$IDatenschutz = new HTMLInput("datenschutz", "checkbox");
		$IDatenschutz->style("vertical-align:middle;");
		
		$TZahlungsart->addRow(array("", $IDatenschutz." Hinweise zum Datenschutz gelesen"));
		
		$IAGB = new HTMLInput("agb", "checkbox");
		$IAGB->style("vertical-align:middle;");
		
		$TZahlungsart->addRow(array("", $IAGB." AGB akzeptiert"));
		
		if($Beleg->A("GRLBMpayedVia") != "debit")
			$js .= "$('.rowZahlungsart').hide();";
		#	$TZahlungsart->addRowStyle("display:none;");
		#else
		#	echo OnEvent::script("CustomerPage.saveKontodaten();");
		
		
		
		
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
			$html .= $TBemerkung.$TZahlungsart;
		
		$html .= $this->getBottom($Beleg);
		
		$html .= $TFinish;
		
		return $html.OnEvent::script("
			CustomerPage.timeout = null;
			CustomerPage.saveBemerkung  = function(){ CustomerPage.rme('setBemerkung', {GRLBMID: $data[GRLBMID], bemerkung: $('textarea[name=bemerkung]').val() }); };
			CustomerPage.getBIC = function(){
				CustomerPage.rme('getBIC', {
					IBAN: $('input[name=IBAN]').val()
				}, function(t){ $('#bankMessage').html(t); });
			};
			CustomerPage.saveKontodaten = function(){ CustomerPage.rme('setKontodaten', {
				GRLBMID: $data[GRLBMID],
				zahlungsart: $('select[name=zahlungsart]').val(),
				IBAN: $('input[name=IBAN]').val(),
				BIC: $('input[name=BIC]').val()
			}, function(t){ $('#bankMessage').html(t); }); };
				
			$js");
	}
	
	public function getBottom($Beleg){
		if(!$this->showSignature)
			return;
		
		$TA = new HTMLTable(1, "Unterschrift Auftragnehmer");
		$TA->setTableStyle("width:100%;");
		
		$P = new Button("Unterschrift", "pen_alt2", "iconic");
		$P->style("float:left;");
		
		$padAN = $P.'
	<div class="sigPadAN" style="margin-left:30px;">
		<canvas class="pad" width="300" height="150" style="border:1px solid grey;"></canvas>
		<input type="hidden" id="sigAN" name="sigAN" class="output">
		<br>
		<span class="clearButton"><a href="#" onclick="return false;">Nochmal</a></span>
	</div>';
		
		$TA->addRow(array($padAN));
		
		
		$TK = new HTMLTable(1, "Unterschrift Kunde");
		$TK->setTableStyle("width:100%;");
		
		$padKunde = $P.'
	<div class="sigPadKunde" style="margin-left:30px;">
		<canvas class="pad" width="300" height="150" style="border:1px solid grey;"></canvas>
		<input type="hidden" id="sigKunde" name="sigKunde" class="output">
		<br>
		<span class="clearButton"><a href="#" onclick="return false;">Nochmal</a></span>
	</div>';
		
		$TK->addRow(array($padKunde));
		
		$IID = new HTMLInput("GRLBMID", "hidden", $Beleg->getID());
		
		return "$IID<div style=\"width:49%;margin-right:1%;display:inline-block;vertical-align:top;\">$TA</div><div style=\"width:49%;display:inline-block;vertical-align:top;margin-right:1%;\">$TK</div>
			".OnEvent::script("$('.sigPadAN').signaturePad({drawOnly:true, lineTop: 100}).regenerate(".$Beleg->A("GRLBMServiceSigAN").");
				$('.sigPadKunde').signaturePad({drawOnly:true, lineTop: 100}).regenerate(".$Beleg->A("GRLBMServiceSigAG").");");
	}
	
	public function getAdresse($Beleg){
		$Auftrag = new Auftrag($Beleg->A("AuftragID"));
		$Adresse = new Adresse($Auftrag->A("AdresseID"));
		
		#$BCheckK = "";
		#if(Session::isPluginLoaded("mklickTel")){
		#	$BCheckK = new Button("Adresse mit klickTel prüfen", "compass", "iconic");
		#	$BCheckK->style("float:right;font-size:30px;margin-right:15px;");
		#	$BCheckK->onclick("CustomerPage.popup('Adressprüfung', 'checkAddressKlickTel', {AdresseID: {$Adresse->getID()}, GRLBMID: $data[GRLBMID]}, {modal: true, width: 500, resizable: false, position: ['center', 40]});");
		#	$BCheckK->id("BCheckKT");
		#	Aspect::joinPoint("modButtonKlickTel", $this, __METHOD__, array($BCheckK, $Auftrag));
		#}
		
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
		$TAdresse->addRow(array(new Button("Adresse", "home", "iconic"), $BCheckG.$BUpdate.$Adresse->getHTMLFormattedAddress()));
		$TAdresse->setColStyle(1, "vertical-align:top;");
		
		return $TAdresse;
	}
	
	public function getScriptFiles(){
		$files = array();
		if($this->showSignature)
			$files[] = "./lib/jquery.signaturepad.min.js";
		
		return $files;
	}
	
	public function getScript(){
		return "var CCAuftrag = {
			lastValue: null,
			allowSave: false,
			lastTextbausteinUnten: null,
			
			openBeleg: function(ID){
				CustomerPage.rme('getAuftrag', {GRLBMID: ID}, function(transport){ 
						if(transport == 'TIMEOUT') { document.location.reload(); return; } 
						$('#contentLeft').html(transport); 
					}, 
					function(){},
					'POST');
					
				CustomerPage.rme('getArtikel', {GRLBMID: ID, query : '', KategorieID: ''}, function(transport){ 
						if(transport == 'TIMEOUT') { document.location.reload(); return; } 
						$('#contentRight').html(transport); 
						$('.selected').removeClass('selected');
						$('#frameSelect').hide(); $('#frameEdit').show();
					}, 
					function(){},
					'POST');
			}
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
		$TArtikel->setColWidth(3, 100);
		$TArtikel->setColStyle(4, "text-align:right;");
		
		
		$I = new HTMLInput("addByBarcode", "text", $data["query"]);
		$I->placeholder("Hinzufügen über Nummer");
		$I->style("width:calc(100% - 30px);max-width:calc(100% - 30px);");
		$I->onEnter("CustomerPage.rme('addArtikel', {code : this.value, GRLBMID: $data[GRLBMID]}, function(transport){ CustomerPage.rme('getAuftrag', {GRLBMID: $data[GRLBMID]}, function(transport){ $('#contentLeft').html(transport); }); \$('[name=addByBarcode]').val(''); });");
		
		$BQ = new Button("Suche", "target", "iconic");
		$TArtikel->addRow(array($BQ, $I));
		$TArtikel->addRowColspan(2, 3);
		
		
		$BQ = "";
		if($data["query"] != ""){
			$BQ = new Button("Suche löschen", "x_alt", "iconic");
			$BQ->onclick("CustomerPage.rme('getArtikel', {KategorieID: '$data[KategorieID]', query : '', GRLBMID: $data[GRLBMID]}, function(transport){ $('#contentRight').html(transport); });");
			$BQ->style("color:darkred;float:left;");
		} else
			$BQ = new Button("Suche", "question_mark", "iconic");
		
		
		$I = new HTMLInput("query", "text", $data["query"]);
		$I->placeholder("Liste filtern nach Name, Nummer oder Beschreibung");
		$I->style("width:calc(100% - 30px);max-width:calc(100% - 30px);");
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
				$A->A("name").($A->A("bemerkung") != "" ? "<br><small style=\"color:grey;\">".$A->A("bemerkung")."</small>" : ""),
				$A->A("artikelnummer"), 
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
		if(isset($data["code"])){
			$ACA = anyC::get("Artikel");
			if(substr($data["code"], 0, 3) == "ART"){
				$ACA->addAssocV3("ArtikelID", "=", substr($data["code"], 3) - 10000);
			} else {
				$ACA->addAssocV3("artikelnummer", "=", "$data[code]", "AND", "1");
				$ACA->addAssocV3("EAN", "=", "$data[code]", "OR", "1");
				$ACA->addAssocV3("artikelnummerHersteller", "=", "$data[code]", "OR", "1");
			}
		
			$A = $ACA->n();
			if($ACA->numLoaded() == 1){
				
			} elseif($ACA->numLoaded() > 1) 
				Red::errorD("Nummer nicht eindeutig!");
			else
				Red::errorD("Nummer nicht gefunden!");
			
			$data["ArtikelID"] = $A->getID();
		}
		#$Beleg = new GRLBM($data["GRLBMID"]);
		#$Beleg->addArtikel($data["ArtikelID"]);
		$p = new Posten(-1);
		$p->increaseCount = $this->increaseCount;
		$p->newFromArtikel($data["ArtikelID"], $data["GRLBMID"], 1);
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
	
	public function getBIC($data){
		$uri = "https://soapi.io/soap/blz";
		
		$Soap = new SoapClient(null, array(
			"location" => $uri,
			"uri" => $uri));
		
		$R = $Soap->requestBICByIBAN($data["IBAN"]);
		
		if($R["isValidBank"])
			echo OnEvent::script("$('[name=BIC]').val('$R[BIC]'); $('#ktoCheck').removeClass('question_mark').removeClass('x_alt').addClass('check').css('color', 'green');");
		else
			echo OnEvent::script("$('[name=BIC]').val('');$('#ktoCheck').removeClass('question_mark').removeClass('check').addClass('x_alt').css('color', 'red');");
	}
	
	public function setKontodaten($data){
		if($data["zahlungsart"] != "debit"){
			$data["IBAN"] = "";
			$data["BIC"] = "";
		}
		
		$GRLBM = new GRLBM($data["GRLBMID"]);
		$GRLBM->changeA("GRLBMpayedVia", $data["zahlungsart"]);
		#$GRLBM->changeA("GRLBMKontonummer", $data["kontonummer"]);
		#$GRLBM->changeA("GRLBMBankleitzahl", $data["bankleitzahl"]);
		$GRLBM->saveMe();
		
		if($data["BIC"] == "")
			die("&nbsp;");
		
		$uri = "https://soapi.io/soap/blz";
		
		$Soap = new SoapClient(null, array(
			"location" => $uri,
			"uri" => $uri));
		
		$R = $Soap->checkIBAN($data["IBAN"]);
		var_dump($R);
		#echo $data["bankleitzahl"].": ".$R["bankname"];
		
		if($R)
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
	
	public function sendViaEMail($data){
		$Auftrag = new Auftrag($data["AuftragID"]);
		$Auftrag->sendViaEmail($data["GRLBMID"]);
	}
	
	/*public function getEMailViewer($data){
		if(!$this->loggedIn)
			return "TIMEOUT";
		
		
		
		$I = new HTMLInput("emailBody", "textarea", "<p>TEST</p>");
		
		return $I.OnEvent::script("\$j('[name=emailBody]').trumbowyg({
			lang: 'de',
			resetCss: true,
			btns: [['undo', 'redo'], ['bold', 'italic', 'underline'], ['fullscreen', 'viewHTML']]
		});"); #, 'removeformat'
	}*/
	
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
