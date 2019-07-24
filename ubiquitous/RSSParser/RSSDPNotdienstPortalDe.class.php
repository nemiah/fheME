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
 *  2007 - 2019, open3A GmbH - Support@open3A.de
 */
class RSSDPNotdienstPortalDe implements iFileBrowser, iRSSDataParser {
	public function getLabel() {
		return "notdienst-portal.de";
	}

	public function parse($data) {
		// Specify configuration
		$config = array(
			'indent'         => true,
			'output-xhtml'   => true,
			'wrap'           => 200);

		// Tidy
		$tidy = new tidy();
		$tidy->parseString($data, $config, 'utf8');
		$tidy->cleanRepair();
		$S = new HTMLSlicer();
		try {
			libxml_use_internal_errors(true);
			$tidy = str_replace("&nbsp;", " ", $tidy."");
			$XML = new SimpleXMLElement($tidy."");
		} catch (Exception $e){
			var_dump(libxml_get_errors());
		}
		$rss = "<?xml version=\"1.0\" encoding=\"utf-8\"?>
<rss version=\"2.0\">
	<channel>
		<title>Notdienste</title>
		<link>notdienst-portal.de</link>
		<description>Notdienste</description>
		<language>de-de</language>
		<copyright>Bayerische Landesapothekerkammer</copyright>
		<pubDate>".date("r")."</pubDate>

";
		foreach($XML->body->table[0]->tbody[0]->tr AS $row){
			$lat = "data-lat";
			$lon = "data-lon";
			
			#print_r();
			#print_r($row->attributes()->$lon);
			
			$von = "";
			$bis = "";
			$title = "";
			$content = "";
			$i = 0;
			foreach($row->td AS $cell){
				
				
				$td = trim(strip_tags($cell->asXML()));
				$td = trim(str_replace("Entfernung", "", $td));
				
				$ex = explode("\n", $td);
				foreach($ex AS $k => $line)
					$ex[$k] = trim($line);
				
				if($i == 0){
					$title = trim($ex[0]);
					unset($ex[0]);
				}
				
				foreach($ex AS $k => $l){
					if(stripos($l, "von") === 0){
						$von = $ex[$k];
						unset($ex[$k]);
					}
					if(stripos($l, "bis") === 0){
						$bis = $ex[$k];
						unset($ex[$k]);
					}
				}
				
				$content .= implode("\n", $ex)."\n";
				$i++;
			}
			
			$content = nl2br(trim($content));
			
			$rss .= "
		<item>
			<title><![CDATA[".html_entity_decode($title)."]]></title>
			<description><![CDATA[".preg_replace('/ +/', ' ', html_entity_decode($content))."]]></description>
			<link></link>
			<author></author>
			<guid></guid>
			<valid>$von $bis</valid>
			<lat>".$row->attributes()->$lat."</lat>
			<lon>".$row->attributes()->$lon."</lon>
			<pubDate>".date("r")."</pubDate>
		</item>";
			
			#print_r($content);
		}
		#$X = $XML->xpath("//tr");
		#var_dump($X);
		// Output
		
		
		$rss .= "
	</channel>
</rss>";
		return $rss;
	}
}
?>