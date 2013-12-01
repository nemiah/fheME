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
class CCTimeTerminal implements iCustomContent {
	function getLabel(){
		return "Zeiterfassungs-Terminal";
	}
	
	function getTitle(){
		return "Zeiterfassungs-Terminal";
	}
	
	function getCMSHTML() {
		if(!isset($_GET["terminalID"]))
			return "<p>Keine Terminal-ID per GET übergeben! (terminalID)</p>";
		
		if(!isset($_GET["done"])){
			return "<p style=\"font-size:30px;\">Terminal IP: ".$_SERVER['REMOTE_ADDR']."<br />Server IP: ".$_SERVER['SERVER_ADDR']."</p>".OnEvent::script("window.setTimeout(function(){ document.location.href=\"?".$_SERVER['QUERY_STRING']."&done=true\"; }, 3000);");
		}
		
		$html = "
		<style type=\"text/css\">
			html {
				overflow-y: hidden;
			}
			body {
				/*background-image:url(./images/TimeTerminalBG.svg);
				background-size:100%;*/
				margin:0px;
			}
			.message {
			
			}
			
			.overlay {
				position: fixed;
				top: 0;
				left: 0;
				width: 100%;
				height: 100%;
				background-color: #72c100;
				z-index: 1100;
				display:none;
			}
			
			.overlayCenter {
				position: fixed;
				top: 6%;
				left: 3%;
				width: 94%;
				height: 88%;
				background-color: white;
				z-index: 1101;
			}
			
			.green {
				background-color:#72c100;
			}
			
			.red {
				background-color:#c90021;
			}
			
			.touchField {
				background-color:#CCC;
				margin-top:40px;
			}
			
			.currentAction {
				background-color:#c5ffab;
			}
		</style>
		<script type=\"text/javascript\">
			var months = new Array('".implode("', '", Datum::getGerMonthArray())."');
			var days = new Array('".  implode("', '", Datum::getGerWeekArray())."');
			var action = 'K';

			$(function(){
				window.setInterval(function(){
					clokk();
				}, 1000 * 30);
				
				clokk();
				
				$('#chipCode').focus();
			});
			
			function clokk(){
				var jetzt = new Date();
				$('#clock').html(jetzt.getHours()+':'+(jetzt.getMinutes() < 10 ? '0' : '')+jetzt.getMinutes());
				$('#day').html(days[jetzt.getDay()]+', '+jetzt.getDate()+'. '+months[jetzt.getMonth()]+' '+jetzt.getFullYear());
			}
			
			function stampp(){
				CustomerPage.rme('stamp', [\$('#chipCode').val(), ".$_GET["terminalID"].", action], function(t){
					if(t == '')
						return;
						
					var r = jQuery.parseJSON(t);
					\$('#chipCode').val('');
					if(r.status == 'command' && r.action =='reload'){
						document.location.reload();
						return;
					}

					var color = 'green';
					if(r.status == 'error')
						color = 'red';

					\$j('.overlayText').html(r.message);
					\$j('.overlayDetails').html('');
					if(r.details)
						\$j('.overlayDetails').html(r.details);

					\$('.overlay').addClass(color).show();
					
					window.setTimeout(function(){
						\$('.overlay').hide().removeClass(color);
					}, 2000);
					
					if(r.status == 'OK')
						moep();

				}, function(){
					var color = 'red';
					\$('#chipCode').val('');
					\$j('.overlayText').html('Server nicht erreichbar!');
					\$j('.overlayDetails').html('');

					\$('.overlay').addClass(color).show();
					
					window.setTimeout(function(){
						\$('.overlay').hide().removeClass(color);
					}, 1000);
				});
			}
			
			function moep(){
				if(typeof alex == 'undefined')
					return;
				
				alex.beep();

			}

			//if(typeof alex != 'undefined')
			//	alex.louder(100);
			
			moep();

			$('html').click(function() {
				$('#chipCode').focus();
			}); 

			$(function(){
				$('.touchField').css('height', $(window).height() - $('#displayTime').outerHeight() - 40 - 130);
				$('.touchField').hammer().on('touch release', function(ev){
					switch(ev.type){
						case 'touch':
							$(ev.target).closest('.touchField').css('background-color', 'rgba(255,204,0,0.3)');
						break;
						
						case 'release':
							$(ev.target).closest('.touchField').css('background-color', '');
							action = $(ev.target).closest('.touchField').data('action');
							$('.currentAction').removeClass('currentAction');
							$(ev.target).closest('.touchField').addClass('currentAction');
						break;
					}
				});
			});

		</script>";
		
		$I = new HTMLInput("chipCode");
		$I->id("chipCode");
		$I->onEnter("stampp();");
		$I->style("background-color:white;border:1px solid white;font-size:30px;width:96%;position:fixed;bottom:20px;");
		
		$BK = new Button("Kommen", "arrow_right", "iconic");
		$BK->style("font-size:200px;width:auto;");
		
		$BG = new Button("Gehen", "arrow_left", "iconic");
		$BG->style("font-size:200px;width:auto;");
		
		$html .= "
			<div id=\"displayTime\">
				<div id=\"clock\" style=\"font-size:190px;text-align:center;margin-top:40px;color:#333;\"></div>
				<div id=\"day\" style=\"font-size:35px;text-align:right;padding-right:45px;margin-top:0px;color:#CCC;\"></div>
			</div>
			<div>
				<div data-action=\"G\" class=\"touchField\" style=\"width:48%;display:inline-block;margin-right:3.7%;vertical-align:top;\">
					<p style=\"padding:30px;font-size:60px;\">Gehen<br />$BG</p>
				</div>
				<div data-action=\"K\" class=\"touchField currentAction\" style=\"width:48%;display:inline-block;vertical-align:top;\">
					<p style=\"padding:30px;font-size:60px;text-align:right;\">Kommen<br />$BK</p>
				</div>
			</div>
			$I
			<div class=\"overlay\">
				<div class=\"overlayCenter\">
					<p style=\"font-size:70px;margin-top:60px;font-weight:bold;\" class=\"overlayText\"></p>
					<p style=\"font-size:50px;margin-top:60px;\" class=\"overlayDetails\"></p>
				</div>
			</div>";
		
		
		return $html;
	}
	
