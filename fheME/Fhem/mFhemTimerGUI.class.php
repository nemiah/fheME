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
class mFhemTimerGUI extends UnpersistentClass implements iGUIHTML2 {
	public function getHTML($id){
		$bps = $this->getMyBPSData();
		
		$t = new HTMLTable(1);
		$t->setTableStyle("width:160px;float:right;margin-right:10px;");
		
		if(!isset($bps["ID"])){
			$F = new mFhemGUI();
			$F->addAssocV3("FhemType","!=","FHZ");
			while($f = $F->getNextEntry()){
				$B = new Button($f->getA()->FhemName,"./fheME/Fhem/fhem.png");
				$B->onclick("contentManager.loadFrame('contentRight','mFhemTimer',-1,0,'mFhemTimerGUI;ID:".$f->getID().";type:D;name:".$f->getA()->FhemName."');");
				
				$t->addRow($B);
				$t->addRowClass("backgroundColor0");
			}
			
			$t->addRow("");
			$t->addRowClass("backgroundColor1");
				
			$F = new anyC();
			$F->setCollectionOf("FhemPreset");
			$F->addAssocV3("FhemPresetHide","=","0");
			while($f = $F->getNextEntry()){
				$B = new Button($f->getA()->FhemPresetName,"./fheME/Fhem/events.png");
				$B->onclick("contentManager.loadFrame('contentRight','mFhemTimer',-1,0,'mFhemTimerGUI;ID:".$f->getID().";type:P;name:".$f->getA()->FhemPresetName."');");
				
				$t->addRow($B);
				$t->addRowClass("backgroundColor0");
			}
			
			return $t;
		}
		
		if(isset($bps["ID"])){
			
			if($bps["type"] == "D"){

				$F = new Fhem($bps["ID"]);
				$F->loadMe();
				$FF = new Fhem("timer");
				$FF->setA($F->getA());
			
				$C = new FhemControlGUI();
				$control = $C->getControl($FF);
			} else $control = "";
			$rand = rand(10,10000000);
			
			$B = new Button("set timer","okCatch");
			$B->rme("FhemControl",'','setTimer',array($bps["ID"], "'setBPSValue'", "'$bps[type]'", "parent.clock4Timer$rand.stunden", "parent.clock4Timer$rand.minuten", "'$bps[name]'"),"contentManager.loadFrame(\'contentRight\',\'mFhem\',-1,0,\'\');");
			$B->style("float:right;");
			
			$t->addRow("<iframe name=\"clock4Timer$rand\" style=\"width:240px;height:330px;border:0px;\" src=\"./libraries/ClockGUI.class.php\"></iframe>");
			$t->addRowClass("backgroundColor0");
			
			$t->addRow($B);
			$t->addRowClass("backgroundColor0");
			return $control.$t;
		}
		
	}
}
?>