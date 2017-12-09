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
require_once __DIR__.'/CCAuftrag.class.php';
		
class CCService extends CCAuftrag implements iCustomContent {
	function __construct() {
		parent::__construct();
		$this->loadPlugin("open3A", "Niederlassungen", true);
		#$this->showPosten = false;
		$this->showPrices = false;
	}
	
	function getLabel(){
		return "Serviceschein-Erfassung";
	}
	
	function getCMSHTML() {
		if(!$this->loggedIn)
			return $this->formLogin();
		
		$BRL = new Button("Aktualisieren");
		$BRL->className("submitFormButton");
		$BRL->onclick("document.location.reload();");
		$BRL->style("float:right;margin-top:0px;");
		
		$BRA = new Button("Abmelden");
		$BRA->className("submitFormButton");
		$BRA->onclick("CustomerPage.rme('logout', {}, function(transport){ document.location.reload(); });");
		$BRA->style("background-color:#DDD;color:grey;margin-top:0px;float:right;margin-right:20px;");
		
		$IOK = new Button("Fertig");
		$IOK->className("submitFormButton");
		$IOK->onclick("$('#frameSelect').show(); $('#frameEdit').hide(); ");
		$IOK->style("margin-top:0px;float:right;margin-right:20px;");

		
		return "
		<div style=\"max-width:1200px;\">
			<div id=\"frameEdit\" style=\"display:none;\">
				<div style=\"display:inline-block;width:61%;vertical-align:top;margin-right:3%;\" id=\"contentLeft\">
						".$this->getAuftrag(array("GRLBMID" => 0))."
				</div>
				<div style=\"display:inline-block;width:35%;vertical-align:top;\" id=\"contentRight\">
					
				</div>
			</div>
			<div id=\"frameSelect\">
				<div style=\"display:inline-block;width:100%;vertical-align:top;\" id=\"contentScreen\">
					<h1>{$BRL}{$BRA}Service-Berichte</h1>
					<div class=\"content\" style=\"overflow:auto;\">
						".$this->getService(array())."
					</div>
				</div>
			</div>
		</div>
			".OnEvent::script("
				/*\$('#contentRight .content .tableForSelection ').parent().css('max-height', $(window).height() - $('h1').outerHeight() - 40 - $('#contentRight .Tab').outerHeight())
				\$('#contentLeft ').css('height', $(window).height() - $('h1').outerHeight() - 25)*/");

	}
	public function getScriptFiles(){
		return array("../../libraries/tinymce/tinymce.min.js", "../../libraries/tinymce/jquery.tinymce.min.js", "./lib/jquery.signaturepad.min.js");
	}
	
	public function getStyleFiles(){
		return array("./lib/jquery.signaturepad.css");
	}
	
	public function getPosten(GRLBM $Beleg){
		$T = new HTMLTable(1, "Arbeitsbeschreibung");
		$T->setTableStyle("width:100%;");
		
		$tinyMCEID = "tinyMCE".rand(100, 90000000);
		$I = new HTMLInput("textbausteinUnten", "textarea", $Beleg->A("textbausteinUnten"));
		$I->style("width:100%;height:300px;margin-left:10px;");
		
		$I->id($tinyMCEID);
		$T->addRow(array($I));
		
		$buttons = "undo redo | pastetext | styleselect fontsizeselect fontselect | bold italic underline forecolor | hr";
		
		
		return $T.parent::getPosten($Beleg).OnEvent::script("if(CCAuftrag.lastTextbausteinUnten != null) \$('[name=textbausteinUnten]').val(CCAuftrag.lastTextbausteinUnten); CCAuftrag.lastTextbausteinUnten = null;".tinyMCEGUI::editorDokument($tinyMCEID, "function(content){}", $buttons, "../../styles/tinymce/email.css"));
	}#".OnEvent::rme($args[1], "setTextbaustein", array("'textbausteinUnten'", "content.getContent()"))."
	
