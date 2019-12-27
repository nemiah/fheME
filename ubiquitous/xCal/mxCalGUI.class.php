<?php
/**
 *  This file is part of ubiquitous.

 *  ubiquitous is free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 3 of the License, or
 *  (at your option) any later version.

 *  ubiquitous is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.

 *  You should have received a copy of the GNU General Public License
 *  along with this program.  If not, see <http://www.gnu.org/licenses></http:>.
 * 
 *  2007 - 2019, open3A GmbH - Support@open3A.de
 */

class mxCalGUI extends anyC implements iGUIHTMLMP2 {

	public function getHTML($id, $page) {
		// :-)
	}
	
	public function createNewServer() {
		$F = new Factory("xCal");
		$F->sA("xCalServerActive", "1");
		$F->sA("xCalUserID", Session::currentUser()->getID());
		$F->store();
	}
	
	/**
	 * Popup zur Konfiguration eines Servers.
	 */
	public function getConfigPopup($echo = true) {
		// Button
		$BCreate = new Button("Server\nhinzufügen", "new");
		$BCreate->rmePCR("mxCal", "-1", "createNewServer", array(), OnEvent::reloadPopup("mxCal"));
		$htmlTableButton = new HTMLTable(1);
		$htmlTableButton->addRow($BCreate);
		
		// Liste der konfigurierten Server
		$userId = $_SESSION["S"]->getCurrentUser()->getID();
		$T = new HTMLTable(1, "xCal Server");
		$BDeleteRaw = new Button("Eintrag löschen", "./images/i2/delete.gif", "icon");
		$BDeleteRaw->style("float: right;");
		$serverList = anyC::get("xCal", "xCalUserID", $userId);
		$counter = 0;
		while ($S = $serverList->getNextEntry()) {
			$BDelete = clone $BDeleteRaw;
			$BDelete->onclick("deleteClass('xCal','" . $S->getID()."', function() { Popup.refresh('mxCal'); }, 'Eintrag wirklich löschen?');");
			
			$F = new HTMLForm("xCal_" . $S->A("xCalID"), array(
				"xCalName",
				"xCalUrl",
				"xCalServerActive"
			));
			$F->getTable()->setColWidth(1, 120);
			$F->setValues($S);
			$F->setLabel("xCalName", "Bezeichnung");
			$F->setLabel("xCalUrl", "URL");
			$F->setLabel("xCalServerActive", "Für den Import verwenden");
			$F->setType("xCalServerActive", "checkbox");
			$F->useRecentlyChanged();
			$F->setSaveClass("xCal", $S->getID(), "''");
			
			$display = "none";
			if($S->A("xCalName") == "" && $S->A("xCalUrl") == "")
				$display = "";
			$div = "<div
					onmouseover=\"this.className = 'backgroundColor3';\" 
					onmouseout=\"this.className = '';\" 
					style=\"padding:3px;cursor:pointer;\" 
					onclick=\"if($('APDetails" . $S->getID() . "').style.display == 'none') new Effect.BlindDown('APDetails" . $S->getID() . "'); else new Effect.BlindUp('APDetails" . $S->getID() . "');\">
					$BDelete<span id=\"APPosition" . $S->getID() . "\">" . ($S->A("xCalName") != "" ? $S->A("xCalName") : "Neuer Server") . "</span>&nbsp;<br />
					<small style=\"color:grey;\" id=\"APName" . $S->getID() . "\">" . ($S->A("xCalServerActive") == "1" ? "Import aktiviert" : "Import deaktiviert") . "&nbsp;</small>
				</div>";
			
			$T->addRow(array($div."<div id=\"APDetails" . $S->getID() . "\" style=\"display:" . $display . ";\">" . $F . "</div>"));
			$T->addRowClass("backgroundColor0");
			$T->addCellClass(1, "borderColor1");
			$counter++;
		}
		
		if ($counter == 0)
			$T->addRow("Keine Server eingetragen!");
		
		if ($echo == "" || $echo === true) {
			echo $htmlTableButton . $T;
		} else {
			return $htmlTableButton . $T;
		}
	}
	
