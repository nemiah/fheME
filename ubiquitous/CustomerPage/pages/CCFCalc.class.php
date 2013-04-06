<?php
/**
 *  This file is part of FCalc.

 *  FCalc is free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 3 of the License, or
 *  (at your option) any later version.

 *  FCalc is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.

 *  You should have received a copy of the GNU General Public License
 *  along with this program.  If not, see <http://www.gnu.org/licenses/>.
 * 
 *  2007 - 2013, Rainer Furtmeier - Rainer@Furtmeier.IT
 */
class CCFCalc implements iCustomContent {
	function getLabel(){
		return "FCalc Kundenseite";
	}
	
	function getCMSHTML() {
		registerClassPath("GSRaumgruppe", Util::getRootPath()."FCalc/GSRaumgruppe/GSRaumgruppe.class.php");
		registerClassPath("GSRaumgruppe", Util::getRootPath()."FCalc/GSRaumgruppe/GSRaumgruppe.class.php");
		registerClassPath("ObjektL", Util::getRootPath()."personalKartei/ObjekteL/ObjektL.class.php");
		registerClassPath("mGSTaetigkeitGUI", Util::getRootPath()."FCalc/GSTaetigkeit/mGSTaetigkeitGUI.class.php");
		registerClassPath("GSTaetigkeit", Util::getRootPath()."FCalc/GSTaetigkeit/GSTaetigkeit.class.php");
		registerClassPath("GSQMPruefer", Util::getRootPath()."FCalc/GSQM/GSQMPruefer.class.php");

		$html = "
		<script src=\"./lib/jquery.signaturepad.min.js\"></script>
		<style type=\"text/css\">
			h3 {
				padding:7px;
				margin-top:30px;
				clear:both;
				border-bottom:1px solid #97a652;
				margin-left:-10px;
				margin-right:-10px;
				background-color:rgb(".mGSTaetigkeitGUI::$fillColor[0].", ".mGSTaetigkeitGUI::$fillColor[1].", ".mGSTaetigkeitGUI::$fillColor[2].");
			}
			
			.backgroundColor1 {
				background-color:rgb(".mGSTaetigkeitGUI::$fillColor[0].", ".mGSTaetigkeitGUI::$fillColor[1].", ".mGSTaetigkeitGUI::$fillColor[2].");
			}
			
			h4 {
				padding-top:15px;
				padding-bottom:15px;
			}
			
			.frameRight td {
				padding-left:3px;
				padding-top:3px;
				padding-bottom:3px;
				cursor:pointer;
			}
			
			.frameRight tr:hover {
				background-color:rgb(".mGSTaetigkeitGUI::$fillColor[0].", ".mGSTaetigkeitGUI::$fillColor[1].", ".mGSTaetigkeitGUI::$fillColor[2].");
			}
			
			.frameRight {
				border-left:1px solid #97a652;
				padding-left:10px;
			}
			
			.frameLeft td {
				padding-top:3px;
				padding-bottom:3px;
				
			}
			
			@media print { 
				.frameRight {
					display:none;
				}
				
				h3 {
					page-break-before:always;
				}
				
				h2 + h3 {
					 page-break-before:avoid;
				}
			}
			
			.buttonQuestion {
				font-size:30px;
				padding-top:40px;
				padding-bottom:40px;
				text-align:center;
				width:120px;
				margin-right:20px;
			}
			
			.box {
				margin-top:20px;
				width:420px;
				padding:30px;
				border-width:1px;
				border-style:solid;
				border-radius:5px;
				box-shadow:2px 2px 2px #bbb;
				margin-left:10px;
				margin-bottom:20px;
			}
			
			#headerStep1 span, #headerStep2 span, #headerStep3 span, #headerStep4 span {
				/*-webkit-transform: translate(15px,200px) rotate(270deg);
				-moz-transform: translate(15px,200px) rotate(270deg);
				-o-transform: translate(15px,200px) rotate(270deg);
				-ms-transform: translate(15px,200px) rotate(270deg);
				-moz-transform-origin: top left;
				-webkit-transform-origin: top left;
				-o-transform-origin: top left;
				-ms-transform-origin: top left;*/
			}
			
