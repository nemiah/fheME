<?php
/*
 *  This file is part of phynx.

 *  phynx is free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 3 of the License, or
 *  (at your option) any later version.

 *  phynx is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.

 *  You should have received a copy of the GNU General Public License
 *  along with this program.  If not, see <http://www.gnu.org/licenses/>.
 * 
 *  2007 - 2013, Rainer Furtmeier - Rainer@Furtmeier.IT
 */
class TodoGUI extends Todo implements iGUIHTML2 {
	public $GUI;
	
	function __construct($ID){
		parent::__construct($ID);
		$this->setParser("TodoTillDay","Util::CLDateParser");
		$this->setParser("TodoTillTime","Util::CLTimeParser");

		$this->setParser("TodoFromDay","Util::CLDateParser");
		$this->setParser("TodoFromTime","Util::CLTimeParser");
		try {
			$this->GUI = new CRMHTMLGUI($this);
		} catch (ClassNotFoundException $e){
			$this->GUI = new HTMLGUIX($this);
		}
	}
	
	function getHTML($id){
		// <editor-fold defaultstate="collapsed" desc="Aspect:jP">
		try {
			$MArgs = func_get_args();
			return Aspect::joinPoint("around", $this, __METHOD__, $MArgs);
		} catch (AOPNoAdviceException $e) {}
		Aspect::joinPoint("before", $this, __METHOD__, $MArgs);
		// </editor-fold>
		
		$this->loadMeOrEmpty();
		$bps = $this->getMyBPSData();
		
		if($id == -1) {
			$this->A->TodoTillDay = Util::CLDateParser(time() + 7 * 24 * 3600);
			$this->A->TodoTillTime = Util::CLTimeParser(10*3600);

			$this->A->TodoFromDay = Util::CLDateParser(time() + 7 * 24 * 3600);
			$this->A->TodoFromTime = Util::CLTimeParser(9*3600);
			$this->A->TodoType = "2";
			if($bps != -1 AND isset($bps["TodoTillDay"])){
				$this->A->TodoTillDay = $bps["TodoTillDay"];
				$this->A->TodoFromDay = $bps["TodoTillDay"];
				
				BPS::unsetProperty("TodoGUI", "TodoTillDay");
			}
			
			if($bps != -1 AND isset($bps["TodoFromTime"])){
				$this->A->TodoFromTime = Util::CLTimeParser($bps["TodoFromTime"] * 3600);
				$this->A->TodoTillTime = Util::CLTimeParser(($bps["TodoFromTime"] + 1) * 3600);
				
				BPS::unsetProperty("TodoGUI", "TodoFromTime");
			}
			
			if($bps != -1 AND isset($bps["TodoDescription"])){
				$this->A->TodoDescription = $bps["TodoDescription"];
				
				BPS::unsetProperty("TodoGUI", "TodoDescription");
			}
			
			if($bps != -1 AND isset($bps["TodoLocation"])){
				$this->A->TodoLocation = $bps["TodoLocation"];
				
				BPS::unsetProperty("TodoGUI", "TodoLocation");
			}
			
			if($bps != -1 AND isset($bps["TodoName"])){
				$this->A->TodoName = $bps["TodoName"];
				
				BPS::unsetProperty("TodoGUI", "TodoName");
			}

			$this->A->TodoUserID = Session::currentUser()->getID();#"-1";
			$this->A->TodoClass = BPS::getProperty("mTodoGUI", "ownerClass");
			$this->A->TodoClassID = BPS::getProperty("mTodoGUI", "ownerClassID");
		}
		
		$gui = $this->GUI;

		$gui->name("Aktivität");

		$gui->label("TodoDescription","Details");
		$gui->label("TodoTillDay","Ende");
		$gui->label("TodoTillTime","Uhrzeit");
		$gui->label("TodoFromDay","Anfang");
		$gui->label("TodoFromTime","Uhrzeit");
		$gui->label("TodoType","Typ");
		$gui->label("TodoUserID","Zuständig");
		$gui->label("TodoStatus","Status");
		$gui->label("TodoRemind","Erinnerung");
		$gui->label("TodoName","Betreff");

		$gui->label("TodoRepeat","Wiederholen");
		#$gui->label("TodoRepeatInterval","Intervall");

		$gui->label("TodoClassID", "Kunde");
		$gui->label("TodoLocation", "Ort");
		$gui->label("TodoAllDay", "Ganzer Tag");
		
		$gui->space("TodoRemind", "Optionen");
		$gui->space("TodoFromDay", "Zeit");

		if($this->A("TodoFromDay") == "01.01.1970" AND $this->A("TodoFromTime") == "00:00"){
			$this->changeA("TodoFromDay", $this->A("TodoTillDay"));
			$this->changeA("TodoFromTime", $this->A("TodoTillTime"));
		}

		$gui->attributes(array(
			"TodoType",
			"TodoClass",
			"TodoClassID",
			"TodoDescription",
			"TodoLocation",
			"TodoFromDay",
			#"TodoFromTime",
			#"TodoTillTime",
			"TodoTillDay",
			"TodoAllDay",
			#"TodoRepeat",
			#"TodoRepeatInterval",
			"TodoRemind",
			#"TodoStatus",
			"TodoUserID"
			#"TodoIsPublic"
			));

		$gui->type("TodoType","select", TodoGUI::types());
		
		$gui->type("TodoRemind","select", array("-1" => "keine Erinnerung", "60" => "1 Minute vorher", "300" => "5 Minuten vorher", "600" => "10 Minuten vorher", "900" => "15 Minuten vorher", "1800" => "30 Minuten vorher", "2700" => "45 Minuten vorher", "3600" => "1 Stunde vorher"));

		$gui->type("TodoClass","hidden");
		$gui->type("TodoDescription","textarea");
		$gui->type("TodoAllDay", "checkbox");

		$gui->addFieldEvent("TodoAllDay", "onchange", "\$j('#TodoFromTimeDisplay').css('display', this.checked ? 'none' : 'inline'); \$j('#TodoTillTimeDisplay').css('display', this.checked ? 'none' : 'inline');");
		
		$gui->parser("TodoFromDay", "TodoGUI::dayFromParser");
		$gui->parser("TodoTillDay", "TodoGUI::dayTillParser");

		$allowed = array();
		$ACS = anyC::get("Userdata", "name", "shareCalendarTo".Session::currentUser()->getID());
		while($Share = $ACS->getNextEntry())
			$allowed[$Share->A("UserID")] = $Share->A("wert");
		
		$ac = Users::getUsers();
		$users = array();
		while($u = $ac->getNextEntry()){
			if(!isset($allowed[$u->getID()]) AND $u->getID() != Session::currentUser()->getID())
				continue;
			
			if(isset($allowed[$u->getID()]) AND strpos($allowed[$u->getID()], "create") === false)
				continue;
			
			$users[$u->getID()] = $u->A("name");			
		}
		
		$users["-1"] = "Alle";

		if(Session::isPluginLoaded("mWAdresse") AND ($this->A("TodoClass") == "WAdresse" OR $this->A("TodoClass") == "Kalender"))
			$gui->parser("TodoClassID", "TodoGUI::parserKunde");
		else
			$gui->type ("TodoClassID", "hidden");
		
		$gui->type("TodoUserID","select", $users);

		$gui->type("TodoStatus","select", $this->getStatus());
		
		$gui->activateFeature("CRMEditAbove", $this);
		
		if($gui instanceof CRMHTMLGUI)
			return $gui->getEditTableHTML(4);
	}
	
