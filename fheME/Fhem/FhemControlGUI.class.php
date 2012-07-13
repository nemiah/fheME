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
class FhemControlGUI implements iGUIHTML2 {

	public function getHTML($id){
		$html = "<div style=\"width:800px;\">
			<div style=\"width:230px;float:left;\">";

		$tab = new HTMLTable(1);
		$ac = new anyC();
		$ac->setCollectionOf("FhemPreset");
		$ac->addAssocV3("FhemPresetHide","=","0");
		$ac->addJoinV3("FhemLocation", "FhemPresetLocationID", "=", "FhemLocationID");
		$ac->addAssocV3("INSTR(FhemLocationBindHosts, '$_SERVER[REMOTE_ADDR]')", ">", "0", "AND", "2");
		$ac->addAssocV3("FhemLocationBindHosts", "=", "", "OR", "2");

		while($a = $ac->getNextEntry())
			$html .= $this->getPresetControl($a);

		$html .= "</div>
		<div style=\"width:230px;float:left;\">";

		$ac = $this->getDevices();
		while($t = $ac->getNextEntry())
			$html .= $this->getControl($t);

		$html .= "</div><div style=\"width:230px;float:left;\">";

		$ac = $this->getDevicesFHT();
		while($t = $ac->getNextEntry())
			$html .= $this->getFHTControl($t);

		$html .= "</div>";

		$html .= "</div><script type=\"text/javascript\">Fhem.refreshControls();</script>";

		return $html;
	}

	public function getDevices($overview = false){
		$ac = new anyC();
		$ac->setCollectionOf("Fhem");
		$ac->addJoinV3("FhemLocation", "FhemLocationID", "=", "FhemLocationID");
		if($overview)
			$ac->addAssocV3("FhemInOverview", "=", "1");
		$ac->addAssocV3("INSTR(FhemLocationBindHosts, '$_SERVER[REMOTE_ADDR]')", ">", "0", "AND", "2");
		$ac->addAssocV3("FhemLocationBindHosts", "=", "", "OR", "2");
		$ac->addAssocV3("t1.FhemLocationID", "=", "0", "OR", "2");
		$ac->addOrderV3("FhemModel");
		$ac->addOrderV3("FhemITModel");
		$ac->addOrderV3("FhemHMModel");
		$ac->addOrderV3("FhemEMModel");

		return $ac;
	}

	public function getDevicesFHT($overview = false){
		$ac = new anyC();
		$ac->setCollectionOf("Fhem");
		$ac->addJoinV3("FhemLocation", "FhemLocationID", "=", "FhemLocationID");
		if($overview)
			$ac->addAssocV3("FhemInOverview", "=", "1");
		$ac->addAssocV3("INSTR(FhemLocationBindHosts, '$_SERVER[REMOTE_ADDR]')", ">", "0", "AND", "2");
		$ac->addAssocV3("FhemLocationBindHosts", "=", "", "OR", "2");
		$ac->addAssocV3("t1.FhemLocationID", "=", "0", "OR", "2");
		$ac->addAssocV3("t1.FhemType", "=", "FHT", "AND", "3");
		$ac->addOrderV3("FhemModel");
		$ac->addOrderV3("FhemITModel");
		$ac->addOrderV3("FhemHMModel");
		$ac->addOrderV3("FhemEMModel");

		return $ac;
	}

	function setPreset($PresetID){
		$S = new FhemPreset($PresetID);
		$S->activate();
	}

	function getPresetControl(FhemPreset $f){
		$B = new Button("", "./fheME/Fhem/events.png", "icon");
		$B->style("float:left;margin-left:-10px;margin-top:-13px;margin-right:3px;");

		return "<div onclick=\"".OnEvent::rme($this, "setPreset", $f->getID(), "Fhem.requestUpdate();")."\" style=\"cursor:pointer;width:200px;float:left;min-height:15px;border-radius:5px;border-width:1px;border-style:solid;margin:5px;padding:5px;\" class=\"borderColor1\">
				$B
				<div>
					<b>".$f->A("FhemPresetName")."</b>
				</div>
			</div>";
	}

	function getFHTControl(Fhem $f){
		$B = new Button("", "./fheME/Fhem/fhemFHT.png", "icon");
		$B->style("float:left;margin-left:-10px;margin-top:-13px;margin-right:3px;");

		$values = array("desired-temp 17.0" => "17.0°", "desired-temp 18.0" => "18.0°", "desired-temp 21.0" => "21.0°", "desired-temp 21.5" => "21.5°", "desired-temp 22.0" => "22.0°", "desired-temp 23.0" => "23.0°", "desired-temp 24.0" => "24.0°");

		$controls = $this->getSetTable("H".$f->getID(), $values);

		return "<div onclick=\"\$j('.fhemeControl:not(#controls_H".$f->getID().")').hide(); \$j('#controls_H".$f->getID()."').toggle();\" style=\"cursor:pointer;width:200px;float:left;min-height:15px;border-radius:5px;border-width:1px;border-style:solid;margin:5px;padding:5px;\" class=\"borderColor1\">
				$B$controls
				<div id=\"FhemID_".$f->getID()."\">
					<b>".$f->A("FhemName")."</b>
				</div>
			</div>";
	}