	public function getService($data){
		if(!$this->loggedIn)
			return "TIMEOUT";

		$html = "";
		
		
		$T = new HTMLTable(4);#, "Bitte wählen Sie einen Lieferschein");
		$T->setTableStyle("width:100%;margin-top:10px;");
		$T->setColWidth(1, 200);
		$T->setColWidth(4, 200);
		$T->useForSelection(false);
		$T->maxHeight(400);
		
		$AC = anyC::get("GRLBM", "isWhat", "S");
		$AC->addJoinV3("Auftrag", "AuftragID", "=", "AuftragID");
		#$AC->addAssocV3("UserID", "=", Session::currentUser()->getID());
		$AC->addAssocV3("isPrinted", "=", "0");
		$AC->addAssocV3("isEMailed", "=", "0");
		$AC->addAssocV3("isPixelLetteredTime", "=", "0");
		
		
		#$AC->addAssocV3("status", "=", "delivered");
		$AC->addAssocV3("GRLBMServiceMitarbeiter", "=", Session::currentUser()->getID(), "AND", "2");
		$AC->addAssocV3("GRLBMServiceMitarbeiter2", "=", Session::currentUser()->getID(), "OR", "2");
		$AC->addAssocV3("GRLBMServiceMitarbeiter3", "=", Session::currentUser()->getID(), "OR", "2");
		$AC->addAssocV3("GRLBMServiceMitarbeiter4", "=", Session::currentUser()->getID(), "OR", "2");
		$AC->addOrderV3("datum", "DESC");
		#$AC->addOrderV3("nummer", "DESC");
		#$AC->setLimitV3(100);
		#$AC->addJoinV3("Adresse", "t2.AdresseID", "=", "AdresseID");
		$i = 0;
		while($B = $AC->n()){
			$BPDF = new Button("PDF anzeigen");
			$BPDF->className("submitFormButton");
			$BPDF->style("background-color:#DDD;color:grey;float:right;");
			$BPDF->onclick("CustomerPage.popup('Service PDF', 'getPDFViewer', {GRLBMID: '".$B->getID()."'}, {width:'800px'});");

			$BOK = "";
			if($B->A("GRLBMServiceSigAG") != "" AND $B->A("GRLBMServiceSigAG") != "[]"){
				$BOK = new Button("Kunde hat unterschrieben", "check", "iconic");
				$BOK->style("font-size:55px;");
			}
			
			$Adresse = new Adresse($B->A("AdresseID"));
			$T->addRow(array(
				"<span style=\"font-size:20px;font-weight:bold;\">".$B->A("prefix").$B->A("nummer")."</span><br><span style=\"color:grey;\">".Util::CLDateParser($B->A("datum"))."</span>", 
				$Adresse->getHTMLFormattedAddress(),
				$BOK,
				$BPDF));
			$T->addCellStyle(1, "vertical-align:top;");
			
			$T->addRowStyle("border-bottom:1px solid #ccc;");
			
			#if($i % 2 == 1)
			#	$T->addRowStyle ("background-color:#eee;");
			
			$event = "
				$(this).addClass('selected');
				CCAuftrag.lastTextbausteinUnten = null;
				
				CustomerPage.rme('getAuftrag', {GRLBMID: ".$B->getID()."}, function(transport){ 
						if(transport == 'TIMEOUT') { document.location.reload(); return; } 
						$('#contentLeft').html(transport); 
						$('#frameSelect').hide(); $('#frameEdit').show();
					}, 
					function(){},
					'POST');
					
				CustomerPage.rme('getArtikel', {GRLBMID: ".$B->getID().", query : '', KategorieID: ''}, function(transport){ 
						if(transport == 'TIMEOUT') { document.location.reload(); return; } 
						$('#contentRight').html(transport); 
						$('.selected').removeClass('selected');
					}, 
					function(){},
					'POST');";
			
			if($B->A("GRLBMServiceSigAG") == "" OR $B->A("GRLBMServiceSigAG") == "[]"){
				$T->addCellEvent(1, "click", $event);
				$T->addCellEvent(2, "click", $event);
				$T->addRowStyle("cursor:pointer;");
			} else
				$T->addRowStyle("cursor:default;");
			
			$i++;
		}
		
		$html .= $T;
		
		return $html;
	}
	
	public function getBottom($Beleg){
		$IV = new HTMLInput("GRLBMServiceVon", "text", Util::CLTimeParserE($Beleg->A("GRLBMServiceVon")));
		$IB = new HTMLInput("GRLBMServiceBis", "text", Util::CLTimeParserE($Beleg->A("GRLBMServiceBis")));
		$IS = new HTMLInput("GRLBMServiceStunden", "text", Util::CLTimeParserE($Beleg->A("GRLBMServiceStunden")));
		
		$IG = new HTMLInput("GRLBMServiceIsGarantie", "checkbox", $Beleg->A("GRLBMServiceIsGarantie"));
		$IA = new HTMLInput("GRLBMServiceIsAbgeschlossen", "checkbox", $Beleg->A("GRLBMServiceIsAbgeschlossen"));
		$IE = new HTMLInput("GRLBMServiceIsBerechnung", "checkbox", $Beleg->A("GRLBMServiceIsBerechnung"));
		
		$IID = new HTMLInput("GRLBMID", "hidden", $Beleg->getID());
		
		$I = new Button("Arbeitszeit", "info", "iconic");
		
		$T = new HTMLTable(3, "Details");
		$T->addRow(array($I, "<label>Anfang der Arbeitszeit:</label>", $IV));
		$T->addRow(array("", "<label>Ende der Arbeitszeit:</label>", $IB));
		$T->addRow(array("", "<label>Stunden ges:</label>", $IS));
		
		$T->addRow(array("", "<label>Garantie?:</label>", $IG));
		$T->addRow(array("", "<label>Abgeschlossen?:</label>", $IA));
		$T->addRow(array("", "<label>Berechnung:</label>", $IE));
		
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
		
		return "
			<div style=\"width:50%;\">
				$T$IID
			</div>
			<div style=\"width:50%;display:inline-block;vertical-align:top;\">$TA</div><div style=\"width:49%;display:inline-block;vertical-align:top;margin-left:1%;\">$TK</div>
			".OnEvent::script("$('.sigPadAN').signaturePad({drawOnly:true, lineTop: 100}).regenerate(".$Beleg->A("GRLBMServiceSigAN").");
				$('.sigPadKunde').signaturePad({drawOnly:true, lineTop: 100}).regenerate(".$Beleg->A("GRLBMServiceSigAG").");");
	}
	
