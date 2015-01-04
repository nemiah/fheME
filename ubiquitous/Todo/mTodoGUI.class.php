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
 *  2007 - 2014, Rainer Furtmeier - Rainer@Furtmeier.IT
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

	public function getInviteForm($id) {
		$gui = new HTMLForm("inviteForm", array("todoid", "adress_0"));
		$gui->setType("todoid", "hidden", $id);
		$gui->setAutoComplete("adress_0", "mTodo", "function(selection) { (typeof selection.email != 'undefined')? \$j('input[name=adress_0]').val(selection.email) : \$j('input[name=adress_0]').val(selection.value); return false; }");
		$gui->getTable()->setColWidth(1, 120);
		$gui->setLabel("adress_0", "Teilnehmer 1");
		$gui->addJSEvent("adress_0", "onfocus", OnEvent::rme($this, "newInput", 0, "function(transport) {\$j('input[name=adress_0]').closest('tr').after(transport.responseText); \$j('input[name=adress_0]').attr('onfocus', ''); }"));
		$gui->setSaveJSON("Einladung verschicken", "", "mTodo", -1, "sendInvitation", OnEvent::closePopup("mKalender"));
		
		$gui->getAllFields();
		echo $gui->getHTML();
	}
	
	public function newInput($number) {
		$htmlTable = new HTMLTable(2);
		$input = new HTMLInput("adress_" . ++$number, "text");
		$input->autocomplete("mTodo", "function(selection) { (typeof selection.email != 'undefined')? \$j('input[name=adress_" . $number . "]').val(selection.email) : \$j('input[name=adress_" . $number . "]').val(selection.value); return false; }");
		$input->onfocus(OnEvent::rme($this, "newInput", $number, "function(transport) {\$j('input[name=adress_" . $number . "]').closest('tr').after(transport.responseText); \$j('input[name=adress_" . $number . "]').attr('onfocus', ''); }"));
		$htmlTable->addLV("Teilnehmer " . ($number + 1) . ":", $input);
		echo $htmlTable->getHTMLForUpdate(true);
	}
	
	public function getACData($attributeName, $query) {
		$users = Users::getUsers();
		$selection = array();
		$query = (preg_match("/%/", $query))? preg_replace("/%/", $query, "") : $query;
		while ($user = $users->getNextEntry()) {
			if (preg_match("/.*" . $query . ".*/", $user->A("UserEmail")) || preg_match("/.*" . $query . ".*/", $user->A("name")) || preg_match("/.*" . $query . ".*/", $user->A("username"))) {
				$subSelection = array(
					"label" => $user->A("name"),
					"value" => $user->A("UserEmail"),
					"email" => $user->A("UserEmail"),
					"description" => ""
				);
				$selection[] = $subSelection;
			}
		}
		
		if (Session::isPluginLoaded("mWAdresse")) {
			$adresses = new Adressen();
			$adresses->setSearchStringV3($query);
			$adresses->setSearchFieldsV3(array("firma", "nachname", "email"));
			$adresses->setFieldsV3(array("firma AS label", "AdresseID AS value", "vorname", "nachname", "CONCAT(strasse, ' ', nr, ', ', plz, ' ', ort) AS description","email", "firma"));
			$adresses->setLimitV3("10");
			$adresses->setParser("label", "AdressenGUI::parserACLabel");
			if($attributeName == "SendMailTo")
				$adresses->addAssocV3 ("email", "!=", "");
			
			while ($adress  = $adresses->getNextEntry()) {
				$subSelection = array();
				foreach ($adress->getA() as $key => $value)
					$subSelection[$key] = $value;
				$selection[] = $subSelection;
			}
		}
		
		echo json_encode($selection);
	}
	
	/**
	 * Erstellt eine zufallsgenerierte Zeichenkette. Zeichenkette besteht aus 
	 * arabischen Ziffern und lateinischen Buchstaben, generiert aus ASCII.
	 * Länge der Zeichenkette wird anhand des Parameters spezifiziert. 
	 * 
	 * @param int $length
	 * @return String
	 * @throws Exception
	 */
	public static function getRandomId($length) {
		// TODO: Diese Methode möglicherweise in Util übernehmen?!
		$length = (int) $length;
		if ($length == null || $length == 0)
			throw new Exception("Submitted length could not be processed.");
		
		$id = "";
		$i = 0;
		$excludedSigns = array(58, 59, 60, 61, 62, 63, 64, 73, 79, 91, 92, 93, 94, 95, 96, 108, 111);
		while ($i < $length) {
			mt_srand((double) microtime() * 1000000);
			$randomNum = mt_rand(50, 122);
			if (in_array($randomNum, $excludedSigns))
				continue;
			$id .= chr($randomNum);
			$i++;
		}
		return $id;
	}
	
	public function sendInvitation($json) {
		$participants = json_decode($json);
		$todoId = 0;
		
		// Filtern der TodoId
		foreach ($participants as $i => $participant) {
			if ($participant->name == "todoid") {
				$todoId = (int) $participant->value;
				unset($participants[$i]);
				break;
			}
		}
		if ($todoId == 0 || !preg_match("/^\d+$/", $todoId))
			return null;
		
		// Einladungen speichern und versenden
		$todo = new Todo($todoId);
		$userMail = Session::currentUser()->A("UserEmail");
		// Wenn der aktuelle Benutzer über eine ungültige Adresse verfügt, 
		// werden keine Einladungen verschickt.
		if (!Util::checkIsEmail($userMail))
			return;
		
		$userName = Session::currentUser()->A("name");
		$startTime = $todo->A("TodoFromDay") + $todo->A("TodoFromTime");
		$endTime = $todo->A("TodoTillDay") + $todo->A("TodoTillTime");
		
		foreach ($participants as $i => $participant) {
			// Falls E-Mail-Adresse leer ist, oder ungültig, wird dieser 
			// Teilnehmer übersprungen.
			if (!Util::checkIsEmail($participant->value))
				continue;
			
			$todoInvitation = new TodoInvitation();
			$todoInvitation->changeA("TodoInvitationTodoID", $todoId);
			$todoInvitation->changeA("ToDoInvitationUserEmail", $participant->value);
			$todoInvitation->changeA("ToDoInvitationStatus", 0);
			$todoInvitation->newMe();
			
			$htmlMimeMail = new htmlMimeMail5();
			$htmlMimeMail->setFrom($userMail);
			$htmlMimeMail->setSubject($todo->A("TodoName"));
			$htmlMimeMail->setReturnPath($userMail);
			$htmlMimeMail->setText("huhu");
			$attachment = "BEGIN:VCALENDAR
PRODID:-//lightCRM Kalender//DE
VERSION:2.0
METHOD:REQUEST
BEGIN:VEVENT
ATTENDEE;CN=\"\";RSVP=TRUE:mailto:" . $participant->value . "
CLASS:PUBLIC
DESCRIPTION:\n\n\n
DTEND:" . gmdate("Ymd", $endTime) . "T" . gmdate("His", $endTime) . "Z
DTSTAMP:" . gmdate("Ymd", time()) . "T" . gmdate("His", time()) . "Z
DTSTART:" . gmdate("Ymd", $startTime) . "T" . gmdate("His", $startTime) . "Z
ORGANIZER;CN=\"" . $userName . "\":mailto:" . $userMail . "
PRIORITY:5
SEQUENCE:0
SUMMARY;LANGUAGE=de:" . $todo->A("TodoName") . "
TRANSP:OPAQUE
UID:" . self::getRandomId(72) . "
BEGIN:VALARM
TRIGGER:-PT15M
ACTION:DISPLAY
DESCRIPTION:Reminder
END:VALARM
END:VEVENT
END:VCALENDAR";
//			$htmlMimeMail->setCalendar($attachment, "REQUEST");
//			$htmlMimeMail->setCalendarCharset("UTF-8");
//			$htmlMimeMail->addAttachment(new stringAttachment($attachment, "invite.ics", "application/ics"));
			$htmlMimeMail->send(array($participant->value));
		}
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
		$T->GUI->displayMode("popupS");
		$T->GUI->requestFocus("TodoName");
		#if($id == -1)
		$T->GUI->addToEvent("onSave", OnEvent::popup("Event", "mKalender", "-1", "getInfo", array("'mTodoGUI'", "transport.responseText", "'$date'")));
		
		
		if($targetClass != null){
			BPS::setProperty("mTodoGUI", "ownerClass", $targetClass);
			BPS::setProperty("mTodoGUI", "ownerClassID", $targetClassID);
		}
		
		$T->getHTML($id);

		#if($T->A("TodoClass") == "Kalender" OR $T->A("TodoClass") == "DBMail")
			$T->GUI->insertAttribute("after", "TodoClassID", "TodoName");

		echo $T->GUI->getEditHTML();#.OnEvent::script("\$j('#editTodoGUI input[name=TodoName]').trigger('focus');");
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
				#$B->doBefore(OnEvent::rme($T, "setStatus", array("'2'"), OnEvent::closePopup("mKalender").OnEvent::reload("Screen"))." %AFTER");
				
				$B->doBefore(OnEvent::closePopup("mKalender")."contentManager.emptyFrame('contentScreen'); %AFTER");
				$B->loadFrame("contentLeft", "WAdresse", $T->A("TodoClassID"), 0, "mWAdresseGUI;Akquise:1;from:mKalender");
		
				#$B->popup("", "Akquise", "mAkquise", "-1", "showTelPopup", array($T->A("TodoClassID")), "", "{width: 950, top:20, hPosition:'center'}");
				
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
			$KE->remind($T->A("TodoRemind") / 60, $T->A("TodoReminded"));
		
		$editMethod = "editInPopup";
		if($T->A("TodoOrganizer") != ""){
			$editMethod = null;
			$KE->organizer($T->A("TodoOrganizer"));
		}
		$KE->editable($editMethod, "deleteFromCalendar");
		
		if($T->A("TodoOrganizer") == "")
			$KE->repeatable("editRepeatable");

		$KE->location($T->A("TodoLocation"));

		$KE->repeat($T->A("TodoRepeat") != "", $T->A("TodoRepeat"), $T->A("TodoRepeatWeekOfMonth") * 1, $T->A("TodoRepeatDayOfWeek"), $T->A("TodoRepeatInterval"), $T->A("TodoRepeatUntil"));

		$KE->UID("TodoID".$T->getID()."@".substr(Util::eK(), 0, 20));
		
		return $KE;
	}

	public static function getCalendarCategories(){
		$tabs = array();
		$bps = BPS::getAllProperties("mKalenderGUI");
		
		$tabs[] = new stdClass();
		$tabs[0]->onclick = OnEvent::reload("Screen", "_mKalenderGUI;KID:".Session::currentUser()->getID());
		$tabs[0]->elementID = "TodoCurrentUser";
		$tabs[0]->label = "Mein Kalender";
		$tabs[0]->isCurrent = (!isset($bps["KID"]) OR $bps["KID"] == Session::currentUser()->getID());
		
		
		$ACS = anyC::get("Userdata", "name", "shareCalendarTo".Session::currentUser()->getID());
		$ACS->addAssocV3("name", "=", "shareCalendarTo0", "OR");
		while($Share = $ACS->getNextEntry()){
			
			$U = new User($Share->A("UserID"));
			
			$C = new stdClass();
			$C->onclick = OnEvent::reload("Screen", "_mKalenderGUI;KID:".$U->getID());
			$C->elementID = "TodoCurrentUser";
			$C->label = $U->A("name");
			$C->isCurrent = ($bps["KID"] == $U->getID());
			
			
			$tabs[] = $C;
		}
		
		return $tabs;
	}
	
	public static function getCalendarData($firstDay, $lastDay, $UserID = null) {
		if($UserID === null)
			$UserID = Session::currentUser();
		#echo $UserID;
		$K = new Kalender();
		#$include = array();
		//TERMINE IN DIESEM MONAT
		$AC = new anyC();
		$AC->setCollectionOf("Todo");

		$AC->addAssocV3("TodoTillDay",">=",$firstDay, "AND", "1");
		$AC->addAssocV3("TodoTillDay","<=",$lastDay, "AND", "1");
		$AC->addAssocV3("TodoRepeat", "=", "", "AND", "1");
		
		if($UserID != 0)
			$AC->addAssocV3("TodoUserID", "=", $UserID, "AND", "2");
		else
			$AC->addAssocV3("TodoUserID", ">", "0", "AND", "2");
		$AC->addAssocV3("TodoUserID", "=", "-1", "OR", "2");

		/*$ACS = anyC::get("Userdata", "name", "shareCalendarTo".($UserID != 0 ? $UserID : "0")); //disabled for all users at the moment!
		while($Share = $ACS->getNextEntry()){
			$include[$Share->A("UserID")] = mUserdata::getUDValueS("showCalendarOf".$Share->A("UserID"), "1");
			if($include[$Share->A("UserID")] == "1")
				$AC->addAssocV3("TodoUserID", "=", $Share->A("UserID"), "OR", "2");
		}*/
		
		$AC->addOrderV3("TodoTillTime");

		while($t = $AC->getNextEntry())
			$K->addEvent(self::getCalendarDetails("mTodoGUI", $t->getID(), $t));
		
		
		//TERMINE ÜBER DIESEN MONAT HINAUS
		$AC = new anyC();
		$AC->setCollectionOf("Todo");

		$AC->addAssocV3("TodoFromDay","<=",$lastDay, "AND", "1");
		$AC->addAssocV3("TodoTillDay",">",$lastDay, "AND", "1");
		$AC->addAssocV3("TodoRepeat", "=", "", "AND", "1");
		
		if($UserID != 0)
			$AC->addAssocV3("TodoUserID", "=", $UserID, "AND", "2");
		else
			$AC->addAssocV3("TodoUserID", ">", "0", "AND", "2");
		$AC->addAssocV3("TodoUserID", "=", "-1", "OR", "2");

		#$ACS->resetPointer();
		#while($Share = $ACS->getNextEntry()){
		#	if($include[$Share->A("UserID")] == "1")
		#		$AC->addAssocV3("TodoUserID", "=", $Share->A("UserID"), "OR", "2");
		#}
		
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
		
		if($UserID != 0)
			$AC->addAssocV3("TodoUserID", "=", $UserID, "AND", "2");
		else
			$AC->addAssocV3("TodoUserID", ">", "0", "AND", "2");
		$AC->addAssocV3("TodoUserID", "=", "-1", "OR", "2");

		$AC->addAssocV3("TodoRepeatUntil", "=", "0", "AND", "3");
		$AC->addAssocV3("TodoRepeatUntil", ">=", $firstDay, "OR", "3");
		#$ACS->resetPointer();
		#while($Share = $ACS->getNextEntry()){
		#	if($include[$Share->A("UserID")] == "1")
		#		$AC->addAssocV3("TodoUserID", "=", $Share->A("UserID"), "OR", "2");
		#}
		
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
	
	public function setReminded($className, $classID){
		$T = new Todo($classID);
		$T->changeA("TodoReminded", time());

		$T->saveMe(true, false, false);
	}
	
	public function editRepeatable($todoID){
		$F = new HTMLForm("RepeatableForm", array("TodoRepeat", "TodoRepeatWeekOfMonth", "TodoRepeatDayOfWeek", "TodoRepeatInterval", "TodoRepeatUntil"), "Wiederholungen");
		$F->getTable()->setColWidth(1, 120);
		
		$T = new Todo($todoID);
		
		$F->setValues($T);
		
		$F->setValue("TodoRepeatUntil", Util::CLDateParserE($T->A("TodoRepeatUntil")));
		
		$F->setLabel("TodoRepeat","Wiederholen");
		$F->setLabel("TodoRepeatWeekOfMonth", "Tag");
		$F->setLabel("TodoRepeatDayOfWeek", "Tage");
		$F->setLabel("TodoRepeatInterval", "Intervall");
		$F->setLabel("TodoRepeatDayOfWeek", "Tage");
		$F->setLabel("TodoRepeatUntil", "Bis");
		
		$F->setType("TodoRepeatInterval", "select", null, array("Wöchentlich", "Jede 2. Woche", "Jede 3. Woche", "Jede 4. Woche"));
		$F->setType("TodoRepeatUntil", "date");
		
		#$currentWeek = ceil((date("d", $T->A("TodoFromDay")) - date("w", $T->A("TodoFromDay")) - 1) / 7) + 1;
		#echo $currentWeek;
		
		$D = new Datum($T->A("TodoFromDay"));
		$nthDay = $D->getNthDayOfMonth();
		if($nthDay > 4)
			$nthDay = 4;
		
		$weeks = array(0 => "am ".date("d", $T->A("TodoFromDay")).". jeden Monats");
		$weeks[$nthDay] = "jeden $nthDay. ".Util::CLWeekdayName(date("w", $T->A("TodoFromDay")))." des Monats";
		$weeks[127] = "am letzten Tag des Monats";
		$F->setType("TodoRepeat", "select", "", Todo::$repeatTypes);
		$F->setType("TodoRepeatWeekOfMonth", "select", "", $weeks);
		
		#$F->setType("TodoRepeatDayOfWeek", "checkbox");
		$F->setType("TodoRepeatDayOfWeek", "parser", $T->A("TodoRepeatDayOfWeek"), array("mTodoGUI::parserDayOfWeek"));
		
		$F->hideIf("TodoRepeat", "!=", "monthly", "onchange", array("TodoRepeatWeekOfMonth"));
		$F->hideIf("TodoRepeat", "!=", "daily", "onchange", array("TodoRepeatDayOfWeek"));
		$F->hideIf("TodoRepeat", "!=", "weekly", "onchange", array("TodoRepeatInterval", "TodoRepeatUntil"));
		
		$F->setSaveClass("Todo", $todoID, "function(){ /*\$j('#eventAdditionalContent').slideUp();*/ contentManager.reloadFrame('contentScreen'); Kalender.refreshInfoPopup(); }", "Aktivität");
		
		return $F;
	}
	
	public static function parserDayOfWeek($value){
		$I = new HTMLInput("TodoRepeatDayOfWeek", "hidden", $value);
	
		$checked = array();
		if($value != "")
			$checked = explode(",", $value);
		
		$Is = "";
		for($i = 0; $i < 7; $i++){
			$I2 = new HTMLInput("DOW$i", "checkbox", in_array($i, $checked) ? "1" : "0");
			$I2->style("float:left;margin:0px;margin-right:5px;");
			$I2->onchange("var selectedDOW = ''; \$j('.DOWSelector:checked').each(function(k, v){ selectedDOW += (selectedDOW == '' ? '' : ',')+\$j(v).prop('name').replace('DOW', '');}); \$j('[name=TodoRepeatDayOfWeek]').val(selectedDOW);");
			$I2->setClass("DOWSelector");
			
			$Is .= "<div style=\"margin-bottom:5px;\">".$I2.Util::CLWeekdayName($i)."</div>";
		}
		
		return $Is.$I;
	}
}
?>