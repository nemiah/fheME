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
require_once __DIR__.'/CCAuftrag.class.php';
		
class CCService extends CCAuftrag implements iCustomContent {
	function __construct() {
		parent::__construct();
		$this->loadPlugin("open3A", "Niederlassungen", true);
		#$this->loadPlugin("ubiquitous", "Ansprechpartner", true);
		#$this->showPosten = false;
		$this->showPrices = false;
		$this->showSignature = true;
		
		if(Session::currentUser() == null AND isset($_POST["benutzer"]) AND Users::login($_POST["benutzer"], $_POST["password"], "open3A"))
			$this->loggedIn = true;
	}
	
	function getLabel(){
		return "Service";
	}
	
	public function getApps(){
		return ["open3A"];
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
		
		$BRC = new HTMLInput("newInvoice", "text");
		$BRC->placeholder("Kunde auswählen");
		$BRC->style("margin-top:0px;float:right;margin-right:20px;height:31px;box-sizing:border-box;font-size:18px;");
		
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
					<h1>{$BRL}{$BRA}{$BRC}Service-Berichte</h1>
					<div class=\"content\" style=\"overflow:auto;\">
						".$this->getService(array())."
					</div>
				</div>
			</div>
		</div>
			".OnEvent::script("
				\$('[name=newInvoice]').autocomplete({
					source: function(request, response){ 
						CustomerPage.rme('searchCustomer', [request.term], function(t){ response(JSON.parse(t)); });
					},
					select: function(event, ui) {
						CustomerPage.rme('newService', [ui.item.value], function(t){ CustomerPage.rme('getService', [], function(t){ \$('#list').html(t); }); CCAuftrag.openBeleg(t); });
						return false;
					}
				});
				
				var contentManager = {
			
					timeInput: function(event, timeInputID){
						if(event.keyCode == 8)
							return;

						if(event.keyCode == 9)
							return;


						if(\$j('#'+timeInputID).val().length == 2 && \$j('#'+timeInputID).val().lastIndexOf(':') == -1){
							if(\$j('#'+timeInputID).val() < 24)
								\$j('#'+timeInputID).val(\$j('#'+timeInputID).val()+':');
							else
								\$j('#'+timeInputID).val(\$j('#'+timeInputID).val()[0]+':'+\$j('#'+timeInputID).val()[1]);
						}

						\$j('#'+timeInputID).val(\$j('#'+timeInputID).val().replace(/:+/, ':').replace(/[^0-9:]/g, ''));
					}
				};
				
				/*\$('#contentRight .content .tableForSelection ').parent().css('max-height', $(window).height() - $('h1').outerHeight() - 40 - $('#contentRight .Tab').outerHeight())
				\$('#contentLeft ').css('height', $(window).height() - $('h1').outerHeight() - 25)*/");

	}
	
	public function newService($data){
		$GID = $this->newDocument($data, "S");
		
		$G = new GRLBM($GID, false);
		$G->changeA("GRLBMServiceMitarbeiter", Session::currentUser()->getID());
		$G->saveMe();
		
		echo $GID;
	}
	