	public static function stamp($args){
		#if(strtolower($args["P0"]) == "303005f7b4")
		#	die('{"status":"command", "action":"reload"}');
		
		if(!isset($_SESSION["BPS"]))
			$_SESSION["BPS"] = new BackgroundPluginState();

		addClassPath(Util::getRootPath()."personalKartei/Zeiterfassung/");
		addClassPath(Util::getRootPath()."personalKartei/Personal/");
		addClassPath(Util::getRootPath()."personalKartei/ObjekteL/");
		addClassPath(Util::getRootPath()."open3A/Kategorien/");
		
		$T = anyC::getFirst("ZETerminal", "ZETerminalID", $args["P1"]);
		if(!$T)
			die('{"status":"error", "message":"Unbekanntes Terminal"}');
		
		
		$A = new stdClass();

		$Date = new Datum();
		$Date->subDay();
		$Date->addDay();

		$A->ChipID = trim(strtolower($args["P0"]));
		$A->Date = $Date->time();
		$A->Time = Util::parseTime("de_DE", date("H:i", time()));
		$A->Type = $args["P2"];
		$A->Mode = "";
		$A->TerminalID = $args["P1"];
		try {
			$ok = ZEData::addTime($A);

			if($args["P2"] == "G"){
				$AC = anyC::get("ZEData", "ZEDataChipID", trim(strtolower($args["P0"])));
				$AC->addAssocV3("ZEDataType", "=", "K");
				$AC->addAssocV3("ZEDataDate + ZEDataTime", ">", time() - 3600 * 13);
				$AC->addOrderV3("ZEDataID", "DESC");
				$AC->addAssocV3("ZEDataIsDeleted", "=", "0");
				$AC->setLimitV3("1");

				$D = $AC->getNextEntry();
				if($D != null){
					$pause = ZEAuswertung::calcPause($D, $ok["ZEData"]);
					if($pause !== null){
						$DE = $ok["ZEData"];
						$DE->changeA("ZEDataPause", $pause);
						$DE->saveMe(false, false);
					}
				}
			}#303046a1b7
			
			BPS::setProperty("ZEAuswertung", "objektLID", $T->A("ZETerminalObjektLID"));
			BPS::setProperty("ZEAuswertung", "personalID", $ok["Personal"]->getID());
			BPS::setProperty("ZEAuswertung", "month", date("Ym"));
			
			$ZEA = new ZEAuswertung(trim(strtolower($args["P0"])));
			$ZEA->debug = false;
			$current = $ZEA->getContent();
			
			die('{"status":"OK", "message": "'.addslashes ($ok["Personal"]->A("vorname")." ".$ok["Personal"]->A("nachname")).'", "details": ""}');#Stunden '.Util::CLMonthName(date("m")).': '.Util::formatSeconds($current["totalHours"][1], false).'
			
		} catch (Exception $e){
			switch($e->getCode()){
				case 100:
					die('{"status":"error", "message":"Unbekannter Chip"}');
				break;
				default:
					die('{"status":"error", "message":"'.$e->getMessage().'"}');
			}
		}
	}
	
}

?>