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
 *  2007 - 2014, Rainer Furtmeier - Rainer@Furtmeier.IT
 */

class xCalUtil {
	
	/**
	 * Erstellt aus der Liste der Events ein vollständiges XML Dokument. 
	 * Die Ausgabe erfolgt wahlweise als XML-Zeichenkette oder als DOMDocument-Objekt.
	 * 
	 * @param Array $vEventArray Liste der vevent DOMElemente
	 * @param String $format "XML"|"DOMElement"
	 * @return String|DOMElement
	 * @throws Exception
	 */
	public static function getXCal($vEventArray, $format = "XML") {
		// Struktur
		$xmlDocument = new DOMDocument("1.0", "UTF-8");
		$root = $xmlDocument->appendChild($xmlDocument->createElement("icalendar"));
		$vCalendar = $root->appendChild($xmlDocument->createElement("vcalendar"));
		$vCalendar->appendChild($xmlDocument->createElement("prodid", "-//Mozilla.org/NONSGML Mozilla Calendar V 1.0 //EN"));
		$vCalendar->appendChild($xmlDocument->createElement("version", "2.0"));
		
		// Eventliste
		foreach ($vEventArray as $i => $vEvent) {
			if (get_class($vEvent) !== "DOMElement")
				throw new Exception("Unknown type in vEventArray at position " . $i);
			$vEvent = $xmlDocument->importNode($vEvent, true);
			$vCalendar->appendChild($vEvent);
		}
		
		// Rückgabe
		switch ($format) {
			case "XML":
				return $xmlDocument->saveXML();
				break;
			case "DOMElement":
				return $root;
				break;
			default:
				throw new Exception("Unknown output format");
				break;
		}
	}
	
	/**
	 * Erstellt aus den übergebenen Datenobjekt ein DOMElement.
	 * @param xCalDataEvent $xCalDataEvent
	 * @return DOMElement
	 */
	public static function getXCalEventByXCalDataEvent(xCalDataEvent $xCalDataEvent) {
		$xmlDocument = new DOMDocument("1.0", "UTF-8");
		$root = $xmlDocument->appendChild($xmlDocument->createElement("vevent"));
		
		$root->appendChild($xmlDocument->createElement("uid", $xCalDataEvent->getUid()));
		$dtStart = $root->appendChild($xmlDocument->createElement("dtstart", $xCalDataEvent->getDtStart()));
		$dtStart->appendChild($xmlDocument->createAttribute("value"))->appendChild($xmlDocument->createTextNode($xCalDataEvent->getDtStartValue()));
		if ($xCalDataEvent->getDtEnd() == "") {
			$root->appendChild($xmlDocument->createElement("duration", $xCalDataEvent->getDuration()));
		} else {
			$dtEnd = $root->appendChild($xmlDocument->createElement("dtend", $xCalDataEvent->getDtEnd()));
			$dtEnd->appendChild($xmlDocument->createAttribute("value"))->appendChild($xmlDocument->createTextNode($xCalDataEvent->getDtEndValue()));
		}
		$root->appendChild($xmlDocument->createElement("description", $xCalDataEvent->getDescription()));
		$root->appendChild($xmlDocument->createElement("url", $xCalDataEvent->getUrl()));
		$root->appendChild($xmlDocument->createElement("summary", $xCalDataEvent->getSummary()));
		
		return $root;
	}
	
	/**
	 * Kovertiert XML-Code in ein SimpleXMLElement.
	 * @param String $xml
	 * @return SimpleXMLElement
	 */
	public static function getSimpleXmlByXCal($xml) {
		$xmlDocument = new DOMDocument("1.0", "UTF-8");
		$xmlDocument->loadXML($xml);
		return simplexml_import_dom($xmlDocument);
	}
	
}

?>