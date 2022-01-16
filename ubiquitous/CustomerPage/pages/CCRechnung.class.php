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
		
class CCRechnung extends CCAuftrag implements iCustomContent {
	function __construct() {
		parent::__construct();
		#$this->showZahlungsart = true;
		#$this->showPrices = false;
		#$this->showSignature = false;
		$this->increaseCount = true;
	}

	function getLabel(){
		return "Rechnungen";
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
		
		$BRN = new Button("Neue Barrechnung");
		$BRN->className("submitFormButton");
		$BRN->onclick("CustomerPage.rme('newInvoice', {}, function(transport){ CustomerPage.rme('getRechnungen', [], function(t){ \$('#list').html(t); }); CCAuftrag.openBeleg(transport); });");
		$BRN->style("background-color:#DDD;color:grey;margin-top:0px;float:right;margin-right:20px;");
		
		#$BRC = new Button("Neue Kundenrechnung");
		#$BRC->className("submitFormButton");
		#$BRC->onclick("CustomerPage.rme('getCustomers', {}, function(transport){ \$('#frameCustomers').html(transport); });");
		$BRC = new HTMLInput("newInvoice", "text");
		$BRC->placeholder("Kunde auswählen");
		$BRC->style("margin-top:0px;float:right;margin-right:20px;height:31px;box-sizing:border-box;font-size:18px;");
		
	
		#$IOK = new Button("Fertig");
		#$IOK->className("submitFormButton");
		#$IOK->onclick("$('#frameSelect').show(); $('#frameEdit').hide(); ");
		#$IOK->style("margin-top:0px;float:right;margin-right:20px;");

		
		return "
		<div style=\"max-width:1200px;\">
			<div id=\"frameEdit\" style=\"display:none;\">
				<div style=\"display:inline-block;width:48%;vertical-align:top;margin-right:3%;\" id=\"contentLeft\">
						".$this->getAuftrag(array("GRLBMID" => 0))."
				</div>
				<div style=\"display:inline-block;width:48%;vertical-align:top;\" id=\"contentRight\">
				
				</div>
			</div>
			<div id=\"frameSelect\">
				<div style=\"display:inline-block;width:100%;vertical-align:top;\" id=\"contentScreen\">
					<h1>{$BRL}{$BRA}{$BRN}{$BRC}Rechnungen</h1>
					<div class=\"content\" style=\"overflow:auto;\" id=\"list\">
						".$this->getRechnungen(array(/*"KategorieID" => "", "query" => "", "GRLBMID" => $GRLBMID*/))."
					</div>
				</div>
			</div>
			<div id=\"frameCustomers\">
				
			</div>
		</div>
			".OnEvent::script("
				\$('[name=newInvoice]').autocomplete({
					source: function(request, response){ 
						CustomerPage.rme('searchCustomer', [request.term], function(t){ response(JSON.parse(t)); });
					},
					select: function(event, ui) {
						CustomerPage.rme('newInvoice', [ui.item.value], function(t){ CustomerPage.rme('getRechnungen', [], function(t){ \$('#list').html(t); }); CCAuftrag.openBeleg(t); });
						return false;
					}
				});
				/*\$('#contentRight .content .tableForSelection ').parent().css('max-height', $(window).height() - $('h1').outerHeight() - 40 - $('#contentRight .Tab').outerHeight())
				\$('#contentLeft ').css('height', $(window).height() - $('h1').outerHeight() - 25)*/");

	}
	
	public function newInvoice($data){
		echo $this->newDocument($data, "R");
	}
	
	public function getRechnungen($data){
		if(!$this->loggedIn)
			return "TIMEOUT";

		$html = "";
		
		
		$T = new HTMLTable(2);#, "Bitte wählen Sie einen Lieferschein");
		$T->setTableStyle("width:100%;margin-top:10px;");
		$T->setColWidth(1, 130);
		$T->useForSelection(false);
		$T->maxHeight(400);
		
		$AC = anyC::get("GRLBM", "isR", "1");
		$AC->addJoinV3("Auftrag", "AuftragID", "=", "AuftragID");
		#$AC->addAssocV3("UserID", "=", Session::currentUser()->getID());
		$AC->addAssocV3("status", "=", "billed");
		#$AC->addOrderV3("datum", "DESC");
		$AC->addOrderV3("nummer", "DESC");
		#$AC->setLimitV3(100);
		#$AC->addJoinV3("Adresse", "t2.AdresseID", "=", "AdresseID");
		$i = 0;
		while($B = $AC->n()){
			$Adresse = new Adresse($B->A("AdresseID"));
			$T->addRow(array("<span style=\"font-size:20px;font-weight:bold;\">".$B->A("prefix").$B->A("nummer")."</span><br><span style=\"color:grey;\">".Util::CLDateParser($B->A("datum"))."</span>", $Adresse->getHTMLFormattedAddress()));
			$T->addCellStyle(1, "vertical-align:top;");
			
			$T->addRowStyle("cursor:pointer;border-bottom:1px solid #ccc;");
			
			#if($i % 2 == 1)
			#	$T->addRowStyle ("background-color:#eee;");
			
			
			$T->addRowEvent("click", "$(this).addClass('selected'); CCAuftrag.openBeleg(".$B->getID().");");
			
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
		$IOK = new Button("PDF anzeigen");
		$IOK->className("submitFormButton");
		$IOK->style("background-color:#DDD;color:grey;float:none;");
		$IOK->onclick("CustomerPage.popup('Lieferschein PDF', 'getPDFViewer', {GRLBMID: '$data[GRLBMID]'}, {width:'800px'});");
		
		return $IOK;
	}
	
	public function buttonDone($data){
		$IOK = new Button("Fertig");
		$IOK->className("submitFormButton");
		$IOK->onclick("$('#frameSelect').show(); $('#frameEdit').hide();");
		
		return $IOK;
	}
}
?>
