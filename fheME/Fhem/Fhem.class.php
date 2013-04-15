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
class Fhem extends PersistentObject implements iCloneable {
	public static $connections = array();

	public function getDefineCommand(){

		$tel2 = "";
		if($this->A("FhemType") == "FS20" || $this->A("FhemType") == "IT" || $this->A("FhemType") == "CUL_HM"){
			$value = "off";
			if($this->A("FhemModel") == "fs20rsu") $value = "on";

			$tel2 = "set ".$this->A("FhemName")." $value";
		}

		$tel13 = "";
		if($this->A("FhemRoom") == true)
			$tel13 = "attr ".$this->A("FhemName")." "."room"." ".$this->A("FhemRoom");

		$tel14 = "";
		if($this->A("FhemType") == "FS20")
			$tel14 = ($this->A("FhemModel"));

		if($this->A("FhemType") == "IT")
			$tel14 = ($this->A("FhemITModel"));

		if($this->A("FhemType") == "CUL_HM")
			$tel14 = ($this->A("FhemHMModel"));

		if($this->A("FhemType") == "CUL_EM")
			$tel14 = ($this->A("FhemEMModel"));

		if($this->A("FhemType") == "FHT")
			$tel14 = ($this->A("FhemFHTModel"));

		$tel15 = "";
		if($this->A("FhemModel") || $this->A("FhemITModel") || $this->A("FhemHMModel") || $this->A("FhemEMModel") || $this->A("FhemFHTModel") == true)
			$tel15 = "attr ".$this->A("FhemName")." "."model"." ".$tel14;

		$tel16 = "";
		if($this->A("FhemHMSub") == true)
			$tel16 = "attr ".$this->A("FhemName")." "."subType"." ".$this->A("FhemHMSub");

		$tel17 = "";
		if($this->A("FhemHMClass") == true)
			$tel17 = "attr ".$this->A("FhemName")." "."hmClass"." ".$this->A("FhemHMClass");
	
		if($this->A("FhemType") == "notify")
			return array("define ".$this->A("FhemName")." notify ".$this->A("FhemRunOn")." ".str_replace("\n"," ",$this->A("FhemCommand")), $tel13);

		if($this->A("FhemType") == "FHT")
			return array("define ".$this->A("FhemName")." FHT ".$this->A("FhemSpecific"), "set ".$this->A("FhemName")." report2 255", $tel13, $tel15);
			
        if($this->A("FhemType") == "FS20" || $this->A("FhemType") == "IT")
		    return array("define ".$this->A("FhemName")." ".$this->A("FhemType")." ".$this->A("FhemSpecific"), $tel2, $tel13, $tel15);
		    			
        if($this->A("FhemType") == "CUL_EM")
		    return array("define ".$this->A("FhemName")." ".$this->A("FhemType")." ".$this->A("FhemSpecific"), $tel13, $tel15);
		    		    			
        if($this->A("FhemType") == "dummy")
		    return array("define ".$this->A("FhemName")." ".$this->A("FhemType")." ".$this->A("FhemSpecific"), $tel13);
		    		    			
        if($this->A("FhemType") == "CUL_HM")
		    return array("define ".$this->A("FhemName")." ".$this->A("FhemType")." ".$this->A("FhemSpecific"), $tel2, $tel13, $tel15, $tel16, $tel17);
		
		return array("define ".$this->A("FhemName")." ".$this->A("FhemType")." ".$this->A("FhemSpecific"), $tel2);
	}

