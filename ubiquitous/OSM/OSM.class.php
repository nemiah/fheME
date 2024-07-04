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
 *  along with this program.  If not, see <http://www.gnu.org/licenses/>.
 * 
 *  2007 - 2024, open3A GmbH - Support@open3A.de
 */
#namespace open3A;
class OSM extends PersistentObject {
	
	public static function getGeoLocation($AdresseID, $country = null, $zip = null, $city = null, $street = null, $die = true){
		$AC = anyC::get("OSM");
		if($AdresseID == null){
			$AC->addAssocV3("OSMLand", "=", $country);
			$AC->addAssocV3("OSMPLZ", "=", $zip);
			$AC->addAssocV3("OSMStadt", "=", $city);
			$AC->addAssocV3("OSMStrasse", "=", $street);
		} else {
			$AC->addAssocV3("OSMAdresseID", "=", $AdresseID);
			
			$Adresse = new Adresse($AdresseID);
			$country = $Adresse->A("land");
			$zip = $Adresse->A("plz");
			$city = $Adresse->A("ort");
			$street = $Adresse->A("strasse");
		}
		
		$Location = $AC->getNextEntry();
		if($Location != null)
			return $Location;
		
		#$xmlStart = self::postToORS("OpenLSLUS_Geocode.php", "FreeFormAdress=$country $zip $city $street&MaxResponse=1&_=");
		#if($xmlStart == null)
		#	Red::alertD("Der Server lieferte keine Antwort für $country $zip $city $street. Vermutlich existiert diese Adresse nicht.");
		
		#$data = $xmlStart->children("xls", true)->Response->GeocodeResponse->GeocodeResponseList->GeocodedAddress->children("gml", true)->Point->pos."";
		$xmlStart = file_get_contents("https://maps.googleapis.com/maps/api/geocode/xml?address=".urlencode(trim("$country $zip $city $street"))."&sensor=false&key=".mUserdata::getGlobalSettingValue("googleAPIKeyMaps"));
		#echo htmlentities(print_r($xmlStart, true));
		#$xmlStart = file_get_contents("http://maps.google.com/maps/geo?q=".urlencode(trim("$country $zip $city $street"))."&output=xml&sensor=false");
		#die("http://maps.google.com/maps/geo?q=".urlencode(trim("$country $zip $city $street"))."&output=xml&sensor=false");
		#die(htmlentities($xmlStart));
		
		#echo "<pre>";
		#echo htmlentities(print_r($xmlStart, true));
		#echo "</pre>";
		
		$xml = new SimpleXMLElement($xmlStart);
		
		if($xml->status."" != "OK"){
			throw new Exception($xml->error_message);
			#Red::alertD("Der Server konnte die Adresse $country $zip $city $street nicht finden.", $die);
		}
		$data = $xml->result->geometry->location->lat." ".$xml->result->geometry->location->lng;
		
		$F = new Factory("OSM");
		$F->sA("OSMLand", $country);
		$F->sA("OSMPLZ", $zip);
		$F->sA("OSMStadt", $city);
		$F->sA("OSMStrasse", $street);
		$F->sA("OSMAdresseID", $AdresseID);
		$F->sA("OSMData", $data);
		$F->sA("OSMUpdated", time());
		$id = $F->store();
		
		return new OSM($id);
	}
	
	public static function getLocation($country, $zip, $city, $street, $die = true){
		$xmlStart = file_get_contents("http://maps.googleapis.com/maps/api/geocode/xml?address=".urlencode(trim("$country $zip $city $street"))."&sensor=false");
		
		$xml = new SimpleXMLElement($xmlStart);
		
		if($xml->status."" != "OK")
			Red::alertD("Der Server konnte die Adresse $country $zip $city $street nicht finden.", $die);
		
		return array($xml->result->geometry->location->lat."", $xml->result->geometry->location->lng."");
	}
	
