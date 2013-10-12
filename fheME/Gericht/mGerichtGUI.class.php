<?php
/*
 *  This file is part of wasGibtsMorgen.

 *  wasGibtsMorgen is free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 3 of the License, or
 *  (at your option) any later version.

 *  wasGibtsMorgen is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.

 *  You should have received a copy of the GNU General Public License
 *  along with this program.  If not, see <http://www.gnu.org/licenses/>.
 * 
 *  2007 - 2012, Rainer Furtmeier - Rainer@Furtmeier.de
 */
class mGerichtGUI extends anyC implements iGUIHTMLMP2 {
	public function getHTML($id, $page){
		
		$this->addOrderV3("GerichtName");
		$this->loadMultiPageMode($id, $page, 20);
		$gui = new HTMLGUIX($this);
		$gui->name("Gericht");
		$gui->screenHeight();
		$gui->attributes(array("GerichtName"));
		
		try {
			return $gui->getBrowserHTML($id);
		} catch (Exception $e){ }
	}
	
	public function getOverviewContent(){
		$html = "<div class=\"touchHeader\"><span class=\"lastUpdate\" id=\"lastUpdatemGerichtGUI\"></span><p>Essen</p></div>
			<div style=\"padding:10px;height:100px;overflow:auto;\">";

		
		/*$BU = new Button("", "./fheME/Gericht/update.png", "icon");
		$BU->style("float:right;");
		$BU->onclick("fheOverview.loadContent('mGerichtGUI::getOverviewContent');");
		
		$AC = anyC::get("Gericht");
		$AC->addOrderV3("RAND()");
		$AC->setLimitV3("1");
		
		$G = $AC->getNextEntry();
		
		if($G != null){

			$B = new Button("", "./fheME/Gericht/Gericht.png", "icon");
			$B->style("float:left;margin-right:10px;margin-bottom:20px;");
			$B->popup("", "Rezept", "Gericht", $G->getID(), "showDetailsPopup");

			$html .= $BU.$B."<b>".$G->A("GerichtName")."</b>";

			if($G->A("GerichtRezeptBuch") != ""){
				$html .= "<br /><small style=\"color:grey;\">Buch: ".$G->A("GerichtRezeptBuch")."<br />";
				$html .= "Seite: ".$G->A("GerichtRezeptBuchSeite")."</small>";
			}
		}*/
		
		$B = new Button("Liste anzeigen", "compass", "touch");
		$B->popup("", "Essen", "mGericht", "-1", "showCurrentList", "", "", "{top:20, width:800, hPosition:'center', blackout:true}");
		
		$html .= "$B</div>";
		echo $html;
	}
	
	public function reAddItem($GerichtID){
		$E = new Gericht($GerichtID);
		
		$E->changeA("GerichtAdded", time());
		$E->saveMe();
		
		#echo $this->getListTable();
	}
	
	public function reMoveItem($GerichtID){
		$E = new Gericht($GerichtID);
		
		$E->changeA("GerichtAdded", "0");
		$E->saveMe();
		
		#echo $this->getListTable();
	}
	
	public function showCurrentList(){
		
		$B = new Button("Fenster\nschließen", "stop");
		$B->onclick(OnEvent::closePopup("mGericht"));
		$B->style("float:right;margin:10px;");
		
		
		echo "
		<div style=\"width:400px;float:right;\">
			$B
			<div style=\"clear:both;\"></div>
			<div id=\"currentList\">".$this->getListTable()."</div>
		</div>
		<div style=\"width:400px;\" id=\"reAddList\">
			".$this->getListReAddTable()."
		</div>
		<div style=\"clear:both;\"></div>
			";
	}
	
	private function getListReAddTable(){
		$AC = anyC::get("Gericht");
		$AC->addAssocV3("GerichtAdded", "=", "0");
		$AC->addOrderV3("GerichtName");
		
		$L = new HTMLList();
		$L->noDots();
		$L->addListStyle("padding-top:10px;width:380px;");
		
		while($B = $AC->getNextEntry()){
			$L->addItem($B->A("GerichtName"));
			$L->addItemStyle("font-size:20px;padding-top:10px;padding-bottom:10px;margin-top:0px;");
			$L->addItemClass("swipe");
			$L->addItemData("itemid", $B->getID());
		}
		
		return $L.OnEvent::script("
			\$j('#reAddList ul').parent().css('height', contentManager.maxHeight()).css('overflow', 'auto');
			\$j('.swipe').hammer().on('touch release dragright', function(event){
				if(event.type == 'touch'){
					\$j(this).addClass('highlight');
					return;
				}
				
				if(event.type == 'release'){
					if(event.gesture.deltaX > 150)
						".OnEvent::rme($this, "reAddItem", array("\$j(this).data('itemid')"))."
					
					\$j(this).removeClass('highlight');
					\$j(this).animate({'margin-left': 15});
					return;
				}
				
				if(event.type == 'dragright'){
					var margin = event.gesture.deltaX;
					if(margin > 250)
						margin = 250;
						
					\$j(this).css('margin-left', margin);
				}
			});
					
				");
	}
	
	public function getListTable(){
		$T = new HTMLTable(2, "Gerichte");
		$T->maxHeight(480);
		$T->setColWidth(2, 30);
		$T->weight("light");
		$T->useForSelection(false);
		
		$AC = anyC::get("Gericht");
		$AC->addAssocV3("GerichtAdded", ">", "0");
		
		while($E = $AC->getNextEntry()){
			$BT = new Button("Löschen", "trash_stroke", "iconicL");
			$BT->onclick(OnEvent::rme($this, "reMoveItem", $E->getID(), OnEvent::reloadPopup("mGericht")));
			
			$T->addRow(array($E->A("GerichtName"), $BT));
			$T->addRowStyle("font-size:20px;");
			#$T->addCellEvent(1, "click", OnEvent::rme($this, "boughtItem", $E->getID(), "function(transport){ \$j('#currentList').html(transport.responseText); }"));
			
		}
		
		if($AC->numLoaded() == 0){
			$T->addRow (array("Die Liste enthält keine Einträge."));
			$T->addRowColspan(1, 2);
		}
		
		return $T;
	}
	
	public static function getOverviewPlugin(){
		return new overviewPlugin("mGerichtGUI", "Essen", 0);
	}
}
?>