	public static function dayFromParser($w, $l, $E){
		$I = new HTMLInput("TodoFromDay", "text", $w);
		$I->style("width:90px;text-align:right;");
		$I->id("TodoFromDay123");
		$I->onchange("$('editTodoGUI').TodoTillDay.value = $('editTodoGUI').TodoFromDay.value;");
		
		$T = self::timeTillParser($E->A("TodoFromTime"), "TodoFromTime", $E);
		
		return "<div style=\"display:inline-block;width:120px;\"><span style=\"color:grey;\">am</span> ".$I."</div> ".$T."<script type=\"text/javascript\">\$j('#TodoFromDay123').datepicker(); \$j('#TodoFromTimeDisplay').parent().mouseenter(function(){ \$j('#TodoFromTimeTable').show(); }).mouseleave(function(){ \$j('#TodoFromTimeTable').hide(); });</script>";
	}
	
	public static function dayTillParser($w, $l, $E){
		$I = new HTMLInput("TodoTillDay", "text", $w);
		$I->style("width:90px;text-align:right;");
		$I->id("TodoTillDay123");
		
		$T = self::timeTillParser($E->A("TodoTillTime"), "TodoTillTime", $E);
		
		return "<div style=\"display:inline-block;width:120px;\"><span style=\"color:grey;\">bis</span> ".$I."</div> ".$T."<script type=\"text/javascript\">\$j('#TodoTillDay123').datepicker(); \$j('#TodoTillTimeDisplay').parent().mouseenter(function(){ \$j('#TodoTillTimeTable').show(); }).mouseleave(function(){ \$j('#TodoTillTimeTable').hide(); });</script>";
	}