			#headerStep1, #headerStep2, #headerStep3, #headerStep4 {
				border-bottom-width:0px;
				border-right-width:1px;
				border-right-style:solid;
				display: inline-block;
				width:30px;
				height:450px;
				margin-right:20px;
			}
			
			#step1, #step2, #step3, #step4 {
				width:600px;
				display: inline-block;
				margin-top:40px;
				vertical-align: top;
			}
			
			#selectPruefer {
				list-style-type: none;
			}

			#selectPruefer li, #selectCategories li {
				font-size:15px;
				padding:10px;
			}
		</style>
		<script type=\"text/javascript\">
			var CP_LV = {
			
			}
			
			var CP_QM = {
				questions: null,
				RaumGruppen: new Array(),
				qCounter: -1,
				cCounter: 0,
				categories: null,
				answers: {},
				author: null,
				
				addRaumGruppe: function(RaumGruppeID){
					if($.inArray(RaumGruppeID, CP_QM.RaumGruppen) >= 0){
						CP_QM.RaumGruppen.splice( $.inArray(RaumGruppeID, CP_QM.RaumGruppen), 1 );
					} else {
						CP_QM.RaumGruppen.push(RaumGruppeID);
					}
					
					CP_QM.updateOrderDisplay();
				},
				
				updateOrderDisplay: function(){
					$('.orderDisplay').html('&nbsp;');
					
					for(var i = 0; i < CP_QM.RaumGruppen.length; i++){
						$('#orderDisplay'+CP_QM.RaumGruppen[i]).html(i + 1);
					}
				},
				
				doQuestionsAgain: function(){
					$('#step3').show();
					$('#step4').hide();
					
					CP_QM.qCounter = 0;
					CP_QM.cCounter = 0;
					CP_QM.answers = {};
					
					CP_QM.showQuestion();
					
					$('#headerStep4').css('background-color', 'white');
					$('#headerStep3').css('background-color', '');
				},
				
				save: function(){
					var value = $.jStorage.get('QM');
					if(!value){
						$.jStorage.set('QM', [{ 'answers': CP_QM.answers, 'signature' : $('.sigPad input[name=output]').val(), 'author' : CP_QM.author, 'timestamp' : Math.round(+new Date()/1000) }])
					} else {
						value.push({ 'answers': CP_QM.answers, 'signature' : $('.sigPad input[name=output]').val(), 'author' : CP_QM.author, 'timestamp' : Math.round(+new Date()/1000) });
						$.jStorage.set('QM', value)
					}
					CP_QM.view();
					//console.log($.jStorage.get('QM'));
				},
				
				upload: function(){
					$.ajax({url: './index.php?CC=FCalc&M=checkConnection', success: function(transport){
						if(transport != '1')
							return;
							
						CustomerPage.rme('saveData', 'data='+JSON.stringify($.jStorage.get('QM')), function(){ /*$.jStorage.deleteKey('QM');*/ });
						
					},
					
					error: function(XMLHttpRequest, textStatus, errorThrown) {
						if(textStatus == 'timeout')
							alert('Keine Verbindung zum Server!');
						
					},
					
					type: 'GET'});
						
/*

					var value = $.jStorage.get('QM');
					if(!value){
						$('#view').html('Es wurden keine Prüfungen gespeichert');
					} else {
						for(var i = 0; i < value.length; i++){
							
						}
						
						
					}*/
					
					
				},
				
				view: function(){
					var value = $.jStorage.get('QM');
					if(!value){
						$('#view').html('Es wurden keine Prüfungen gespeichert');
					} else {
						var s = '<ul>';
						
						console.log(value);
						for(var i = 0; i < value.length; i++){
							var date = new Date(value[i].timestamp*1000);
							var hours = date.getHours();
							var minutes = date.getMinutes();
							//var seconds = date.getSeconds();
							var year = date.getFullYear();
							var month = date.getMonth();
							var day = date.getDate();


							s += '<li>Prüfung vom '+day+'.'+month+'.'+year+', '+hours+':'+minutes+' Uhr</li>';
						}
						
						s += '</ul>';
						
						$('#view').html(s);
					}
				},

				new: function(){
					$('#step1').show();
					$('#step4').hide();
					
					CP_QM.qCounter = -1;
					CP_QM.cCounter = 0;
					CP_QM.answers = {};
					
					//CP_QM.showQuestion();

					$('#headerStep4').css('background-color', 'white');
					$('#headerStep1').css('background-color', '');
				},

				step2: function(authorID){
					$('#step1').hide();
					$('#step2').show();
					
					CP_QM.author = authorID;
					
					$('#headerStep1').css('background-color', 'white');
					$('#headerStep2').css('background-color', '');
					
				},

				step3: function(){
					$('#step1').hide();
					$('#step2').hide();
					$('#step3').show();
					
					$('#headerStep2').css('background-color', 'white');
					$('#headerStep3').css('background-color', '');
					
					CP_QM.nextQuestion();
				},
				
				step4: function(){
					$('#step3').hide();
					$('#step4').show();
					
					$('#headerStep3').css('background-color', 'white');
					$('#headerStep4').css('background-color', '');
					
					CP_QM.view();
					
					//console.log(CP_QM.answers);
				},
				
				answerQuestion: function(answer){
					if(typeof CP_QM.answers[CP_QM.cCounter] == 'undefined')
						CP_QM.answers[CP_QM.cCounter] = {};
						
					var current = CP_QM.questions[CP_QM.RaumGruppen[CP_QM.cCounter]][CP_QM.qCounter];
					
					CP_QM.answers[CP_QM.cCounter][CP_QM.qCounter] = {'answer' : answer, 'object' : current };
					
					//console.log(CP_QM.answers);
					
					CP_QM.nextQuestion();
				},

				nextQuestion: function(){
					CP_QM.qCounter++;
					
					if(CP_QM.qCounter >= CP_QM.questions[CP_QM.RaumGruppen[CP_QM.cCounter]].length) {
						CP_QM.qCounter = 0;
						CP_QM.cCounter++;
						CP_QM.showCategoryBox();
					}
					
					if(CP_QM.cCounter >= CP_QM.RaumGruppen.length){
						CP_QM.step4();
						return;
					}
					
					CP_QM.showQuestion();
				},
				
				previousQuestion: function(){
					CP_QM.qCounter--;
					
					if(CP_QM.qCounter == -1) {
						CP_QM.cCounter--;
						CP_QM.qCounter = CP_QM.questions[CP_QM.RaumGruppen[CP_QM.cCounter]].length - 1;
						CP_QM.showCategoryBox();
					}
					
					if(CP_QM.cCounter == -1){
						//CP_QM.step4();
						return;
					}
					
					CP_QM.showQuestion();

					
				},

				showQuestion: function(){
					$('#buttonPreviousQuestion').removeAttr('disabled');
					$('.buttonQuestion').css('background-color', '');
					
					if(CP_QM.qCounter == 0 && CP_QM.cCounter == 0)
						$('#buttonPreviousQuestion').attr('disabled', 'disabled');

					if(typeof CP_QM.answers[CP_QM.cCounter] != 'undefined' && CP_QM.answers[CP_QM.cCounter][CP_QM.qCounter] != undefined){
						var B = 'Nein';
						
						if(CP_QM.answers[CP_QM.cCounter][CP_QM.qCounter] == 1)
							B = 'Teils';
							
						if(CP_QM.answers[CP_QM.cCounter][CP_QM.qCounter] == 2)
							B = 'Ja';
							
						$('#button'+B).css('background-color', '#F5FFC5');
					}

					$('#category').html(CP_QM.categories[CP_QM.RaumGruppen[CP_QM.cCounter]]);
					$('#categoryInfo').html(CP_QM.categories[CP_QM.RaumGruppen[CP_QM.cCounter]]);
					
					var current = CP_QM.questions[CP_QM.RaumGruppen[CP_QM.cCounter]][CP_QM.qCounter];
					var q = current.label;
					if(current.turnusWoechentlich > 0)
						q += ' '+current.turnusWoechentlich+'xW';
						
					if(current.turnusMonatlich > 0)
						q += ' '+current.turnusMonatlich+'xM';
						
					if(current.turnusJaehrlich > 0)
						q += ' '+current.turnusJaehrlich+'xJ';
						
					$('#question').html(q);
					
					$('#qCounter').html((CP_QM.qCounter + 1)+'/'+CP_QM.questions[CP_QM.RaumGruppen[CP_QM.cCounter]].length);
					$('#cCounter').html((CP_QM.cCounter + 1)+'/'+CP_QM.RaumGruppen.length);
				},
				
				closeCategoryBox: function(){
					$('#categoryBox').hide();
					$('#questionBox').show();
				},
				
				showCategoryBox: function(){
					$('#categoryBox').show();
					$('#questionBox').hide();
				}
			}
			$(document).ready(function(){
				$('#selectPruefer').attr('size', 10);
			});
		</script>";
		
		
		$OID = $_GET["OBJ"] / GSRaumgruppe::$mult;

		$O = new ObjektL($OID);

		switch($_GET["A"]){
			case "LV":
				if(file_exists(Util::getRootPath()."specifics/CPLogo.png"))
					$html .= "<img src=\"../../specifics/CPLogo.png\" style=\"float:right;\" />";
				
				$html .= "<h1>Leistungsverzeichnis</h1>
					<h2>für ".$O->A("objektName")."</h2>";


				$GSR = anyC::get("GSRaumgruppe", "GSRaumgruppeObjektLID", $OID);
				$GSR->addOrderV3("GSRaumgruppeKuerzel");

				while($R = $GSR->getNextEntry()){
					$html .= "<h3>".$R->A("GSRaumgruppeKuerzel")." (".$R->A("GSRaumgruppeName").")</h3>
						<div style=\"max-width:1200px;\">";

					$leftFrame = "<div style=\"width:490px;float:left;\" class=\"frameLeft\" id=\"frameLeft".$R->getID()."\">".$this->leftFrame($R)."</div>";

					$rightFrame = "<div style=\"float:right;width:490px;margin-bottom:20px;\" class=\"frameRight\" id=\"frameRight".$R->getID()."\">".$this->rightFrame($R)."</div>";

					$html .= "$rightFrame$leftFrame<div style=\"clear:both;\"></div></div>";

				}
		
			break;
			
			case "QM":
				
				$html .= "<h1>Qualitätsmanagement</h1>
					<h2>für ".$O->A("objektName")."</h2>";
				
				$html .= "<h3 id=\"headerStep1\" class=\"borderColor1\"><span>".$this->getStringImage("Prüfer")."</span></h3><div id=\"step1\">
					<p>Bitte wählen Sie den Prüfer:</p>";
				
				$L = new HTMLList();
				$L->setListID("selectPruefer");
				#$pruefer = array(0 => "bitte auswählen...");
				#$Users = Users::getUsers();
				$ACP = anyC::get("GSQMPruefer");
				$ACP->addJoinV3("Personal", "GSQMPrueferPersonalID", "=", "PersonalID");
				$ACP->addOrderV3("nachname");
				$ACP->addOrderV3("vorname");
				while($U = $ACP->getNextEntry())
					$L->addItem("<a href=\"#\" onclick=\"CP_QM.step2(".$U->getID()."); return false;\">".$U->A("nachname")." ".$U->A("vorname")."</a>");
					#$pruefer[$U->getID()] = $U->A("name");
				
				
				#$IU = new HTMLInput("pruefer", "select", "0", $pruefer);
				#$IU->onchange("if(this.value > 0) CP_QM.step2();");
				#$IU->id("selectPruefer");
				#$IU->style("width:400px;");
				
				$html .= $L;
				
				$html .= "</div>
					<h3 id=\"headerStep2\" style=\"background-color:white;\" class=\"borderColor1\"><span>".$this->getStringImage("Kategorienauswahl")."</span></h3>
					<div id=\"step2\" style=\"display:none;\">
					
					<p>Bitte wählen Sie die Kategorien in der Reihenfolge, in der Sie prüfen möchten:</p>
					<ul style=\"list-style-type:none;\" id=\"selectCategories\">";
				
				$GSR = anyC::get("GSRaumgruppe", "GSRaumgruppeObjektLID", $OID);
				$GSR->addOrderV3("GSRaumgruppeKuerzel");
				$data = array();
				$categories = array();
				while($R = $GSR->getNextEntry()){
					$html .= "
						<li style=\"margin-bottom:10px;\">
							<div style=\"width:30px;float:left;margin-right:10px;text-align:right;font-weight:bold;\" class=\"orderDisplay\" id=\"orderDisplay".$R->getID()."\">&nbsp;</div>
							<div style=\"margin-left:40px;\"><a href=\"#\" onclick=\"CP_QM.addRaumGruppe(".$R->getID()."); return false;\">".$R->A("GSRaumgruppeKuerzel")."
							<span style=\"color:grey;\">(".$R->A("GSRaumgruppeName").")</span></div></a>
						</li>";
				
					$data[$R->getID()] = json_encode($R->getQuestions());
					$categories[$R->getID()] = $R->A("GSRaumgruppeName");
				}
				
				$GSR->resetPointer();
				
				
				$BOK = new Button("Weiter", "");
				$BOK->onclick("CP_QM.step3();");
				
				
				$html .= "
				<script type=\"text/javascript\">
					CP_QM.questions = {";
				foreach($data AS $RGID => $D)
					$html .= "\n".$RGID.": $D,";
				
				$html .= "
					}
					
					CP_QM.categories = ".  json_encode($categories).";
				</script>";
				
				$html .= "</ul>
					$BOK
					</div>";
				
				$BJa = new Button("Ja");
				$BJa->onclick("CP_QM.answerQuestion(2);");
				$BJa->id("buttonJa");
				$BJa->className("buttonQuestion");
				
				$BTeils = new Button("Teils");
				$BTeils->onclick("CP_QM.answerQuestion(1);");
				$BTeils->id("buttonTeils");
				$BTeils->className("buttonQuestion");
				
				$BNein = new Button("Nein");
				$BNein->onclick("CP_QM.answerQuestion(0);");
				$BNein->id("buttonNein");
				$BNein->className("buttonQuestion");
				
				$BP = new Button("vorherige Frage");
				$BP->onclick("CP_QM.previousQuestion(0);");
				$BP->id("buttonPreviousQuestion");
				
				$BN = new Button("nächste Frage");
				$BN->onclick("CP_QM.nextQuestion(0);");
				$BN->id("buttonNextQuestion");
				
				$BW = new Button("Weiter");
				$BW->onclick("CP_QM.closeCategoryBox();");
				$BW->style("float:right;");
				
				$html .= "
						<h3 id=\"headerStep3\" class=\"borderColor1\" style=\"background-color:white;\"><span>".$this->getStringImage("Prüfen")."</span></h3>
						<div id=\"step3\" style=\"display:none;\">
						<div id=\"questionBox\" style=\"display:none;\">
							<p>$BP $BN</p>
							<div class=\"box borderColor1\">
								<p style=\"\">Kategorie: <span id=\"category\"></span></p>
								<p style=\"font-size:20px;font-weight:bold;\" id=\"question\"></p>
								<table>
									<tr>
										<td>$BJa</td>
										<td>$BTeils</td>
										<td>$BNein</td>
									</tr>
								</table>
							</div>
						</div>
						<div class=\"borderColor1 box\" id=\"categoryBox\">
							<span style=\"font-size:30px;\">Kategorie:</span><br/><br/><span id=\"categoryInfo\" style=\"font-size:15px;\"></span><br /><br/>
							$BW
						</div>
						<p>Fragen in dieser Kategorie: <span id=\"qCounter\"></span></p>
						<p>Kategorien: <span id=\"cCounter\"></span></p>
					</div>";
				
				$BNochmal = new Button("Fragen erneut\nausfüllen");
				$BNochmal->onclick("CP_QM.doQuestionsAgain();");
					
				$BSave = new Button("Prüfung\nspeichern");
				$BSave->onclick("CP_QM.save();");
					
				$BNew = new Button("Neue\nPrüfung");
				$BNew->onclick("CP_QM.new();");
					
				$BUpload = new Button("Daten\nhochladen");
				$BUpload->onclick("CP_QM.upload();");
								
				$html .= "
					<h3 id=\"headerStep4\" class=\"borderColor1\" style=\"background-color:white;\"><span>".$this->getStringImage("Abschluss")."</span></h3>
					<div id=\"step4\" style=\"display:none;\">
						<div class=\"backgroundColor1\" style=\"height:450px;width:290px;float:right;\">
							<div id=\"view\"></div>
							$BUpload
						</div>
						$BNochmal
				
						<div class=\"sigPad\" style=\"margin-top:20px;\">
							<p>Unterschrift des Kunden:</p>
							<div>
								<canvas style=\"border-width:1px;border-style:solid;width:300px;height:150px;\" class=\"pad borderColor1\"></canvas>
								<input type=\"hidden\" name=\"output\" class=\"output\">
							</div>
							$BSave
							<!--<a href=\"#clear\">Leeren</a>-->
						</div>
						
						$BNew
					</div>
				
					<script type=\"text/javascript\">$(document).ready(function() { $('.sigPad').signaturePad({drawOnly:true}); });</script>";
			break;
		}
		
		return $html;
	}
	
	public function checkConnection(){
		die("1");
	}
	
	public function saveData($jsonString){
		$a = json_decode($jsonString["data"]);
		foreach($a AS $set)
			print_r($set->timestamp);
	}
	
	public function addTaetigkeit(){
		$GST = new mGSTaetigkeitGUI();
		
		$GST->addTaetigkeitToRaumgruppe($_GET["P0"], $_GET["P1"]);
		
		$_GET["RGID"] = $_GET["P0"];
		echo $this->leftFrame();
	}
	
	public function leftFrame($R = null){
		if($R == null)
			$R = new GSRaumgruppe($_GET["RGID"]);
		
		$GST = $this->getTaetigkeiten($R);

		$Tab = new HTMLTable(3);
		$Tab->setColWidth(1, 500);
		$Tab->setColWidth(2, 120);
		$Tab->setColWidth(3, 120);
		$Tab->addColStyle(1, "text-align:left;font-size:11px;");
		$Tab->addColStyle(2, "text-align:right;");
		$Tab->addColStyle(3, "text-align:right;");

		$Tab->addHeaderRow(array("", "wöchentlich", "jährlich"));

		$i = 0;
		while($T = $GST->getNextEntry()){
			$IW = new HTMLInput("GSTaetigkeitTurnusWoechentlich", "select", $T->A("GSTaetigkeitTurnusWoechentlich"), mGSTaetigkeitGUI::$woechentlich);
			$IW->style("text-align:right;width:50px;");

			$IJ = new HTMLInput("GSTaetigkeitTurnusJaehrlich", "select", $T->A("GSTaetigkeitTurnusJaehrlich"), mGSTaetigkeitGUI::$jaehrlich);
			$IJ->style("text-align:right;width:50px;");

			$Tab->addRow(array($T->A("GSTaetigkeitName"), $IW, $IJ));

			$i++;
		}
		
		return "<h4>Zugeordnete Tätigkeiten</h4>$Tab".($i == 0 ? "<p>Keine Tätigkeiten zugeordnet</p>" : "");
	}
	
	public function rightFrame($R = null){
		if($R == null)
			$R = new GSRaumgruppe($_GET["RGID"]);
		
		$GSTG = anyC::get("GSTaetigkeit", "GSTaetigkeitGSRaumgruppeID", "0");
		$GSTG->addOrderV3("GSTaetigkeitName");

		$GST = $this->getTaetigkeiten($R);
		$used = array();
		while($T = $GST->getNextEntry())
			$used[$T->A("GSTaetigkeitParentID")] = true;
		
		
		$Tab = new HTMLTable(1);
		$Tab->setColWidth(1, 500);
		$Tab->maxHeight(300);
		while($T = $GSTG->getNextEntry()){
			if(isset($used[$T->getID()]))
				continue;

			$Tab->addRow(array($T->A("GSTaetigkeitName")));
			$Tab->setRowID("GST_".$R->getID()."_".$T->getID());
			$Tab->addRowEvent("click", "CustomerPage.rme('addTaetigkeit', [".$R->getID().", ".$T->getID()."], function(transport) { $('#frameLeft".$R->getID()."').html(transport); $('#GST_".$R->getID()."_".$T->getID()."').hide(); });");
		}

		$GSTG->resetPointer();
		
		return "<h4>Verfügbare Tätigkeiten</h4>$Tab";
	}
	
	public function getStringImage($string){
		$im = imagecreate(30, 200);

		$bg = imagecolorallocate($im, 255, 255, 255);
		imagecolortransparent($im, $bg);
		$textcolor = imagecolorallocate($im, 0, 0, 0);
		$font = realpath(__DIR__."/../lib/Ubuntu-Regular.ttf");
		
		$bbox = imageftbbox(15, 90, $font, utf8_decode($string));
		imagettftext($im, 15, 90, 26, $bbox[3]*-1, $textcolor, $font, utf8_decode($string));
		
		ob_start();
		imagepng($im);
		$img = ob_get_clean();
		ob_end_flush();
		imagedestroy($im);
		
		return "<img src=\"data:image/png;base64,".base64_encode($img)."\" />";
	}
	
	public function getTaetigkeiten($R){
		$GST = new mGSTaetigkeitGUI();
		$GST->addAssocV3("GSTaetigkeitGSRaumgruppeID", "=", $R->getID());
		$GST->addOrderV3("GSTaetigkeitTurnusWoechentlich", "DESC");
		$GST->addOrderV3("GSTaetigkeitTurnusJaehrlich", "DESC");
		
		return $GST;
	}
}

?>