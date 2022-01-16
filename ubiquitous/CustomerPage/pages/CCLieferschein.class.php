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
 *  2007 - 2021, open3A GmbH - Support@open3A.de
 */

ini_set('session.gc_maxlifetime', 24 * 60 * 60);
require_once __DIR__.'/CCAuftrag.class.php';
		
class CCLieferschein extends CCAuftrag implements iCustomContent {
	function __construct() {
		parent::__construct();
		
		$this->showPrices = false;
		$this->showSignature = true;
	}

	function getLabel(){
		return "Lieferscheine";
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
					<h1>{$BRL}{$BRA}Lieferscheine</h1>
					<div class=\"content\" style=\"overflow:auto;\">
						".$this->getLieferscheine(array(/*"KategorieID" => "", "query" => "", "GRLBMID" => $GRLBMID*/))."
					</div>
				</div>
			</div>
		</div>
			".OnEvent::script("
				/*\$('#contentRight .content .tableForSelection ').parent().css('max-height', $(window).height() - $('h1').outerHeight() - 40 - $('#contentRight .Tab').outerHeight())
				\$('#contentLeft ').css('height', $(window).height() - $('h1').outerHeight() - 25)*/");

	}

	public function getLieferscheine($data){
		if(!$this->loggedIn)
			return "TIMEOUT";

		$html = "";
		
		
		$T = new HTMLTable(5);#, "Bitte wählen Sie einen Lieferschein");
		$T->setTableStyle("width:100%;margin-top:10px;");
		$T->setColWidth(1, 130);
		$T->setColWidth(4, 200);
		$T->setColWidth(5, 200);
		$T->useForSelection(false);
		$T->maxHeight(400);
		
		$AC = anyC::get("GRLBM", "isL", "1");
		$AC->addJoinV3("Auftrag", "AuftragID", "=", "AuftragID");
		$AC->addAssocV3("UserID", "=", Session::currentUser()->getID());
		$AC->addAssocV3("status", "=", "delivered");
		#$AC->addOrderV3("datum", "DESC");
		$AC->addOrderV3("nummer", "DESC");
		#$AC->setLimitV3(100);
		#$AC->addJoinV3("Adresse", "t2.AdresseID", "=", "AdresseID");
		$i = 0;
		while($B = $AC->n()){
			$BPDF = new Button("PDF anzeigen");
			$BPDF->className("submitFormButton");
			$BPDF->style("background-color:#DDD;color:grey;float:right;");
			$BPDF->onclick("CustomerPage.popup('Beleg PDF', 'getPDFViewer', {GRLBMID: '".$B->getID()."'}, {width:'800px'});");
			
			
			$BM = "";
			$BOK = "";
			if($B->A("GRLBMServiceSigAG") != "" AND $B->A("GRLBMServiceSigAG") != "[]"){
				$BOK = new Button("Kunde hat unterschrieben", "check", "iconic");
				$BOK->style("font-size:55px;");
				
				$BM = new Button("Per E-Mail");
				$BM->className("submitFormButton");
				$BM->style("float:right;");
				if(!$B->A("isEMailed")){
					$BM->onclick("if(!confirm('Soll der Lieferschein per E-Mail verschickt werden?')) return; CustomerPage.rme('sendViaEMail', {GRLBMID: '".$B->getID()."', AuftragID: '".$B->A("AuftragID")."'}, function(transport){ document.location.reload(); });");
					$BM->style ("background-color:#DDD;color:grey;");
				}
				#$BM->onclick("CustomerPage.popup('Per E-Mail', 'getEMailViewer', {GRLBMID: '".$B->getID()."'}, {width:'800px'});");
			}
			
			$Adresse = new Adresse($B->A("AdresseID"));
			$T->addRow(array(
				"<span style=\"font-size:20px;font-weight:bold;\">".$B->A("prefix").$B->A("nummer")."</span><br><span style=\"color:grey;\">".Util::CLDateParser($B->A("datum"))."</span>", 
				$Adresse->getHTMLFormattedAddress(),
				$BOK,
				$BM,
				$BPDF));
			$T->addCellStyle(1, "vertical-align:top;");
			
			$T->addRowStyle("cursor:pointer;border-bottom:1px solid #ccc;");
			
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
	
	public function getStyle(){
		return ".selected {
			background-color:#ddd;
		}";
	}
	
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
	
	/*public function buttonCancel($data){
		$IOK = new Button("PDF anzeigen");
		$IOK->className("submitFormButton");
		$IOK->style("background-color:#DDD;color:grey;float:none;");
		$IOK->onclick("CustomerPage.popup('Lieferschein PDF', 'getPDFViewer', {GRLBMID: '$data[GRLBMID]'}, {width:'800px'});");
		
		return $IOK;
	}*/
	
	public function buttonDone($data){
		$IOK = new Button("Belegdaten speichern");
		$IOK->className("submitFormButton");
		$IOK->onclick("CustomerPage.rme('saveLieferschein', $('#contentLeft :input').serialize(), function(){ document.location.reload();/*$('#frameSelect').show(); $('#frameEdit').hide();*/ }, function(){}, 'POST');");
		
		return $IOK;
	}
	
	public function saveLieferschein($data){
		#print_r($data);
		if(!$this->loggedIn)
			return "TIMEOUT";
		
		$G = new GRLBM($data["GRLBMID"]);
		#$G->changeA("textbausteinUnten", $data["textbausteinUnten"]);
		#$G->changeA("GRLBMServiceVon", Util::CLTimeParserE($data["GRLBMServiceVon"], "store"));
		#$G->changeA("GRLBMServiceBis", Util::CLTimeParserE($data["GRLBMServiceBis"], "store"));
		#$G->changeA("GRLBMServiceStunden", Util::CLTimeParserE($data["GRLBMServiceStunden"], "store"));
		
		#$G->changeA("GRLBMServiceIsGarantie", $data["GRLBMServiceIsGarantie"] == "on" ? 1 : 0);
		#$G->changeA("GRLBMServiceIsAbgeschlossen", $data["GRLBMServiceIsAbgeschlossen"] == "on" ? 1 : 0);
		#$G->changeA("GRLBMServiceIsBerechnung", $data["GRLBMServiceIsBerechnung"] == "on" ? 1 : 0);
		
		$G->changeA("GRLBMServiceSigAN", $data["sigAN"]);
		if($data["sigAN"])
			$G->changeA ("GRLBMServiceSigANDate", time());
		
		$G->changeA("GRLBMServiceSigAG", $data["sigKunde"]);
		$G->saveMe();
	}
	
	/*public function buttonDone($data){
		$IOK = new Button("Fertig");
		$IOK->className("submitFormButton");
		$IOK->onclick("$('#frameSelect').show(); $('#frameEdit').hide();");
		
		return $IOK;
	}*/
}
?>