	function getControl(Fhem $f){
		if($f->getA()->FhemType == "FHZ") return;
		if($f->getA()->FhemType == "notify") return;
		if($f->getA()->FhemModel == "fs20s4u") return;

		if($f->getID() == "timer") $_SESSION["BPS"]->setProperty("mFhemTimer", "FhemValue", "off");

		$html = "";

		if($f->A("FhemType") == "FS20")
			switch($f->A("FhemModel")){
				/*case "fs20du":
					$html = "
					<script type=\"text/javascript\">
						Fhem.startSlider('".$f->getID()."', ".($f->getID() != "timer" ? "100" : "0.00001").");
					</script>

					<fieldset
						style=\"
							float:left;
							margin-left:".($f->getID() != "timer" ? "50px" : "0").";
							border-width:0px;
							padding:10px;
							width:60px;\"
						class=\"\">
						<legend>".$f->getA()->FhemName."</legend>

						<div style=\"width:32px;margin:auto;margin-bottom:10px;\"><img src=\"./fheME/Fhem/fhem.png\" /></div>

						<div id=\"track".$f->getID()."\" style=\"margin:auto;width:20px;height:300px;background-image:none;\" class=\"borderColor1 backgroundColor3\">
							<div id=\"slider".$f->getID()."\" style=\"width:40px;height:20px;margin-left:-8px;\" class=\"ui-slider-handle backgroundColor1\"></div>
						</div>
					</fieldset>";
				break;*/

				case "fs20du":
				case "fs20st":
					$onclick = "";
					if($f->A("FhemModel") == "fs20st")
						$values = array("on" => "on", "off" => "off");

					if($f->A("FhemModel") == "fs20du")
						$values = array("off" => "off", /*6, 12, 18,*/ "dim25%" => "25%", /*31, 37, 43,*/ "dim50%" => "50%", /*56, 62, 68,*/ "dim75%" => "75%", /*81, 87, 93,*/ "dim100%" => "100%");

					$controls = $this->getSetTable("D".$f->getID(), $values);

					$html = "<div onclick=\"\$j('.fhemeControl:not(#controls_D".$f->getID().")').hide(); \$j('#controls_D".$f->getID()."').toggle();\" style=\"cursor:pointer;width:210px;float:left;min-height:15px;border-radius:5px;border-width:1px;border-style:solid;margin:5px;padding:5px;\" class=\"borderColor1\">
							$B$controls
							<div id=\"FhemID_".$f->getID()."\">
								<b>".$f->A("FhemName")."</b>
							</div>
						</div>";

				break;

				case "fs20irf":

					$html = "
					<script type=\"text/javascript\">
						Fhem.startSliderOnOff('".$f->getID()."', ".($f->getID() != "timer" ? "100" : "0.00001").", true);
					</script>


					<fieldset
						style=\"
							float:left;
							margin-left:".($f->getID() != "timer" ? "50px" : "0").";
							/*border-style:dashed;*/
							border-width:0px;
							padding:10px;
							width:60px;\"
						class=\"\">
						<legend>".$f->getA()->FhemName."</legend>

						<div style=\"width:32px;margin:auto;margin-bottom:10px;\"><img src=\"./fheME/Fhem/fhem.png\" /></div>
						<div id=\"track".$f->getID()."\" style=\"margin:auto;width:20px;height:80px;\" class=\"backgroundColor3\">
							<div id=\"slider".$f->getID()."\" style=\"width:40px;height:20px;margin-left:-8px;\" class=\"ui-slider-handle borderColor1 backgroundColor1\"></div>
						</div>
					</fieldset>";
				break;

				case "fs20rsu":

					$html = "
					<script type=\"text/javascript\">
						Fhem.startSliderUpDown('".$f->getID()."');
					</script>


					<fieldset
						style=\"
							float:left;
							margin-left:".($f->getID() != "timer" ? "50px" : "0").";
							/*border-style:dashed;*/
							border-width:0px;
							padding:10px;
							width:60px;\"
						class=\"\">
						<legend>".$f->getA()->FhemName."</legend>

						<div style=\"width:32px;margin:auto;margin-bottom:10px;\"><img src=\"./fheME/Fhem/fhem.png\" /></div>
						<div id=\"track".$f->getID()."\" style=\"margin:auto;width:20px;height:80px;\" class=\"backgroundColor3\">
							<div id=\"slider".$f->getID()."\" style=\"width:40px;height:20px;margin-left:-8px;\" class=\"ui-slider-handle borderColor1 backgroundColor1\"></div>
						</div>
					</fieldset>";
				break;
			}

		if($f->A("FhemType") == "Intertechno")
			switch($f->A("FhemITModel")){
				/*case "itdimmer":
					$html = "
					<script type=\"text/javascript\">
						Fhem.startSlider('".$f->getID()."', ".($f->getID() != "timer" ? "100" : "0.00001").");
					</script>

					<fieldset
						style=\"
							float:left;
							margin-left:".($f->getID() != "timer" ? "50px" : "0").";
							border-width:0px;
							padding:10px;
							width:60px;\"
						class=\"\">
						<legend>".$f->getA()->FhemName."</legend>

						<div style=\"width:32px;margin:auto;margin-bottom:10px;\"><img src=\"./fheME/Fhem/fhem.png\" /></div>

						<div id=\"track".$f->getID()."\" style=\"margin:auto;width:20px;height:300px;background-image:none;\" class=\"borderColor1 backgroundColor3\">
							<div id=\"slider".$f->getID()."\" style=\"width:40px;height:20px;margin-left:-8px;\" class=\"ui-slider-handle backgroundColor1\"></div>
						</div>
					</fieldset>";
				break;*/

				case "itdimmer":
				case "itswitch":
					$onclick = "";
					if($f->A("FhemITModel") == "itswitch")
						$values = array("on" => "on", "off" => "off");

					if($f->A("FhemITModel") == "itdimmer")
						$values = array("off" => "Off", /*6, 12, 18, 25,*/ "dimdown" => "Down", /*31, 37, 43, 50,*/ "dimup" => "Up", /*56, 62, 68, 75, 81, 87, 93, 100*/ "on" => "On");

					$controls = $this->getSetTable("D".$f->getID(), $values);

					$html = "<div onclick=\"\$j('.fhemeControl:not(#controls_D".$f->getID().")').hide(); \$j('#controls_D".$f->getID()."').toggle();\" style=\"cursor:pointer;width:210px;float:left;min-height:15px;border-radius:5px;border-width:1px;border-style:solid;margin:5px;padding:5px;\" class=\"borderColor1\">
							$B$controls
							<div id=\"FhemID_".$f->getID()."\">
								<b>".$f->A("FhemName")."</b>
							</div>
						</div>";

				break;
			}

		if($f->A("FhemType") == "HomeMatic")
			switch($f->A("FhemHMModel")){
				/*case "dimmer":
					$html = "
					<script type=\"text/javascript\">
						Fhem.startSlider('".$f->getID()."', ".($f->getID() != "timer" ? "100" : "0.00001").");
					</script>

					<fieldset
						style=\"
							float:left;
							margin-left:".($f->getID() != "timer" ? "50px" : "0").";
							border-width:0px;
							padding:10px;
							width:60px;\"
						class=\"\">
						<legend>".$f->getA()->FhemName."</legend>

						<div style=\"width:32px;margin:auto;margin-bottom:10px;\"><img src=\"./fheME/Fhem/fhem.png\" /></div>

						<div id=\"track".$f->getID()."\" style=\"margin:auto;width:20px;height:300px;background-image:none;\" class=\"borderColor1 backgroundColor3\">
							<div id=\"slider".$f->getID()."\" style=\"width:40px;height:20px;margin-left:-8px;\" class=\"ui-slider-handle backgroundColor1\"></div>
						</div>
					</fieldset>";
				break;*/

				case "dimmer":
				case "switch":
					$onclick = "";
					if($f->A("FhemHMModel") == "switch")
						$values = array("on" => "on", "off" => "off");

					if($f->A("FhemHMModel") == "dimmer")
						$values = array("off" => "off", /*6, 12, 18,*/ "25%" => "25%", /*31, 37, 43,*/ "50%" => "50%", /*56, 62, 68,*/ "75%" => "75%", /*81, 87, 93,*/ "100%" => "100%");

					$controls = $this->getSetTable("D".$f->getID(), $values);

					$html = "<div onclick=\"\$j('.fhemeControl:not(#controls_D".$f->getID().")').hide(); \$j('#controls_D".$f->getID()."').toggle();\" style=\"cursor:pointer;width:210px;float:left;min-height:15px;border-radius:5px;border-width:1px;border-style:solid;margin:5px;padding:5px;\" class=\"borderColor1\">
							$B$controls
							<div id=\"FhemID_".$f->getID()."\">
								<b>".$f->A("FhemName")."</b>
							</div>
						</div>";

				break;
			}

		if($f->A("FhemType") == "ELV EM")
			switch($f->A("FhemEMModel")){

				case "emem":

					$html = "<div onclick=\"\$j('.fhemeControl:not(#controls_D".$f->getID().")').hide(); \$j('#controls_D".$f->getID()."').toggle();\" style=\"cursor:pointer;width:210px;float:left;min-height:15px;border-radius:5px;border-width:1px;border-style:solid;margin:5px;padding:5px;\" class=\"borderColor1\">
							$B$controls
							<div id=\"FhemID_".$f->getID()."\">
								<b>".$f->A("FhemName")."</b>
							</div>
						</div>";

				break;
			}

		if($f->A("FhemType") == "RGB")
			$html = "
					<script type=\"text/javascript\">
						Fhem.startRGBSlider('".$f->getID()."', 256);
					</script>


					<fieldset
						style=\"
							float:left;
							margin-left:".($f->getID() != "timer" ? "50px" : "0").";
							/*border-style:dashed;
							border-width:1px;*/
							padding:10px;
							width:125px;\"
						class=\"\">
						<legend>".$f->getA()->FhemName."</legend>

						<div style=\"width:32px;margin:auto;margin-bottom:10px;\"><img src=\"./fheME/Fhem/fhem.png\" /></div>

						<div id=\"trackr".$f->getID()."\" style=\"float:left;margin:auto;margin-right:15px;width:30px;height:300px;\" class=\"backgroundColor3\">
							<div id=\"sliderr".$f->getID()."\" style=\"width:40px;height:40px;margin-left:-5px;border-width:1px;border-style:solid;\" class=\"ui-slider-handle borderColor1 backgroundColor1\"></div>
						</div>
						<div id=\"trackg".$f->getID()."\" style=\"float:left;margin:auto;margin-right:15px;width:30px;height:300px;\" class=\"backgroundColor3\">
							<div id=\"sliderg".$f->getID()."\" style=\"width:40px;height:40px;margin-left:-5px;border-width:1px;border-style:solid;\" class=\"ui-slider-handle borderColor1 backgroundColor1\"></div>
						</div>
						<div id=\"trackb".$f->getID()."\" style=\"float:left;margin:auto;width:30px;height:300px;\" class=\"backgroundColor3\">
							<div id=\"sliderb".$f->getID()."\" style=\"width:40px;height:40px;margin-left:-5px;border-width:1px;border-style:solid;\" class=\"ui-slider-handle borderColor1 backgroundColor1\"></div>
						</div>
					</fieldset>";
		return $html;
	}