	public function getScriptFiles(){
		return array_merge(parent::getScriptFiles(), array("../../libraries/tinymce/tinymce.min.js", "../../libraries/tinymce/jquery.tinymce.min.js"));
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
		
		
		$T = new HTMLTable(5);
		$T->setTableStyle("width:100%;margin-top:10px;");
		$T->setColWidth(1, 200);
		$T->setColWidth(4, 200);
		$T->setColWidth(5, 200);
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
			$BPDF = new Button("Service");
			$BPDF->className("submitFormButton");
			$BPDF->style("background-color:#DDD;color:grey;width:150px;");
			$BPDF->onclick("CustomerPage.popup('Service PDF', 'getPDFViewer', {GRLBMID: '".$B->getID()."'}, {width:'800px'});");

			$Adresse = new Adresse($B->A("AdresseID"));
			$BI = "";
			#$BOK = "";
			$BM = "";
			if($B->A("GRLBMServiceSigAG") != "" AND $B->A("GRLBMServiceSigAG") != "[]"){
				$BPDF->style("width:150px;");
				#$BOK = new Button("Kunde hat unterschrieben", "check", "iconic");
				#$BOK->style("font-size:55px;");
				
				$attach = "";
				
				$ACS = anyC::get("GRLBM", "AuftragID", $B->A("AuftragID"));
				$ACS->addAssocV3("isR", "=", "1");
				$R = $ACS->n();
				$BI = new Button("Rechnung");
				if(!$R){
					$BI->style ("background-color:#DDD;color:grey;width:150px;");
					$BI->onclick("if(!confirm('Rechnung zu Service erstellen?')) return; CustomerPage.rme('createInvoice', {GRLBMID: '".$B->getID()."', AuftragID: '".$B->A("AuftragID")."'}, function(transport){ document.location.reload(); });");
				} else {
					$BI->onclick("CustomerPage.popup('Rechnung PDF', 'getPDFViewer', {GRLBMID: '".$R->getID()."'}, {width:'800px'});");
					$BI->style("width:150px;");
					$attach = ", attachments: ".$R->getID();
				}
				$BI->className("submitFormButton");
				
				
				$BM = new Button("Per E-Mail");
				$BM->className("submitFormButton");
				$BM->style("float:right;");
				if(!$B->A("isEMailed")){
					$BM->onclick("if(!confirm('".($R ? "Sollen die Belege" : "Soll der Service-Bericht")." per E-Mail an ".$Adresse->A("email")." verschickt werden?')) return; CustomerPage.rme('sendViaEMail', {GRLBMID: '".$B->getID()."', AuftragID: '".$B->A("AuftragID")."'$attach}, function(transport){ document.location.reload(); });");
					$BM->style ("background-color:#DDD;color:grey;");
				}
			}
			
			$T->addRow(array(
				"<span style=\"font-size:20px;font-weight:bold;\">".$B->A("prefix").$B->A("nummer")."</span><br><span style=\"color:grey;\">".Util::CLDateParser($B->A("datum"))."</span>", 
				$Adresse->getHTMLFormattedAddress(),
				"",
				$BPDF.$BI,
				$BM));
			$T->addCellStyle(1, "vertical-align:top;");
			
			$T->addRowStyle("border-bottom:1px solid #ccc;");
			
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
	
	public function sendViaEMail($data){
		$Auftrag = new Auftrag($data["AuftragID"]);
		$Auftrag->sendViaEmail($data["GRLBMID"], "", "", "", true, $data["attachments"]);
	}
	
	public function createInvoice($data){
		$Auftrag = new Auftrag($data["AuftragID"]);
		#$Auftrag->sendViaEmail($data["GRLBMID"]);
		$RID = $Auftrag->createGRLBM("R", true);
		$R = new GRLBM($RID);
		$R->copyPostenFrom($data["GRLBMID"]);
	}
	
	public function calcHours($data){
		$C = new CustomizerBelegServiceGUI();
		$C->calcHours($data["P0"], $data["P1"], $data["P2"]);
	}
	
	public function getBottom($Beleg){
		$html = "";
		
		
		$I = new Button("", "info", "iconic");
		
		for($i = 1; $i <= 4; $i++){
			$p = $i;
			if($i  == 1)
				$p = "";
			
			if($Beleg->A("GRLBMServiceMitarbeiter$p") == 0)
				continue;
			
			$U = new User($Beleg->A("GRLBMServiceMitarbeiter$p"));
			
			$action = "CustomerPage.rme('calcHours', [\$j('[name=GRLBMServiceVon$p]').val(), \$j('[name=GRLBMServiceBis$p]').val(), \$j('[name=GRLBMServicePause$p]').val()], function(transport){ \$j('[name=GRLBMServiceStunden$p]').val(transport);});";

			$IV = new HTMLInput("GRLBMServiceVon$p", "time", Util::CLTimeParserE($Beleg->A("GRLBMServiceVon$p")));
			$IV->style("width:50px;");
			$IV->onkeyup($action);
			#OnEvent::rme(new CustomizerBelegServiceGUI(), "calcHours", array("\$j('[name=GRLBMServiceVon]').val()", "\$j('[name=GRLBMServiceBis]').val()", "\$j('[name=GRLBMServicePause]').val()"), "function(t){ \$j('[name=GRLBMServiceStunden]').val(t.responseText); }"));

			$IB = new HTMLInput("GRLBMServiceBis$p", "time", Util::CLTimeParserE($Beleg->A("GRLBMServiceBis$p")));
			$IB->style("width:50px;");
			$IB->onkeyup($action);

			$IS = new HTMLInput("GRLBMServiceStunden$p", "time", Util::CLTimeParserE($Beleg->A("GRLBMServiceStunden$p")));
			$IS->style("width:50px;");
			
			$IP = new HTMLInput("GRLBMServicePause$p", "time", Util::CLTimeParserE($Beleg->A("GRLBMServicePause$p")));
			$IP->onkeyup($action);
		
			$IG = new HTMLInput("GRLBMServiceStundensatz$p", "text", Util::CLNumberParserZ($Beleg->A("GRLBMServiceStundensatz$p")));
			$IG->style("width:50px;");
			
			$BA = new Button("Hinzufügen", "plus", "iconicG");
			$BA->style("float:right;");
			$BA->onclick("CustomerPage.rme('addPersonal', [".$Beleg->getID().", ".$Beleg->A("GRLBMServiceMitarbeiter$p")."], function(transport){ CustomerPage.rme('saveService', $('#contentLeft :input').serialize(), function(){ CustomerPage.rme('getAuftrag', {GRLBMID: ".$Beleg->getID()."}, function(transport){ 
						$('#contentLeft').html(transport); 
					}, 
					function(){},
					'POST'); }, function(){}, 'POST'); })");
			if($i != 1)
				$BA = "";
			
			$T1 = new HTMLTable(3, $BA.$U->A("name"));
			$T1->addRow(array($I, "<label>Arbeitszeit:</label>", $IV." bis ".$IB));
			$T1->addRow(array("", "<label>Pause:</label>", $IP));
			$T1->addRow(array("", "<label>Stunden ges:</label>", $IS." <span style=\"float:right;\">".$IG." €/h</span>"));

			$html .= "<div style=\"width:49%;margin-right:1%;display:inline-block;\">$T1</div>";
		}
		
		$IG = new HTMLInput("GRLBMServiceIsGarantie", "checkbox", $Beleg->A("GRLBMServiceIsGarantie"));
		$IA = new HTMLInput("GRLBMServiceIsAbgeschlossen", "checkbox", $Beleg->A("GRLBMServiceIsAbgeschlossen"));
		$IE = new HTMLInput("GRLBMServiceIsBerechnung", "checkbox", $Beleg->A("GRLBMServiceIsBerechnung"));
		
		$I = new Button("", "info", "iconic");
		$T = new HTMLTable(3, "Details");
		$T->addRow(array($I, "<label>Garantie?:</label>", $IG));
		$T->addRow(array("", "<label>Abgeschlossen?:</label>", $IA));
		$T->addRow(array("", "<label>Berechnung:</label>", $IE));
		
		$html .= "<div style=\"width:49%;margin-right:1%;display:inline-block;\">$T</div>";
		
		$html .= "<div></div>";
		
		return $html.parent::getBottom($Beleg);
	}
	
	public function addPersonal($data){
		$Beleg = new GRLBM($data["P0"], false);
		
		for($i = 1; $i <= 4; $i++){
			$p = $i;
			if($i  == 1)
				$p = "";
			
			if($Beleg->A("GRLBMServiceMitarbeiter$p") != 0)
				continue;
			
			$Beleg->changeA("GRLBMServiceMitarbeiter$p", $data["P1"]);
			$Beleg->saveMe();
			break;
		}
		
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
		
		$IA = new HTMLInput("GRLBMServiceAnsprechpartner", "text", $Beleg->A("GRLBMServiceAnsprechpartner"));
		$IB = new HTMLInput("GRLBMServiceAuftraggeber", "text", $Beleg->A("GRLBMServiceAuftraggeber"));
		
		$T->addRow(array($I, "<label>Auftraggeber:</label>", $IB));
		$T->addRow(array("", "<label>Ansprechpartner:</label>", $IA));
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
		
		#print_r($data);
		
		for($i = 1; $i <= 4; $i++){
			$p = $i;
			if($i == 1)
				$p = "";
			
			if(!isset($data["GRLBMServiceVon$p"]))
				continue;
			
			$G->changeA("GRLBMServiceVon$p", Util::CLTimeParserE($data["GRLBMServiceVon$p"], "store"));
			$G->changeA("GRLBMServiceBis$p", Util::CLTimeParserE($data["GRLBMServiceBis$p"], "store"));
			$G->changeA("GRLBMServiceStunden$p", Util::CLTimeParserE($data["GRLBMServiceStunden$p"], "store"));
			$G->changeA("GRLBMServicePause$p", Util::CLTimeParserE($data["GRLBMServicePause$p"], "store"));
			$G->changeA("GRLBMServiceStundensatz$p", Util::CLNumberParserZ($data["GRLBMServiceStundensatz$p"], "store"));
		}
		
		$G->changeA("GRLBMServiceIsGarantie", $data["GRLBMServiceIsGarantie"] == "on" ? 1 : 0);
		$G->changeA("GRLBMServiceIsAbgeschlossen", $data["GRLBMServiceIsAbgeschlossen"] == "on" ? 1 : 0);
		$G->changeA("GRLBMServiceIsBerechnung", $data["GRLBMServiceIsBerechnung"] == "on" ? 1 : 0);
		$G->changeA("GRLBMServiceAnsprechpartner", $data["GRLBMServiceAnsprechpartner"]);
		$G->changeA("GRLBMServiceAuftraggeber", $data["GRLBMServiceAuftraggeber"]);
		
		$G->changeA("GRLBMServiceSigAN", $data["sigAN"]);
		if($data["sigAN"])
			$G->changeA("GRLBMServiceSigANDate", time());
		
		$G->changeA("GRLBMServiceSigAG", $data["sigKunde"]);
		try {
			$G->saveMe();
		} catch (FieldDoesNotExistException $e){
			Red::errorD("Kann nicht speichern, die Datenbank ist nicht aktuell. Das Feld ".$e->getField()." fehlt!");
		}
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