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
				background-image:url(./images/TimeTerminalBG.png);
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
				filter:alpha(opacity=100);
				-moz-opacity:1;
				-khtml-opacity: 1;
				opacity: 1;
				z-index: 1100;
				display:none;
			}
			
			.green {
				background-color:#72c100;
			}
			
			.red {
				background-color:#c90021;
			}
		</style>
		<script type=\"text/javascript\">
			var months = new Array('".implode("', '", Datum::getGerMonthArray())."');
			var days = new Array('".  implode("', '", Datum::getGerWeekArray())."');
		
			$(function(){
				window.setInterval(function(){
					clokk();
				}, 1000);
				
				clokk();
				
				$('#chipCode').focus();
			});
			
			function clokk(){
				var jetzt = new Date();
				$('#clock').html(jetzt.getHours()+':'+(jetzt.getMinutes() < 10 ? '0' : '')+jetzt.getMinutes());
				$('#day').html(days[jetzt.getDay()]+', '+jetzt.getDate()+'. '+months[jetzt.getMonth()]+' '+jetzt.getFullYear());
			}
			
			function stampp(){
				CustomerPage.rme('stamp', [\$('#chipCode').val(), ".$_GET["terminalID"]."], function(t){
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

					\$('.overlay').addClass(color).show();
					
					window.setTimeout(function(){
						\$('.overlay').hide().removeClass(color);
					}, 1000);
				}, function(){
					var color = 'red';
					\$('#chipCode').val('');
					\$j('.overlayText').html('Server nicht erreichbar!');

					\$('.overlay').addClass(color).show();
					
					window.setTimeout(function(){
						\$('.overlay').hide().removeClass(color);
					}, 1000);
				});
			}
		</script>";
		
		$I = new HTMLInput("chipCode");
		$I->id("chipCode");
		$I->onEnter("stampp();");
		$I->style("background-color:white;border:1px solid white;font-size:30px;width:99%;margin-top:40px;");
		
		$html .= "
			<div style=\"height:60px;\"></div>
			<div id=\"displayTime\">
				<div id=\"clock\" style=\"font-size:190px;text-align:center;margin-top:40px;color:#333;\"></div>
				<div id=\"day\" style=\"font-size:35px;text-align:right;padding-right:45px;margin-top:0px;color:#CCC;\"></div>
			</div><div></div>$I<div class=\"overlay\"><p style=\"font-size:70px;margin-top:140px;\" class=\"overlayText\"></p></div>";
		
		
		return $html;
	}
	
	public static function stamp($args){
		if(strtolower($args["P0"]) == "303005f7b4")
			die('{"status":"command", "action":"reload"}');
		
		registerClassPath("ZEData", Util::getRootPath()."personalKartei/Zeiterfassung/ZEData.class.php");
		registerClassPath("Personal", Util::getRootPath()."personalKartei/Personal/Personal.class.php");

		$A = new stdClass();

		$Date = new Datum();
		$Date->subDay();
		$Date->addDay();

		$A->ChipID = strtolower($args["P0"]);
		$A->Date = $Date->time();
		$A->Time = Util::parseTime("de_DE", date("H:i", time()));
		$A->Type = "K";
		$A->Mode = "";
		$A->TerminalID = $args["P1"];
		try {
			$ok = ZEData::addTime($A);

			if($ok != null)
				die('{"status":"OK", "message": "'.addslashes ($ok->A("vorname")."<br />".$ok->A("nachname")).'"}');
		} catch (Exception $e){
			switch($e->getCode()){
				case 100:
					die('{"status":"error", "message":"Unbekannter Chip"}');
				break;
			}
		}
	}
	
}

?>