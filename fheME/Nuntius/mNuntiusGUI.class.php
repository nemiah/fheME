<?php
/**
 *  This file is part of fheME.

 *  fheME is free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 3 of the License, or
 *  (at your option) any later version.

 *  fheME is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.

 *  You should have received a copy of the GNU General Public License
 *  along with this program.  If not, see <http://www.gnu.org/licenses></http:>.
 * 
 *  2007 - 2013, Rainer Furtmeier - Rainer@Furtmeier.IT
 */

class mNuntiusGUI extends anyC implements iGUIHTMLMP2 {

	public function getHTML($id, $page){
		$this->loadMultiPageMode($id, $page, 0);

		$gui = new HTMLGUIX($this);
		$gui->version("mNuntius");

		$gui->name("Nuntius");
		
		$gui->attributes(array());
		
		return $gui->getBrowserHTML($id);
	}


	public function getOverviewContent($DeviceID){
		$html = "<div class=\"touchHeader\"><span class=\"lastUpdate\" id=\"lastUpdatemNuntiusGUI\"></span><p>Nuntius</p></div>
			<div style=\"padding:10px;overflow:auto;\">";

		$Device = new Device($DeviceID);
		
		$BS = new Button("Nachricht senden", "comment_alt2_stroke", "touch");
		$BS->popup("", "Nachricht senden", "mNuntius", "-1", "sendMessagePopup", "", "", "{remember: true".($Device->A("DeviceType") != "4" ? ", top: 10" : "")."}");
		
		$AC = anyC::get("Nuntius");
		$AC->addAssocV3("NuntiusDeviceID", "=", $DeviceID, "AND", "1");
		$AC->addAssocV3("NuntiusDeviceID", "=", "0", "OR", "1");
		$AC->addAssocV3("NuntiusRead", "=", "0", "AND", "2");
		$AC->setFieldsV3(array("COUNT(*) AS anzahl"));
		
		$N = $AC->getNextEntry();
		
		if($N->A("anzahl") == 0){
			$BM = new Button("Keine Nachrichten", "check", "touch");
			$BM->style("background-color:transparent;");
		} else {
			$BM = new Button($N->A("anzahl")." Nachricht".($N->A("anzahl") == 1 ? "" : "en"), "info", "touch");
			$BM->className("highlight");
		}
		
		$BM->popup("", "Nachrichten", "mNuntius", "-1", "showMessages", array($DeviceID), "", "{remember: true".($Device->A("DeviceType") != "4" ? ", top: 10" : "")."}");
		
		$html .= "$BM$BS</div>";
		echo $html;
	}
	
	public static function getOverviewPlugin(){
		$P = new overviewPlugin("mNuntiusGUI", "Nuntius", 0);
		$P->updateInterval(30);
		
		return $P;
	}
	
	public function showMessages($DeviceID){
		$AC1 = anyC::get("Nuntius");
		$AC1->addAssocV3("NuntiusDeviceID", "=", $DeviceID, "AND", "1");
		$AC1->addAssocV3("NuntiusDeviceID", "=", "0", "OR", "1");
		$AC1->addOrderV3("NuntiusTime", "DESC");
		$AC1->addAssocV3("NuntiusRead", "=", "0", "AND", "2");
		
		
		$L = "<div style=\"max-height:400px;overflow:auto;\">";
		while($N = $AC1->getNextEntry())
			$L .= $N->message();
		
		
		$AC2 = anyC::get("Nuntius");
		$AC2->addAssocV3("NuntiusDeviceID", "=", $DeviceID, "AND", "1");
		$AC2->addAssocV3("NuntiusDeviceID", "=", "0", "OR", "1");
		$AC2->addOrderV3("NuntiusTime", "DESC");
		$AC2->addAssocV3("NuntiusRead", ">", "0", "AND", "2");
		$AC2->setLimitV3("15");
		
		while($N = $AC2->getNextEntry())
			$L .= $N->message();
		
		if($AC1->numLoaded() == 0 AND $AC2->numLoaded() == 0)
			$L .= "<p>Keine Nachrichten</p>";
		
		echo $L."</div>";
		
	}
	
	public function sendMessagePopup(){
		$AC = anyC::get("Device");
		$AC->addOrderV3("DeviceName");
		
		$B = new Button("Alle", "fullscreen", "touch");
		$B->className("nuntiusMessageTo");
		$B->id("nuntiusMessageTarget0");
		$B->onclick("\$j('.nuntiusMessageTo:not(#nuntiusMessageTarget0)').slideToggle(); \$j('#nuntiusMessageContainer').slideToggle();");
		
		$L = $B;
		while($D = $AC->getNextEntry()){
			$B = new Button($D->A("DeviceName"), "iphone", "touch");
			$B->className("nuntiusMessageTo");
			$B->id("nuntiusMessageTarget".$D->getID());
			
			$B->onclick("\$j('.nuntiusMessageTo:not(#nuntiusMessageTarget".$D->getID().")').slideToggle(); \$j('#nuntiusMessageContainer').slideToggle();");
			$L .= $B;
		}
		
		$BS = new Button("Senden", "arrow_right", "touch");
		$BS->style("margin-top:0px;height:190px;margin-bottom:0px;");
		$BS->rmePCR("mNuntius", "-1", "sendMessage", array("\$j('.nuntiusMessageTo:visible').attr('id').replace('nuntiusMessageTarget', '')", "\$j('[name=NuntiusMessage]').val()", "\$j.jStorage.get('phynxDeviceID', -1)"), "function(){ ".OnEvent::closePopup("mNuntius")." }");
		
		$I = new HTMLInput("NuntiusMessage", "textarea");
		$I->style("width:100%;height:200px;max-width:400px;font-size:15px;");
		$I->placeholder("Nachricht...");
		
		echo "
			<div id=\"nuntiusTargets\" style=\"padding-top:15px;padding-bottom:1px;\">
				".$L."
			</div>
			<div style=\"display:none;padding-bottom:1px;\" id=\"nuntiusMessageContainer\">
				<div style=\"width:100px;float:right;\">$BS</div><div style=\"width:300px;\">$I</div>
			</div>";
	}

	public function sendMessage($target, $message, $from){
		if(trim($message) == "")
			return;
		
		$F = new Factory("Nuntius");
		
		$F->sA("NuntiusDeviceID", $target);
		$F->sA("NuntiusSender", "Device:".$from);
		$F->sA("NuntiusTime", time());
		$F->sA("NuntiusUrgency", 50);
		$F->sA("NuntiusMessage", $message);
		$F->sA("NuntiusRead", "0");
		
		$F->store();
	}
}
?>