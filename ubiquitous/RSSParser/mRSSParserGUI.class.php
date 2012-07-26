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
 *  2007 - 2012, Rainer Furtmeier - Rainer@Furtmeier.de
 */

class mRSSParserGUI extends anyC implements iGUIHTMLMP2 {

	public function getHTML($id, $page){
		$this->loadMultiPageMode($id, $page, 0);

		$gui = new HTMLGUIX($this);
		$gui->version("mRSSParser");

		$gui->name("RSSParser");
		
		$gui->attributes(array("RSSParserName"));
		
		return $gui->getBrowserHTML($id);
	}

	public function getOverviewContent($echo = true){
		$html = "";
		$i = 0;
		$c = 0;
		while($RSS = $this->getNextEntry()){
			$html .= "<div class=\"Tab backgroundColor1\">
				".($c == 0 ? "<span class=\"lastUpdate\" id=\"lastUpdatemRSSParserGUI\"></span>" : "")."
				<p>".$RSS->A("RSSParserName")."</p>
				</div>
				<div style=\"padding:10px;\">";
			
			$list = new HTMLList();
			$list->addListStyle("list-style-type:none;");
			$E = $RSS->parseFeed();
			
			foreach($E AS $item){
				$B = new Button("", "empty", "icon");
				$B->style("float:left;margin-right:10px;margin-top:-5px;");
				
				if($item->icon != null)
					$B->image($item->icon);
				else
					$B = "";
				
				$list->addItem($B."<div id=\"RSSParserItem$i\" style=\"margin-top:33px;position:absolute;width:200px;display:none;border-width:1px;border-style:solid;padding:5px;border-radius:5px;\" onclick=\"\$j(this).toggle();\" class=\"backgroundColor0 borderColor1 RSSParserItem\"><small>".$item->description."</small></div>".($item->description != "" ? "<a href=\"#\" onclick=\"\$j('.RSSParserItem').hide(); \$j('#RSSParserItem$i').toggle();\" >" : "").$item->title.($item->description != "" ? "</a>" : ""));
				$list->addItemStyle("clear:both;height:40px;display:block;margin-left:0px;");
				
				$i++;
			}
			$c++;
			$html .= $list."</div>";
		}
		
		if($echo)
			echo $html;
		
		return $html;
	}

}
?>