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

class mRSSParserGUI extends anyC implements iGUIHTMLMP2 {

	public function getHTML($id, $page){
		$this->loadMultiPageMode($id, $page, 0);

		$gui = new HTMLGUIX($this);
		$gui->version("mRSSParser");
		$gui->screenHeight();

		$gui->name("RSSParser");
		
		$gui->attributes(array("RSSParserName"));
		
		return $gui->getBrowserHTML($id);
	}

	public function getOverviewContent($echo = true){
		$html = "";
		$i = 0;
		
		$header = false;
		$AC = anyC::get("RSSParser", "RSSParserOnCall", "1");
		while($RSS = $AC->getNextEntry()){
			if(!$header){
				$html .= "<div class=\"touchHeader\"><p>RSS</p></div><div style=\"padding:10px;\">";
				$header = true;
			}
			
			$B = new Button($RSS->A("RSSParserName"), "rss", "touch");
			$B->popup("", $RSS->A("RSSParserName"), "RSSParser", $RSS->getID(), "showFeed");
			$html .= $B;
		}
		
		if($header)
			$html .= "</div>";
		
		$this->addAssocV3("RSSParserOnCall", "=", "0");
		while($RSS = $this->getNextEntry()){
			$html .= "<div class=\"touchHeader\">
				<span class=\"lastUpdate\" id=\"lastUpdatemRSSParserGUI\"></span>
				<p>".$RSS->A("RSSParserName")."</p></div>
					<div id=\"RSSParserItemText\" class=\"backgroundColor4\" style=\"padding:10px;display:none;font-size:10px;\" onclick=\"\$j(this).hide(); \$j('#RSSParserItemList').show();\">asd</div>
					<div id=\"RSSParserItemList\" style=\"padding:10px;\">";
			
			$list = new HTMLList();
			$list->addListStyle("list-style-type:none;");
			$E = $RSS->parseFeed();
			
			foreach($E AS $item){
				if($RSS->A("RSSParserCount") > 0 AND $RSS->A("RSSParserCount") <= $i)
					break;
			
				#$B = new Button("", "empty", "icon");
				#
				
				$B = "";
				if($item->icon != null){
					$B = $item->icon;
					$B->style($B->getStyle()."float:left;margin-right:10px;margin-top:-5px;margin-bottom:10px;");
				}
				
				$list->addItem($B."<div id=\"RSSParserItem$i\" style=\"display:none;\">".$item->description."</div>".($item->description != "" ? "<a href=\"#\" onclick=\"\$j('#RSSParserItemList').hide(); \$j('#RSSParserItemText').html(\$j('#RSSParserItem$i').html()).show();\" >" : "").$item->title.($item->description != "" ? "</a>" : ""));
				$list->addItemStyle("clear:both;display:block;margin-left:0px;");
				
				$i++;
			}
			
			$html .= $list."</div>";
		}
		
		if($echo)
			echo $html;
		
		return $html;
	}
	
	public static function getOverviewPlugin(){
		$P = new overviewPlugin("mRSSParserGUI", "RSS", 249);
		$P->updateInterval(3600);
		
		return $P;
	}

	
	public function getACData($attributeName, $query){
		$this->setSearchStringV3($query);
		$this->setSearchFieldsV3(array("RSSParserName"));
		
		$this->setFieldsV3(array("RSSParserName AS label", "RSSParserID AS value"));
		
		$this->setLimitV3("10");
		
		Aspect::joinPoint("query", $this, __METHOD__, $this);
		
		echo $this->asJSON();
	}
}
?>