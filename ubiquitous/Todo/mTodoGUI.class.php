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
 *  2007 - 2012, Rainer Furtmeier - Rainer@Furtmeier.de
 */
class mTodoGUI extends mTodo implements iGUIHTMLMP2, iKalender {
	public function  __construct() {
		parent::__construct();
		$this->customize();
	}
	public function setOwner($class, $id){
		$_SESSION["BPS"]->setProperty(get_class($this), "ownerClass", $class);
		$_SESSION["BPS"]->setProperty(get_class($this), "ownerClassID", $id);
	}
	
	public function getHTML($id, $page){
		$bps = $this->getMyBPSData();
		$this->setParser("TodoTillDay","Util::CLDateParser");
		$this->setParser("TodoTillTime","Util::CLTimeParser");
		$this->addOrderV3("TodoTillDay","DESC");
		$this->addOrderV3("TodoTillTime","DESC");

		if($bps != -1 AND isset($bps["ownerClass"])){
			$this->addAssocV3("TodoClass","=",$bps["ownerClass"]);
			$this->addAssocV3("TodoClassID","=",$bps["ownerClassID"]);
		}

		$gui = new HTMLGUIX();
		
		$this->loadMultiPageMode($id, $page, 8);

		$gui->name("Aktivität");
		$gui->colWidth("TodoType","20px");
		$gui->colWidth("TodoStatus","20px");
		$gui->object($this);
		
		$gui->parser("TodoStatus","mTodoGUI::statusParser");
		$gui->parser("TodoDescription","mTodoGUI::descParser");
		$gui->parser("TodoType","TodoGUI::typesImage");
		$gui->parser("TodoTillDay","mTodoGUI::dayParser");

		$gui->activateFeature("CRMEditAbove", $this);
		
		$gui->displayMode("CRMSubframeContainer");
		$gui->attributes(array("TodoType","TodoTillDay","TodoDescription","TodoStatus"));
		$gui->customize($this->customizer);
		return $gui->getBrowserHTML($id);
	}

	function editInPopup($id, $date = null, $targetClass = null, $targetClassID = null, $time = null, $description = null, $location = null){
		if($date != null)
			BPS::setProperty("TodoGUI", "TodoTillDay", Util::CLDateParser($date));
		
		if($time != null)
			BPS::setProperty("TodoGUI", "TodoFromTime", $time);
		
		if($description != null)
			BPS::setProperty("TodoGUI", "TodoDescription", $description);
		
		if($location != null)
			BPS::setProperty("TodoGUI", "TodoLocation", $location);
		
		$T = new TodoGUI($id);
		$T->GUI = new HTMLGUIX($T);
		$T->GUI->displayMode("popupC");
		$T->GUI->requestFocus("TodoKunde", "TodoName");
		#if($id == -1)
		$T->GUI->addToEvent("onSave", OnEvent::popup("Event", "mKalender", "-1", "getInfo", array("'mTodoGUI'", "transport.responseText", "'$date'")));
		
		
		if($targetClass != null){
			BPS::setProperty("mTodoGUI", "ownerClass", $targetClass);
			BPS::setProperty("mTodoGUI", "ownerClassID", $targetClassID);
		}
		
		$T->getHTML($id);

		if($T->A("TodoClass") == "Kalender" OR $T->A("TodoClass") == "DBMail")
			$T->GUI->insertAttribute("after", "TodoClassID", "TodoName");

		echo $T->GUI->getEditHTML().OnEvent::script("\$j('#editTodoGUI input[name=TodoName]').trigger('focus');");
	}

	function deleteFromCalendar($todoID, $makeException = false){
		if(!$makeException){
			$T = new Todo($todoID);
			$T->deleteMe();
		} else {
			$T = new Todo($todoID);
			$T->changeA("TodoIsDeleted", "1");
			$T->changeA("TodoExceptionForID", $todoID);
			$T->changeA("TodoFromDay", Util::parseDate("de_DE", Util::formatDate("de_DE", $makeException)));
			$T->changeA("TodoTillDay", Util::parseDate("de_DE", Util::formatDate("de_DE", $makeException)));
			$T->changeA("TodoExceptionStarttime", $makeException);
			$T->changeA("TodoRepeat", "");
			$T->newMe();
			
			$TO = new Todo($todoID);
			$TO->saveMe();
		}
	}

	public static function descParser($v){
		return "<small>".nl2br($v)."</small>";
	}

	public static function dayParser($v, $E){
		$U = new User($E->A("TodoUserID"));

		return $E->A("TodoTillDay")." ".$E->A("TodoTillTime")."<br /><small style=\"color:grey;\">".$U->A("name")."</small>";
	}

	public static function statusParser($v){
		if($v == 2) $v = 1;
		return Util::catchParser($v, null, "erledigt?");
	}