	function getSetTable($DeviceID, $values){
		$controls = new HTMLTable(1);
		$controls->setTableStyle("width:100%;border:0px;");
		$i = 0;
		foreach($values AS $v => $l){
			$controls->addRow(array($l));
			$controls->addRowClass("backgroundColor0");
			$controls->addCellStyle(1, "padding-top:10px;padding-bottom:10px;cursor:pointer;");
			if($i != count($values) - 1){
				$controls->addCellStyle(1, "border-bottom-style:solid;border-bottom-width:1px;");
				$controls->addCellClass(1, "borderColor1");
			}

			$controls->addCellEvent(1, "click", OnEvent::rme($this, "setDevice", array(str_replace(array("D", "H"), "", $DeviceID), "'$v'"), "function(){ \$j('#controls_$DeviceID').hide(); Fhem.requestUpdate(); }"));

			$i++;
		}
		$controls = "<div id=\"controls_$DeviceID\" style=\"display:none;width:50px;position:absolute;margin-left:150px;border-style:solid;border-width:1px;border-radius:5px;padding:3px;\" class=\"borderColor1 backgroundColor0 fhemeControl\">$controls</div>";

		return $controls;
	}

	public function setDevice($id, $action){
		if($id == "timer") {
			$_SESSION["BPS"]->setProperty("mFhemTimer", "FhemValue", $action);
			return;
		}
		$F = new Fhem($id);
		$F->loadMe();

		$S = new FhemServer($F->getA()->FhemServerID);
		$S->loadMe();

		switch($S->A("FhemServerType")){
			case "0":
				try {
					$T = new Telnet($S->getA()->FhemServerIP, $S->getA()->FhemServerPort);
				} catch(NoServerConnectionException $e){
					die("error:'The connection to the server with IP-address ".$S->getA()->FhemServerIP." could not be established!'");
				}

				$T->fireAndForget("set ".$F->A("FhemName")." $action");
				$T->disconnect();
			break;
			case "1":
				$S->setDevice($F->getID(), $action);
				#$url = $S->A("FhemServerURL")."?device=".$F->A("FhemName")."&value=".$action;
				#echo "opening URL $url...";
				#fopen($url, "r");
			break;
		}
	}

