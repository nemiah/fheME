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
 *  2007 - 2017, Furtmeier Hard- und Software - Support@Furtmeier.IT
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
		$ac->addOrderV3("FhemOrder");
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

		return "<div onclick=\"".OnEvent::rme($this, "setPreset", $f->getID(), "if(Fhem.doAutoUpdate) Fhem.requestUpdate();")."\" style=\"cursor:pointer;width:200px;float:left;min-height:15px;border-radius:5px;border-width:1px;border-style:solid;margin:5px;padding:5px;\" class=\"borderColor1\">
				$B
				<div>
					<b>".$f->A("FhemPresetName")."</b>
				</div>
			</div>";
	}

	function getFHTControl(Fhem $f){
		#$B = new Button("", "./fheME/Fhem/fhemFHT.png", "icon");
		#$B->style("float:left;margin-left:-10px;margin-top:-13px;margin-right:3px;");

		#$values = array("desired-temp 5.5" => "off", "desired-temp 17.0" => "17.0°", "desired-temp 18.0" => "18.0°", "desired-temp 21.0" => "21.0°", "desired-temp 21.5" => "21.5°", "desired-temp 22.0" => "22.0°", "desired-temp 23.0" => "23.0°", "desired-temp 24.0" => "24.0°");

		#$controls = $this->getSetTable("H".$f->getID(), $values);

		return "<div class=\"touchButton\" onclick=\"Touchy.wheelOnFire(event, {
				data: {'desired-temp 17.0': '17,0°', 'desired-temp 21.0': '21,0°', 'desired-temp 21.5': '21,5°', 'desired-temp 22.0': '22,0°', 'desired-temp 22.5': '22,5°', 'desired-temp 23.0': '23,0°', 'desired-temp 28.0': '28,0°'},
				selection: function(value){
					".OnEvent::rme($this, "setDevice", array($f->getID(), "value"), "function(){ if(Fhem.doAutoUpdate) Fhem.requestUpdate(); }")."
				},
				value: function(){
					return \$j('#FhemID_".$f->getID()."TargetTemp').data('value');
				}
			});/*\$j('.fhemeControl:not(#controls_H".$f->getID().")').hide(); \$j('#controls_H".$f->getID()."').toggle();*/\" style=\"cursor:pointer;\" class=\"\">
				
				<div id=\"FhemID_".$f->getID()."\">
					
				</div>
			</div>";
	}

	private static $counter = 0;
	
	function getControl(Fhem $f){
		if($f->getA()->FhemType == "FHZ") return;
		if($f->getA()->FhemType == "notify") return;
		if($f->getA()->FhemModel == "fs20s4u") return;

		if($f->getID() == "timer") $_SESSION["BPS"]->setProperty("mFhemTimer", "FhemValue", "off");

		$html = "";

		if($f->A("FhemSpace") != ""){
			$html .= "<div style=\"margin-bottom:5px;\">".$f->A("FhemSpace")."</div>";
			self::$counter = 0;
		}
		
		if($f->A("FhemType") == "HUEDevice")
			switch($f->A("FhemHUEModel")){
				case "lightDimmable":
					$onclick = "Touchy.wheelOnFire(event, {
						data: {'off': 'aus', 'dim25': '25%', 'dim31': '31%', 'dim50': '50%', 'dim56': '56%', 'dim75': '75%', 'dim100': '100%'},
						selection: function(value){
							".OnEvent::rme($this, "setDevice", array($f->getID(), "value"), "function(){ if(Fhem.doAutoUpdate) Fhem.requestUpdate(); }")."
						},
						value: function(){
							return \$j('#FhemID_".$f->getID()."State').data('value');
						}
					})";
					
					
					$white = "width:calc(50% - 5px);";
					if(self::$counter++ % 2 == 0)
						$white = "width:calc(50% - 5px);margin-right: 10px;";
					
					$html .= "<div id=\"FhemControlID_".$f->getID()."\" onclick=\"$onclick\" style=\"cursor:pointer;{$white}box-sizing:border-box;display:inline-block;vertical-align:top;\" class=\"touchButton\">
							
							<div id=\"FhemID_".$f->getID()."\">
								
							</div>
						</div>";

				break;
			
				case "plugToggle":
					$togggle = false;
					$onclick = "\$j('.fhemeControl:not(#controls_D".$f->getID().")').hide(); \$j('#controls_D".$f->getID()."').toggle();";
					
					$values = array("on" => "on", "off" => "off");
					$togggle = true;
					$onclick = OnEvent::rme($this, "toggleDevice", $f->getID(), "if(Fhem.doAutoUpdate) Fhem.requestUpdate();");
					
					
					if($f->A("FhemModel") == "fs20du")
						$values = array("off" => "off", /*6, 12, 18,*/ "dim25%" => "25%", /*31, 37, 43,*/ "dim50%" => "50%", /*56, 62, 68,*/ "dim75%" => "75%", /*81, 87, 93,*/ "dim100%" => "100%");

					
					$white = "width:calc(50% - 5px);";
					if(self::$counter++ % 2 == 0)
						$white = "width:calc(50% - 5px);margin-right: 10px;";
					
					$html .= "<div id=\"FhemControlID_".$f->getID()."\" onclick=\"$onclick\" style=\"cursor:pointer;{$white}box-sizing:border-box;display:inline-block;vertical-align:top;\" class=\"touchButton\">
							<div id=\"FhemID_".$f->getID()."\">
								
							</div>
						</div>";
				break;
			}
			
		
		if($f->A("FhemType") == "FS20")
			switch($f->A("FhemModel")){
				case "fs20du":
				case "fs20st":
					$togggle = false;
					$onclick = "\$j('.fhemeControl:not(#controls_D".$f->getID().")').hide(); \$j('#controls_D".$f->getID()."').toggle();";
					if($f->A("FhemModel") == "fs20st"){
						$values = array("on" => "on", "off" => "off");
						$togggle = true;
						$onclick = OnEvent::rme($this, "toggleDevice", $f->getID(), "if(Fhem.doAutoUpdate) Fhem.requestUpdate();");
					}
					
					if($f->A("FhemModel") == "fs20du")
						$values = array("off" => "off", /*6, 12, 18,*/ "dim25%" => "25%", /*31, 37, 43,*/ "dim50%" => "50%", /*56, 62, 68,*/ "dim75%" => "75%", /*81, 87, 93,*/ "dim100%" => "100%");

					$controls = $this->getSetTable("D".$f->getID(), $values);

					if($togggle)
						$controls = "";
					
					$html .= "<div id=\"FhemControlID_".$f->getID()."\" onclick=\"$onclick\" style=\"cursor:pointer;\" class=\"touchButton\">
							$controls
							<div id=\"FhemID_".$f->getID()."\">
								
							</div>
						</div>";

				break;

				case "fs20irf":

					$html .= "
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

					$html .= "
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

		if($f->A("FhemType") == "HMCCUDEV"){

			#$togggle = false;
			#$onclick = "\$j('.fhemeControl:not(#controls_D".$f->getID().")').hide(); \$j('#controls_D".$f->getID()."').toggle();";
			
			$values = array("on" => "on", "off" => "off");
			#$togggle = true;
			$onclick = OnEvent::rme($this, "toggleDevice", $f->getID(), "if(Fhem.doAutoUpdate) Fhem.requestUpdate();");
			

			#$controls = $this->getSetTable("D".$f->getID(), $values);

			#if($togggle)
			#	$controls = "";

			$html .= "<div id=\"FhemControlID_".$f->getID()."\" onclick=\"$onclick\" style=\"cursor:pointer;\" class=\"touchButton\">
					$controls
					<div id=\"FhemID_".$f->getID()."\">

					</div>
				</div>";

		}
		
		if($f->A("FhemType") == "dummy"){
			$onclick = "\$j('.fhemeControl:not(#controls_D".$f->getID().")').hide(); \$j('#controls_D".$f->getID()."').toggle();";

			$values = array("on" => "on", "off" => "off");
			$onclick = OnEvent::rme($this, "toggleDevice", $f->getID(), "if(Fhem.doAutoUpdate) Fhem.requestUpdate();");

			$html .= "<div id=\"FhemControlID_".$f->getID()."\" onclick=\"$onclick\" style=\"cursor:pointer;\" class=\"touchButton\">
					
					<div id=\"FhemID_".$f->getID()."\">

					</div>
				</div>";
		}
			
		if($f->A("FhemType") == "IT")
			switch($f->A("FhemITModel")){
				case "itdimmer":
				case "itswitch":
					$onclick = "";
					if($f->A("FhemITModel") == "itswitch")
						$values = array("on" => "on", "off" => "off");

					if($f->A("FhemITModel") == "itdimmer")
						$values = array("off" => "Off", /*6, 12, 18, 25,*/ "dimdown" => "Down", /*31, 37, 43, 50,*/ "dimup" => "Up", /*56, 62, 68, 75, 81, 87, 93, 100*/ "on" => "On");

					$controls = $this->getSetTable("D".$f->getID(), $values);

					$html .= "<div onclick=\"\$j('.fhemeControl:not(#controls_D".$f->getID().")').hide(); \$j('#controls_D".$f->getID()."').toggle();\" style=\"cursor:pointer;width:210px;float:left;min-height:15px;border-radius:5px;border-width:1px;border-style:solid;margin:5px;padding:5px;\" class=\"borderColor1\">
							$controls
							<div id=\"FhemID_".$f->getID()."\">
								<b>".$f->A("FhemName")."</b>
							</div>
						</div>";

				break;
			}

		if($f->A("FhemType") == "CUL_HM")
			switch($f->A("FhemHMModel")){
				case "dimmer":
				case "HM-LC-Dim1L-CV":
				case "HM-LC-Dim1L-Pl":
				case "HM-LC-Dim1PBU-FM":
				case "HM-LC-Dim1T-CV":
				case "HM-LC-Dim1T-Pl":
				case "HM-LC-Dim2L-SM":
				case "HM-LC-Dim2T-SM":
				case "switch":
				case "HM-LC-Sw1-FM":
			    case "HM-LC-Sw1-Pl":
			    case "HM-LC-Sw1-SM":
			    case "HM-LC-Sw1PB-FM":
			    case "HM-LC-Sw1PBU-FM":
			    case "HM-LC-Sw2-FM":
			    case "HM-LC-Sw4-WM":
					$onclick = "";

					if($f->A("FhemHMModel") == "switch" || $f->A("FhemHMModel") == "HM-LC-Sw1-Pl" || $f->A("FhemHMModel") == "HM-LC-Sw1-FM"  || $f->A("FhemHMModel") == "HM-LC-Sw1PB-FM" || $f->A("FhemHMModel") == "HM-LC-Sw1-SM" || $f->A("FhemHMModel") == "HM-LC-Sw1PBU-FM" || $f->A("FhemHMModel") == "HM-LC-Sw4-WM" || $f->A("FhemHMModel") == "HM-LC-Sw2-FM")
						$values = array("on" => "on", "off" => "off");

					if($f->A("FhemHMModel") == "dimmer" || $f->A("FhemHMModel") == "HM-LC-Dim1PBU-FM"  || $f->A("FhemHMModel") == "HM-LC-Dim1T-Pl" || $f->A("FhemHMModel") == "HM-LC-Dim1L-Pl" || $f->A("FhemHMModel") == "HM-LC-Dim1L-CV" || $f->A("FhemHMModel") == "HM-LC-Dim1T-CV" || $f->A("FhemHMModel") == "HM-LC-Dim2T-SM" || $f->A("FhemHMModel") == "HM-LC-Dim2L-SM")
						$values = array("off" => "off", /*6, 12, 18,*/ "25%" => "25%", /*31, 37, 43,*/ "50%" => "50%", /*56, 62, 68,*/ "75%" => "75%", /*81, 87, 93,*/ "100%" => "100%");

					$controls = $this->getSetTable("D".$f->getID(), $values);

					$html .= "<div onclick=\"\$j('.fhemeControl:not(#controls_D".$f->getID().")').hide(); \$j('#controls_D".$f->getID()."').toggle();\" style=\"cursor:pointer;width:210px;float:left;min-height:15px;border-radius:5px;border-width:1px;border-style:solid;margin:5px;padding:5px;\" class=\"borderColor1\">
							$B$controls
							<div id=\"FhemID_".$f->getID()."\">
								<b>".$f->A("FhemName")."</b>
							</div>
						</div>";

				break;
				
				case "HM-Sec-RHS":
					$html .= "<div id=\"FhemControlID_".$f->getID()."\" onclick=\"\" style=\"cursor:pointer;\" class=\"touchButton\">
							<div id=\"FhemID_".$f->getID()."\">
								
							</div>
						</div>";
				break;
			}

		if($f->A("FhemType") == "CUL_EM")
			switch($f->A("FhemEMModel")){

				case "EMEM":

					$html .= "<div onclick=\"\$j('.fhemeControl:not(#controls_D".$f->getID().")').hide(); \$j('#controls_D".$f->getID()."').toggle();\" style=\"cursor:pointer;width:210px;float:left;min-height:15px;border-radius:5px;border-width:1px;border-style:solid;margin:5px;padding:5px;\" class=\"borderColor1\">
							$B$controls
							<div id=\"FhemID_".$f->getID()."\">
								<b>".$f->A("FhemName")."</b>
							</div>
						</div>";

				break;
			}

		/*if($f->A("FhemType") == "RGB")
			$html = "
					<script type=\"text/javascript\">
						Fhem.startRGBSlider('".$f->getID()."', 256);
					</script>


					<fieldset
						style=\"
							float:left;
							margin-left:".($f->getID() != "timer" ? "50px" : "0").";
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
					</fieldset>";*/
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

			$controls->addCellEvent(1, "click", OnEvent::rme($this, "setDevice", array(str_replace(array("D", "H"), "", $DeviceID), "'$v'"), "function(){ \$j('#controls_$DeviceID').hide(); if(Fhem.doAutoUpdate) Fhem.requestUpdate(); }"));

			$i++;
		}
		$controls = "<div id=\"controls_$DeviceID\" style=\"display:none;width:50px;position:absolute;margin-left:150px;border-style:solid;border-width:1px;padding:3px;\" class=\"borderColor1 backgroundColor0 fhemeControl\">$controls</div>";

		return $controls;
	}

	public function setDevice($id, $action){
		if($id == "timer") {
			$_SESSION["BPS"]->setProperty("mFhemTimer", "FhemValue", $action);
			return;
		}
		$F = new Fhem($id);

		$S = new FhemServer($F->A("FhemServerID"));

		switch($S->A("FhemServerType")){
			case "0":
				try {
					$T = new Telnet($S->A("FhemServerIP"), $S->A("FhemServerPort"));
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
	
	public function toggleDevice($FhemID){
		$status = $this->getDeviceStatus($FhemID);

		if($status == "unreachable")
			return;
		
		if($status == null)
			$this->setDevice($FhemID, "off");
		
		if($status == "on")
			$this->setDevice($FhemID, "off");
		
		if($status == "off" OR $status == "Initialized")
			$this->setDevice($FhemID, "on");
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
				#$T->fireAndForget('define fht_setdate notify fht_setdate { if ( $year gt 2010 && $wday == 1 ) { my @@fhts=devspec2array("TYPE=FHT");; foreach(@@fhts) { my $cmd="set ".$_." date time";;  fhem $cmd;;  Log 4, "sent cmd ".$cmd;;   } } else { Log 1, "error setting date for fhts: year <= 2010 - date invalid?!" } }');
				#$T->fireAndForget("define t_fht_setdate at *04:00:00 trigger fht_setdate");
				$T->fireAndForget('define fht_dateupdate at *04:00:01 {if ($wday == 4)  { fhem("set TYPE=FHT date") } }');
				$T->fireAndForget('define fht_timeupdate at *04:00:01 {if ($wday == 5)  { fhem("set TYPE=FHT time") } }');
				
				
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
		$this->registerType($tab, "IT");
		$this->registerType($tab, "CUL_HM");
		$this->registerType($tab, "CUL_EM");
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
	
	private function getDeviceStatus($FhemID){
		$F = new Fhem($FhemID);
		$S = new FhemServer($F->A("FhemServerID"));
		
		try {
			$T = new Telnet($S->A("FhemServerIP"), $S->A("FhemServerPort"));
			$T->setPrompt("</FHZINFO>");
			$answer = $T->fireAndGet("xmllist")."</FHZINFO>";
		} catch(Exception $e) {
			return null;
		}
		
		$x = simplexml_load_string($answer);
		
		if(isset($x->FS20_LIST->FS20) AND count($x->FS20_LIST->FS20) > 0)
			foreach($x->FS20_LIST->FS20 AS $k => $v){
				if($v->attributes()->name != $F->A("FhemName"))
					continue;
				
				return $v->attributes()->state;
			}
			
		if(isset($x->HMCCUDEV_LIST->HMCCUDEV) AND count($x->HMCCUDEV_LIST->HMCCUDEV) > 0)
			foreach($x->HMCCUDEV_LIST->HMCCUDEV AS $k => $v){
				if($v->attributes()->name != $F->A("FhemName"))
					continue;
				
				$state = $v->attributes()->state;
				if($state == "Initialized")
					$state = "off";
					
				return $state;
			}
			
		if(isset($x->HUEDevice_LIST->HUEDevice) AND count($x->HUEDevice_LIST->HUEDevice))
			foreach($x->HUEDevice_LIST->HUEDevice AS $k => $v){
				if($v->attributes()->name != $F->A("FhemName"))
					continue;
				
				return $v->attributes()->state;
			}
			
		return null;
	}
	
	public function updateGUI($ID = null){
		$result = array();
		$S = new mFhemServerGUI();
		$S->addAssocV3("FhemServerType", "=", "0");

		while($s = $S->getNextEntry()){
			try {
				libxml_use_internal_errors(true);
				$x = simplexml_load_string(utf8_encode($s->getListXML()));
				if ($x === false) {
					echo "Laden des XML fehlgeschlagen\n";
					foreach(libxml_get_errors() as $error) {
						echo "\t", $error->message;
					}
				}
			} catch(NoServerConnectionException $e) {
				continue;
			}
			
			if(isset($x->HUEDevice_LIST->HUEDevice) AND count($x->HUEDevice_LIST->HUEDevice))
				foreach($x->HUEDevice_LIST->HUEDevice AS $k => $v){
					$F = new mFhemGUI();
					$F->addAssocV3("FhemServerID","=",$s->getID());
					$F->addAssocV3("FhemName","=",$v->attributes()->name);

					$F = $F->getNextEntry();
					
					if($F == null)
						continue;
					
					if($ID AND $F->getID() != $ID)
						continue;

					$state = $v->attributes()->state;

					#if($F->A("FhemModel") == "fs20irf") $state = "off";

					$state = strtolower(str_replace("dim", "", $state));

					$FS = new Button("", "./fheME/Fhem/off.png", "icon");
					$FS->style("float:left;margin-right:5px;");

					if($state != "off" && $state != "aus" && $state != "initialized")
						$FS->image("./fheME/Fhem/on.png");

					if($state == "unreachable"){
						$FS->image("./fheME/Fhem/off.png");
						$FS->style ("float:left;margin-right:5px;opacity:0.5;");
					}
					
					#if(!is_numeric(str_replace("%", "", $state)))
					#	$state = "";

					$trigger = [];
					if(isset($x->at_LIST->at) AND count($x->at_LIST->at)){
						foreach($x->at_LIST->at AS $kat => $vat){
							if($vat->attributes()->name != "auto_".$F->A("FhemName")."_ein" AND $vat->attributes()->name != "auto_".$F->A("FhemName")."_aus")
								continue;
							
							foreach($vat->INT AS $int){
								if($int->attributes()->key != "TRIGGERTIME")
									continue;
								
								$trigger[str_replace("auto_".$F->A("FhemName")."_", "", $vat->attributes()->name)] = date("H:i", $int->attributes()->value."");
							}
							
							
						}
					}
					
					$result[$F->getID()] = array("model" => $F->A("FhemModel"), "state" => "$FS<b>".($F->A("FhemAlias") == "" ? $F->A("FhemName") : $F->A("FhemAlias"))."</b><br><small style=\"color:grey;\" id=\"FhemID_".$F->getID()."State\">".(count($trigger) ? (isset($trigger["ein"]) ? $trigger["ein"]." - " : "").$trigger["aus"] : "")."</small><div style=\"clear:both;\"></div>");
					
				}
			
			if(isset($x->HMCCUDEV_LIST->HMCCUDEV) AND count($x->HMCCUDEV_LIST->HMCCUDEV) > 0)
				foreach($x->HMCCUDEV_LIST->HMCCUDEV AS $k => $v){
					$F = new mFhemGUI();
					$F->addAssocV3("FhemServerID","=",$s->getID());
					$F->addAssocV3("FhemName","=",$v->attributes()->name);

					$F = $F->getNextEntry();
					
					if($F == null)
						continue;
					
					if($ID AND $F->getID() != $ID)
						continue;

					$state = $v->attributes()->state;
					if($state == "Initialized")
						$state = "off";
					#if($F->A("FhemModel") == "fs20irf") $state = "off";

					#$state = strtolower(str_replace("dim", "", $state));

					$FS = new Button("", "./fheME/Fhem/off.png", "icon");
					$FS->style("float:left;margin-right:5px;");

					if($state != "off" && $state != "aus")
						$FS->image("./fheME/Fhem/on.png");

					if(!is_numeric(str_replace("%", "", $state)))
						$state = "";

					$result[$F->getID()] = array("model" => $F->A("FhemModel"), "state" => "$FS<b>".($F->A("FhemAlias") == "" ? $F->A("FhemName") : $F->A("FhemAlias"))."</b> <small style=\"color:grey;\">$state</small><div style=\"clear:both;\"></div>");
				}
				
			if(isset($x->dummy_LIST->dummy) AND count($x->dummy_LIST->dummy) > 0)
				foreach($x->dummy_LIST->dummy AS $k => $v){
					$F = new mFhemGUI();
					$F->addAssocV3("FhemServerID","=",$s->getID());
					$F->addAssocV3("FhemName","=",$v->attributes()->name);

					$F = $F->getNextEntry();
					
					if($F == null)
						continue;
					
					if($ID AND $F->getID() != $ID)
						continue;
					

					$state = $v->attributes()->state;

					#if($F->A("FhemModel") == "fs20irf") $state = "off";

					#$state = strtolower(str_replace("dim", "", $state));

					$FS = new Button("", "./fheME/Fhem/off.png", "icon");
					$FS->style("float:left;margin-right:5px;");

					if($state != "off" && $state != "aus")
						$FS->image("./fheME/Fhem/on.png");

					if(!is_numeric(str_replace("%", "", $state)))
						$state = "";

					$return = "$FS<b>".($F->A("FhemAlias") == "" ? $F->A("FhemName") : $F->A("FhemAlias"))."</b> <small style=\"color:grey;\">$state</small><div style=\"clear:both;\"></div>";
					if($F->A("FhemExtension") != "none" AND $F->A("FhemExtension") != ""){
						$c = $F->A("FhemExtension");
						$c = new $c(-1);
						$return = $c->process($F, $v);
					}
					
					$result[$F->getID()] = array("model" => $F->A("FhemModel"), "state" => $return);
				}
				
			if(isset($x->FS20_LIST->FS20) AND count($x->FS20_LIST->FS20) > 0)
				foreach($x->FS20_LIST->FS20 AS $k => $v){
					$F = new mFhemGUI();
					$F->addAssocV3("FhemServerID","=",$s->getID());
					$F->addAssocV3("FhemName","=",$v->attributes()->name);

					$F = $F->getNextEntry();
					
					if($F == null)
						continue;
					
					if($ID AND $F->getID() != $ID)
						continue;

					$state = $v->attributes()->state;

					if($F->A("FhemModel") == "fs20irf") $state = "off";

					$state = strtolower(str_replace("dim", "", $state));

					$FS = new Button("", "./fheME/Fhem/off.png", "icon");
					$FS->style("float:left;margin-right:5px;");

					if($state != "off" && $state != "aus")
						$FS->image("./fheME/Fhem/on.png");

					if(!is_numeric(str_replace("%", "", $state)))
						$state = "";

					$result[$F->getID()] = array("model" => $F->A("FhemModel"), "state" => "$FS<b>".($F->A("FhemAlias") == "" ? $F->A("FhemName") : $F->A("FhemAlias"))."</b> <small style=\"color:grey;\">$state</small><div style=\"clear:both;\"></div>");
				}

			if(isset($x->IT_LIST->IT) AND count($x->IT_LIST->IT) > 0)
				foreach($x->IT_LIST->IT AS $k => $v){
					$F = new mFhemGUI();
					$F->addAssocV3("FhemServerID","=",$s->getID());
					$F->addAssocV3("FhemName","=",$v->attributes()->name);

					$F = $F->getNextEntry();
					
					if($F == null)
						continue;
					
					if($ID AND $F->getID() != $ID)
						continue;

					$state = $v->attributes()->state;

					$state = strtolower(str_replace("dim", "", $state));

					$IT = new Button("", "./fheME/Fhem/off.png", "icon");
					$IT->style("float:right;margin-right:-10px;margin-top:-13px;margin-left:3px;");
					if($state != "off" && $state != "aus")
						$IT->image("./fheME/Fhem/on.png");


					$result[$F->getID()] = array("model" => $F->A("FhemITModel"), "state" => "$IT<b>".($F->A("FhemAlias") == "" ? $F->A("FhemName") : $F->A("FhemAlias"))."</b> ");

				}

			if(isset($x->CUL_HM_LIST->CUL_HM) AND count($x->CUL_HM_LIST->CUL_HM) > 0)
				foreach($x->CUL_HM_LIST->CUL_HM AS $k => $v){
					$F = new mFhemGUI();
					$F->addAssocV3("FhemServerID","=",$s->getID());
					$F->addAssocV3("FhemName","=",$v->attributes()->name);

					$F = $F->getNextEntry();
					
					if($F == null)
						continue;
					
					if($ID AND $F->getID() != $ID)
						continue;

					$result[$F->getID()] = $F->getStatus($v);
				}

			if(isset($x->CUL_EM_LIST->CUL_EM) AND count($x->CUL_EM_LIST->CUL_EM) > 0)
				foreach($x->CUL_EM_LIST->CUL_EM AS $em){
					$F = anyC::get("Fhem", "FhemServerID", $s->getID());
					$F->addAssocV3("FhemName", "=", $em->attributes()->name);
					$F = $F->getNextEntry();

					if($F == null)
						continue;
					
					if($ID AND $F->getID() != $ID)
						continue;

					foreach($em->STATE AS $state)
						if($state->attributes()->key == "current")
							$current = $state->attributes()->value;


					$result[$F->getID()] = array("model" => $F->A("FhemEMModel"), "state" => "<b>".($F->A("FhemAlias") == "" ? $F->A("FhemName") : $F->A("FhemAlias"))."</b><small style=\"color:grey;\">".$current."</small>");

				}

			if(isset($x->FHT_LIST->FHT) AND count($x->FHT_LIST->FHT) > 0){
				foreach($x->FHT_LIST->FHT AS $fht){
					$F = anyC::get("Fhem", "FhemServerID", $s->getID());
					$F->addAssocV3("FhemName", "=", $fht->attributes()->name);

					$F = $F->getNextEntry();
					
					if($F == null)
						continue;
					
					if($ID AND $F->getID() != $ID)
						continue;

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
						$B->style("float:left;margin-right:5px;");
					}

					if($warnings == "Window open"){
						$B = new Button("", "./fheME/Fhem/windowOpen.png", "icon");
						$B->style("float:left;margin-right:5px;");
					}

					if($warnings == "Battery low"){
						$B = new Button("", "./fheME/Fhem/batteryLow.png", "icon");
						$B->style("float:left;margin-right:5px;");
					}


					$Icon = new Button("", "./fheME/Fhem/fhemFHT.png", "icon");
					$Icon->style("float:left;margin-right:5px;");


					$result[$F->getID()] = array("model" => $F->A("FhemFHTModel"), "state" => "$Icon$M<b>".($F->A("FhemAlias") == "" ? $F->A("FhemName") : $F->A("FhemAlias"))."</b><small style=\"color:grey;\"> ".Util::CLFormatNumber(str_replace(".", ".", $measuredTemp), 1)."/".Util::CLFormatNumber(str_replace(".", ".", $desiredTemp), 1)."<span id=\"FhemID_".$F->getID()."TargetTemp\" data-value=\"desired-temp $desiredTemp\"></span> <small>({$actuator})</small>".($warnings != "none" ? "<br />$B{$warnings}" : "")."<div style=\"clear:both;\"></div>");
				}
			}
		}

		echo json_encode($result, defined("JSON_UNESCAPED_UNICODE") ? JSON_UNESCAPED_UNICODE : 0);
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