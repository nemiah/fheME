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
class RSSParserGUI extends RSSParser implements iGUIHTML2 {
	function getHTML($id){
		$gui = new HTMLGUIX($this);
		$gui->name("RSSParser");
	
		$FB = new FileBrowser();
		$FB->addDir(dirname(__FILE__));
		
		$P = array_merge(array("" => "kein Parser"), array_flip($FB->getAsLabeledArray("iRSSParser", ".class.php")));
		
		$gui->type("RSSParserParserClass", "select", $P);
		
		$gui->label("RSSParserParserClass", "Parser");
		$gui->label("RSSParserOnCall", "Button");
		$gui->label("RSSParserCount", "Anzahl");
		
		$gui->descriptionField("RSSParserParserClass", "Der Parser kann die Anzeige des Feeds anpassen");
		$gui->descriptionField("RSSParserOnCall", "Der Feed wird mit einem eigenen Button geöffnet");
		$gui->descriptionField("RSSParserCount", "Die maximale Anzahl der angezeigten Einträge. 0 zeigt alle");
		
		$gui->type("RSSParserLastUpdate", "hidden");
		$gui->type("RSSParserOnCall", "checkbox");
		
		return $gui->getEditHTML();
	}
	
	public function showFeed(){
		$list = new HTMLList();
		$list->addListStyle("list-style-type:none;padding:5px;max-height:400px;overflow:auto;");
		$E = $this->parseFeed();
		$i = 0;
		
		foreach($E AS $item){
			if($this->A("RSSParserCount") > 0 AND $this->A("RSSParserCount") <= $i)
				break;
			
			$B = new Button("", "empty", "icon");
			$B->style("float:left;margin-right:10px;margin-top:-5px;");

			if($item->icon != null)
				$B->image($item->icon);
			else
				$B = "";
			
			$list->addItem(
				$B.
				"<div id=\"RSSParserItemSF$i\" style=\"margin-top:33px;position:absolute;width:400px;display:none;border-width:1px;border-style:solid;padding:5px;border-radius:5px;\" onclick=\"\$j(this).toggle();\" class=\"backgroundColor0 borderColor1 RSSParserItemSF\"><small>".$item->description."</small></div>
				".($item->description != "" ? "<a href=\"#\" onclick=\"\$j('.RSSParserItemSF').hide(); \$j('#RSSParserItemSF$i').toggle();\" >" : "").
				$item->title.($item->description != "" ? "</a>" : "")."<br /><small style=\"color:grey;\">".Util::CLDateTimeParser($item->pubDate)."</small>");
			$list->addItemStyle("clear:both;display:block;margin-left:0px;");

			$i++;
		}
		
		echo $list;
	}
}
?>