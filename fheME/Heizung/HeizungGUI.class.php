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
 *  2007 - 2022, open3A GmbH - Support@open3A.de
 */
class HeizungGUI extends Heizung implements iGUIHTML2 {
	function getHTML($id){
		$gui = new HTMLGUIX($this);
		$gui->name("Heizung");
	
		$gui->type("HeizungFhemServerID", "select", anyC::get("FhemServer"), "FhemServerName", "Bitte auswählen…");
		
		$gui->label("HeizungFhemServerID", "Fhem-Server");
		$gui->label("HeizungOpenWeatherMapID", "Wetter");
		
		#$B = $gui->addSideButton("Sonne", "new");
		#$B->popup("", "Datenanzeigen", "Heizung", $this->getID(), "sun");
		
		$gui->space("HeizungVentStage", "Sommerlüftung");
		$gui->space("HeizungWaterHotTemp", "Wasser-Speicher");
		
		$gui->parser("HeizungTempLog", "parserTempLog");
		
		$gui->descriptionField("HeizungWaterHotTemp", "Max 55 Grad");
		
		$gui->type("HeizungWechselrichterID", "select", anyC::get("Wechselrichter"), "WechselrichterName", "Bitte auswählen…");
		$gui->type("HeizungOpenWeatherMapID", "select", anyC::get("OpenWeatherMap"), "OpenWeatherMapName", "Bitte auswählen…");
		
		$B = $gui->addSideButton("Daten\nanzeigen", "new");
		$B->popup("", "Datenanzeigen", "Heizung", $this->getID(), "showData", "", "", "{width:800}");
		
		return $gui->getEditHTML();
	}
	
	public static function parserTempLog($w, $l, $E){
		$B = new Button("Log\nanzeigen", "new");
		$B->popup("", "Log", "Heizung", $E->getID(), "logPopup");
		return $B;
	}
	
	public function logPopup(){
		$log = json_decode($this->A("HeizungTempLog"), true);
		$T = new HTMLTable(2);
		$T->maxHeight(400);
		foreach($log AS $time => $value)
			$T->addRow([Util::CLDateTimeParser($time), $value]);
		
		echo $T;
	}
	
	function showData(){
		$T = new HTMLTable(3);
		
		$xml = $this->getData();
		foreach($xml->THZ_LIST->THZ[0]->STATE AS $STATE)
			$T->addRow([$STATE["key"], $STATE["value"], $STATE["measured"]]);
		
		echo $T;
	}
	
	public function waterPopup(){
		#$data = $this->getParsedData();
		$tage = ["" => "keiner", "Mo" => "Montag", "Th" => "Dienstag", "We" => "Mittwoch", "Th" => "Donnerstag", "Fr" => "Freitag", "Sa" => "Samstag", "So" => "Sonntag"];
		#echo "<pre>";
		#print_r($data);
		#echo str_replace("--", " - ", $data["programDHW_Mo-So_0"]);
		#echo "</pre>";
		
		#echo "<p>Aktuelle Einstellungen:<br>";
		$bathDay = mUserdata::getGlobalSettingValue("HeizungWaterBathDay", "");
		#echo "Badetag: ".$tage[$bathDay];
		#if($override > time())
		#	echo "Manueller Modus bis ".Util::CLDateTimeParser($override)."<br>";
		
		#if($override < time())
		#	echo "Manueller Modus beendet seit ".Util::CLDateTimeParser($override)."<br>";
		#echo "</p>";
		#echo "Start: ".$data["pHolidayBeginDay"].".".$data["pHolidayBeginMonth"].".".$data["pHolidayBeginYear"].", ".$data["pHolidayBeginTime"]." Uhr<br>";
		#echo "Ende: ".$data["pHolidayEndDay"].".".$data["pHolidayEndMonth"].".".$data["pHolidayEndYear"].", ".$data["pHolidayEndTime"]." Uhr<br>";
		
		$F = new HTMLForm("water", ["badetag"]);
		$F->getTable()->setColWidth(1, 120);
		$F->setType("badetag", "select", $bathDay, $tage);
		
		$F->setSaveRMEPCR("Speichern", "", "Heizung", $this->getID(), "waterSet", OnEvent::closePopup("Heizung"));
		
		echo $F;
	}
	
	public function waterSet($bathDay){
		mUserdata::setUserdataS("HeizungWaterBathDay", $bathDay, "", -1);
	}
	
