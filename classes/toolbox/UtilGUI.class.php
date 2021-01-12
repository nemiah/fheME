<?php
/*
 *  This file is part of phynx.

 *  phynx is free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 3 of the License, or
 *  (at your option) any later version.

 *  phynx is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.

 *  You should have received a copy of the GNU General Public License
 *  along with this program.  If not, see <http://www.gnu.org/licenses/>.
 * 
 *  2007 - 2020, open3A GmbH - Support@open3A.de
 */
class UtilGUI extends Util {
	function __construct($nonSense = ""){}
	
	static function CLDateParser($date, $l = "load"){
		echo Util::CLDateParser($date, $l);
	}

	static function CLNumberParserZ($number, $l = "load") {
		echo parent::CLNumberParserZ($number, $l);
	}

	public function reloadApplication(){
		session_destroy();
	}
	
	/**
	 * Method to display an E-Mail popup for easy E-Mail sending
	 * 
	 * Requires a method named "getEMailData" in $dataClass which returns an array:
	 * array(fromName, fromAddress, recipientName, recipientAddress, subject, body)
	 * 
	 * Will call $dataClass($dataClassID)::sendEmail afterwards
	 * 
	 * @param string $dataClass
	 * @param string $dataClassID 
	 */
	public static function EMailPopup($dataClass, $dataClassID, $callbackParameter = null, $onSuccessFunction = null, $onAbortFunction = null, $return = false, $inWindow = false){
		$c = new $dataClass($dataClassID);
		$data = $c->getEMailData($callbackParameter);
		
		$html = "";
		
		if(isset($data["attachmentsAlways"]))
			$html .= OnEvent::script(OnEvent::popupSidePanel("Util", "-1", "EMailPopupAttachmentsSP", array("'$dataClass'", "'$dataClassID'", "'$callbackParameter'")));
		
		
		#$html .= "<p class=\"prettyTitle\">Neue E-Mail</p>";
		
		#$html .= "<div style=\"clear:both;\"></div>";
		
		$useOther = (isset($data["allowOtherRecipient"]) AND $data["allowOtherRecipient"]);
		
		$tab = new HTMLTable(2);
		$tab->setColWidth(1, "120px;");
		if(trim($data["fromAddress"]) != "")
			$tab->addLV("Absender:", "$data[fromName]<br /><small style=\"color:grey;\">&lt;$data[fromAddress]&gt;</small>");
		else {
			$I = new HTMLInput("fromAddress");
			$I->id("EMailSender$dataClassID");
			$tab->addLV("Absender:", $I);
		}
		if(is_array($data["recipients"])){
			if(count($data["recipients"]) == 1 AND !$useOther)
				$tab->addLV("Empfänger:", $data["recipients"][0][0]."<br /><small style=\"color:grey;\">&lt;".$data["recipients"][0][1]."&gt;</small>");
			else {
				$recipients = array();
				foreach($data["recipients"] AS $ID => $Rec)
					$recipients[$ID] = new HTMLInput ($Rec[0]." &lt;".$Rec[1]."&gt;", "option", $ID);

				$IO = "";
				if($useOther){
					$recipients[-1] = T::_("Anderer Empfänger");
					
					$IO = new HTMLInput("otherRecipient", "text");
					$IO->style("display:none;margin-top:5px;");
					$IO->placeholder("E-Mail-Adresse");
				}
				
				$IS = new HTMLInput("EMailRecipient$dataClassID", "select", isset($data["default"]) ? $data["default"] : "0", $recipients);
				$IS->id("EMailRecipient$dataClassID");
				$IS->onchange("if(this.value != '-1') { \$j('[name=otherRecipient]').hide(); } else { \$j('[name=otherRecipient]').show(); }");

				
				$BP = "";
				if(isset($data["cc"])){
					$BP = new Button("Empfänger hinzufügen", "./images/i2/add.png", "icon");
					$BP->style("float:right;margin-top:5px;");
					$BP->onclick("\$j('#EMailCC1Row$dataClassID').toggle();");
				}
				
				$tab->addLV("Empfänger:", $BP.$IS.$IO);
			}
		}
		
		else {
			$IS = new HTMLInput("EMailRecipient$dataClassID", "text", $data["recipients"]);
			$IS->id("EMailRecipient$dataClassID");

			$tab->addLV("Empfänger:", $IS);
		}
		
		if(isset($data["cc"]) AND is_array($data["cc"])){
			$cc = array(-1 => "Nicht verwenden");
			foreach($data["cc"] AS $ID => $Rec)
				$cc[$ID] = new HTMLInput ($Rec[0]." &lt;".$Rec[1]."&gt;", "option", $ID);;

			$ICC = new HTMLInput("EMailCC$dataClassID", "select", -1, $cc);
			$ICC->id("EMailCC1$dataClassID");

			$tab->addLV("CC:", $ICC);
			$tab->addRowStyle("display:none;");
			$tab->setRowID("EMailCC1Row$dataClassID");
		}
		
		if(isset($data["bcc"]) AND count($data["bcc"]) > 0){
			foreach($data["bcc"] AS $bcc)
				$tab->addLV("BCC:", $bcc);
		}
		
		$tab->addLV("Betreff:", "<input type=\"text\" id=\"EMailSubject$dataClassID\" value=\"$data[subject]\" />");
		
		$html .= $tab;
		$html .= "<div style=\"width:94%;margin:auto;\"><textarea class=\"tinyMCEEditor\" id=\"EMailBody$dataClassID\" style=\"width:100%;height:300px;font-size:10px;\">$data[body]</textarea></div>";
		
		#$tab->addRow(array(""));
		#$tab->addRowColspan(1, 2);
		#$tab->addRowClass("backgroundColor0");
		
		$tab = new HTMLTable(2);
		$tab->setColWidth(1, "120px;");

		if($onSuccessFunction == null)
			$onSuccessFunction = "".OnEvent::reload("Left")." Popup.close('Util', 'edit');";
		

		$BAbort = new Button("Abbrechen", ($inWindow ? "." : "")."./images/navi/stop.png");
		if($onAbortFunction == null){
			if($inWindow)
				$BAbort->onclick("window.close();");
			else
				$BAbort->onclick("Popup.close('Util', 'edit');");
		} else
			$BAbort->onclick($onAbortFunction);
		$BAbort->style("margin-bottom:10px;margin-top:10px;");
		
		$optional = "var files = '';";
		if(isset($data["attachmentsOptional"])){
			$optional .= "\$j('#UtilEmailFormAttachments input[type=checkbox]:checked').each(function(k, v){ files += \$j(v).data('value')+'##'; });";
		}
		
		$BGo = new Button("E-Mail\nsenden", ($inWindow ? "." : "")."./images/navi/okCatch.png");
		$BGo->style("float:right;margin-top:10px;");
		$BGo->loading();
		$BGo->doBefore("$optional %AFTER");
		if(strpos($data["body"], "<p") !== false OR trim($data["body"]) == "")
			$BGo->doBefore("$optional \$j('#EMailBody$dataClassID').val(tinyMCE.get('EMailBody$dataClassID').getContent()); %AFTER");
		$BGo->rmePCR(str_replace("GUI", "", $dataClass), $dataClassID, "sendEmail", 
			array(
				"$('EMailSubject$dataClassID').value",
				"\$j('#EMailBody$dataClassID').val()", 
				(is_array($data["recipients"]) AND count($data["recipients"]) == 1 AND !$useOther) ? "0" : "$('EMailRecipient$dataClassID').value",
				"'".$callbackParameter."'", 
				"files", 
				(!isset($data["cc"]) ? "-1" : "\$j('#EMailCC1$dataClassID').val()"),
				(trim(trim($data["fromAddress"]) == "") ? "\$j('#EMailSender$dataClassID').val()" : "''"),
				"\$j('[name=otherRecipient]').val()"
			), $onSuccessFunction);
		
		#$BGo->onclick("CloudKunde.directMail('$this->ID', '$data[recipientAddress]', $('EMailSubject$this->ID').value, $('EMailBody$this->ID').value); ");


		$tab->addRow(array($BGo.$BAbort));
		$tab->addRowColspan(1, 2);
		$tab->addRowClass("backgroundColor0");

		$html .= $tab;
		
		if(strpos($data["body"], "<p") !== false OR trim($data["body"]) == "")
			echo OnEvent::script("
				setTimeout(function(){
				".tinyMCEGUI::editorMail ("EMailBody$dataClassID", null, "undo redo | pastetext | bold italic underline | fullscreen code")."
			}, 100);");
		
		if($return)
			return $html;
		else
			echo $html;
	}
	
	public static function EMailPopupAttachmentsSP($dataClass, $dataClassID, $callbackParameter = null){
		$c = new $dataClass($dataClassID);
		$data = $c->getEMailData($callbackParameter);
		
		$T = new HTMLTable(1, "Anhänge");
		foreach($data["attachmentsAlways"] AS $file){
			$T->addRow("<small>$file</small>");
			$T->addCellStyle(1, "max-width: 100px;overflow: hidden;text-overflow: ellipsis;white-space: nowrap;");
		}
		
		echo $T;
		
		
		$T = new HTMLTable(2, "Mögliche Anhänge");
		$T->setColWidth(1, 20);
		foreach($data["attachmentsOptional"] AS $k => $file){
			$I = new HTMLInput("addFile$k", "checkbox");
			$I->style("margin:0px;");
			$I->data("value", $file);
			
			$T->addRow(array($I, "<small>$file</small>"));
			$T->addCellStyle(2, "max-width: 100px;overflow: hidden;text-overflow: ellipsis;white-space: nowrap;cursor:pointer;");
			$T->addCellEvent(2, "click", "\$j('[name=addFile$k]').prop('checked', !\$j('[name=addFile$k]').prop('checked'));");
		}
		
		echo ((isset($data["attachmentsOptional"]) AND count($data["attachmentsOptional"])) ? "<div style=\"height:30px;\"></div>" : "")."<form id=\"UtilEmailFormAttachments\">".$T."</form>";
	}

	public static function newSession($physion, $application, $plugin, $cloud = "", $title = "", $icon = ""){
		echo "<p>Bitte haben Sie etwas Geduld, während die neue Sitzung initialisiert wird...</p><iframe onload=\"window.open(contentManager.getRoot()+'?physion=$physion&application=$application&plugin=$plugin".($cloud != "" ? "&cloud=$cloud" : "")."".($title != "" ? "&title=$title" : "")."".($icon != "" ? "&icon=$icon" : "")."');".OnEvent::closePopup("Util")."\" src=\"interface/rme.php?class=Users&construct=&method=doLogin&parameters=%27".Session::currentUser()->A("username")."%27,%27".Session::currentUser()->A("SHApassword")."%27,%27".Applications::activeApplication()."%27,%27".Session::currentUser()->A("language")."%27&physion=$physion".($cloud != "" ? "&cloud=$cloud" : "")."\" style=\"display:none;\"></iframe>";
	}
	
	private function reminderCheck(){
		try {
			$R = new Reminder(-1);
			$K = $R->kalenderData("box", false, Session::currentUser()->getID());
			$A = $R->aufgabenData("box", false, Session::currentUser()->getID());

			$B = array_merge($K, $A);
		} catch (ClassNotFoundException $e){
			$B = array();
		}
		return $B;
	}
	
	public function reminderDone($target, $id){
		switch($target){
			case "KalenderEvent":
				$ex = explode("@", $id);
				if(strpos($ex[0], "TodoID") !== false){
					$T = new Todo(str_replace("TodoID", "", $ex[0]));
					
					$D = new Datum();
					$D->normalize();
					
					$after = $D->time() + $T->A("TodoFromTime") - 60 * 5;
					
					$T->changeA("TodoReminded", $after + 60);
					$T->saveMe(true, false, false);
				}
			break;
			case "Aufgabe":
				$A = new Aufgabe($id);
				$A->changeA("AufgabeReminded", time());
				$A->saveMe();
			break;
		}
	}
	
	public function reminderList(){
		#$T = new HTMLTable(2);
		#$T->setTableStyle("width:100%;");
		#$T->setColWidth(1, "30%");
		$B = $this->reminderCheck();
		
		$R = array();
		
		$Kal = new Kalender();
		foreach($B AS $E){
			switch(get_class($E)){
				case "KalenderEvent":
					$id = str_replace("@", "", $E->UID());
					
					$BD = new Button("Erledigt", "check", "iconic");
					$BD->style("color:#333;");
					$BD->doBefore("event.stopPropagation(); %AFTER");
					$BD->rmePCR("Util", "-1", "reminderDone", array("'KalenderEvent'", "'".$E->UID()."'"), "function(){ \$j('#bottom').html(''); \$j('#$id').hide(); if(!\$j('.event:visible').length) window.close(); }");
					#print_r($E);
					$actions = "";
										
					if($E->ownerClass() == "mTodoGUI"){
						$T = new Todo($E->ownerClassID());
						if($T->A("TodoClass") == "DBMail"){
							$BWindow = new Button("Neues Fenster", "new_window", "iconicL");
							$BWindow->windowRme("Mail", $T->A("TodoClassID"), "getInWindow");
							$BWindow->style("margin-left:10px;");
			
							$actions = $BWindow."";
						}
					}
					$R[] = array(
						$id,
						"<div class=\"event\" id=\"$id\" style=\"padding:5px;cursor:pointer;\" onclick=\"\$j('.confirm').removeClass('confirm'); \$j(this).addClass('confirm');\$j('#bottom').html('".str_replace("\n", "", $E->summary() != "" ? addslashes($E->summary()) : "Keine Beschreibung")."');\$j('#actions').html(window.atob('". base64_encode($actions)."'));\" onmouseover=\"\$j(this).addClass('highlight');\" onmouseout=\"\$j(this).removeClass('highlight');\">
							<div style=\"width:15%;display:inline-block;vertical-align:top;\">
								Kalender
							</div><div style=\"width:45%;display:inline-block;overflow:hidden;vertical-align:top;\">
								".$E->title()."
							</div><div style=\"width:33%;display:inline-block;vertical-align:top;\">
								".Util::CLTimeParser($Kal->parseTime($E->getTime()))." - ".Util::CLTimeParser($Kal->parseTime($E->getEndTime()))." Uhr"."
							</div><div style=\"width:7%;display:inline-block;vertical-align:top;\">
								$BD
							</div>
						</div>"
					);
				break;
			
				case "Aufgabe":
					$id = "Aufgabe_".$E->getID();
					
					$BD = new Button("Erledigt", "check", "iconic");
					$BD->style("color:#333;");
					$BD->doBefore("event.stopPropagation(); %AFTER");
					$BD->rmePCR("Util", "-1", "reminderDone", array("'Aufgabe'", "'".$E->getID()."'"), "function(){ \$j('#bottom').html(''); \$j('#$id').hide(); if(!\$j('.event:visible').length) window.close(); }");
					
					$R[] = array(
						$id,
						"<div class=\"event\" id=\"$id\" style=\"padding:5px;cursor:pointer;\" onclick=\"\$j('.confirm').removeClass('confirm'); \$j(this).addClass('confirm');\$j('#bottom').html('".str_replace("\n", "", addslashes(nl2br($E->A("AufgabeText"))))."');\" onmouseover=\"\$j(this).addClass('highlight');\" onmouseout=\"\$j(this).removeClass('highlight');\">
							<div style=\"width:15%;display:inline-block;vertical-align:top;\">
								Aufgabe
							</div><div style=\"width:45%;display:inline-block;overflow:hidden;vertical-align:top;\">
								".mb_substr($E->A("AufgabeText"), 0, 30)."
							</div><div style=\"width:33%;display:inline-block;vertical-align:top;\">
								".Util::CLTimeParser($E->A("AufgabeUhrzeitVon"))." Uhr"."
							</div><div style=\"width:7%;display:inline-block;vertical-align:top;\">
								$BD
							</div>
						</div>"
					);
					/*$id = str_replace("@", "", $E->UID());
					
					$BD = new Button("Erledigt", "check", "iconic");
					$BD->style("color:#333;");
					$BD->doBefore("event.stopPropagation(); %AFTER");
					$BD->rmePCR("Util", "-1", "reminderDone", array("'KalenderEvent'", "'".$E->UID()."'"), "function(){ \$j('#bottom').html(''); \$j('#$id').hide(); if(!\$j('.event:visible').length) window.close(); }");
					
					$R[] = array(
						$id,
						"<div class=\"event\" id=\"$id\" style=\"padding:5px;cursor:pointer;\" onclick=\"\$j('.confirm').removeClass('confirm'); \$j(this).addClass('confirm');\$j('#bottom').html('".str_replace("\n", "", addslashes($E->summary()))."');\" onmouseover=\"\$j(this).addClass('highlight');\" onmouseout=\"\$j(this).removeClass('highlight');\">
							<div style=\"width:15%;display:inline-block;vertical-align:top;\">
								Kalender
							</div><div style=\"width:45%;display:inline-block;overflow:hidden;vertical-align:top;\">
								".$E->title()."
							</div><div style=\"width:33%;display:inline-block;vertical-align:top;\">
								".Util::CLTimeParser($Kal->parseTime($E->getTime()))." - ".Util::CLTimeParser($Kal->parseTime($E->getEndTime()))." Uhr"."
							</div><div style=\"width:7%;display:inline-block;vertical-align:top;\">
								$BD
							</div>
						</div>"
					);*/
				break;
			}
		}

		echo json_encode($R, JSON_UNESCAPED_UNICODE);
		
	}
	
	public function reminderContent(){
		$html = "
			<div style=\"padding:5px;background-color:white;\">
				<div style=\"width:15%;display:inline-block;font-weight:bold;\">
					Quelle
				</div><div style=\"width:45%;display:inline-block;font-weight:bold;\">
					Name
				</div><div style=\"width:33%;display:inline-block;font-weight:bold;\">
					Zeit
				</div><div style=\"width:7%;display:inline-block;font-weight:bold;\">
					
				</div>
			</div>
			<div id=\"top\" style=\"height:124px;overflow:auto;\">
				
			</div>
			<div id=\"bottom\" style=\"padding:5px;height:150px;box-sizing:border-box;background-color:#eee;overflow:auto;\"></div>
			<div id=\"actions\" style=\"padding:5px;\">
			
			</div>";
		
		echo self::getBasicHTML($html.OnEvent::script("function reloadList(){
			".OnEvent::rme(new UtilGUI(), "reminderList", array(), "function(t){
				var data = jQuery.parseJSON(t.responseText);
				for(var i = 0; i < data.length; i++){
					if(\$j('#'+data[i][0]).length)
						continue;
						
					\$j('#top').append(data[i][1]);
				}
				//\$j('#top').html(t.responseText);
			}")."
		};
		contentManager.setRoot('../');".
		(filter_input(INPUT_GET, "physion") ? "Ajax.physion = '".filter_input(INPUT_GET, "physion")."';" : "")."
		reloadList();"), "Erinnerungen");
	}
	
	public function reminderProxy(){
		if(count($this->reminderCheck()))
			echo "1";
	}
}

?>