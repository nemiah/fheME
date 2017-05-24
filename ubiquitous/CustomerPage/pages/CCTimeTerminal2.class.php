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
 *  2007 - 2017, Furtmeier Hard- und Software - Support@Furtmeier.IT
 */
class CCTimeTerminal2 implements iCustomContent {
	protected $switch = false;

	function getLabel(){
		return "Zeiterfassungs-Terminal";
	}
	
	function getTitle(){
		return "Zeiterfassungs-Terminal";
	}
	
	function getCMSHTML() {
		if(!isset($_GET["terminalID"]))
			return "<p>Keine Terminal-ID per GET übergeben! (terminalID)</p>";
		
		if(!isset($_GET["done"]))
			return "<p style=\"font-size:30px;\">
				Terminal IP: ".$_SERVER['REMOTE_ADDR']."<br>
				Server IP: ".$_SERVER['SERVER_ADDR']."</p>
				<p>
				<a href=\"#\" onclick=\"$.jStorage.set('pKTransferStack', []); $.jStorage.set('pKTimeStack', []); return false;\">Datenspeicher löschen</a>
				</p>".OnEvent::script("window.setTimeout(function(){ document.location.href=\"?".$_SERVER['QUERY_STRING']."&done=true\"; }, 3000);");
		
		
		self::loadClasses();
		
		$AC = self::getPersonal();
		$json = $AC->asJSON();
		
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
			
			.orange {
				background-color:orange;
			}
			
			.touchField {
				background-color:#CCC;
				margin-top:40px;
			}
			
			.currentAction {
				background-color:#c5ffab;
			}
			
			.overlay ul {
				list-style-type:none;
			}

			.overlay li {
				font-size:30px;
				font-weight:normal;
				padding-top:14px;
				padding-bottom:14px;
			}
		</style>
		<script type=\"text/javascript\">
			var months = new Array('".implode("', '", Datum::getGerMonthArray())."');
			var days = new Array('".  implode("', '", Datum::getGerWeekArray())."');
			var action = '".($this->switch ? "G" : "K")."';
			var inOverlay = false;
			var inTransfer = false;
			var Personal = $json;
			var PersonalLastUpdate = ".time().";
			var timeoutTransfer = null;
			
			$(function(){
				window.setInterval(function(){
					clokk();
				}, 1000 * 30);
				
				clokk();
				
				$('#chipCode').focus();
				
				window.setInterval(function(){
					if(inOverlay || document.activeElement.id == 'chipCode')
						return;
					
					$('#chipCode').focus();
				}, 1000);
				
				window.setInterval(function(){
					alive();
				}, 60000);
			});
			
			function alive(){
				CustomerPage.rme('alive', [".$_GET["terminalID"].", PersonalLastUpdate], function(t){
					\$('#aliveStatus').css('background-color', 'green');
					
					if(t != ''){
						Personal = JSON.parse(t);
						PersonalLastUpdate = Math.round(Date.now() / 1000);
						//console.log(Personal);
					}
				}, function(){
					\$('#aliveStatus').css('background-color', 'red');
				});
			}

			function clokk(){
				var jetzt = new Date();
				$('#clock').html(jetzt.getHours()+':'+(jetzt.getMinutes() < 10 ? '0' : '')+jetzt.getMinutes());
				$('#day').html(days[jetzt.getDay()]+', '+jetzt.getDate()+'. '+months[jetzt.getMonth()]+' '+jetzt.getFullYear());
			}
			
			function heightContent(){
				var max = \$j(window).height() / 100 * 88;
				
				\$j.each(\$j('.overlayContentSub'), function(k, v){
					max -= \$j(v).outerHeight();
				});

				return max;
			}

			function transfer(){
				if(inOverlay){
					timeoutTransfer = window.setTimeout(function(){
						transfer();
					}, 60000);
				}
				
				inTransfer = true;
				var timeStack = $.jStorage.get('pKTimeStack', []);
				$.jStorage.set('pKTimeStack', []);
				inTransfer = false;
				
				
				var transferStack = $.jStorage.get('pKTransferStack', []);
				var newTransferStack = transferStack.concat(timeStack);
				$.jStorage.set('pKTransferStack', newTransferStack);
				
				if(newTransferStack.length > 0)
					CustomerPage.rme('stampStack', [JSON.stringify(newTransferStack)/*, PersonalLastUpdate*/], function(t){
						$.jStorage.set('pKTransferStack', []);
						//console.log('Transfer succeeded!');
						counter();
						
						/*if(t != ''){
							Personal = JSON.parse(t);
							PersonalLastUpdate = Math.round(Date.now() / 1000);
						}*/
					}, function(){
						//console.log('Transfer failed!');
						counter();
						
						timeoutTransfer = window.setTimeout(function(){
							transfer();
						}, 60000);
					});
			}

			function counter(){
				\$('#transferCounter').html($.jStorage.get('pKTimeStack', []).length+'/'+$.jStorage.get('pKTransferStack', []).length);
			}

			function stampp(){
				if(inTransfer){
					window.setTimeout(function(){
						stampp();
					}, 20);
				}
					
				inOverlay = true;
				var stack = $.jStorage.get('pKTimeStack', []);
				var chip = \$('#chipCode').val();
				
				stack.push({'chip': chip, 'terminal': ".$_GET["terminalID"].", 'action': action, 'time': Math.round(Date.now() / 1000)});
				$.jStorage.set('pKTimeStack', stack);

				var usePersonal = null;
				for (k in Personal) {
				//console.log(Personal[k].PersonalName+':'+Personal[k].PersonalChipNummer+';'+Personal[k].PersonalChipNummer2+';'+Personal[k].PersonalChipNummer3+';'+Personal[k].PersonalChipNummer4);
					if(chip == Personal[k].PersonalChipNummer){
						usePersonal = Personal[k];
						break;
					}
					
					if(chip == Personal[k].PersonalChipNummer2){
						usePersonal = Personal[k];
						break;
					}
					
					if(chip == Personal[k].PersonalChipNummer3){
						usePersonal = Personal[k];
						break;
					}
					
					if(chip == Personal[k].PersonalChipNummer4){
						usePersonal = Personal[k];
						break;
					}
				}
				

				var color = 'green';
				\$('#chipCode').val('');
				if(usePersonal)
					\$j('.overlayText').html(usePersonal.PersonalName);
				else
					\$j('.overlayText').html('Zeit erfasst');
				\$j('.overlayDetails').html('');

				\$('.overlay').addClass(color).show();

				moep();

				window.setTimeout(function(){
					\$('.overlay').hide().removeClass(color);
					inOverlay = false;
				}, ".(isset($_GET["wait"]) ? $_GET["wait"] : "1500").");
				

				if(timeoutTransfer){
					window.clearTimeout(timeoutTransfer);
					timeoutTransfer = null;
				}
					
				timeoutTransfer = window.setTimeout(function(){
					transfer();
				}, 60000 * 2);
				
				counter();
			}
			
			function moep(){
				if(typeof alex == 'undefined')
					return;
				console.log(alex);
				alex.beep();

			}
			
			
			$('html').focus(function() {
				if(inOverlay)
					return;
					
				$('#chipCode').focus();
			}); 

			$(function(){
				moep();
				counter();
				alive();
				transfer();
				
				$('.touchField').css('height', $(window).height() - $('#displayTime').outerHeight() - 60 - 130);
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
		$I->style("background-color:white;border:1px solid white;font-size:30px;width:96%;position:fixed;bottom:50px;");
		
		$BK = new Button("Kommen", "arrow_right", "iconic");
		$BK->style("font-size:200px;width:auto;");
		
		$BG = new Button("Gehen", "arrow_left", "iconic");
		$BG->style("font-size:200px;width:auto;");
		
		$actschion = "
			<div>
				<div data-action=\"G\" class=\"touchField\" style=\"width:48%;display:inline-block;margin-right:4%;vertical-align:top;\">
					<p style=\"padding:30px;font-size:60px;\">Gehen<br />$BG</p>
				</div><div data-action=\"K\" class=\"touchField currentAction\" style=\"width:48%;display:inline-block;vertical-align:top;\">
					<p style=\"padding:30px;font-size:60px;text-align:right;\">Kommen<br />$BK</p>
				</div>
			</div>";

		if($this->switch)
			$actschion = "
			<div>
				<div data-action=\"K\" class=\"touchField\" style=\"width:48%;display:inline-block;margin-right:4%;vertical-align:top;\">
					<p style=\"padding:30px;font-size:60px;\">Kommen<br />$BG</p>
				</div><div data-action=\"G\" class=\"touchField currentAction\" style=\"width:48%;display:inline-block;vertical-align:top;\">
					<p style=\"padding:30px;font-size:60px;text-align:right;\">Gehen<br />$BK</p>
				</div>
			</div>";

		$html .= "
			<div id=\"transferCounter\" style=\"position:absolute;top:0px;left:0px;padding:3px;color:grey;\"></div>
			<div id=\"aliveStatus\" style=\"position:absolute;top:0px;right:0px;height:10px;width:10px;\"></div>
			
			<div id=\"displayTime\">
				<div id=\"clock\" style=\"font-size:190px;text-align:center;margin-top:40px;color:#333;\"></div>
				<div id=\"day\" style=\"font-size:35px;text-align:right;padding-right:45px;margin-top:0px;color:#CCC;\"></div>
			</div>
			$actschion
			$I
			<div class=\"overlay\">
				<div class=\"overlayCenter\">
					<div class=\"overlayList\"></div>
					<p style=\"font-size:70px;margin-top:60px;font-weight:bold;\" class=\"overlayText\"></p>
					<p style=\"font-size:50px;margin-top:60px;\" class=\"overlayDetails\"></p>
				</div>
			</div>";
		
		
		return $html;
	}
	
	public static function alive($args){
		self::loadClasses();
		
		#header("HTTP/1.0 404 Not Found");
		if(!isset($args["P0"]) OR !isset($args["P1"]))
			return;
		
		$T = new ZETerminal($args["P0"]);
		$T->changeA("ZETerminalLastContact", time());
		$T->saveMe();
		
		$AC = anyC::get("Personal");
		$AC->addAssocV3("lastchange", ">", $args["P1"]);
		$AC->setLimitV3(1);
		$P = $AC->n();
		if($P == null)
			return;
		
		$T = new ZETerminal($args["P0"]);
		$T->changeA("ZETerminalLastUpdate", time());
		$T->saveMe();
		
		$AC = self::getPersonal();
		echo $AC->asJSON();
	}
	
	public static function stampStack($args){
		#header("HTTP/1.0 404 Not Found");
		#die();
		
		$data = json_decode($args["P0"]);
		
		foreach($data AS $stamp)
			self::stamp(array("P0" => $stamp->chip, "P1" => $stamp->terminal, "P2" => $stamp->action, "P3" => "", "P4" => "", "P5" => $stamp->time), false);
		
		/*$AC = anyC::get("Personal");
		$AC->addAssocV3("lastchange", ">", $args["P1"]);
		$AC->setLimitV3(1);
		$P = $AC->n();
		if($P == null)
			return;
		
		$AC = self::getPersonal();
		echo $AC->asJSON();*/
	}
	
	private static function getPersonal(){
		$AC = anyC::get("Personal");
		$AC->addAssocV3("isDeleted", "=", "0", "AND", "1");
		$AC->addAssocV3("PersonalChipNummer", "!=", "", "AND", "2");
		$AC->addAssocV3("PersonalChipNummer2", "!=", "", "OR", "2");
		$AC->setFieldsV3(array("PersonalID", "PersonalChipNummer", "PersonalChipNummer2", "PersonalChipNummer AS PersonalChipNummer3", "PersonalChipNummer2 AS PersonalChipNummer4", "CONCAT(vorname, ' ', nachname) AS PersonalName"));
		while($P = $AC->n()){
			$P->changeA("PersonalChipNummer3", hexdec($P->A("PersonalChipNummer3")));
			$P->changeA("PersonalChipNummer4", hexdec($P->A("PersonalChipNummer4")));
		}
		$AC->resetPointer();
		
		return $AC;
	}
	
	private static function loadClasses(){
		addClassPath(Util::getRootPath()."personalKartei/Zeiterfassung/");
		addClassPath(Util::getRootPath()."personalKartei/Personal/");
		addClassPath(Util::getRootPath()."personalKartei/ObjekteL/");
		addClassPath(Util::getRootPath()."open3A/Kategorien/");
	}
	
	public static function stamp($args, $die = true){
		#if(strtolower($args["P0"]) == "303005f7b4")
		#	die('{"status":"command", "action":"reload"}');
		if(!isset($_SESSION["BPS"]))
			$_SESSION["BPS"] = new BackgroundPluginState();

		self::loadClasses();
		
		
		$T = anyC::getFirst("ZETerminal", "ZETerminalID", $args["P1"]);
		if(!$T){
			if($die)
				die('{"status":"error", "message":"Unbekanntes Terminal"}');
			else
				return;
		}
		
		$CT = FileStorage::getFilesDir()."ChipTrans.csv";
		
		
		
		if($args["P3"] AND $args["P4"] > 0){
			$P = new Personal($args["P4"]);
			if(trim($P->A("PersonalChipNummer")) == ""){
				$P->changeA("PersonalChipNummer", trim(strtolower($args["P0"])));
				$P->saveMe();
			} else
				file_put_contents($CT, "$args[P4]:".trim(strtolower($args["P0"]))."\n", FILE_APPEND);
		}
		
		$A = new stdClass();

		$Date = new Datum();
		$Date->subDay();
		$Date->addDay();

		$A->ChipID = trim(strtolower($args["P0"]));
		$A->Date = Util::parseDate("de_DE", date("d.m.Y", !isset($args["P5"]) ? $Date->time() : $args["P5"]));
		$A->Time = Util::parseTime("de_DE", date("H:i", !isset($args["P5"]) ? time() : $args["P5"]));
		$A->Type = $args["P2"];
		$A->Mode = "";
		$A->TerminalID = $args["P1"];
		try {
			$ok = ZEData::addTime($A);
		} catch(Exception $e){
			try {
				$hex = str_pad(trim(strtolower(dechex($args["P0"]))), 10, "0", STR_PAD_LEFT);
				$A->ChipID = $hex;
				$ok = ZEData::addTime($A);
			} catch(Exception $e){
				try {
					if(!$args["P3"])
						throw new Exception ("Chip unknown", 100);
					
					if(!file_exists($CT))
						file_put_contents($CT, "");
					
					$trans = file_get_contents($CT);
					$found = false;
					foreach(explode("\n", $trans) AS $line){
						$line = trim($line);
						$ex = explode(":", $line);
						
						if(trim(strtolower($ex[1])) != trim(strtolower($args["P0"])))
							continue;
						
						$P = new Personal($ex[0]);
						$A->ChipID = $P->A("PersonalChipNummer");
						$found = true;
						
					}
					
					if(!$found)
						throw new Exception ("Learn", 200);
					
					#if(!$found)
					#	throw new Exception ("Chip unknown", 100);
					
					$ok = ZEData::addTime($A);
					
				} catch(Exception $e){
					switch($e->getCode()){
						case 100:
							try {
								$F = new Factory("ZETerminalFail");
								$F->sA("ZETerminalFailTime", time());
								$F->sA("ZETerminalFailData", json_encode($args));
								$F->sA("ZETerminalFailZETerminalID", $args["P1"]);
								$F->store();
							} catch(Exception $e){ }
							
							if($die)
								die('{"status":"error", "message":"Unbekannter Chip"}');
							else
								return;
						break;
					
						case 200:
							$AC = anyC::get("Personal", "isDeleted", "0");
							$AC->setFieldsV3(array("CONCAT(nachname, ' ', vorname) AS name"));
							$AC->addAssocV3("TRIM(CONCAT(nachname, vorname))", "!=", "");
							$AC->addOrderV3("nachname");
							$AC->addOrderV3("vorname");

							$knownPID = array();
							$file = file($CT);
							foreach($file AS $line){
								$line = trim($line);
								$ex = explode(":", $line);
								$knownPID[$ex[0]] = true;
							}
							
							$array = array();
							while($A = $AC->n()){
								if(isset($knownPID[$A->getID()]))
									continue;
								
								$subArray = array();
								foreach($A->getA() as $key => $value)
									$subArray[$key] = $value;

								$array[] = $subArray;
							}
							if($die)
								die('{"status":"learn", "message":'.json_encode($array, defined("JSON_UNESCAPED_UNICODE") ? JSON_UNESCAPED_UNICODE : 0).'}');
							else
								return;
						break;
					
						default:
							try {
								$F = new Factory("ZETerminalFail");
								$F->sA("ZETerminalFailTime", time());
								$F->sA("ZETerminalFailData", $e->getMessage());
								$F->sA("ZETerminalFailZETerminalID", $args["P1"]);
								$F->store();
							} catch(Exception $e){ }
							
							if($die)
								die('{"status":"error", "message":"'.$e->getMessage().'"}');
							else
								return;
					}
				}
			}
		}

		if($args["P2"] == "G"){
			$AC = anyC::get("ZEData", "ZEDataChipID", $A->ChipID);
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
		
		#$ZEA = new ZEAuswertung($A->ChipID);
		#$ZEA->debug = false;
		#$current = $ZEA->getContent();
		
		if($die)
			die('{"status":"OK", "message": "'.addslashes ($ok["Personal"]->A("vorname")." ".$ok["Personal"]->A("nachname")).'", "details": ""}');#Stunden '.Util::CLMonthName(date("m")).': '.Util::formatSeconds($current["totalHours"][1], false).'
		else 
			return;
		
			
	}
	
}

?>