	#public static function timeFromParser($w){
	#	return self::timeTillParser($w, "TodoFromTime");
	#}
	
	public static function timeTillParser($w, $f, $E){
		if($f == "")
			 $f = "TodoTillTime";
		
		$f2 = "TodoFromTime";
		if($f == "TodoFromTime")
			$f2 = "TodoTillTime";
		
		$rawTime = Util::CLTimeParser($w, "store");

		$down = floor($rawTime / (15 * 60)) * 15 * 60;
		$up = ceil($rawTime / (15 * 60)) * 15 * 60;

		if(Util::CLTimeParser($down) == $w){
			$down -= 15 * 60;
			$up += 15 * 60;
		}

		$I = new HTMLInput($f, "time", $w);
		$I->style("width:50px;text-align:right;");
		$I->id($f);
		if($f == "TodoFromTime")
			$I->connectTo("TodoTillTime");

		$values = array($down - 900 * 2, $down - 900, $down, $up, $up + 900);
		if($f == "TodoFromTime")
			$values = array($down, $up, $up + 900, $up + 900 * 2, $up + 900 * 3);
			
		$T = new HTMLTable(count($values));
		$T->setTableStyle("margin-top:5px;display:none;");
		$val = array_map("Util::CLTimeParser", $values);
		$T->addRow($val);
		$T->setTableID("{$f}Table");

		for($i = 1; $i < 6; $i++){
			#$T->setColClass($i, "");
			
			$T->setColWidth($i, (100 / count($values))."%");
			$T->addCellStyle($i, "cursor:pointer;text-align:center;color:grey;");
			$T->addCellEvent($i, "mouseover", "this.className = 'backgroundColor0';");
			$T->addCellEvent($i, "mouseout", "this.className = '';");
			
			$T->addCellEvent($i, "click", "$('$f').value = '".Util::CLTimeParser($values[$i-1])."'; ".($f == "TodoFromTime" ? "$('$f2').value = '".Util::CLTimeParser($values[$i-1] + 3600)."';" : ""));
		}
		return "<span id=\"{$f}Display\" style=\"".($E->A("TodoAllDay") ? "display:none;" : "")."\" ><span style=\"color:grey;margin-left:30px;\">um</span> ".$I.($w != "" ? $T : "")."</span>";
	}

	public static function parserKunde($w, $l, $E){
		$I = new HTMLInput("TodoClassID", "hidden", $w);

		$T = "";
		if($E->A("TodoClass") == "WAdresse" AND $E->A("TodoClassID") > 0){
			$A = new WAdresseGUI($E->A("TodoClassID"));
			$T = $A->getShortAddress();
		}
		$IK = new HTMLInput("TodoKunde", "text", $T);
		$IK->autocomplete("mWAdresse", "function(selection){ $('editTodoGUI').TodoClass.value = 'WAdresse'; $('editTodoGUI').TodoClassID.value = selection.value; $('editTodoGUI').TodoKunde.value = selection.label; if($('editTodoGUI').TodoName) contentManager.toggleFormFields('hide', ['TodoName'], 'editTodoGUI'); return false; }");
		return $I.$IK;
	}
	