	public static function calcDistance(OSM $geoLocationStart, OSM $geoLocationEnd){
		$AC = anyC::get("OSMDistance");
		$AC->addAssocV3("OSMDistanceOSMID1", "=", $geoLocationStart->getID());
		$AC->addAssocV3("OSMDistanceOSMID2", "=", $geoLocationEnd->getID());
		
		$OSMDistance = $AC->getNextEntry();
		if($OSMDistance != null)
			return $OSMDistance;
		
		$sxml = self::postToORS("OpenLSRS_DetermineRoute.php", "Start=".$geoLocationStart->A("OSMData")."&End=".$geoLocationEnd->A("OSMData")."&Via=&lang=de&distunit=KM&routepref=Fastest&avoidAreas=&useTMC=&&noMotorways=false&noTollways=false&instructions=false&_=");

		$km = $sxml->children("xls", true)->Response->DetermineRouteResponse->RouteSummary->TotalDistance->attributes()->value."";
		$duration = $sxml->children("xls", true)->Response->DetermineRouteResponse->RouteSummary->TotalTime."";
		#print_r($duration);
		$duration = str_replace("PT", "", $duration);
		$ex = explode("M", $duration);
		$duration = $ex[0];
		#echo "<br />".$duration."<br />";
		if(strpos($duration, "H") === false)
			$duration = "00:".(strlen($duration) < 2 ? "0" : "").$duration;
		else {
			$ex = explode("H", $duration);
			$duration = $ex[0].":".(strlen($ex[1]) < 2 ? "0" : "").$ex[1];
		}
		
		#echo "<br />".$duration."<br />";
		#$duration = str_replace("M", ":", $duration);
		#$duration = str_replace("S", "", $duration);
		$duration = Util::CLTimeParser($duration, "store");
		
		$F = new Factory("OSMDistance");
		$F->sA("OSMDistanceOSMID1", $geoLocationStart->getID());
		$F->sA("OSMDistanceOSMID2", $geoLocationEnd->getID());
		$F->sA("OSMDistanceDuration", $duration);
		$F->sA("OSMDistanceKM", $km);
		$F->sA("OSMDistanceUpdated", time());
		$id = $F->store();
		
		return new OSMDistance($id);
	}
	
	public static function postToORS($target, $data_to_send){
		$startTime = time();
		
		$fp = fsockopen("openrouteservice.org", 80, $errno, $errstr, 3);
		stream_set_blocking($fp, 0);
		fputs($fp, "POST http://openrouteservice.org/php/$target HTTP/1.1\r\n");
		fputs($fp, "Host: openrouteservice.org\r\n");
		fputs($fp, "User-Agent: ".$_SERVER["HTTP_USER_AGENT"]."\r\n");
		fputs($fp, "Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8\r\n");
		fputs($fp, "Accept-Language: de-de,de;q=0.8,en;q=0.6,en-us;q=0.4,it;q=0.2\r\n");
		fputs($fp, "Accept-Charset: ISO-8859-1,utf-8;q=0.7,*;q=0.7\r\n");
		fputs($fp, "X-Requested-With: XMLHttpRequest\r\n");
		fputs($fp, "X-Prototype-Version: OpenLayers\r\n");
		fputs($fp, "Content-Type: application/x-www-form-urlencoded; charset=UTF-8\r\n");
		fputs($fp, "Referer: http://openrouteservice.org/index.php\r\n");

		fputs($fp, "Content-length: ".strlen($data_to_send)."\r\n");
		fputs($fp, "Connection: close\r\n\r\n");
		fputs($fp, $data_to_send);

		$timedOut = false;
		$res = "";
		while(!feof($fp)){
			
			if(time() - $startTime > 20) {
				$timedOut = true;
				break;
			}
			
			$res .= fgets($fp, 128);
		}
		
		#print_r(stream_get_meta_data($fp));
		fclose($fp);
		#print_r($res);
		if($timedOut)
			return null;
		
		$xml = substr($res, strpos($res, '<?xml version="1.0" encoding="UTF-8"?>'));

		$xml = trim($xml);
		$lastXml = strpos($xml, "</xls:XLS>");
		$xml = substr($xml, 0, $lastXml + 10);
				
		#print_r($xml);
		$sxml = new SimpleXMLElement($xml);
		
		return $sxml;
	}
}
?>