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
 *  2007 - 2020, open3A GmbH - Support@open3A.de
 */
class exportKalenderGUI extends exportDefault implements iExport, iGUIHTML2 {

	public function getApps(){
		return array("lightCRM");
	}
	
	
	public function getHTML($id){

		$fields = array();
		$fields[] = "start";
		$fields[] = "ende";
		
		$F = new HTMLForm("lexEx", $fields, "Export-Einstellungen");
		
		$F->setType("start", "date", mUserdata::getUDValueS("exportKalenderStart", ""));
		$F->setType("ende", "date", mUserdata::getUDValueS("exportKalenderEnde", ""));
		
		
		$F->setDescriptionField("start", "inklusive dem ausgewählten Tag");
		$F->setDescriptionField("ende", "inklusive dem ausgewählten Tag");
		
		#$F->setLabel("buchungssaetze", "Buchungssätze");
		$F->setLabel("dateStart", "Datum Start");
		$F->setLabel("dateEnd", "Datum Ende");
		#$F->setLabel("defaultErloes", "Standard Erlöskonto");
		
		$F->getTable()->addColStyle(1, "width:120px;");
		
		#$F->hideIf("buchungssaetze", "=", "0", "onchange", array("defaultErloes"));
		
		$F->addJSEvent("start", "onChange", "rmeP('exportKalender','','saveStart',this.value,'checkResponse(transport);')");
		$F->addJSEvent("ende", "onChange", "rmeP('exportKalender','','saveEnde',this.value,'checkResponse(transport);')");
		#$F->addJSEvent("month", "onChange", "contentManager.rmePCR('exportWilkenEntire','','saveMonth',this.value); ");

		#$F->hideIf("month", "=", "last", "onChange", array("stichtag"));
		#$F->hideIf("month", "!=", "manual", "onChange", array("start", "ende"));
		
		return parent::getHTML($id).$F;
	}
		
	public static function saveStart($start){
		mUserdata::setUserdataS("exportKalenderStart", $start);

		echo "message:'Einstellung gespeichert'";
	}

	public static function saveEnde($ende){
		mUserdata::setUserdataS("exportKalenderEnde", $ende);

		echo "message:'Einstellung gespeichert'";
	}
	

	/*public static function saveUser($month){
		mUserdata::setUserdataS("exportKalenderUser", $month);

		echo "message:'Einstellung gespeichert'";
	}*/


	public function getExportCollection(){
		$start = mUserdata::getUDValueS("exportKalenderStart", "");
		$ende = mUserdata::getUDValueS("exportKalenderEnde", "");
		if($start == "" OR $ende == "")
			die(Util::getBasicHTMLError ("Bitte tragen Sie ein Start- und ein Endedatum ein", "Fehler"));
		
		$day = new Datum(Util::CLDateParser($start, "store"));
		$lastDay = new Datum(Util::CLDateParser($ende, "store"));
		
		$Kalender = new mKalenderGUI();
		$K = $Kalender->getData($day->time(), $lastDay->time());
		
		#echo "<pre>";
		
		$AC = new ArrayCollection();
		while($day->time() <= $lastDay->time()){
			$events = $K->getEventsOnDay(date("dmY", $day->time()));
			if(count($events) == 0){
				$day->addDay();
				continue;
			}
			
			foreach($events AS $time)
				foreach($time AS $event){
					#print_r($event);
					$PO = new PersistentObject($event->UID());
					$A = new stdClass();
					$A->titel = $event->title();
					$A->start = Util::CLDateTimeParser(Kalender::parseDay($event->getDay())+Kalender::parseTime($event->getTime()) - 60);
					$A->ende = Util::CLDateTimeParser(Kalender::parseDay($event->getEndDay())+Kalender::parseTime($event->getEndTime()) - 60);
					$PO->setA($A);
					
					$AC->add($PO);
				}
			$day->addDay();
		}
		#echo "</pre>";
		
		return $AC;
	}
	
	public function getLabel(){
		return "Kalender";
	}

	protected function entryParser(\PersistentObject $entry) {
		
	}

}
?>