	static function getPriorities($nr = -1){
		$a = array("1" => "hoch", "2" => "normal", "3" => "niedrig");
		if($nr == -1) return $a;
		else return $a[$nr];
	}
	
	public function setStatus($nr){
		$this->changeA("TodoStatus", $nr);
		$this->saveMe();
	}
	
	static function getStatus($nr = -1){
		$a = array(
			"0" => "Nicht begonnen",
			#"1" => "In Bearbeitung",
			"2" => "Erledigt",
			#"3" => "Wartestatus",
			#"4" => "Verschoben",
			#"5" => "Abgebrochen"
			);
		if($nr == -1) return $a;
		else return $a[$nr];
	}
	
	public static function typesImage($nr, $getPath = false){
		// <editor-fold defaultstate="collapsed" desc="Aspect:jP">
		try {
			$MArgs = func_get_args();
			return Aspect::joinPoint("around", null, __METHOD__, $MArgs);
		} catch (AOPNoAdviceException $e) {}
		Aspect::joinPoint("before", null, __METHOD__, $MArgs);
		// </editor-fold>
		
		$types = TodoGUI::types();
		
		if($getPath === true)
			return "./ubiquitous/Todo/".$types[$nr].".png";

		return "<img title=\"".$types[$nr]."\" src=\"./ubiquitous/Todo/".$types[$nr].".png\" />";
	}
	
	public static function types($nr = null){
		// <editor-fold defaultstate="collapsed" desc="Aspect:jP">
		try {
			$MArgs = func_get_args();
			return Aspect::joinPoint("around", null, __METHOD__, $MArgs);
		} catch (AOPNoAdviceException $e) {}
		Aspect::joinPoint("before", null, __METHOD__, $MArgs);
		// </editor-fold>

		$types = array(1 => "Anruf", 2 => "Termin");

		if($nr == null) return $types;
		else return $types[$nr];
	}
	
	public function saveMultiEditField($field,$value){
		if($field != "TodoStatus" AND $field != "TodoPercent") return;
		
		$T = new mTeilnehmerGUI();
		$T->addAssocV3("TeilnehmerUserID", "=",$_SESSION["S"]->getCurrentUser()->getID());
		$T->addAssocV3("TeilnehmerTodoID","=",$this->ID);
		$T->lCV3();
		if($T->numLoaded() != 1) return;
		
		if($this->A == null) $this->loadMe();
		$this->A->$field = $value;
		$this->saveMe();
		
		if($field == "TodoStatus") echo "message:ToDoMessages.M001";
		else echo "message:";
	}
	