	public function setTimer($id, $action, $type, $stunden, $minuten, $deviceName){
		if($action == "setBPSValue"){
			$action = $_SESSION["BPS"]->getProperty("mFhemTimer", "FhemValue");
		}

		switch($type){
			case "D":
				$F = new Fhem($id);
				$F->loadMe();

				$S = new FhemServer($F->getA()->FhemServerID);
				$S->loadMe();
			break;
			case "P":
				$ac = new anyC();
				$ac->setCollectionOf("FhemPreset");
				$ac->addJoinV3("FhemServer","FhemPresetServerID","=","FhemServerID");
				$ac->addAssocV3("FhemPresetID","=",$id);
				$ac->setLimitV3("1");

				$S = $ac->getNextEntry();

				$action = "on";
			break;
		}


		try {
			$T = new Telnet($S->getA()->FhemServerIP, $S->getA()->FhemServerPort);
		} catch(NoServerConnectionException $e){
			die("error:'The connection to the server with IP-address ".$S->getA()->FhemServerIP." could not be established!'");
		}

		$T->fireAndForget("define a".rand(10,10000000)." at ".($stunden < 10 ? "0" : "")."$stunden:".($minuten < 10 ? "0" : "")."$minuten:00 set ".$deviceName." $action");
		$T->disconnect();
	}

	private function registerType($tab, $type){
		$oldServer = "";
		$T = null;

		$ac = new anyC();
		$ac->setCollectionOf("Fhem");
		$ac->addJoinV3("FhemServer","FhemServerID","=","FhemServerID");
		$ac->addOrderV3("t1.FhemServerID");
		$ac->addAssocV3("FhemType","=",$type);


		while($t = $ac->getNextEntry()){
			try {
				if($oldServer != $t->getA()->FhemServerID) {
					$T = new Telnet($t->getA()->FhemServerIP, $t->getA()->FhemServerPort);
					$hasFHT = false;
				}
			} catch(NoServerConnectionException $e){
				die("error:'The connection to the server with IP-address ".$t->getA()->FhemServerIP." could not be established!'");
			}
			$tel = $t->getDefineCommand();#"define ".$t->getA()->FhemName." ".$t->getA()->FhemType." ".$t->getA()->FhemSpecific;

			foreach($tel AS $c)
				$T->fireAndForget($c);

			if($t->A("FhemType") == "FHT" AND !$hasFHT){
				$T->fireAndForget('define fht_setdate notify fht_setdate { if ( $year gt 2010 && $wday == 1 ) { my @@fhts=devspec2array("TYPE=FHT");; foreach(@@fhts) { my $cmd="set ".$_." date time";;  fhem $cmd;;  Log 4, "sent cmd ".$cmd;;   } } else { Log 1, "error setting date for fhts: year <= 2010 - date invalid?!" } }');
				$T->fireAndForget("define t_fht_setdate at *04:00:00 trigger fht_setdate");
				$hasFHT = true;
			}

			$tab->addRow(array($t->A("FhemServerName"), implode("<br />", $tel)));
			$oldServer = $t->A("FhemServerID");
		}
	}

