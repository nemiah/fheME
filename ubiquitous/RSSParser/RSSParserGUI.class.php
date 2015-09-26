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
class RSSParserGUI extends RSSParser implements iGUIHTML2 {
	function getHTML($id){
		$gui = new HTMLGUIX($this);
		$gui->name("RSSParser");
	
		$FB = new FileBrowser();
		$FB->addDir(dirname(__FILE__));
		
		$P = array_merge(array("" => "kein Parser"), array_flip($FB->getAsLabeledArray("iRSSParser", ".class.php")));
		
		$FB = new FileBrowser();
		$FB->addDir(dirname(__FILE__));
		
		$PD = array_merge(array("" => "kein Parser"), array_flip($FB->getAsLabeledArray("iRSSDataParser", ".class.php")));
		
		$fields = array(
			"RSSParserName",
			"RSSParserURL",
			"RSSParserPOST",
			"RSSParserDataParserClass",
			"RSSParserUseCache",
			"RSSParserLastUpdate",
			"RSSParserCache"
		);
		
		if(Applications::activeApplication() == "fheME"){
			$fields[] = "RSSParserParserClass";
			$fields[] = "RSSParserOnCall";
			$fields[] = "RSSParserCount";
		}
		
		$gui->attributes($fields);
		
		$gui->type("RSSParserParserClass", "select", $P);
		$gui->type("RSSParserDataParserClass", "select", $PD);
		$gui->type("RSSParserUseCache", "checkbox");
		$gui->type("RSSParserPOST", "textarea");
		
		$gui->label("RSSParserParserClass", "Parser");
		$gui->label("RSSParserOnCall", "Button");
		$gui->label("RSSParserCount", "Anzahl");
		$gui->label("RSSParserDataParserClass", "Data-Parser");
		
		$gui->descriptionField("RSSParserUseCache", "Der Cache funktioniert nur zusammen mit dem cronjob");
		$gui->descriptionField("RSSParserPOST", "Format: Jeweils ein Name:Wert pro Zeile<br>Variablen: \$timestampToday");
		
		$gui->parser("RSSParserLastUpdate", "parserLastUpdate");
		$gui->parser("RSSParserCache", "parserCache");
		
		$gui->descriptionField("RSSParserParserClass", "Der Parser kann die Anzeige des Feeds anpassen");
		$gui->descriptionField("RSSParserDataParserClass", "Der Parser kann die Daten des Feeds anpassen");
		$gui->descriptionField("RSSParserOnCall", "Der Feed wird mit einem eigenen Button geöffnet");
		$gui->descriptionField("RSSParserCount", "Die maximale Anzahl der angezeigten Einträge. 0 zeigt alle");
		
		#$gui->type("RSSParserLastUpdate", "hidden");
		$gui->type("RSSParserOnCall", "checkbox");
		
		$B = $gui->addSideButton("Update", "down");
		$B->rmePCR("RSSParser", $this->getID(), "download","", OnEvent::reload("Left"));
		
		return $gui->getEditHTML();
	}
	
	public function showCache(){
		echo "<pre style=\"font-size:10px;padding:5px;max-height:400px;overflow:auto;\">".htmlentities($this->A("RSSParserCache"))."</pre>";
	}
	
	public static function parserCache($w, $l, $E){
		$B = new Button("Cache anzeigen", "./images/i2/details.png", "icon");
		$B->style("float:right;");
		$B->popup("", "Cache anzeigen", "RSSParser", $E->getID(), "showCache", "", "", "{width:600}");
		
		return $B.Util::formatByte(strlen($w));
	}
	
	public static function parserLastUpdate($w){
		return Util::CLDateTimeParser($w);
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
	
	public function ACLabel(){
		if($this->getID() == "0")
			return "";
		
		return $this->A("RSSParserName");
	}
}
?>