	public static function getCalendarDetails($className, $classID, $T = null) {
		$K = new Kalender();
		if($T == null)
			$T = new Todo($classID);

		$name = "";
		if($T->A("TodoClass") == "WAdresse" OR $T->A("TodoClass") == "Projekt" OR $T->A("TodoClass") == "GRLBM"){
			$O = $T->getOwnerObject();
			$name = $O->getCalendarTitle();
		} else
			$name = $T->A("TodoName");
		
		$day = $T->A("TodoTillDay");
		if($T->A("TodoFromDay") != "0")
			$day = $T->A("TodoFromDay");

		$time = $T->A("TodoTillTime");
		if($T->A("TodoFromTime") != "0")
			$time = $T->A("TodoFromTime");
		
		$KE = new KalenderEvent($className, $classID, $K->formatDay($day), $K->formatTime($time), $name);
		#echo $T->A("TodoOrt");
		$KE->value("Typ", TodoGUI::types($T->A("TodoType")));
		$KE->value("Ort", $T->A("TodoLocation"));
		#$KE->value("Status", TodoGUI::getStatus($T->A("TodoStatus")));
		$KE->owner($T->A("TodoUserID"));
		if($T->A("TodoClass") == "WAdresse"){
			$KE->value("Telefon", $O->A("tel"));
			$KE->value("Notiz", nl2br($O->A("bemerkung")));
			
			$KE->canNotify(true, $T->A("TodoNotified") == "1");
			
			if($T->A("TodoType") == "1" AND Session::isPluginLoaded("mAkquise")){
				$B = new Button("Akquise", "./lightCRM/Akquise/callTel.png");
				$B->doBefore(OnEvent::rme($T, "setStatus", array("'2'"), OnEvent::closePopup("mKalender").OnEvent::reload("Left"))." %AFTER");
				$B->popup("", "Akquise", "mAkquise", "-1", "showTelPopup", array($T->A("TodoClassID")), "", "{width: 950, top:20, left:20}");
				
				$KE->addTopButton($B);
			}
		}
		
		$KE->allDay($T->A("TodoAllDay") == "1");
		
		if($T->A("TodoClass") == "Projekt" AND $O->A("ProjektKunde") != "0"){
			$Adresse = new Adresse($O->A("ProjektKunde"));
			
			$KE->value("Telefon", $Adresse->A("tel"));
		}
		
		if($T->A("TodoClass") == "GRLBM"){
			$Auftrag = new Auftrag($O->A("AuftragID"));
			$Adresse = new Adresse($Auftrag->A("AdresseID"));
			
			$KE->value("Telefon", $Adresse->A("tel"));
		}

		$KE->status($T->A("TodoStatus"));
		
		$KE->endDay($K->formatDay($T->A("TodoTillDay")));
		$KE->endTime($K->formatTime($T->A("TodoTillTime")));

		$KE->icon(TodoGUI::typesImage($T->A("TodoType"), true));
		$KE->summary(nl2br($T->A("TodoDescription")));

		if($T->A("TodoExceptionForID") != "0")
			$KE->exception($T->A("TodoExceptionStarttime"), $T->A("TodoIsDeleted") == "1", $T->A("TodoExceptionForID"));

		
		if($T->A("TodoRemind") != "-1")
			$KE->remind($T->A("TodoRemind") / 60);
		
		$KE->editable("editInPopup", "deleteFromCalendar");
		$KE->repeatable("editRepeatable");

		$KE->location($T->A("TodoLocation"));

		$KE->repeat($T->A("TodoRepeat") != "", $T->A("TodoRepeat"), $T->A("TodoRepeatWeekOfMonth") * 1);

		$KE->UID("TodoID".$T->getID()."@".substr(Util::eK(), 0, 20));
		
		return $KE;
	}