	public function registerSettings(){

		$tab = new HTMLTable(2,"Telnet-Commands");
		$tab->maxHeight(500);

		$this->registerType($tab, "FHZ");
		$this->registerType($tab, "FS20");
		$this->registerType($tab, "FHT");
		$this->registerType($tab, "Intertechno");
		$this->registerType($tab, "HomeMatic");
		$this->registerType($tab, "ELV EM");
		$this->registerType($tab, "dummy");

		$oldServer = "";
		$T = null;
		$ac = new anyC();
		$ac->setCollectionOf("Fhem");
		$ac->addJoinV3("FhemServer","FhemServerID","=","FhemServerID");
		$ac->addOrderV3("t1.FhemServerID");
		$ac->addAssocV3("FhemType","=","notify");
		$ac->addAssocV3("FhemServerType","=","0");
		$oldServer = "";


		while($t = $ac->getNextEntry()){
			try {
				if($oldServer != $t->A("FhemServerID")) $T = new Telnet($t->A("FhemServerIP"), $t->A("FhemServerPort"));
			} catch(NoServerConnectionException $e){
				die("error:'The connection to the server with IP-address ".$t->A("FhemServerIP")." could not be established!'");
			}
			#$tel = "define ".$t->getA()->FhemName." notify ".$t->getA()->FhemRunOn." ".str_replace("\n"," ",$t->getA()->FhemCommand);
			#$T->fireAndForget($tel);
			$tel = $t->getDefineCommand();
			foreach($tel AS $c)
				$T->fireAndForget($c);

			$tab->addRow(array($t->A("FhemServerName"), implode("<br />", $tel)));
			$oldServer = $t->A("FhemServerID");
		}

		$ac = new mFhemPresetGUI();
		$ac->addJoinV3("FhemServer","FhemPresetServerID","=","FhemServerID");
		$ac->addJoinV3("FhemEvent","FhemPresetID","=","FhemEventPresetID");
		$ac->addJoinV3("Fhem","t3.FhemEventFhemID","=","FhemID");#sleep 0.5;;
		$ac->setFieldsV3(array("FhemEventAction", "FhemPresetRunOn", "FhemPresetName", "FhemServerIP", "FhemPresetNightOnly", "FhemServerName", "FhemServerPort", "t2.FhemServerID", "FhemName", "FhemEventFhemID"));
		$ac->addOrderV3("FhemPresetID");
		$ac->addOrderV3("FhemEventID");
		$ac->addAssocV3("FhemServerType","=","0");

		$tab->addRow(array("", ""));
		$tab->addRowColspan(1, 2);

		$command = "";
		$oldServer = "";
		while($b = $ac->getNextEntry()){
			try {
				if($oldServer != $b->A("FhemServerID")) $T = new Telnet($b->A("FhemServerIP"), $b->A("FhemServerPort"));
			} catch(NoServerConnectionException $e){
				die("error:'The connection to the server with IP-address ".$t->A("FhemServerIP")." could not be established!'");
			}

			if($b->A("FhemEventFhemID") != "-1") $command .= "set ".$b->A("FhemName")." ".$b->A("FhemEventAction").";;";#sleep 0.5;;";
			else $command .= $b->A("FhemEventAction").";;";

			$next = $ac->getNextEntry();
			if($next == null OR $next->A("FhemPresetID") != $b->A("FhemPresetID")){

				$runOn = null;
				if($b->A("FhemPresetRunOn") != "")
					$runOn = $b->A("FhemPresetRunOn");
				$d = "";
				if($runOn == null)
					$d = "define ".$b->A("FhemPresetName")." dummy";
				$c = "define n".$b->A("FhemPresetID")." notify ".($runOn == null ? $b->A("FhemPresetName") : $runOn)." {fhem(\"".str_replace("%","%%",$command)."\") ".($b->A("FhemPresetNightOnly") == "1" ? "if(!isday())" : "")."}";

				$tab->addRow(array($b->A("FhemPresetName")."<br />".$b->A("FhemServerName"), $d."<br />".$c));

				if($runOn == null)
					$T->fireAndForget($d);
				$T->fireAndForget($c);

				$command = "";
			}

			if($next != null) $ac->subPointer();

		}

		/*$oldServer = "";
		while($b = $ac->getNextEntry()){
			try {
				if($oldServer != $b->getA()->FhemServerID) $T = new Telnet($b->getA()->FhemServerIP, $b->getA()->FhemServerPort);
			} catch(NoFhemServerConnectionException $e){
				die("error:'The connection to the server with IP-address ".$t->getA()->FhemServerIP." could not be established!'");
			}

			$c = "define n".$b->getA()->FhemPresetID." notify ".$b->getA()->FhemPresetName." {fhem(\"".str_replace("%","%%",$b->getA()->action)."\") ".($b->getA()->FhemPresetNightOnly == "1" ? "if(!isday())" : "")."}";
			$d = "define ".$b->getA()->FhemPresetName." dummy";
			$tab->addRow(array($b->getA()->FhemPresetName."<br />".$b->getA()->FhemServerName,$d."<br />".$c));

			$T->fireAndForget($d);
			$T->fireAndForget($c);

			$oldServer = $b->getA()->FhemServerID;
		}*/
		echo $tab;
	}

