<?php
/*
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
class mFhemGUI extends anyC implements iGUIHTML2 {
	public function getHTML($id){
		$bps = $this->getMyBPSData();

		$B1 = new Button("Servers","./fheME/Fhem/fhemServers.png");
		$B1->style("float:right;");
		$B1->onclick("contentManager.loadFrame('contentRight','mFhemServer'); contentManager.emptyFrame('contentLeft');");
		
		$B2 = new Button("Devices","./fheME/Fhem/fhem.png");
		$B2->style("float:right;");
		$B2->onclick("contentManager.loadFrame('contentRight','mFhem', -1, 0,'mFhemGUI;showDevices:true'); contentManager.emptyFrame('contentLeft');");
		
//		$B3 = new Button("Rooms","gutschrift");
//		$B3->style("float:right;");
//		$B3->onclick("contentManager.loadFrame('contentRight','mGruppe');");

		$B6 = new Button("Presets","./fheME/Fhem/events.png");
		$B6->style("float:right;");
		$B6->onclick("contentManager.loadFrame('contentRight','mFhemPreset'); contentManager.emptyFrame('contentLeft');");

		$BLoc = new Button("Locations","./fheME/FhemLocation/FhemLocation.png");
		$BLoc->style("float:right;");
		$BLoc->onclick("contentManager.loadFrame('contentRight','mFhemLocation'); contentManager.emptyFrame('contentLeft');");
		if(!Session::isPluginLoaded("mFhemLocation"))
			$BLoc = "";
		
//		$B4 = new Button("Refresh","refresh");
//		$B4->style("float:right;");
//		$B4->onclick("Fhem.refreshControls();");
		
		#$B5 = new Button("Timers","backup");
		#$B5->style("float:right;");
		#$B5->onclick("contentManager.loadFrame('contentRight','mFhemTimer',-1,0,'mFhemTimerGUI;-'); contentManager.emptyFrame('contentLeft');");
		
		$html = "
		<script type=\"text/javascript\">
			contentManager.loadFrame('contentLeft','FhemControl');
		</script>";
		
		if(isset($bps["noLeft"])) $html = "";
		
		$t = new HTMLTable(1);
		$t->setTableStyle("width:160px;float:right;margin-right:10px;");
		#$t->addRow($B5);
		#$t->addRowClass("backgroundColor0");
		#$t->addRow("");
		#$t->addRowClass("backgroundColor1");
//		$t->addRow($B3);
//		$t->addRowClass("backgroundColor0");
		$t->addRow($B2);
		$t->addRowClass("backgroundColor0");
		$t->addRow($B6);
		$t->addRowClass("backgroundColor0");
//		$t->addRow($B4);
//		$t->addRowClass("backgroundColor0");
		$t->addRow($B1);
		$t->addRowClass("backgroundColor0");
		$t->addRow($BLoc);
		$t->addRowClass("backgroundColor0");
		
		if(!isset($bps["showDevices"]) AND !isset($bps["selectionMode"]))
			return $html.$t;
			

		if(isset($bps["selectionMode"])) {
			$this->addAssocV3("FhemServerID","=",$_SESSION["BPS"]->getProperty("mFhemSelection", "selectionServerID"));
			$this->addAssocV3("FhemType","!=","FHZ");
		}
		#----------------------------------------------
		$this->addOrderV3("FhemServerID");
		$this->addOrderV3("FhemType");
		$this->addOrderV3("FhemName");
		$B2 = new Button("register\nsettings","./fheME/Fhem/fhem.png");
		$B2->rme("FhemControl","","registerSettings","","if(checkResponse(transport)) $(\'contentLeft\').update(transport.responseText);");
		
		$B3 = new Button("reset\nServers","empty");
		$B3->rme("FhemControl","","resetServers","","if(checkResponse(transport)) $(\'contentLeft\').update(transport.responseText);");
		$B3->style("float:right;");
		
		$t2 = new HTMLTable(1);
		$t2->addRow($B3.$B2);
			
		$gui = new HTMLGUIX($this);
		$gui->displayGroup("FhemServerID", "mFhemGUI::DGParser");
		
		#$this->lCV3($id);
		
		$gui->attributes(array("FhemName","FhemType"));
		
		$gui->name("Device");
		
		if($bps != -1 AND isset($bps["selectionMode"]))
			$t2 = "";
		
		return ($id == -1 ? $t2 : "").$gui->getBrowserHTML($id);
	}
	
	public static function DGParser($w){
		$S = new FhemServer($w);
		$S->loadMe();
		
		return $S->getA()->FhemServerName;
	}
	
	public function getOverviewContent($echo = true){
		$html = "<div class=\"touchHeader\"><span class=\"lastUpdate\" id=\"lastUpdatemFhemGUI\"></span><p>Fhem</p></div>
			<div style=\"padding:10px;\">
			<div style=\"padding-bottom:15px;\" class=\"borderColor1\">";
		
		$FC = new FhemControlGUI();
		$ac = $FC->getDevicesFHT(true);
		while($t = $ac->getNextEntry())
			$html .= $FC->getFHTControl($t);
		
		$html .= "<div style=\"clear:both;\"></div></div><div style=\"margin-top:10px;\">";
		
		$ac = $FC->getDevices(true);
		while($t = $ac->getNextEntry())
			$html .= $FC->getControl($t);
		
		$html .= "</div></div>".OnEvent::script(OnEvent::rme(new FhemControlGUI(-1), "updateGUI", "", "function(transport){ Fhem.updateControls(transport); }"));
		
		if($echo)
			echo $html;
		
		return $html;
	}
	
	public static function getOverviewPlugin(){
		return array("mFhemGUI", "Fhem");
	}
}
?>