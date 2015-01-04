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
 *  2007 - 2014, Rainer Furtmeier - Rainer@Furtmeier.IT
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
	public static function EMailPopup($dataClass, $dataClassID, $callbackParameter = null, $onSuccessFunction = null, $onAbortFunction = null, $return = false){
		$c = new $dataClass($dataClassID);
		$data = $c->getEMailData($callbackParameter);

		$html = "";
		
		if(isset($data["attachmentsAlways"])){
			
			/*$B = new Button("Anhänge", "export", "LPBig");
			$B->style("margin:10px;float:right;");
			$B->sidePanel("Util", "-1", "EMailPopupAttachmentsSP", array("'$dataClass'", "'$dataClassID'", "'$callbackParameter'"));
			
			$html .= $B;*/
			
			$html .= OnEvent::script(OnEvent::popupSidePanel("Util", "-1", "EMailPopupAttachmentsSP", array("'$dataClass'", "'$dataClassID'", "'$callbackParameter'")));
		}
		
		#$html .= "<p class=\"prettyTitle\">Neue E-Mail</p>";
		
		#$html .= "<div style=\"clear:both;\"></div>";
		
		$tab = new HTMLTable(2);
		$tab->setColWidth(1, "120px;");
		$tab->addLV("Absender:", "$data[fromName]<br /><small style=\"color:grey;\">&lt;$data[fromAddress]&gt;</small>");
		
		if(is_array($data["recipients"])){
			if(count($data["recipients"]) == 1)
				$tab->addLV("Empfänger:", $data["recipients"][0][0]."<br /><small style=\"color:grey;\">&lt;".$data["recipients"][0][1]."&gt;</small>");
			else {
				$recipients = array();
				foreach($data["recipients"] AS $ID => $Rec)
					$recipients[$ID] = new HTMLInput ($Rec[0]." &lt;".$Rec[1]."&gt;", "option", $ID);;

				$IS = new HTMLInput("EMailRecipient$dataClassID", "select", isset($data["default"]) ? $data["default"] : "0", $recipients);
				$IS->id("EMailRecipient$dataClassID");

				$tab->addLV("Empfänger:", $IS);
			}
		}
		
		else {
			$IS = new HTMLInput("EMailRecipient$dataClassID", "text", $data["recipients"]);
			$IS->id("EMailRecipient$dataClassID");

			$tab->addLV("Empfänger:", $IS);
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
		

		$BAbort = new Button("Abbrechen","stop");
		if($onAbortFunction == null)
			$BAbort->onclick("Popup.close('Util', 'edit');");
		else
			$BAbort->onclick($onAbortFunction);
		$BAbort->style("margin-bottom:10px;margin-top:10px;");
		
		$optional = "var files = '';";
		if(isset($data["attachmentsOptional"])){
			$optional .= "\$j('#UtilEmailFormAttachments input[type=checkbox]:checked').each(function(k, v){ files += \$j(v).data('value')+'##'; });";
		}
		
		$BGo = new Button("E-Mail\nsenden","okCatch");
		$BGo->style("float:right;margin-top:10px;");
		$BGo->loading();
		$BGo->doBefore("$optional %AFTER");
		if(strpos($data["body"], "<p") !== false OR trim($data["body"]) == "")
			$BGo->doBefore("$optional \$j('#EMailBody$dataClassID').val(tinyMCE.get('EMailBody$dataClassID').getContent()); %AFTER");
		$BGo->rmePCR(str_replace("GUI", "", $dataClass), $dataClassID, "sendEmail", array("$('EMailSubject$dataClassID').value", "\$j('#EMailBody$dataClassID').val()", (is_array($data["recipients"]) AND count($data["recipients"]) == 1) ? "0" : "$('EMailRecipient$dataClassID').value", "'".$callbackParameter."'", "files"), $onSuccessFunction);
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
}

?>