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
				fitOverview();
			});
			
			fitOverview();
		</script>";
		
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

	public function addPlugin($col, $name, $reloadTime, $onReloadFunction = "null"){
		$this->Columns[] = $col;
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
	
	public function manage($deviceID){
		echo "<style type=\"text/css\">
				.dropPlaceholder {
					border:1px dashed green;
					padding:3px;
					height: 1.5em;
					margin-left:5px;
					margin-right:5px;
				} 
				</style>";
		
		$L = new HTMLList();
		$L->addListStyle("list-style-type:none;min-height:50px;");
		$L->addListClass("OverviewPlugins");
		$L->sortable("", "", ".OverviewCol1, .OverviewCol2, .OverviewCol3, .OverviewCol4", "dropPlaceholder", "");
		while($callback = Registry::callNext("Overview")){
			$L->addItem($callback[1]);
			$L->addItemStyle("padding:3px;min-height: 1.5em;cursor:move;");
			$L->setItemID("P_$callback[0]");
		}
		
		echo "<div style=\"display:inline-block;width:200px;\"><p>Plugins</p>$L</div>";
		
		$Lists = array();
		for($i = 1; $i < 5; $i++){
			$List = new HTMLList();
			$List->addListClass("OverviewCol$i");
			$List->addListStyle("list-style-type:none;min-height:50px;");
			#$List->addItem("TEST");
			$List->addItemStyle("padding:3px;min-height: 1.5em;cursor:move;");
			
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
			$List->sortable("", "mfheOverviewGUI::saveCols", $group, "dropPlaceholder", "", array($deviceID, $k+1));
			
			echo "<div style=\"vertical-align:top;min-height:400px;display:inline-block;width:149px;border-left-style:solid;border-left-width:1px;\" class=\"borderColor1\"><p>Spalte ".($k+1)."</p>$List</div>";
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