	public function heatPopup(){
		#$data = $this->getParsedData();
		#echo "<pre>";
		#print_r($data);
		#echo "</pre>";
		echo "<p>Aktuelle Einstellungen:<br>";
		$override = mUserdata::getGlobalSettingValue("HeizungHeatUntil", "0");
		if($override > time())
			echo "Manueller Modus bis ".Util::CLDateTimeParser($override)."<br>";
		
		if($override < time())
			echo "Manueller Modus beendet seit ".Util::CLDateTimeParser($override)."<br>";
		echo "</p>";
		#echo "Start: ".$data["pHolidayBeginDay"].".".$data["pHolidayBeginMonth"].".".$data["pHolidayBeginYear"].", ".$data["pHolidayBeginTime"]." Uhr<br>";
		#echo "Ende: ".$data["pHolidayEndDay"].".".$data["pHolidayEndMonth"].".".$data["pHolidayEndYear"].", ".$data["pHolidayEndTime"]." Uhr<br>";
		
		$F = new HTMLForm("heat", ["dauer"]);
		$F->getTable()->setColWidth(1, 120);
		$F->setType("dauer", "select", "0", [-1 => "Ausschalten", 0 => "Nichts ändern", 60 => "Heizen für 1 Stunde", 120 => "Heizen für 2 Stunden", 180 => "Heizen für 3 Stunden"]);
		
		$F->setSaveRMEPCR("Speichern", "", "Heizung", $this->getID(), "heatSet", OnEvent::closePopup("Heizung"));
		
		echo $F;
	}
	
	public function heatSet($duration){
		if($duration == 0)
			return;
		
		$this->connect();
		
		if($duration == -1){
			mUserdata::setUserdataS("HeizungHeatUntil", 0);
			
			$c = "set ".$this->A("HeizungFhemName")." p01RoomTempDayHC1 ".Heizung::$raumtempDefault;
			$this->connection->fireAndForget($c);
			echo $c."\n";
			mUserdata::setUserdataS("p01RoomTempDayHC1", Heizung::$raumtempDefault, "", -1);
			
			$c = "set ".$this->A("HeizungFhemName")." p49SummerModeTemp ".Heizung::$summerModeTempDefault;
			$this->connection->fireAndForget($c);
			echo $c."\n";
			
			return;
		}
		
		#$start = Util::CLDateParser($start, "store");
		#$end = Util::CLDateParser($end, "store");
		
		$c = "set ".$this->A("HeizungFhemName")." p49SummerModeTemp ".Heizung::$summerModeTempHeat;
		$this->connection->fireAndForget($c);
		echo $c."\n";
		
		$c = "set ".$this->A("HeizungFhemName")." p01RoomTempDayHC1 ".Heizung::$raumtempHeat;
		$this->connection->fireAndForget($c);
		echo $c."\n";
		
		mUserdata::setUserdataS("p01RoomTempDayHC1", Heizung::$raumtempHeat, "", -1);
		
		mUserdata::setUserdataS("HeizungHeatUntil", time() + 60 * $duration, "", -1);
	}
	
	public function ferienPopup(){
		$data = $this->getParsedData();
		#echo "<pre>";
		#print_r($data);
		#echo "</pre>";
		echo "Aktuelle Einstellungen:<br>";
		echo "Start: ".$data["pHolidayBeginDay"].".".$data["pHolidayBeginMonth"].".".$data["pHolidayBeginYear"].", ".$data["pHolidayBeginTime"]." Uhr<br>";
		echo "Ende: ".$data["pHolidayEndDay"].".".$data["pHolidayEndMonth"].".".$data["pHolidayEndYear"].", ".$data["pHolidayEndTime"]." Uhr<br>";
		
		$F = new HTMLForm("holiday", ["start", "end"]);
		$F->getTable()->setColWidth(1, 120);
		$F->setType("start", "date");
		$F->setType("end", "date");
		
		$F->setSaveRMEPCR("Speichern", "", "Heizung", $this->getID(), "ferienSet", OnEvent::closePopup("Heizung"));
		
		echo $F;
	}
	
	public function ferienSet($start, $end){
		$start = Util::CLDateParser($start, "store");
		$end = Util::CLDateParser($end, "store");
		$c = $this->connect();
		
		$com = "set ".$this->A("HeizungFhemName")." pHolidayBeginDay ".date("d", $start);
		$c->fireAndForget($com);
		echo $com."\n";
		
		$com = "set ".$this->A("HeizungFhemName")." pHolidayBeginMonth ".date("m", $start);
		$c->fireAndForget($com);
		echo $com."\n";
		
		$com = "set ".$this->A("HeizungFhemName")." pHolidayBeginYear ".date("y", $start);
		$c->fireAndForget($com);
		echo $com."\n";
		
		$com = "set ".$this->A("HeizungFhemName")." pHolidayBeginTime 12:00";
		$c->fireAndForget($com);
		echo $com."\n";
		
		$com = "set ".$this->A("HeizungFhemName")." pHolidayEndDay ".date("d", $end);
		$c->fireAndForget($com);
		echo $com."\n";
		
		$com = "set ".$this->A("HeizungFhemName")." pHolidayEndMonth ".date("m", $end);
		$c->fireAndForget($com);
		echo $com."\n";
		
		$com = "set ".$this->A("HeizungFhemName")." pHolidayEndYear ".date("y", $end);
		$c->fireAndForget($com);
		echo $com."\n";
		
		$com = "set ".$this->A("HeizungFhemName")." pHolidayEndTime 01:00";
		$c->fireAndForget($com);
		echo $com."\n";
	}
}
?>