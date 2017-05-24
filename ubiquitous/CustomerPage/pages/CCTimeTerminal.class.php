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
class CCTimeTerminal implements iCustomContent {
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
			var learnPersonalID = 0;
			var learnChipID = 0;
			
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
			});
			
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

			function stampp(){
				inOverlay = true;
				if(typeof alex != 'undefined' && typeof alex.takePhoto == 'function')
					alex.takePhoto();
				
				learnChipID = \$('#chipCode').val();

				CustomerPage.rme('stamp', [\$('#chipCode').val(), ".$_GET["terminalID"].", action, ".(isset($_GET["learn"]) ? "1" : "0").", learnPersonalID], function(t){
					if(t == '')
						return;
						
					var r = jQuery.parseJSON(t);
					\$('#chipCode').val('');
					learnPersonalID = 0;
					if(r.status == 'command' && r.action =='reload'){
						document.location.reload();
						return;
					}
					

					var message = r.message;
					var color = 'green';
					var list = '';
					if(r.status == 'error')
						color = 'red';
					if(r.status == 'learn')
						color = 'orange';

					if(r.status == 'learn'){
						
						list = '<p class=\"overlayContentSub\" style=\"font-size:70px;font-weight:bold;background-color:white;margin-top:-20;\">Bitte wählen Sie:</p>';
						list += '<ul class=\"overlayContentHeight\" style=\"padding-left:0px;padding-top:0px;padding-bottom:0px;margin-top:0;margin-bottom:0;overflow:auto;\">';
						for(var i = 0;i < r.message.length; i++){
							list += '<li data-personalid=\"'+r.message[i].PersonalID+'\" class=\"selectionName\" style=\"padding-left:40px;\">'+r.message[i].name+'</li>';
						}
						list += '</ul>';
						
						list += '<div class=\"overlayContentSub\" id=\"buttonCancel\" style=\"background-color: rgb(204, 204, 204);padding-top:20px;padding-bottom:20px;font-size:30px;width:50%;display:inline-block;\"><div style=\"padding-left:10px;\"><span style=\"font-size:40px;margin-right:20px;\" class=\"iconic x\"></span> Abbrechen</div></div>';
						list += '<div id=\"buttonSave\" style=\"background-color: rgb(204, 204, 204);padding-top:20px;padding-bottom:20px;width:50%;display:inline-block;font-size:30px;color:grey;\"><div style=\"padding-left:10px;\"><span style=\"font-size:40px;margin-right:20px;\" class=\"iconic check\"></span> Speichern</div></div>';
						//return;
					}

					
					\$j('.overlayText').html(message);
					\$j('.overlayDetails').html('');
					\$j('.overlayList').html(list);
					\$j('.overlayCenter').scrollTop(0);
					
					if(r.details)
						\$j('.overlayDetails').html(r.details);

					\$('.overlay').removeClass().addClass('overlay '+color).show();
					
					if(\$j('.overlayContentHeight').length)
						\$j('.overlayContentHeight').css('height', heightContent());

					if(r.status == 'learn'){
						$('.selectionName').hammer().on('tap', function(ev){
							\$j('.selectionName').css('background-color', '');
							\$j(ev.target).css('background-color', 'rgba(255,204,0,0.3)');
							\$j('#buttonSave').css('background-color', '#c5ffab').css('color', '');
							learnPersonalID = \$j(ev.target).data('personalid');
						});
						
						$('#buttonSave').hammer().on('touch release', function(ev){
							switch(ev.type){
								case 'touch':
									$(ev.target).closest('#buttonSave').css('background-color', 'rgba(255,204,0,0.3)');
								break;

								case 'release':
									$(ev.target).closest('#buttonSave').css('background-color', 'rgb(204, 204, 204)');
									
									if(learnPersonalID == 0)
										return;
										
									\$('.overlay').hide();
									inOverlay = false;
									\$('#chipCode').val(learnChipID);
									stampp();
								break;
							}
						});

						$('#buttonCancel').hammer().on('touch release', function(ev){
							switch(ev.type){
								case 'touch':
									$(ev.target).closest('#buttonCancel').css('background-color', 'rgba(255,204,0,0.3)');
								break;

								case 'release':
									$(ev.target).closest('#buttonCancel').css('background-color', 'rgb(204, 204, 204)');
									\$('.overlay').hide();
									inOverlay = false;
									learnPersonalID = 0;
								break;
							}
						});
					}

					if(r.status != 'learn')
						window.setTimeout(function(){
							\$('.overlay').hide();
							\$('#chipCode').focus();
							inOverlay = false;
						}, 1500);
					
					if(r.status == 'OK')
						moep();


					var stack = $.jStorage.get('pKTimeStack', [])
					if(stack.length > 0)
						CustomerPage.rme('stampStack', [JSON.stringify(stack)], function(t){
							$.jStorage.set('pKTimeStack', []);
						});
					
				}, function(){
					var stack = $.jStorage.get('pKTimeStack', []);
					//console.log(stack);
					stack.push({'chip': \$('#chipCode').val(), 'terminal': ".$_GET["terminalID"].", 'action': action, 'time': Math.round(Date.now() / 1000)});
					$.jStorage.set('pKTimeStack', stack);
					
					var color = 'orange';
					\$('#chipCode').val('');
					\$j('.overlayText').html('Zeit erfasst');
					\$j('.overlayDetails').html('Der Server ist nicht erreichbar.');

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

			$('html').focus(function() {
				if(inOverlay)
					return;
					
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
		
		$actschion = "
			<div>
				<div data-action=\"G\" class=\"touchField\" style=\"width:48%;display:inline-block;margin-right:3.7%;vertical-align:top;\">
					<p style=\"padding:30px;font-size:60px;\">Gehen<br />$BG</p>
				</div>
				<div data-action=\"K\" class=\"touchField currentAction\" style=\"width:48%;display:inline-block;vertical-align:top;\">
					<p style=\"padding:30px;font-size:60px;text-align:right;\">Kommen<br />$BK</p>
				</div>
			</div>";

		if($this->switch)
			$actschion = "
			<div>
				<div data-action=\"K\" class=\"touchField\" style=\"width:48%;display:inline-block;margin-right:3.7%;vertical-align:top;\">
					<p style=\"padding:30px;font-size:60px;\">Kommen<br />$BG</p>
				</div>
				<div data-action=\"G\" class=\"touchField currentAction\" style=\"width:48%;display:inline-block;vertical-align:top;\">
					<p style=\"padding:30px;font-size:60px;text-align:right;\">Gehen<br />$BK</p>
				</div>
			</div>";

		$html .= "
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
	
	public static function stampStack($args){
		$data = json_decode($args["P0"]);
		
		foreach($data AS $stamp)
			self::stamp(array("P0" => $stamp->chip, "P1" => $stamp->terminal, "P2" => $stamp->action, "P3" => "", "P4" => "", "P5" => $stamp->time), false);
		
	}
	
	public static function stamp($args, $die = true){
		#if(strtolower($args["P0"]) == "303005f7b4")
		#	die('{"status":"command", "action":"reload"}');
		
		if(!isset($_SESSION["BPS"]))
			$_SESSION["BPS"] = new BackgroundPluginState();

		addClassPath(Util::getRootPath()."personalKartei/Zeiterfassung/");
		addClassPath(Util::getRootPath()."personalKartei/Personal/");
		addClassPath(Util::getRootPath()."personalKartei/ObjekteL/");
		#if(file_exists(Util::getRootPath()."personalKartei/Schichten/"))
		#	addClassPath(Util::getRootPath()."personalKartei/Schichten/");
		addClassPath(Util::getRootPath()."open3A/Kategorien/");
		
		$CCP = new CCPage();
		$CCP->loadPlugin("personalKartei", "Schichten", true);
		
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
		$A->Date = $Date->time();
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
			$AC = anyC::get("ZEData", "ZEDataPersonalID", $ok["Personal"]->getID());
			$AC->addAssocV3("ZEDataType", "=", "K");
			$AC->addAssocV3("ZEDataDate + ZEDataTime", ">", time() - 3600 * 13);
			$AC->addAssocV3("ZEDataDate + ZEDataTime", "<", time());
			$AC->addAssocV3("ZEDataIsDeleted", "=", "0");
			$AC->addOrderV3("ZEDataDate + ZEDataTime", "DESC");
			$AC->setLimitV3("1");
			
			$Kommen = $AC->getNextEntry();
			if($Kommen != null){
				$Gehen = $ok["ZEData"];
				
				$T = new ZETerminal($args["P1"]);
				
				$AC2 = anyC::get("PZuO", "ObjektLID", $T->A("ZETerminalObjektLID"));
				$AC2->addAssocV3("PersonalID", "=", $Kommen->A("ZEDataPersonalID"));
				$PZuO = $AC2->n();

				if($PZuO !== null){
					$worked = ($Gehen->A("ZEDataDate") + $Gehen->A("ZEDataTime")) - ($Kommen->A("ZEDataDate") + $Kommen->A("ZEDataTime"));
					$AZ = mZEArbeitsZeit::getArbeitszeiten($PZuO->getID(), time());
					
					if(isset($AZ[0])){
						$hasTo = $AZ[0]->A("ZEArbeitsZeitEnde") - $AZ[0]->A("ZEArbeitsZeitStart");
						
						if($worked > 0 AND $worked / $hasTo > 0.9){# AND $hasTo / $worked < 1.15){
							$DE = $ok["ZEData"];
							$DE->changeA("ZEDataPause", $AZ[0]->A("ZEArbeitsZeitMittag"));
							$DE->saveMe(false, false);
						}
					}
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