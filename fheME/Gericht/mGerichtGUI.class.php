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
			<div style=\"padding:10px;overflow:auto;\">";

		
		$B = new Button("Liste anzeigen", "compass", "touch");
		$B->popup("", "Essen", "mGericht", "-1", "showCurrentList", "", "", "{top:20, width:800, hPosition:'center', blackout:true}");
		
		$BF = new Button("Gefrierschrank", "hash", "touch");
		$BF->popup("", "Gefrierschrank", "mGericht", "-1", "showCurrentFrozenList", "", "", "{top:20, width:1000, hPosition:'center', blackout:true}");
		
		
		$html .= "$B$BF</div>";
		echo $html;
	}
	
	public function reAddItem($GerichtID){
		$E = new Gericht($GerichtID);
		
		$E->changeA("GerichtAdded", time());
		$E->saveMe();
		
		echo $this->getListTable();
		#echo $this->getListTable();
	}
	
	public function reAddFrozenItem($GefrierschrankID){
		$E = new Gefrierschrank($GefrierschrankID);
		
		$E->changeA("GefrierschrankAdded", "1");
		$E->saveMe();
		
		echo $this->getFrozenListTable();
		#echo $this->getListTable();
	}
	
	
	public function reMoveFrozenItem($GefrierschrankID){
		$E = new Gefrierschrank($GefrierschrankID);
		
		$E->changeA("GefrierschrankAdded", "0");
		$E->saveMe();
	}
	
	public function reMoveItem($GerichtID){
		$E = new Gericht($GerichtID);
		
		$E->changeA("GerichtAdded", "0");
		$E->saveMe();
	}
	
	public function addFrozenItem($name){
		#if(preg_match("/[0-9]+/", $name)){
		#	$this->addEAN($name, false);
		#} elseif(trim($name) != ""){
			$F = new Factory("Gefrierschrank");
			$F->sA("GefrierschrankName", $name);
			$F->sA("GefrierschrankAdded", "1");
			$F->store();
		#}
		
		echo $this->getFrozenListTable();
	}
	
	/*public function showCurrentFrozenList(){
		
		$B = new Button("Fenster\nschließen", "stop");
		$B->onclick(OnEvent::closePopup("mGericht"));
		$B->style("float:right;margin:10px;");
		
		
		$I = new HTMLInput("GefrierschrankNewEntry", "textarea", "");
		$I->placeholder("Neuer Eintrag");
		$I->style("width:390px;padding:5px;margin-left:5px;font-size:20px;float:left;font-family:monospace;max-width:390px;resize:none;height:35px;max-height:35px;");
		$I->onEnter(OnEvent::rme($this, "addFrozenItem", array("this.value"), "function(transport){ \$j('#currentList').html(transport.responseText); }")." \$j(this).val('');");
		
		
		echo "
		<div style=\"width:400px;float:right;\">
			$B
			$I
			<div style=\"clear:both;\"></div>
			<div id=\"currentList\">".$this->getFrozenListTable()."</div>
		</div>
		<div style=\"width:385px;\" id=\"reAddList\" style=\"overflow-y:auto;overflow-x:hidden;\">
			".$this->getFrozenListReAddTable()."
		</div>
		<div style=\"clear:both;\"></div>
			";
	}*/
	
	public function showCurrentFrozenList(){
		
		$I = new HTMLInput("GefrierschrankNewEntry", "text", "");
		$I->placeholder("Neuer Eintrag");
		$I->style("width:390px;padding:5px;margin-left:5px;font-size:20px;float:left;font-family:monospace;max-width:390px;resize:none;");
		$I->onEnter(OnEvent::rme($this, "addFrozenItem", array("this.value"), "function(transport){ \$j('#currentList').html(transport.responseText); }")." \$j(this).val('');");
		
		
		
		$B = new Button("Liste schließen", "stop");
		$B->onclick(OnEvent::closePopup("mGericht"));
		$B->style("float:right;margin:10px;");
		
		#<div id=\"EinkaufslisteNewEntryAC\" style=\"width:390px;height:35px;padding:5px;font-size:20px;margin-top:3px;font-family:monospace;color:grey;float:left;\"></div>
		echo "
		<div style=\"width:600px;display:inline-block;vertical-align:top;\" id=\"reAddList\">
			".$this->getFrozenListReAddTable()."
		</div><div style=\"width:400px;display:inline-block;vertical-align:top;\">
			<div id=\"headerList\">
			$B
			$I<div style=\"clear:both;\"></div></div>
			
			<div id=\"currentList\">".$this->getFrozenListTable()."</div>
		</div>
			".OnEvent::script("\$j('#editDetailsContentmEinkaufszettel').css('overflow', ''); setTimeout(function(){ \$j('input[name=EinkaufslisteNewEntry]').focus(); }, 200);");
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
		<div style=\"width:385px;\" id=\"reAddList\" style=\"overflow-y:auto;overflow-x:hidden;\">
			".$this->getListReAddTable()."
		</div>
		<div style=\"clear:both;\"></div>
			";
	}
	
	private function getFrozenListReAddTable(){
		$AC = anyC::get("Gefrierschrank");
		$AC->addAssocV3("GefrierschrankAdded", "=", "0");
		$AC->addOrderV3("GefrierschrankName");
		
		$L = new HTMLList();
		$L->noDots();
		$L->addListStyle("padding-top:10px;padding-left:0px;");
		
		while($B = $AC->getNextEntry()){
			$L->addItem($B->A("GefrierschrankName"));
			$L->addItemStyle("padding:10px;margin-bottom:10px;background-color:#eee;display:inline-block;margin-left:5px;height:24px;white-space:nowrap;font-size:20px;cursor:pointer;user-select: none;");
			$L->addItemData("maxid", $B->getID());
			$L->setItemID("Gefriere".$B->getID());
			$L->addItemEvent("onclick", OnEvent::rme($this, "reAddFrozenItem", array("\$j(this).data('maxid')"), "function(transport){ \$j('#Gefriere".$B->getID()."').remove(); \$j('#currentList').html(transport.responseText); }"));
		}
		
		return $L.OnEvent::script("\$j('#reAddList').css('height', contentManager.maxHeight()).css('overflow', 'auto');");
		
	}
	/*
	private function getFrozenListReAddTable(){
		$AC = anyC::get("Gefrierschrank");
		$AC->addAssocV3("GefrierschrankAdded", "=", "0");
		$AC->addOrderV3("GefrierschrankName");
		
		$L = new HTMLList();
		$L->noDots();
		$L->addListStyle("padding-top:10px;width:370px;padding-left:0px;");
		
		while($B = $AC->getNextEntry()){
			$L->addItem($B->A("GefrierschrankName"));
			$L->addItemStyle("margin-left:5px;height:24px;white-space:nowrap;font-size:20px;padding-left:10px;padding-top:10px;padding-bottom:10px;margin-top:0px;cursor:move;-webkit-touch-callout: none;-webkit-user-select: none;-khtml-user-select: none;-moz-user-select: none;-ms-user-select: none;user-select: none;");
			$L->addItemClass("swipe");
			$L->addItemData("itemid", $B->getID());
		}
		
		return $L.$this->js("reAddFrozenItem");
	}*/
	
	private function getListReAddTable(){
		$AC = anyC::get("Gericht");
		$AC->addAssocV3("GerichtAdded", "=", "0");
		$AC->addOrderV3("GerichtName");
		
		$L = new HTMLList();
		$L->noDots();
		$L->addListStyle("padding-top:10px;width:370px;padding-left:0px;");
		
		while($B = $AC->getNextEntry()){
			$L->addItem($B->A("GerichtName"));
			$L->addItemStyle("margin-left:5px;height:24px;white-space:nowrap;font-size:20px;padding-left:10px;padding-top:10px;padding-bottom:10px;margin-top:0px;cursor:move;-webkit-touch-callout: none;-webkit-user-select: none;-khtml-user-select: none;-moz-user-select: none;-ms-user-select: none;user-select: none;");
			$L->addItemClass("swipe");
			$L->addItemData("itemid", $B->getID());
		}
		
		return $L.$this->js("reAddItem");
	}
	
	private function js($method){
		return OnEvent::script("
			\$j('#reAddList ul').parent().css('height', contentManager.maxHeight()).css('overflow', 'auto');
			\$j('.swipe').hammer().on('touch release dragright', function(event){
				if(event.type == 'touch'){
					scrollStartedAt = \$j('#reAddList').scrollTop();
					
					\$j(this).addClass('highlight');
					return;
				}
				
				if(event.type == 'release'){
					event.gesture.preventDefault();
					
					if(event.gesture.deltaX > 150)
						".OnEvent::rme($this, $method, array("\$j(this).data('itemid')"), "function(transport){ \$j('#currentList').html(transport.responseText); }")."
					
					\$j(this).removeClass('confirm');
					\$j(this).removeClass('highlight');
					\$j(this).animate({'margin-left': 5});
					return;
				}
				
				if(event.type == 'dragright'){
					event.gesture.preventDefault();
					var margin = event.gesture.deltaX;

					if(margin >= 150)
						\$j(this).addClass('confirm');
						
					if(margin < 150)
						\$j(this).removeClass('confirm');

					if(margin > 250)
						margin = 250;
						
					\$j(this).css('margin-left', margin);
				}
			});");
	}

	

	public function getFrozenListTable(){
		$T = new HTMLTable(2);
		$T->maxHeight(400);
		$T->setColWidth(2, 30);
		$T->weight("light");
		$T->useForSelection(false);
		
		$AC = anyC::get("Gefrierschrank");
		$AC->addAssocV3("GefrierschrankAdded", ">", "0");
		
		while($E = $AC->getNextEntry()){
			$BT = new Button("Löschen", "trash_stroke", "iconicL");
			#$BT->onclick();
			
			$T->addRow(array($E->A("GefrierschrankName"), $BT));
			$T->addRowStyle("font-size:20px;");
			$T->addRowEvent("click", OnEvent::rme($this, "reMoveFrozenItem", $E->getID(), OnEvent::reloadPopup("mGericht")));
			#$T->addCellEvent(1, "click", OnEvent::rme($this, "boughtItem", $E->getID(), "function(transport){ \$j('#currentList').html(transport.responseText); }"));
			
		}
		
		if($AC->numLoaded() == 0){
			$T->addRow(array("Die Liste enthält keine Einträge."));
			$T->addRowColspan(1, 2);
		}
		
		return $T.OnEvent::script("\$j('#currentList div div').css('max-height', contentManager.maxHeight() - \$j('#headerList').outerHeight());");
	}
	/*
	public function getFrozenListTable(){
		$T = new HTMLTable(2, "Gefrierschrank");
		$T->maxHeight(480);
		$T->setColWidth(2, 30);
		$T->weight("light");
		$T->useForSelection(false);
		
		$AC = anyC::get("Gefrierschrank");
		$AC->addAssocV3("GefrierschrankAdded", ">", "0");
		
		while($E = $AC->getNextEntry()){
			$BT = new Button("Löschen", "trash_stroke", "iconicL");
			$BT->onclick(OnEvent::rme($this, "reMoveFrozenItem", $E->getID(), OnEvent::reloadPopup("mGericht")));
			
			$T->addRow(array($E->A("GefrierschrankName"), $BT));
			$T->addRowStyle("font-size:20px;");
			#$T->addCellEvent(1, "click", OnEvent::rme($this, "boughtItem", $E->getID(), "function(transport){ \$j('#currentList').html(transport.responseText); }"));
			
		}
		
		if($AC->numLoaded() == 0){
			$T->addRow (array("Die Liste enthält keine Einträge."));
			$T->addRowColspan(1, 2);
		}
		
		return $T;
	}*/
	
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
