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
				font-size:30px;
				text-align:right;
			}
			
			#fheOverviewClock span {
				float:left;
				font-size:13px;
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
		</style>
		<script type=\"text/javascript\">
			function fitOverview(){
				if(!\$j('.OverviewCol').length)
					return;

				\$j('.OverviewCol').css('height', (contentManager.maxHeight() - 10)+'px');
			}

			\$j(window).resize(function() {
				if(fheOverview.noresize)
					return;
				fitOverview();
			});
			
			".OnEvent::rme($this, "getOverviewContent", array("\$j.jStorage.get('phynxDeviceID','none')"), "function(transport){ \$j('.overviewContentPlaceholder').replaceWith(transport.responseText); fitOverview(); }")."
		</script>
		
		<div class=\"overviewContentPlaceholder\"></div>";
		
		return $html;
		
		$this->addPlugin(1, "mKalenderGUI", 900);
		#$this->addPlugin(2, "mFhemGUI", 120, "function(){".OnEvent::rme(new FhemControlGUI(-1), "updateGUI", "", "function(transport){ fheOverview.updateTime('mFhemGUI'); Fhem.updateControls(transport); }")."}");		
		$this->addPlugin(3, "mfheOverviewGUI::getOverviewContentCol2", 0);
		$this->addPlugin(3, "mfheOverviewGUI::getOverviewContentCol3", 0);
		$this->addPlugin(4, "mfheOverviewGUI::getOverviewContentCol4", 0);
		#$this->addPlugin(4, "mWetterGUI", null, 1800);
		
		
		#$D = array("mKalenderGUI", "mFhemGUI", "mfheOverviewGUI", "mWetterGUI");
		#$L = array(900, 120, 0, 1800);
		#$R = array("null", "function(){".OnEvent::rme(new FhemControlGUI(-1), "updateGUI", "", "function(transport){ fheOverview.updateTime('mFhemGUI'); Fhem.updateControls(transport); }")."}", "null", "null");
		
		foreach($this->Plugins AS $k => $E){
			$method = explode("::", $E);
			$C = $method[0];
			$E = str_replace("::", "_", $E);
			
			$C = new $C(-1);
			
			$html .= "<div style=\"width:".floor(100 / count($this->Plugins))."%;float:left;\">";
			
			if($k == 0){
				$html .= "<div class=\"OverviewCol borderColor1\">
				<div class=\"touchHeader\"><p>Uhr</p></div>
					<div style=\"padding:10px;\" id=\"fheOverviewClock\"></div>
				<div id=\"fheOverviewContent$E\">";
			}
			else
				$html .= "
			<div class=\"OverviewCol borderColor1\" id=\"fheOverviewContent$E\">";
			
			if($k == 0)
				$html .= "</div>";
			
			$html .= "</div>
				</div>";
		}
		
		$this->addPlugin(0, "mFhemGUI", 120, "function(){".OnEvent::rme(new FhemControlGUI(-1), "updateGUI", "", "function(transport){ fheOverview.updateTime('mFhemGUI'); Fhem.updateControls(transport); }")."}");		
		$this->addPlugin(0, "mRSSParserGUI", 3600);
		$this->addPlugin(0, "mWetterGUI", 1800);
		$this->addPlugin(0, "mEinkaufszettelGUI", 300);
		
		#$D[] = "mRSSParserGUI";
		#$L[] = 3600;
		#$R[] = "null";
		#echo "fheOverview.initUpdate(['".  implode("', '", $this->Plugins)."'], [".  implode(", ", $this->ReloadTimes)."], [".  implode(", ", $this->onReload)."]);";
		return "<div id=\"onfheOverviewPage\"></div>".$html.OnEvent::script("fheOverview.initUpdate(['".  implode("', '", $this->Plugins)."'], [".  implode(", ", $this->ReloadTimes)."], [".  implode(", ", $this->onReload)."]);");
	}

	public function getOverviewContent($DeviceID){
		if($DeviceID == "none")
			die("<p>Bitte registrieren Sie diesen Browser im Geräte-Reiter.</p>");
		
		$O = anyC::getFirst("fheOverview", "fheOverviewDeviceID", $DeviceID);
		
		if($O == null)
			die("<p>Für dieses Gerät wurde keine Übersicht erstellt!</p>");
		
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
			
			foreach($plugins AS $k => $P){
				$C = substr($P, 1);
				$C = new $C();
				
				$E = $C->getOverviewPlugin();
				
				$html .=  "<div id=\"fheOverviewContent".substr($P, 1)."_getOverviewContent\" style=\"".(($k == count($plugins) - 1 OR $E->minHeight() === 0) ? "" : "height:".$E->minHeight()."px;overflow:hidden;")."\">";
				ob_start();
				$C->getOverviewContent();
				$html .= ob_get_contents();
				ob_end_clean();
				$html .=  "</div>";
		
				if($E->updateInterval())
					$this->addPlugin($E->className(), $E->updateInterval(), $E->updateFunction());
				
				
				#$html .= $P;
			}
			
			$html .= "</div>
				</div>";
		}
		
		$html = "<div id=\"onfheOverviewPage\"></div>".$html.OnEvent::script("fheOverview.initUpdate(['".  implode("', '", $this->Plugins)."'], [".  implode(", ", $this->ReloadTimes)."], [".  implode(", ", $this->onReload)."]);");
		
		echo $html;
	}
	
	public function addPlugin($name, $reloadTime, $onReloadFunction = "null"){
		if($onReloadFunction === null)
			$onReloadFunction = "null";
			
		#$this->Columns[] = $col;
		$this->Plugins[] = $name.(strpos($name, "::") === false ? "::getOverviewContent" : "");
		$this->ReloadTimes[] = $reloadTime;
		$this->onReload[] = $onReloadFunction;
	}
	
	public function getOverviewContentCol2(){
		$C = new mFhemGUI();
		echo "<div id=\"fheOverviewContentmFhemGUI_getOverviewContent\" style=\"height:360px;\">";
		$C->getOverviewContent();
		echo "</div>";
		
		$C = new mMailCheckGUI();
		echo "<div id=\"fheOverviewContentmMailCheckGUI_getOverviewContent\">";
		$C->getOverviewContent();
		echo "</div>";
	}
	
	public function getOverviewContentCol3(){
		$C = new mRSSParserGUI();
		echo "<div id=\"fheOverviewContentmRSSParserGUI_getOverviewContent\" style=\"height:249px;overflow:hidden;margin-bottom:11px;\">";
		$C->getOverviewContent();
		echo "</div>";
		
		$C = new mGerichtGUI();
		echo "<div id=\"fheOverviewContentmGerichtGUI_getOverviewContent\" style=\"height:100px;\">";
		$C->getOverviewContent();
		echo "</div>";
		
		$C = new mLogitechMediaServerGUI();
		echo "<div id=\"fheOverviewContentmLogitechMediaServerGUI_getOverviewContent\" style=\"height:130px;\">";
		$C->getOverviewContent();
		echo "</div>";
	}
	
	public function getOverviewContentCol4(){
		$C = new mWetterGUI();
		echo "<div id=\"fheOverviewContentmWetterGUI_getOverviewContent\" style=\"height:249px;margin-bottom:11px;\">";
		$C->getOverviewContent();
		echo "</div>";
		
		$C = new mEinkaufszettelGUI();
		echo "<div id=\"fheOverviewContentmEinkaufszettelGUI_getOverviewContent\" style=\"height:145px;overflow:hidden;\">";
		$C->getOverviewContent();
		echo "</div>";
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
}
?>
