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
class FhemPresetGUI extends FhemPreset implements iGUIHTML2 {
	function getHTML($id){
		$this->loadMeOrEmpty();
		
		$gui = new HTMLGUI();
		$gui->setObject($this);
		$gui->setName("Preset");
		
		
		$gui->selectWithCollection("FhemPresetServerID",new mFhemServerGUI(),"FhemServerName");
		$gui->setLabel("FhemPresetServerID","Server");
		$gui->setLabel("FhemPresetName","Name");
		$gui->setType("FhemPresetHide","checkbox");
		$gui->setLabel("FhemPresetHide","Hide?");
		$gui->setType("FhemPresetNightOnly","checkbox");
		$gui->setLabel("FhemPresetNightOnly","Night only?");
		$gui->setLabel("FhemPresetLocationID","Location");
		$gui->setLabel("FhemPresetRunOn","run on");

		$gui->settype("FhemPresetLocationID", "select");
		$gui->selectWithCollection("FhemPresetLocationID", new mFhemLocationGUI(), "FhemLocationName");
		
		$gui->setFieldDescription("FhemPresetRunOn", "The event that triggers this Preset. For example HomeStatus:here will trigger this Preset when the dummy 'HomeStatus' is set to 'here'. The Preset will create its own dummy if this field is empty.");
		
		$gui->setStandardSaveButton($this, "mFhemPreset");
		#$gui->setIsDisplayMode(true);
		#$gui->setShowAttributes(array("name"));
		
		$gui->setShowAttributes(array("FhemPresetServerID", "FhemPresetLocationID", "FhemPresetName", "FhemPresetRunOn", "FhemPresetHide", "FhemPresetNightOnly"));
		
		$desc = new HTMLTable(1);
		$desc->addRow("For the \"Night only\"-option to work correctly you might want to set <code>{sunrise_coord(\"10.873799\",\"48.699495\",\"Europe/Berlin\")}</code> in the <code>fhem.cfg</code>-file. With your own coordinates/timezone of course.");
		
		if($id == -1) return $gui->getEditHTML().$desc;
		
		$_SESSION["BPS"]->registerClass("mFhemSelection");
		$_SESSION["BPS"]->setACProperty("selectionServerID", $this->A->FhemPresetServerID);
		
		$B = new Button("add Device","./fheME/Fhem/fhem.png");
		$B->select("false","mFhem", "FhemPreset",$this->ID, "addDevice");
		
		$BW = new Button("add custom","backup");
		$BW->rmePCR("FhemPreset", $this->ID, "addWait", "", "contentManager.reloadFrame('contentLeft');");
		$BW->style("float:right;");

		$t = new HTMLTable(1);
		$t->addRow($BW.$B);
		
		$_SESSION["CurrentAppPlugins"]->addClass("Presets", "mFhemPreset");
		
		$mFE = new mFhemEventGUI();
		$mFE->addJoinV3("Fhem","FhemEventFhemID","=","FhemID");
		$mFE->addAssocV3("FhemEventPresetID","=",$this->ID);
		
		
		return $gui->getEditHTML().$desc."<div style=\"height:30px;\"></div>".$t.$mFE->getHTML(-1)."<div style=\"height:30px;\"></div>";
	}
	
	function addDevice($id){
		$Fhem = new Fhem($id);
		$Fhem->loadMe();
		
		/*$test1 = new anyC();
		$test1->setCollectionOf("FhemEvent");
		$test1->addJoinV3("Fhem","FhemEventFhemID","=","FhemID");
		$test1->addAssocV3("FhemEventPresetID","=",$this->ID);
		$test1->addAssocV3("FhemServerID","!=",$Fhem->getA()->FhemServerID);
		
		if($test1->getNextEntry() != null) die("error:'You may only use devices connected to the same server!'");*/
		
		/*$test2 = new anyC();
		$test2->setCollectionOf("FhemEvent");
		$test2->addAssocV3("FhemEventPresetID","=",$this->ID);
		$test2->addAssocV3("FhemEventFhemID","=",$id);
		
		if($test2->getNextEntry() != null) die("error:'Device already added!'");*/
		
		$FE = new FhemEvent(-1);
		$FEA = $FE->newAttributes();
		
		$FEA->FhemEventFhemID = $id;
		$FEA->FhemEventPresetID = $this->ID;
		$FE->setA($FEA);
		
		$FE->newMe(true, true);
	}

	function addWait(){
		$FE = new FhemEvent(-1);
		$FEA = $FE->newAttributes();

		$FEA->FhemEventFhemID = -1;
		$FEA->FhemEventPresetID = $this->ID;
		$FE->setA($FEA);

		$FE->newMe(true, true);
	}
}
?>