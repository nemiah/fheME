<?php
/**
 *  This file is part of fheME.

 *  fheME is free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 3 of the License, or
 *  (at your option) any later version.

 *  fheME is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.

 *  You should have received a copy of the GNU General Public License
 *  along with this program.  If not, see <http://www.gnu.org/licenses/>.
 * 
 *  2007 - 2012, Rainer Furtmeier - Rainer@Furtmeier.de
 */

class mfheOverviewGUI extends anyC implements iGUIHTMLMP2 {
	
	private $Plugins = array();
	private $ReloadTimes = array();
	private $onReload = array();
	
	public function getHTML($id, $page){
		$html = "<style type=\"text/css\">
			.lastUpdate {
				float:right;
				color:grey;
				padding:5px;
				font-weight:normal;
			}
			
			.OverviewCol {
				/*border-left-width:1px;
				border-left-style:solid;*/
				height:505px;
				overflow:hidden;
				/*margin-left:10px;*/
				padding-top:10px;
				padding-left:5px;
				padding-right:5px;
			}

			#fheOverviewClock {
				font-size:35px;
				text-align:right;
			}
			
			#fheOverviewClock span {
				float:left;
				font-size:16px;
				text-align:left;
			}
			
			.touchHeader {
				color:#777;
				font-family:Roboto;
				font-size:20px;
				padding-left:5px;
				padding-right:5px;
			}
			
			.touchHeader p {
				padding:5px;
			}
			
			.touchHeader .lastUpdate {
				font-size:12px;
			}
			
			.desktopMove {
				border:1px dashed grey;
				background-color:white;
				margin-top:-1px;
				margin-left:-1px;
			}

			.desktopAlterIcons {
				display:none;
				float:right;
			}

			.desktopMove .desktopAlterIcons {
				display:block;
			}
		</style>
		<script type=\"text/javascript\">
			function fitOverview(){
				if(!\$j('.OverviewCol').length && !\$j('#OverviewDesktop').length)
					return;

				\$j('.OverviewCol').css('height', contentManager.maxHeight() - 10);
				\$j('#OverviewDesktop').css('height', contentManager.maxHeight());
			}

			\$j('.overviewContentPlaceholder').css('height', contentManager.maxHeight());

			\$j(window).resize(function() {
				if(fheOverview.noresize)
					return;
				fitOverview();
			});
			
			".OnEvent::rme($this, "getOverviewContent", array("\$j.jStorage.get('phynxDeviceID','none')"), "function(transport){ \$j('.overviewContentPlaceholder').replaceWith(transport.responseText); \$j('#fheOverviewWrapper').fadeIn(); fitOverview(); }")."
		</script>
		
		<div class=\"overviewContentPlaceholder\"></div>";
		
		return $html;
	}

	public function getOverviewDesktop($DeviceID){
		$O = anyC::getFirst("fheOverview", "fheOverviewDeviceID", $DeviceID);
		if($O == null){
			$F = new Factory("fheOverview");
			$F->sA("fheOverviewDeviceID", $DeviceID);
			$F->sA("fheOverviewDesktop", "{}");
			$ID = $F->store();
			
			$O = new fheOverview($ID);
		}
		
		$d = json_decode($O->A("fheOverviewDesktop"));
		
		$BM = new Button("Desktop bearbeiten", "pen_alt2", "iconicL");
		$BM->style("position:absolute;right:0px;");
		$BM->popup("", "Desktop bearbeiten", "mfheOverview", "-1", "popupDesktop", array($DeviceID), "", "{hasX: false}");
		echo "
			<div id=\"onfheOverviewPage\"></div>
			<div id=\"OverviewDesktop\" style=\"\">
				$BM
			";
		
		
		$Device = new Device($DeviceID);
		
		$data = json_decode($O->A("fheOverviewDesktop"));
		foreach($data AS $plugin => $options){
			try {
				echo $this->pluginShow($Device, $plugin, false, $options);
			} catch (Exception $e){
				
			}
		}
		
		echo "</div>";
		
		if(isset($d->background))
			echo OnEvent::script ("\$j('#wrapper').css('background-image', 'url(./specifics/$d->background)');");
		
		echo OnEvent::script("\$j('html').css('overflow-y', 'auto');");
		
		$this->pluginUpdate();
	}
	
	public function getOverviewContent($DeviceID){
		if($DeviceID == "none")
			die("<p style=\"padding:5px;\">Bitte registrieren Sie diesen Browser im <a href=\"#\" onclick=\"contentManager.loadPlugin('contentRight', 'mDevice', 'mDeviceGUI;-'); return false;\">Geräte-Reiter</a>.</p>");

		$D = new Device($DeviceID);
		if($D->A("DeviceType") != 4)
			$this->getOverviewCols($DeviceID);
		else
			$this->getOverviewDesktop($DeviceID);
	}
	
	public function getOverviewCols($DeviceID){
		$O = anyC::getFirst("fheOverview", "fheOverviewDeviceID", $DeviceID);
		
		if($O == null)
			die("<p style=\"padding:5px;\">Für dieses Gerät wurde keine <a href=\"#\" onclick=\"contentManager.loadPlugin('contentRight', 'mDevice', 'mDeviceGUI;-', $DeviceID); return false;\">Übersicht</a> erstellt!</p>");
		
		$count = 0;
		for($i = 1; $i < 5; $i++)
			if($O->A("fheOverviewCol$i") != "")
				$count++;
		
		$width = 100 / $count;
		
		$html = "";
		for($i = 1; $i < 5; $i++){
			if($O->A("fheOverviewCol$i") == "")
				continue;
			
			$plugins = explode(";", $O->A("fheOverviewCol$i"));
			
			$html .= "<div style=\"width:$width%;display:inline-block;vertical-align:top;\">
				<div class=\"OverviewCol\">";
			
			foreach($plugins AS $k => $P)
				$html .= $this->pluginShow(new Device($DeviceID), substr($P, 1), $k == count($plugins) - 1, false);
			
			
			$html .= "</div>
				</div>";
		}
		
		$html = "<div id=\"onfheOverviewPage\"></div><div id=\"fheOverviewWrapper\" style=\"display:none;\">".$html."</div>";
		
		echo $html;
		
		$this->pluginUpdate();
	}
	
	public function pluginUpdate(){
		echo OnEvent::script("fheOverview.initUpdate(['".  implode("', '", $this->Plugins)."'], [".  implode(", ", $this->ReloadTimes)."], [".  implode(", ", $this->onReload)."]);");
	}
	
	public function pluginShow(Device $Device, $plugin, $isLast = false, $options = null){
		$C = new $plugin();
		
		$E = $C->getOverviewPlugin();

		$styles = "";
		if($options != null)
			$styles = "position:absolute;top:{$options->top}px;left:{$options->left}px;";
		
		
		$html =  "<div id=\"fheOverviewContent".$plugin."_getOverviewContent\" data-plugin=\"$plugin\" class=\"desktopDraggable\" style=\"".(($isLast OR $E->minHeight() === 0) ? "" : ($Device->A("DeviceType") == 4 ? "min-" : "")."height:".$E->minHeight()."px;overflow:hidden;").($Device->A("DeviceType") == 4 ? "width:300px;" : "")."$styles\">";
		
		$BM = new Button("Plugin verschieben", "move", "iconicL");
		$BM->style("cursor:move;");
		$BM->addClass("handleMove");
		
		$BC = new Button("Plugin schließen", "x", "iconicL");
		$BC->addClass("handleClose");
		$BC->doBefore("var currentPlugin = this; %AFTER");
		$BC->rmePCR("mfheOverview", "-1", "pluginDelete", array($Device->getID(), "'$plugin'"), "function(){ \$j(currentPlugin).closest('.desktopDraggable').remove(); \$j('#addPlugin$plugin').show(); }");
		
		$html .= "<div class=\"desktopAlterIcons\">$BM$BC</div>";
		
		ob_start();
		$C->getOverviewContent($Device->getID());
		$html .= ob_get_contents();
		ob_end_clean();
		$html .=  "</div>";

		if($E->updateInterval())
			$this->addPlugin($E->className(), $E->updateInterval(), $E->updateFunction());

		return $html;
	}
	
	public function pluginDelete($DeviceID, $plugin){
		$O = anyC::getFirst("fheOverview", "fheOverviewDeviceID", $DeviceID);
		
		$d = json_decode($O->A("fheOverviewDesktop"));
		
		if(!isset($d->$plugin))
			return;
		
		unset($d->$plugin);
		
		$O->changeA("fheOverviewDesktop", json_encode($d));
		$O->saveMe();
	}
	
	public function pluginLoad($DeviceID, $plugin){
		echo $this->pluginShow(new Device($DeviceID), $plugin, false);
		
		echo OnEvent::script("fheOverview.draggableStart($DeviceID);");
	}
	
	public function pluginSave($DeviceID, $plugin, $top, $left){
		$O = anyC::getFirst("fheOverview", "fheOverviewDeviceID", $DeviceID);
		
		$d = json_decode($O->A("fheOverviewDesktop"));
		
		if(!isset($d->$plugin))
			$d->$plugin = new stdClass();
		
		$d->$plugin->top = $top;
		$d->$plugin->left = $left;
		
		$O->changeA("fheOverviewDesktop", json_encode($d));
		$O->saveMe();
	}
	
	public function addPlugin($name, $reloadTime, $onReloadFunction = "null"){
		if($onReloadFunction === null)
			$onReloadFunction = "null";
			
		#$this->Columns[] = $col;
		$this->Plugins[] = $name.(strpos($name, "::") === false ? "::getOverviewContent" : "");
		$this->ReloadTimes[] = $reloadTime;
		$this->onReload[] = $onReloadFunction;
	}
	
	
	public function checkAdmin(){
		if(Session::isUserAdminS())
			echo "1";
		else
			echo "0";
	}
	
	public function manage($DeviceID){
		echo "<style type=\"text/css\">
				.dropPlaceholder {
					border:1px dashed green;
					padding:3px;
					height: 1.5em;
					margin-left:5px;
					margin-right:5px;
				} 
				</style>";
		
		$O = anyC::getFirst("fheOverview", "fheOverviewDeviceID", $DeviceID);
		$cols = array();
		if($O != null){
			for($i = 1; $i < 5; $i++){
				$cols[$i] = array();
				if($O->A("fheOverviewCol$i") == "")
					continue;
				
				$cols[$i] = explode (";", $O->A("fheOverviewCol$i"));
			}
		}
		$Plugins = array();
		$L = new HTMLList();
		$L->addListStyle("list-style-type:none;min-height:50px;");
		$L->addListClass("OverviewPlugins");
		$L->sortable("", "", ".OverviewCol1, .OverviewCol2, .OverviewCol3, .OverviewCol4", "dropPlaceholder", "");
		while($callback = Registry::callNext("Overview")){
			$Plugins[$callback->className()] = $callback;
			
			$continue = false;
			for($i = 1; $i < 5; $i++){
				if(in_array("P".$callback->className(), $cols[$i]))
					$continue = true;
			}
			
			if($continue)
				continue;
			
			$L->addItem($callback->name());
			$L->addItemStyle("background-color:#ddd;padding:3px;min-height:".$callback->minHeight()."px;cursor:move;");
			$L->setItemID("P_".$callback->className());
		}
		Registry::reset("Overview");
		
		echo "<div style=\"display:inline-block;width:149px;margin-right:50px;\"><p>Plugins</p><div style=\"overflow:auto;height:500px;\">$L</div></div>";
		#print_r($Plugins);
		$Lists = array();
		for($i = 1; $i < 5; $i++){
			$List = new HTMLList();
			$List->addListClass("OverviewCol$i");
			$List->addListStyle("list-style-type:none;min-height:50px;");
			#$List->addItem("TEST");
			$List->addItemStyle("padding:3px;min-height: 1.5em;cursor:move;");
			
			foreach($cols[$i] AS $class){
				$callback = $Plugins[substr($class, 1)];
				#if(!in_array("P".$callback->className(), $cols[$i]))
				#	continue;
				#$List->addItem($class);
				$List->addItem($callback->name());
				$List->addItemStyle("background-color:#ddd;padding:3px;min-height: ".$callback->minHeight()."px;cursor:move;");
				$List->setItemID("P_".$callback->className());
			}
			Registry::reset("Overview");
			
			$Lists[] = $List;
		}
		
		foreach($Lists AS $k => $List){
			$group = ".OverviewPlugins";
			for($i = 0; $i < 4; $i++){
				if($k == $i)
					continue;
				$group .= ", .OverviewCol".($i+1);
			}
			#echo $group."<br />";
			$List->sortable("", "mfheOverviewGUI::saveCols", $group, "dropPlaceholder", "", array($DeviceID, $k+1));
			
			echo "<div style=\"vertical-align:top;display:inline-block;width:149px;border-left-style:solid;border-left-width:1px;\" class=\"borderColor1\"><p>Spalte ".($k+1)."</p><div style=\"overflow:auto;height:500px;\">$List</div></div>";
		}
		
		echo "<div style=\"clear:both;\"></div>";
	}
	
	public function saveCols($data, $DeviceID, $col){
		$O = anyC::getFirst("fheOverview", "fheOverviewDeviceID", $DeviceID);
		if($O == null){
			$F = new Factory("fheOverview");
			$F->sA("fheOverviewDeviceID", $DeviceID);
			$F->store();
			
			$O = anyC::getFirst("fheOverview", "fheOverviewDeviceID", $DeviceID);
		}
		
		$O->changeA("fheOverviewCol$col", $data);
		$O->saveMe(true, true);
	}
	
	public function popupDesktop($DeviceID){
		$O = anyC::getFirst("fheOverview", "fheOverviewDeviceID", $DeviceID);
		$d = json_decode($O->A("fheOverviewDesktop"));
		
		$BD = new Button("Fertig", "bestaetigung");
		$BD->onclick("fheOverview.draggableStop(); ".OnEvent::closePopup("mfheOverview"));
		$BD->style("float:right;margin:10px;");
		
		echo $BD."<div style=\"clear:both;\"></div>";
		
		$T = new HTMLTable(2, "Plugins");
		$T->useForSelection();
		$T->weight("light");
		
		while($callback = Registry::callNext("Overview")){
			$c = $callback->className();
				
			$B = new Button("Plugin hinzufügen", "arrow_left", "iconic");
			
			$T->addRow(array($B, $callback->name()));
			$T->addRowEvent("click", OnEvent::rme("mfheOverview", "pluginLoad", array($DeviceID, "'".$callback->className()."'"), "function(t){  \$j('#addPlugin$c').hide(); \$j('#OverviewDesktop').append(t.responseText); }"));
			$T->setRowID("addPlugin$c");
			
			if(isset($d->$c))
				$T->addRowStyle("display:none;");
			
		}
		
		Registry::reset("Overview");
		
		echo $T;
		echo OnEvent::script("fheOverview.draggableStart($DeviceID);");
		
		$T = new HTMLTable(1, "Hintergrundbild");
		$T->weight("light");
		
		$ID = new Button("Bild löschen", "trash_stroke", "iconicL");
		$ID->style("float:right;");
		$ID->rmePCR("mfheOverview", "-1", "backgroundDelete", $DeviceID, "function(t){\$j('#OverviewDesktop').append(t.responseText); }");
		
		$I = new HTMLInput("upload", "file", null, array("multiple" => false));
		$I->onchange(OnEvent::rme($this, "backgroudUpload", array($DeviceID, "fileName"), "function(t){\$j('#OverviewDesktop').append(t.responseText); }"));
		$T->addRow($ID."<div style=\"margin-right:30px;\">".$I."</div>");
		
		echo $T;
		
	}
	
	function backgroudUpload($DeviceID, $filename){
		$O = anyC::getFirst("fheOverview", "fheOverviewDeviceID", $DeviceID);
		$d = json_decode($O->A("fheOverviewDesktop"));
		
		$d->background = "OverviewBG.".Util::ext($filename);
		
		$C = $_SESSION["TempFiles"]->getCollector();
		$v = $C[0];
		
		file_put_contents(realpath(FileStorage::getFilesDir())."/OverviewBG.".Util::ext($filename), file_get_contents($v->A("filename")));
		unlink($v->A("filename"));
		unset($_SESSION["TempFiles"]);
		
		$O->changeA("fheOverviewDesktop", json_encode($d));
		$O->saveMe();
		
		echo OnEvent::script("\$j('#wrapper').css('background-image', 'url(./specifics/$d->background)');");
	}
	
	function backgroundDelete($DeviceID){
		$O = anyC::getFirst("fheOverview", "fheOverviewDeviceID", $DeviceID);
		$d = json_decode($O->A("fheOverviewDesktop"));
		
		unset($d->background);
		
		$O->changeA("fheOverviewDesktop", json_encode($d));
		$O->saveMe();
		
		echo OnEvent::script ("\$j('#wrapper').css('background-image', '');");
	}
}
?>