	function getAvailableOptions(){
		switch($this->A->FhemModel){
			case "fs20du":
				return array(
				"On" => "on",
				"100%" => "dim100%",
				"93%" => "dim93%",
				"87%" => "dim87%",
				"81%" => "dim81%",
				"75%" => "dim75%",
				"68%" => "dim68%",
				"62%" => "dim62%",
				"56%" => "dim56%",
				"50%" => "dim50%",
				"43%" => "dim43%",
				"37%" => "dim37%",
				"31%" => "dim31%",
				"25%" => "dim25%",
				"18%" => "dim18%",
				"12%" => "dim12%",
				"6%" => "dim06%",
				"Off" => "off");

			break;
			case "fs20st":
				return array(
				"On" => "on",
				"Off" => "off");

			break;

        switch($this->A->FhemITModel){
			case "itdimmer":
				return array(
				"On" => "on",
				"Up" => "dimup",
				"Down" => "dimdown",
				"Off" => "off");

			break;

			case "itswitch":
				return array(
				"On" => "on",
				"Off" => "off");

			break;

		switch($this->A->FhemHMModel){
			case "dimmer" || "HM-LC-Dim1PBU-FM" || "HM-LC-Dim1T-Pl" || "HM-LC-Dim1L-Pl" || "HM-LC-Dim1L-CV" || "HM-LC-Dim1T-CV" || "HM-LC-Dim2T-SM" || "HM-LC-Dim2L-SM":
				return array(
				"On" => "on",
				"100%" => "dim100%",
				"93%" => "dim93%",
				"87%" => "dim87%",
				"81%" => "dim81%",
				"75%" => "dim75%",
				"68%" => "dim68%",
				"62%" => "dim62%",
				"56%" => "dim56%",
				"50%" => "dim50%",
				"43%" => "dim43%",
				"37%" => "dim37%",
				"31%" => "dim31%",
				"25%" => "dim25%",
				"18%" => "dim18%",
				"12%" => "dim12%",
				"6%" => "dim06%",
				"Off" => "off");

			break;

			case "switch" || "HM-LC-Sw1-Pl" || "HM-LC-Sw1-FM" || "HM-LC-Sw1-SM"  || "HM-LC-Sw1PB-FM"  || "HM-LC-Sw1PBU-FM" || "HM-LC-Sw4-WM" || "HM-LC-Sw2-FM":
				return array(
				"On" => "on",
				"Off" => "off");

			break;

			default:
				return array();
		}
	   }
	  }
	}

	public function getData(){
		$S = new FhemServer($this->A("FhemServerID"));

		try {
			if(!isset(self::$connections[$S->A("FhemServerIP")]))
				self::$connections[$S->getID()] = new Telnet($S->A("FhemServerIP"), $S->A("FhemServerPort"));

			$T = self::$connections[$S->getID()];
		} catch(NoServerConnectionException $e){
			die("error:'The connection to the server with IP-address ".$S->A("FhemServerIP")." could not be established!'");
		}

		$T->setPrompt("</FHZINFO>");
		$answer = $T->fireAndGet("xmllist")."</FHZINFO>";

		$simpleXML = new SimpleXMLElement($answer);

		$S = array();
		$I = array();

		if($this->A("FhemType") == "FHT")
			$target = $simpleXML->FHT_LIST->FHT;

		if($this->A("FhemType") == "FS20")
			$target = $simpleXML->FS20_LIST->FS20;

		if($this->A("FhemType") == "IT")
			$target = $simpleXML->IT_LIST->IT;

		if($this->A("FhemType") == "CUL_HM")
			$target = $simpleXML->CUL_HM_LIST->CUL_HM;

		if($this->A("FhemType") == "CUL_EM")
			$target = $simpleXML->CUL_EM_LIST->CUL_EM;

		if($this->A("FhemType") == "notify")
			$target = $simpleXML->notify_LIST->notify;

		if(isset($target))
			foreach($target AS $fht){
				if($fht->attributes()->name != $this->A("FhemName"))
					continue;

				foreach($fht->STATE AS $state)
					$S[] = array(0 => $state->attributes()->key, 1 => $state->attributes()->value, 2 => $state->attributes()->measured, "key" => $state->attributes()->key, "value" => $state->attributes()->value, "date" => $state->attributes()->measured);#$TabS->addRow(array($state->attributes()->key, $state->attributes()->value, "<small>".$state->attributes()->measured."</small>"));

				foreach($fht->INT AS $int)
					$I[] = array(0 => $int->attributes()->key, 1 => $int->attributes()->value, "key" => $int->attributes()->key, "value" => $int->attributes()->value);#$TabI->addRow(array($int->attributes()->key, $int->attributes()->value, ""));
			}

		return array($S, $I);
		#$T->disconnect();
	}

	public static function disconnectAll(){
		foreach(self::$connections AS $c)
			$c->disconnect();
	}

	public function cloneMe(){
		echo $this->newMe();
	}
	
	public function getStatus($data){
		$state = "unknown";
		
		$B = new Button("", "./fheME/Fhem/off.png", "icon");
		$B->style("float:left;margin-right:5px;");
		
		switch($this->A("FhemType")){
			case "CUL_HM":
				$state = FhemHM::status($data, $this);
				FhemHM::icon($data, $this, $B);
			break;
		}
		
		return array("state" => "$B<b>".($this->A("FhemAlias") == "" ? $this->A("FhemName") : $this->A("FhemAlias"))."</b> <small style=\"color:grey;\">$state</small><div style=\"clear:both;\">");
	}

}
?>