	public function getAdresse($Beleg){
		$html = "<div style=\"width:50%;display:inline-block;vertical-align:top;\">".parent::getAdresse($Beleg)."</div>";
		
		$T = new HTMLTable(3, "Details");
		
		$I = new Button("Details", "info", "iconic");
		
		if(Session::isPluginLoaded("mAdresseNiederlassung") AND strpos($Beleg->A("GRLBMServiceArbeitsort"), "AdresseNiederlassungID:") === 0){
			$N = new AdresseNiederlassung(str_replace("AdresseNiederlassungID:", "", $Beleg->A("GRLBMServiceArbeitsort")));
			AdresseNiederlassung::fill($N);
			
			$new = $N->A("AdresseNiederlassungStrasse")." ".$N->A("AdresseNiederlassungNr").", ".$N->A("AdresseNiederlassungPLZ")." ".$N->A("AdresseNiederlassungOrt");
			
			$Beleg->changeA("GRLBMServiceArbeitsort", $new);
		}
		
		$T->addRow(array($I, "<label>Auftraggeber:</label>", $Beleg->A("GRLBMServiceAuftraggeber")));
		$T->addRow(array("", "<label>Ansprechpartner:</label>", $Beleg->A("GRLBMServiceAnsprechpartner")));
		$T->addRow(array("", "<label>Arbeitsort:</label>", $Beleg->A("GRLBMServiceArbeitsort")));
		if($Beleg->A("GRLBMServiceTerminTag") > 0)
			$T->addRow(array("", "<label>Termin:</label>", Util::CLDateParserE($Beleg->A("GRLBMServiceTerminTag")).($Beleg->A("GRLBMServiceTerminUhr") > 0 ? " um ".Util::CLTimeParser($Beleg->A("GRLBMServiceTerminUhr"))." Uhr" : "")));
		
		
		$html .= "<div style=\"width:49%;margin-left:1%;display:inline-block;vertical-align:top;\">$T</div>";
		
		
		return $html;
	}
	
	public function getStyle(){
		return ".selected {
			background-color:#ddd;
		}";
	}
	
	public function saveService($data){
		#print_r($data);
		if(!$this->loggedIn)
			return "TIMEOUT";
		
		$G = new GRLBM($data["GRLBMID"]);
		$G->changeA("textbausteinUnten", $data["textbausteinUnten"]);
		$G->changeA("GRLBMServiceVon", Util::CLTimeParserE($data["GRLBMServiceVon"], "store"));
		$G->changeA("GRLBMServiceBis", Util::CLTimeParserE($data["GRLBMServiceBis"], "store"));
		$G->changeA("GRLBMServiceStunden", Util::CLTimeParserE($data["GRLBMServiceStunden"], "store"));
		
		$G->changeA("GRLBMServiceIsGarantie", $data["GRLBMServiceIsGarantie"] == "on" ? 1 : 0);
		$G->changeA("GRLBMServiceIsAbgeschlossen", $data["GRLBMServiceIsAbgeschlossen"] == "on" ? 1 : 0);
		$G->changeA("GRLBMServiceIsBerechnung", $data["GRLBMServiceIsBerechnung"] == "on" ? 1 : 0);
		
		$G->changeA("GRLBMServiceSigAN", $data["sigAN"]);
		if($data["sigAN"])
			$G->changeA ("GRLBMServiceSigANDate", time());
		
		$G->changeA("GRLBMServiceSigAG", $data["sigKunde"]);
		$G->saveMe();
	}
	
	/*function setArbeitsbeschreibung($data){
		if(!$this->loggedIn)
			return "TIMEOUT";
		
		$G = new GRLBM($data["GRLBMID"]);
		$G->changeA("textbausteinUnten", $data["text"]);
		$G->saveMe();
	}*/
	
	function handleForm($valuesAssocArray){
		parent::handleForm($valuesAssocArray);
	}
	
	public function buttonCancel($data){
		$IOK = new Button("Abbrechen");
		$IOK->className("submitFormButton");
		$IOK->style("background-color:#DDD;color:grey;float:none;");
		$IOK->onclick("$('#frameSelect').show(); $('#frameEdit').hide();");
		
		return $IOK;
	}
	
	public function buttonDone($data){
		$IOK = new Button("Belegdaten speichern");
		$IOK->className("submitFormButton");
		$IOK->onclick("CustomerPage.rme('saveService', $('#contentLeft :input').serialize(), function(){ document.location.reload();/*$('#frameSelect').show(); $('#frameEdit').hide();*/ }, function(){}, 'POST');");
		
		return $IOK;
	}
}
?>