	// getCalendarData (wie in der Todo)
	public static function getCalendarData($firstDay, $lastDay, $UserID = null, $xCalID = null) {
		$calendar = new Kalender();
		
		$AC = anyC::get("xCal", "xCalUserID", $UserID);
		$AC->addAssocV3("xCalServerActive", "=", "1");
		if($xCalID)
			$AC = anyC::get("xCal", "xCalID", $xCalID);
		
		while ($server = $AC->getNextEntry()) {
			// XML
			$xmlBuffer = xCalUtil::getSimpleXmlByXCal($server->A("xCalCache"));
			
			// Events
			foreach ($xmlBuffer->vcalendar->vevent AS $event) {
				
				// Überprüfung, ob es sich um DATE-TIME Einträge handelt
				$dateTimeFlag = false;
				foreach ($event->dtstart->attributes() AS $dtStartAttributeKey => $dtStartAttributeValue) {
					if ($dtStartAttributeKey == "value" && (String) $dtStartAttributeValue === "DATE-TIME")
						$dateTimeFlag = true;
				}
				if (!$dateTimeFlag)
					continue;
				
				// Konvertierung der Zeitstempel
				$match = array();
				preg_match("/^(\d{4})(\d{2})(\d{2})T(\d{2})(\d{2})(\d{2})Z$/", (String) $event->dtstart, $match);
				$startTimestamp = gmmktime($match[4], $match[5], $match[6], $match[2], $match[3], $match[1]);
				$match = array();
				preg_match("/^(\d{4})(\d{2})(\d{2})T(\d{2})(\d{2})(\d{2})Z$/", (String) $event->dtend, $match);
				$endTimestamp = gmmktime($match[4], $match[5], $match[6], $match[2], $match[3], $match[1]);
				#echo date("d.m.Y H:i", $startTimestamp)." $event->dtstart $event->summary<br />";
				// Weiter wenn Terminende zu weit zurück liegt oder Anfangszeit zu weit in der Zukunft
				if ($endTimestamp < $firstDay OR $startTimestamp > $lastDay)
					continue;
				
				$calendarEvent = new KalenderEvent("mxCalGUI", $server->A("xCalID") . ":" . (String) $event->uid, $calendar->formatDay($startTimestamp), $calendar->formatTime($startTimestamp), (String)$event->summary);
				$calendarEvent->endDay($calendar->formatDay($endTimestamp));
				$calendarEvent->endTime($calendar->formatTime($endTimestamp));
				$calendar->addEvent($calendarEvent);
			}
		}

		return $calendar;
	}
	
	public static function getCalendarDetails($className, $classId) {
		$calendar = new Kalender();
		
		$ids = preg_split("/:/", $classId);
		$server = anyC::getFirst("xCal", "xCalID", $ids[0]);
		$xmlBuffer = xCalUtil::getSimpleXmlByXCal($server->A("xCalCache"));
		
		foreach ($xmlBuffer->vcalendar->vevent as $event) {
			if ((String) $event->uid != $ids[1])
				continue;
			
			// Konvertierung der Zeitstempel
			$match = array();
			preg_match("/^(\d{4})(\d{2})(\d{2})T(\d{2})(\d{2})(\d{2})Z$/", (String) $event->dtstart, $match);
			$startTimestamp = gmmktime($match[4], $match[5], $match[6], $match[2], $match[3], $match[1]);
			$match = array();
			preg_match("/^(\d{4})(\d{2})(\d{2})T(\d{2})(\d{2})(\d{2})Z$/", (String) $event->dtend, $match);
			$endTimestamp = gmmktime($match[4], $match[5], $match[6], $match[2], $match[3], $match[1]);

			$calendarEvent = new KalenderEvent("mxCalGUI", $server->A("xCalID") . ":" . (String) $event->uid, $calendar->formatDay($startTimestamp), $calendar->formatTime($startTimestamp), (String)$event->summary);
			$description = preg_replace("/\n|\r/", "<br>", (String) $event->description);
			$calendarEvent->summary($description);
			return $calendarEvent;
		}
		
	}
	
}
?>