	public function loadDetails(){
		echo $this->getHTML($this->ID, "window");
		return;
		$this->setParser("TodoFromDay","Util::nothingParser");
		$this->setParser("TodoFromTime","Util::nothingParser");
		
		$this->setParser("TodoTillDay","Util::nothingParser");
		$this->setParser("TodoTillTime","Util::nothingParser");
		$this->loadMe();
		
		$head = "<div id=\"TodoDetailsHandler$this->ID\" class=\"backgroundColor1 cMHeader\">Detailansicht ToDo \"".$this->A->TodoName."\"</div>";
		
		try {
			$T = new mTeilnehmerGUI();
			$T->classID = $this->ID;
			$T->className = "Todo";
			$T->addAssocV3("TeilnehmerTodoID","=", $this->ID);
			$T->addJoinV3("User","TeilnehmerUserID","=","UserID");
			$T->addOrderV3("TeilnehmerTeamID");
			
			$table = "
			<table style=\"width:100%;border:0px;\">
				<colgroup>
					<col />
				</colgroup>";
			
			$teilnehmerID = 0;
			$teilnehmerStatus = -1;
			
			$cu = $_SESSION["S"]->getCurrentUser()->getID();
			
			while($s = $T->getNextEntry()){
				if($s->getA()->TeilnehmerUserID == $cu) {
					$teilnehmerID = $s->getID();
					$teilnehmerStatus = $s->getA()->TeilnehmerStatus;
				}
				$table .= "
				<tr>
					<td style=\"padding-left:0px;padding-top:0px;\">".$s->getA()->name."</td>
				</tr>";
			}
			$table .= "
			</table>";
		} catch (ClassNotFoundException $e){ $table = "";}
		
		
		$t = new HTMLTable(2,"Detailansicht ToDo \"".$this->A->TodoName."\"");
		$t->addColStyle(1, "text-align:right;");
		$t->addColStyle(1, "width:110px;");
		
		#<input class=\"multiEditInput2\" onfocus=\"oldValue = this.value;\" onblur=\"if(oldValue != this.value) saveMultiEditInput('Posten','".$t->getID()."','preis');\" value=\"$ta->preis\" id=\"preisID".$t->getID()."\" type=\"text\" onkeyup=\"updateEKs('".$t->getID()."');\" onkeydown=\"if(event.keyCode == 13) saveMultiEditInput('Posten','".$t->getID()."','preis');\"  />
		
		if($teilnehmerID != 0) {
			$st = $this->getStatus();
			$o = "";
			foreach($st AS $k => $v)
				$o .= "<option ".($k == $this->A->TodoStatus ? "selected=\"selected\"" : "")." value=\"$k\">$v</option>";
			$status = "<select id=\"TodoStatusID$this->ID\" onchange=\"saveMultiEditInput('Todo','".$this->getID()."','TodoStatus');\">$o</select>";
		}
		else $status = $this->getStatus($this->A->TodoStatus);
		
		$t->addRow(array("Priorität:", $this->getPriorities($this->A->TodoPriority)));
		
		$t->addRow(array("Status:", $status));
		
		if($teilnehmerID != 0) {
			
			$percent = "
			<select id=\"TodoPercentID$this->ID\" onchange=\"ToDo.onChange();\">";
			for($i = 0; $i <= 100; $i+=5)
				$percent .= "
				<option ".($this->A->TodoPercent == $i ? "selected=\"selected\"" : "")." value=\"$i\">$i %</option>";
			$percent .= "
			</select>
			<input type=\"hidden\" id=\"TodoID\" value=\"$this->ID\" />";
			
			/*<input type=\"text\" readonly=\"readonly\" id=\"TodoPercentID$this->ID\" value=\"{$this->A->TodoPercent}\" style=\"width:30px;float:right;text-align:right;margin-right:13px;\" />
			
			<div id=\"prozentSlider$this->ID\" style=\"height:19px;width:4px;background-color:black;cursor:move;margin-bottom:-17px;\"></div>
			<div id=\"prozentTrack$this->ID\" style=\"width:80%;height:15px;\" class=\"backgroundColor1\" />";
		*/
		}
		else $percent = $this->A->TodoPercent;
		
		$t->addRow(array("Prozent:", $percent));
		
		if($this->A->TodoDescription != "") {
			$t->addRow("");
			$t->addRowClass("backgroundColor0");
			$t->addRow(array("Beschreibung:",nl2br($this->A->TodoDescription)));
		}
		$t->addRow("");
		$t->addRowClass("backgroundColor0");
		
		$t->addRow(array("Beginn am:",Util::CLDateParserL($this->A->TodoFromDay)));
		$t->addRow(array("Fällig bis:",Util::CLDateParserL($this->A->TodoTillDay)));

		$t->addRow("");
		$t->addRowClass("backgroundColor0");

		echo $t->getHTML();
	}
	
	public function newMe($checkUserData = true, $output = false) {
		echo parent::newMe($checkUserData, $output);
	}
	
	public function saveMe($checkUserData = true, $output = false) {
		parent::saveMe($checkUserData, $output);
		echo $this->getID();
	}
}
?>