	public static function getCalendarData($firstDay, $lastDay) {
		$K = new Kalender();

		//TERMINE IN DIESEM MONAT
		$AC = new anyC();
		$AC->setCollectionOf("Todo");

		$AC->addAssocV3("TodoTillDay",">=",$firstDay, "AND", "1");
		$AC->addAssocV3("TodoTillDay","<=",$lastDay, "AND", "1");
		$AC->addAssocV3("TodoRepeat", "=", "", "AND", "1");
		
		
		$AC->addAssocV3("TodoUserID", "=", Session::currentUser()->getID(), "AND", "2");
		$AC->addAssocV3("TodoUserID", "=", "-1", "OR", "2");

		$ACS = anyC::get("Userdata", "name", "shareCalendarTo".Session::currentUser()->getID());
		while($Share = $ACS->getNextEntry())
			$AC->addAssocV3("TodoUserID", "=", $Share->A("UserID"), "OR", "2");
		
		
		$AC->addOrderV3("TodoTillTime");

		while($t = $AC->getNextEntry())
			$K->addEvent(self::getCalendarDetails("mTodoGUI", $t->getID(), $t));
		
		
		//TERMINE ÜBER DIESEN MONAT HINAUS
		$AC = new anyC();
		$AC->setCollectionOf("Todo");

		$AC->addAssocV3("TodoFromDay","<=",$lastDay, "AND", "1");
		$AC->addAssocV3("TodoTillDay",">",$lastDay, "AND", "1");
		$AC->addAssocV3("TodoRepeat", "=", "", "AND", "1");
		
		
		$AC->addAssocV3("TodoUserID", "=", Session::currentUser()->getID(), "AND", "2");
		$AC->addAssocV3("TodoUserID", "=", "-1", "OR", "2");

		$ACS->resetPointer();
		while($Share = $ACS->getNextEntry())
			$AC->addAssocV3("TodoUserID", "=", $Share->A("UserID"), "OR", "2");
		
		
		$AC->addOrderV3("TodoTillTime");

		while($t = $AC->getNextEntry())
			$K->addEvent(self::getCalendarDetails("mTodoGUI", $t->getID(), $t));
		
		
		//WIEDERHOLTE TERMINE
		$AC = new anyC();
		$AC->setCollectionOf("Todo");

		$AC->addAssocV3("TodoFromDay","<=",$lastDay, "AND", "1");
		$AC->addAssocV3("TodoRepeat", "!=", "", "AND", "1");

		/*$AC->addAssocV3("MONTH(FROM_UNIXTIME(TodoFromDay))","=", date("m", $lastDay), "AND", "1");
		if(date("m", $firstDay) != date("m", $lastDay))
			$AC->addAssocV3("MONTH(FROM_UNIXTIME(TodoFromDay))","=", date("m", $firstDay), "OR", "1");
		$AC->addAssocV3("TodoRepeat", "!=", "", "AND", "3");*/
		
		$AC->addAssocV3("TodoUserID", "=", Session::currentUser()->getID(), "AND", "2");
		$AC->addAssocV3("TodoUserID", "=", "-1", "OR", "2");

		$ACS->resetPointer();
		while($Share = $ACS->getNextEntry())
			$AC->addAssocV3("TodoUserID", "=", $Share->A("UserID"), "OR", "2");
		
		
		$AC->addOrderV3("TodoTillTime");

		while($t = $AC->getNextEntry())
			$K->addEvent(self::getCalendarDetails("mTodoGUI", $t->getID(), $t));
		
		return $K;
	}

	public function getAdresse($TodoID){
		$T = new Todo($TodoID);
		
		$C = $T->A("TodoClass");
		if($C == "WAdresse")
			$C = "Adresse";
		
		$C = new $C($T->A("TodoClassID"));
		
		return $C;
	}
	
	public function setNotified($className, $classID){
		$T = new Todo($classID);
		$T->changeA("TodoNotified", "1");
		
		$T->saveMe();
	}
	
	public function editRepeatable($todoID){
		$F = new HTMLForm("RepeatableForm", array("TodoRepeat", "TodoRepeatWeekOfMonth"), "Wiederholungen");
		$F->getTable()->setColWidth(1, 120);
		
		$T = new Todo($todoID);
		
		$F->setValues($T);
		
		$F->setLabel("TodoRepeat","Wiederholen");
		$F->setLabel("TodoRepeatWeekOfMonth", "Tag");
		
		#$currentWeek = ceil((date("d", $T->A("TodoFromDay")) - date("w", $T->A("TodoFromDay")) - 1) / 7) + 1;
		#echo $currentWeek;
		
		$D = new Datum($T->A("TodoFromDay"));
		$nthDay = $D->getNthDayOfMonth();
		if($nthDay > 4)
			$nthDay = 4;
		
		$weeks = array(0 => "am ".date("d", $T->A("TodoFromDay")).". jeden Monats");
		$weeks[$nthDay] = "jeden $nthDay. ".Util::CLWeekdayName(date("w", $T->A("TodoFromDay")))." des Monats";
		$F->setType("TodoRepeat", "select", "", Todo::$repeatTypes);
		$F->setType("TodoRepeatWeekOfMonth", "select", "", $weeks);
		
		$F->hideIf("TodoRepeat", "!=", "monthly", "onchange", array("TodoRepeatWeekOfMonth"));
		
		$F->setSaveClass("Todo", $todoID, "function(){ /*\$j('#eventAdditionalContent').slideUp();*/ contentManager.reloadFrame('contentLeft'); Kalender.refreshInfoPopup(); }", "Aktivität");
		
		return $F;
	}
}
?>