	public function updateGUI(){
		$result = array();
		$S = new mFhemServerGUI();
		#$S->addAssocV3("FhemServerType", "=", "0");
		try {
			while($s = $S->getNextEntry()){
				switch($s->A("FhemServerType")){
					case "0":
						$T = new Telnet($s->getA()->FhemServerIP, $s->getA()->FhemServerPort);
						$T->setPrompt("</FHZINFO>");
						$answer = $T->fireAndGet("xmllist")."</FHZINFO>";

						$x = simplexml_load_string($answer);

						if(isset($x->FS20_LIST->FS20) AND count($x->FS20_LIST->FS20) > 0)
							foreach($x->FS20_LIST->FS20 AS $k => $v){
								$F = new mFhemGUI();
								$F->addAssocV3("FhemServerID","=",$s->getID());
								$F->addAssocV3("FhemName","=",$v->attributes()->name);

								$F = $F->getNextEntry();
								if($F == null)
									continue;

								$state = $v->attributes()->state;

								if($F->A("FhemModel") == "fs20irf") $state = "off";

								$state = str_replace("dim", "", $state);

								$FS = "";
								if($state == "An" || $state == "an" || $state == "On" || $state == "on"){
									$FS = new Button("", "./fheME/Fhem/on.png", "icon");
									$FS->style("float:right;margin-right:-10px;margin-top:-13px;margin-left:3px;");
								}

								if($state == "Aus" || $state == "aus" || $state == "Off" || $state == "off"){
									$FS = new Button("", "./fheME/Fhem/off.png", "icon");
									$FS->style("float:right;margin-right:-10px;margin-top:-13px;margin-left:3px;");
								}

								if($state == "6%"){
									$FS = new Button("", "./fheME/Fhem/6.png", "icon");
									$FS->style("float:right;margin-right:-10px;margin-top:-13px;margin-left:3px;");
								}

								if($state == "12%"){
									$FS = new Button("", "./fheME/Fhem/12.png", "icon");
									$FS->style("float:right;margin-right:-10px;margin-top:-13px;margin-left:3px;");
								}

								if($state == "18%"){
									$FS = new Button("", "./fheME/Fhem/18.png", "icon");
									$FS->style("float:right;margin-right:-10px;margin-top:-13px;margin-left:3px;");
								}

								if($state == "25%"){
									$FS = new Button("", "./fheME/Fhem/25.png", "icon");
									$FS->style("float:right;margin-right:-10px;margin-top:-13px;margin-left:3px;");
								}

								if($state == "31%"){
									$FS = new Button("", "./fheME/Fhem/31.png", "icon");
									$FS->style("float:right;margin-right:-10px;margin-top:-13px;margin-left:3px;");
								}

								if($state == "37%"){
									$FS = new Button("", "./fheME/Fhem/37.png", "icon");
									$FS->style("float:right;margin-right:-10px;margin-top:-13px;margin-left:3px;");
								}

								if($state == "43%"){
									$FS = new Button("", "./fheME/Fhem/43.png", "icon");
									$FS->style("float:right;margin-right:-10px;margin-top:-13px;margin-left:3px;");
								}

								if($state == "50%"){
									$FS = new Button("", "./fheME/Fhem/50.png", "icon");
									$FS->style("float:right;margin-right:-10px;margin-top:-13px;margin-left:3px;");
								}

								if($state == "56%"){
									$FS = new Button("", "./fheME/Fhem/56.png", "icon");
									$FS->style("float:right;margin-right:-10px;margin-top:-13px;margin-left:3px;");
								}

								if($state == "62%"){
									$FS = new Button("", "./fheME/Fhem/62.png", "icon");
									$FS->style("float:right;margin-right:-10px;margin-top:-13px;margin-left:3px;");
								}

								if($state == "68%"){
									$FS = new Button("", "./fheME/Fhem/68.png", "icon");
									$FS->style("float:right;margin-right:-10px;margin-top:-13px;margin-left:3px;");
								}

								if($state == "75%"){
									$FS = new Button("", "./fheME/Fhem/75.png", "icon");
									$FS->style("float:right;margin-right:-10px;margin-top:-13px;margin-left:3px;");
								}

								if($state == "81%"){
									$FS = new Button("", "./fheME/Fhem/81.png", "icon");
									$FS->style("float:right;margin-right:-10px;margin-top:-13px;margin-left:3px;");
								}

								if($state == "87%"){
									$FS = new Button("", "./fheME/Fhem/87.png", "icon");
									$FS->style("float:right;margin-right:-10px;margin-top:-13px;margin-left:3px;");
								}

								if($state == "93%"){
									$FS = new Button("", "./fheME/Fhem/93.png", "icon");
									$FS->style("float:right;margin-right:-10px;margin-top:-13px;margin-left:3px;");
								}

								if($state == "100%"){
									$FS = new Button("", "./fheME/Fhem/100.png", "icon");
									$FS->style("float:right;margin-right:-10px;margin-top:-13px;margin-left:3px;");
								}
								
								$result[$F->getID()] = array("model" => $F->A("FhemModel"), "state" => "$FS<b>".$F->A("FhemAlias")."</b> ");

								#echo $F->getID().":".$F->A("FhemModel").":".$v->attributes()->state."\n";
							}

						if(isset($x->IT_LIST->IT) AND count($x->IT_LIST->IT) > 0)
							foreach($x->IT_LIST->IT AS $k => $v){
								$F = new mFhemGUI();
								$F->addAssocV3("FhemServerID","=",$s->getID());
								$F->addAssocV3("FhemName","=",$v->attributes()->name);

								$F = $F->getNextEntry();
								if($F == null)
									continue;

								$state = $v->attributes()->state;

								$state = str_replace("dim", "", $state);

								$IT = "";
								if($state == "An" || $state == "an" || $state == "On" || $state == "on"){
									$IT = new Button("", "./fheME/Fhem/on.png", "icon");
									$IT->style("float:right;margin-right:-10px;margin-top:-13px;margin-left:3px;");
								}

								if($state == "Aus" || $state == "aus" || $state == "Off" || $state == "off"){
									$IT = new Button("", "./fheME/Fhem/off.png", "icon");
									$IT->style("float:right;margin-right:-10px;margin-top:-13px;margin-left:3px;");
								}

								$result[$F->getID()] = array("model" => $F->A("FhemITModel"), "state" => "$IT<b>".$F->A("FhemAlias")."</b> ");

								#echo $F->getID().":".$F->A("FhemITModel").":".$v->attributes()->state."\n";
							}

						if(isset($x->CUL_HM_LIST->CUL_HM) AND count($x->CUL_HM_LIST->CUL_HM) > 0)
							foreach($x->CUL_HM_LIST->CUL_HM AS $k => $v){
								$F = new mFhemGUI();
								$F->addAssocV3("FhemServerID","=",$s->getID());
								$F->addAssocV3("FhemName","=",$v->attributes()->name);

								$F = $F->getNextEntry();
								if($F == null)
									continue;

								$state = $v->attributes()->state;

								$state = str_replace("dim", "", $state);

								$HM = "";
								if($state == "An" || $state == "an" || $state == "On" || $state == "on"){
									$HM = new Button("", "./fheME/Fhem/on.png", "icon");
									$HM->style("float:right;margin-right:-10px;margin-top:-13px;margin-left:3px;");
								}

								if($state == "Aus" || $state == "aus" || $state == "Off" || $state == "off"){
									$HM = new Button("", "./fheME/Fhem/off.png", "icon");
									$HM->style("float:right;margin-right:-10px;margin-top:-13px;margin-left:3px;");
								}

								if($state == "6%"){
									$HM = new Button("", "./fheME/Fhem/6.png", "icon");
									$HM->style("float:right;margin-right:-10px;margin-top:-13px;margin-left:3px;");
								}

								if($state == "12%"){
									$HM = new Button("", "./fheME/Fhem/12.png", "icon");
									$HM->style("float:right;margin-right:-10px;margin-top:-13px;margin-left:3px;");
								}

								if($state == "18%"){
									$HM = new Button("", "./fheME/Fhem/18.png", "icon");
									$HM->style("float:right;margin-right:-10px;margin-top:-13px;margin-left:3px;");
								}

								if($state == "25%"){
									$HM = new Button("", "./fheME/Fhem/25.png", "icon");
									$HM->style("float:right;margin-right:-10px;margin-top:-13px;margin-left:3px;");
								}

								if($state == "31%"){
									$HM = new Button("", "./fheME/Fhem/31.png", "icon");
									$HM->style("float:right;margin-right:-10px;margin-top:-13px;margin-left:3px;");
								}

								if($state == "37%"){
									$HM = new Button("", "./fheME/Fhem/37.png", "icon");
									$HM->style("float:right;margin-right:-10px;margin-top:-13px;margin-left:3px;");
								}

								if($state == "43%"){
									$HM = new Button("", "./fheME/Fhem/43.png", "icon");
									$HM->style("float:right;margin-right:-10px;margin-top:-13px;margin-left:3px;");
								}

								if($state == "50%"){
									$HM = new Button("", "./fheME/Fhem/50.png", "icon");
									$HM->style("float:right;margin-right:-10px;margin-top:-13px;margin-left:3px;");
								}

								if($state == "56%"){
									$HM = new Button("", "./fheME/Fhem/56.png", "icon");
									$HM->style("float:right;margin-right:-10px;margin-top:-13px;margin-left:3px;");
								}

								if($state == "62%"){
									$HM = new Button("", "./fheME/Fhem/62.png", "icon");
									$HM->style("float:right;margin-right:-10px;margin-top:-13px;margin-left:3px;");
								}

								if($state == "68%"){
									$HM = new Button("", "./fheME/Fhem/68.png", "icon");
									$HM->style("float:right;margin-right:-10px;margin-top:-13px;margin-left:3px;");
								}

								if($state == "75%"){
									$HM = new Button("", "./fheME/Fhem/75.png", "icon");
									$HM->style("float:right;margin-right:-10px;margin-top:-13px;margin-left:3px;");
								}

								if($state == "81%"){
									$HM = new Button("", "./fheME/Fhem/81.png", "icon");
									$HM->style("float:right;margin-right:-10px;margin-top:-13px;margin-left:3px;");
								}

								if($state == "87%"){
									$HM = new Button("", "./fheME/Fhem/87.png", "icon");
									$HM->style("float:right;margin-right:-10px;margin-top:-13px;margin-left:3px;");
								}

								if($state == "93%"){
									$HM = new Button("", "./fheME/Fhem/93.png", "icon");
									$HM->style("float:right;margin-right:-10px;margin-top:-13px;margin-left:3px;");
								}

								if($state == "100%"){
									$HM = new Button("", "./fheME/Fhem/100.png", "icon");
									$HM->style("float:right;margin-right:-10px;margin-top:-13px;margin-left:3px;");
								}
								
								$result[$F->getID()] = array("model" => $F->A("FhemHMModel"), "state" => "$HM<b>".$F->A("FhemAlias")."</b> ");

								#echo $F->getID().":".$F->A("FhemHMModel").":".$v->attributes()->state."\n";
							}

						if(isset($x->CUL_EM_LIST->CUL_EM) AND count($x->CUL_EM_LIST->CUL_EM) > 0)
							foreach($x->CUL_EM_LIST->CUL_EM AS $em){
								$F = anyC::get("Fhem", "FhemServerID", $s->getID());
								$F->addAssocV3("FhemName", "=", $em->attributes()->name);
								$F = $F->getNextEntry();

								if($F == null) continue;

								foreach($em->STATE AS $state){
									if($state->attributes()->key == "current")
										$current = $state->attributes()->value;

								$result[$F->getID()] = array("model" => $F->A("FhemEMModel"), "state" => "<b>".$F->A("FhemAlias")."</b><b style=\"float:right;\">".$current."</b>");

								#echo $F->getID().":".$F->A("FhemEMModel").":".$em->attributes()->current."\n";
							}
					break;

						if(isset($x->FHT_LIST->FHT) AND count($x->FHT_LIST->FHT) > 0)
							foreach($x->FHT_LIST->FHT AS $fht){
								$F = anyC::get("Fhem", "FhemServerID", $s->getID());
								$F->addAssocV3("FhemName", "=", $fht->attributes()->name);

								$F = $F->getNextEntry();
								if($F == null) continue;

								$measuredTemp = 0;
								$warnings = "";
								$actuator = "";
								$desiredTemp = "";
								$mode = "";
								foreach($fht->STATE AS $state){
									if($state->attributes()->key == "measured-temp")
										$measuredTemp = str_replace(" (Celsius)", "", $state->attributes()->value);

									if($state->attributes()->key == "warnings")
										$warnings = $state->attributes()->value;

									if($state->attributes()->key == "actuator")
										$actuator = $state->attributes()->value;

									if($state->attributes()->key == "desired-temp")
										$desiredTemp = $state->attributes()->value;

									if($state->attributes()->key == "mode")
										$mode = $state->attributes()->value;
								}

								$M = "";
								if($mode == "holiday_short"){
									$M = new Button("", "./fheME/Fhem/modeHoliday.png", "icon");
									$M->style("float:right;margin-top:-12px;margin-right:-9px;");
								}

								$B = "";
								if($warnings == "Temperature too low"){
									$B = new Button("", "./fheME/Fhem/tooCold.png", "icon");
									$B->style("float:right;");
								}

								if($warnings == "Window open"){
									$B = new Button("", "./fheME/Fhem/windowOpen.png", "icon");
									$B->style("float:right;");
								}

								if($warnings == "Battery low"){
									$B = new Button("", "./fheME/Fhem/batteryLow.png", "icon");
									$B->style("float:right;");
								}

								$result[$F->getID()] = array("model" => $F->A("FhemFHTModel"), "state" => "$M<b>".$F->A("FhemName")."</b> {$measuredTemp}/{$desiredTemp} <small>({$actuator})</small>".($warnings != "none" ? "<br />$B{$warnings}" : ""));

								#echo $F->getID().":".$F->A("FhemFHTModel").":$B<b>".$F->A("FhemName")."</b> {$measuredTemp}/{$desiredTemp} <small>({$actuator})</small>".($warnings != "none" ? "<br />{$warnings}" : "")."\n";

							}
					break;

					/*case "1":
						$F = new mFhemGUI();
						$F->addAssocV3("FhemServerID","=",$s->getID());

						while($D = $F->getNextEntry())
							echo $D->getID().":web:".file_get_contents($s->A("FhemServerURL")."?device=".$D->A("FhemName")."&getStatus=true")."\n";

					break;*/
				}
			}
 }
			echo json_encode($result);
		} catch(NoServerConnectionException $e) {
			die("message:'Fhem-server unreachable'");
		}
	}

	public function resetServers(){
		$S = new mFhemServerGUI();

		$t = new HTMLTable(2, "Telnet-Commands");

		while($s = $S->getNextEntry()){
			$T = new Telnet($s->getA()->FhemServerIP, $s->getA()->FhemServerPort);
			$T->fireAndForget("rereadcfg");

			$t->addRow(array($s->getA()->FhemServerIP, "rereadcfg"));
		}

		echo $t;
	}
}
?>
