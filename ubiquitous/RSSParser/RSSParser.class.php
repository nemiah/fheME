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
 *  2007 - 2015, Rainer Furtmeier - Rainer@Furtmeier.IT
 */
class RSSParser extends PersistentObject implements iCloneable {
	public function parseFeed(){
		if($this->A("RSSParserUseCache"))
			$data = $this->A("RSSParserCache");
		else
			$data = file_get_contents($this->A("RSSParserURL"));
		
		libxml_use_internal_errors(true);
		
		$data = str_replace("<content:encoded>","<contentEncoded>",$data);
        $data = str_replace("</content:encoded>","</contentEncoded>",$data);
		
		try {
			$xml = new SimpleXMLElement($data);
		} catch(Exception $e){
			try {
				$config = array(
					'indent'     => true,
					'input-xml'  => true,
					'output-xml' => true,
					'wrap'       => false);
				
				$tidy = new tidy();
				$tidy->ParseString($data, $config, "utf8");

				$tidy->cleanRepair();
				#print_r($tidy."");
				$xml = new SimpleXMLElement($tidy."");
			
			} catch (Exception $e){
				/*echo "<pre>";
				echo "Exception: ".$e->getMessage()."\n";

				foreach(libxml_get_errors() as $error){
					if($error->level == LIBXML_ERR_WARNING) echo "Warning: ".$error->message;
					if($error->level == LIBXML_ERR_ERROR) echo "Error: ".$error->message;
					if($error->level == LIBXML_ERR_FATAL) echo "Fatal error: ".$error->message;
				}
				echo "</pre>";*/
			
				return array();
			}
		}
		
		$E = array();
		
		foreach($xml->channel->item AS $k => $item){
			$I = new stdClass();
			
			$I->title = $item->title."";
			$I->description = $item->description."";
			$I->pubDate = strtotime($item->pubDate);
			$I->icon = null;
			$I->content = $item->contentEncoded."";
			
			$E[] = $I;
		}
		
		$P = $this->A("RSSParserParserClass");
		if($P != ""){
			$P = new $P();
		
			foreach($E AS $item){
				$item->title = $P->parseTitle($item->title);
				$item->description = $P->parseDescription($item->description);
				$item->icon = $P->getIcon($item);
			}
		}
		
		return $E;
	}
	
	public function download(){
		if($this->A("RSSParserPOST") != ""){
			$ch = curl_init();

			$D = new Datum();
			$D->normalize();

			$fieldsString = "";
			$fields = explode("\n", trim($this->A("RSSParserPOST")));
			foreach($fields AS $field){
				$field = str_replace("\$timestampToday", $D->time() - 60, $field);
				$field = str_replace("\$dateToday", date("d.m.Y"), $field);
				
				$ex = explode(":", $field);
				$fieldsString .= ($fieldsString != "" ? "&" : "")."$ex[0]=".urlencode($ex[1]);
			}

			curl_setopt($ch,CURLOPT_URL, $this->A("RSSParserURL"));
			curl_setopt($ch,CURLOPT_POST, true);
			curl_setopt($ch,CURLOPT_POSTFIELDS, $fieldsString);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			
			$result = curl_exec($ch);

			curl_close($ch);

			if($this->A("RSSParserDataParserClass") != ""){
				$C = $this->A("RSSParserDataParserClass");
				$C = new $C();
				
				$result = $C->parse($result);
			}
			
			$this->changeA("RSSParserCache", $result);
			$this->changeA("RSSParserLastUpdate", time());
			$this->saveMe();
		
			return;
		}
		
		$data = file_get_contents($this->A("RSSParserURL"));
		if(!$data)
			return;
		
		$this->changeA("RSSParserCache", $data);
		$this->changeA("RSSParserLastUpdate", time());
		$this->saveMe();
	}

	public function cloneMe() {
		echo $this->newMe();
	}
}
?>