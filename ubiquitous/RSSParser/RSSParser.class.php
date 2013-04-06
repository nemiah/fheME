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
 *  2007 - 2013, Rainer Furtmeier - Rainer@Furtmeier.IT
 */
class RSSParser extends PersistentObject {
	public function parseFeed(){
		$xml = new SimpleXMLElement(file_get_contents($this->A("RSSParserURL")));
		
		$E = array();
		
		foreach($xml->channel->item AS $k => $item){
			$I = new stdClass();
			
			$I->title = $item->title."";
			$I->description = $item->description."";
			$I->icon = null;
			
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
}
?>