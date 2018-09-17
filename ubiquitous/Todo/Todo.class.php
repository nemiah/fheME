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
 *  2007 - 2018, Furtmeier Hard- und Software - Support@Furtmeier.IT
 */
class Todo extends PersistentObject {
	
	static function getAllowed(){
		$allowed = array();
		$ACS = anyC::get("Userdata", "name", "shareCalendarTo".Session::currentUser()->getID());
		$ACS->addAssocV3("name", "=", "shareCalendarTo0", "OR");
		while($Share = $ACS->getNextEntry())
			$allowed[$Share->A("UserID")] = $Share->A("wert");
		
		return $allowed;
	}
	public static $repeatTypes = array("" => "nicht Wiederholen", "daily" => "Täglich", "weekly" => "Wöchentlich", "monthly" => "Monatlich", "yearly" => "Jährlich");
	/*public function invitePerson($id, $TeamId = 0, $nooutput = false){
		return mTeilnehmerGUI::invitePerson("Todo", $this->ID, $id, $TeamId, $nooutput);
	}
	
	public function inviteTeam($id){
		list($anz, $ges) = mTeilnehmerGUI::inviteTeam("Todo", $this->ID, $id);
		echo "message:EventMessages.M002('$anz/$ges')";
	}*/
	private $updateGoogle = true;

	public function updateGoogle($b){
		$this->updateGoogle = $b;
	}
	
	public function newAttributes() {
		$A = parent::newAttributes();
		
		$A->TodoRemind = mUserdata::getUDValueS("DefaultValueTodoTodoRemind", "-1");
		
		return $A;
	}
	
	public function saveMe($checkUserData = true, $output = false, $update = true) {
		$old = new Todo($this->getID());
		$old->loadMe();
		#$fromDay = date("Y-m-d", Util::CLDateParser($this->A("TodoFromDay"), "store"));
		#$fromTime = Util::formatTime("de_DE", Util::CLTimeParser($this->A("TodoFromTime"), "store"));
		#die($this->getID());
		
		if($update){
			$this->changeA("TodoLastChange", time());
			$this->changeA("TodoReminded", "0");
		}
		#$name = $this->getOwnerObject()->getCalendarTitle();
		
		if($this->A("TodoAllDay")){
			$this->changeA("TodoFromTime", Util::CLTimeParser(0));
			$this->changeA("TodoTillTime", Util::CLTimeParser(0));
		}
		
		if($this->A("TodoRepeatWeekOfMonth") > 0 AND $this->A("TodoRepeatWeekOfMonth") != 127){
			$D = new Datum($this->hasParsers ? Util::CLDateParser($this->A("TodoFromDay"), "store") : $this->A("TodoFromDay"));
			$nthDay = $D->getNthDayOfMonth();
			if($nthDay > 4)
				$nthDay = 4;
			
			$this->changeA("TodoRepeatWeekOfMonth", $nthDay);
		}
		
		if($this->A("TodoClass") != "" AND $this->A("TodoClass") != "Kalender" AND $this->A("TodoName") == "")
			$this->changeA("TodoName", $this->getOwnerObject()->getCalendarTitle());
		
		
		if(Session::isPluginLoaded("mAufgabe") AND ($this->A("TodoType") == 3 OR $this->A("TodoType") == 4 OR $this->A("TodoType") == 5) AND $this->A("TodoUserID") > 0){
			$F = new Factory("Aufgabe");
			$F->sA("AufgabeByClass", "Todo");
			$F->sA("AufgabeByClassID", $this->getID());
			
			$E = $F->exists(true);
			if(!$E){
				$F->sA("AufgabeUserID", $this->A("TodoUserID"));
				$F->sA("AufgabeText", "Bericht für Termin '".$this->A("TodoName")."' eintragen");
				$F->sA("AufgabeCreated", time());
				if($this->A("TodoDoneTime") > 0){
					$F->sA ("AufgabeStatus", "5");
					$F->sA("AufgabeDone", time());
				}
				
				if($this->hasParsers){
					$F->sA("AufgabeUntil", Util::CLDateParser($this->A("TodoFromDay"), "store"));
					$F->sA("AufgabeUhrzeitVon", Util::CLTimeParser($this->A("TodoTillTime"), "store"));
				} else {
					$F->sA("AufgabeUntil", $this->A("TodoFromDay"));
					$F->sA("AufgabeUhrzeitVon", $this->A("TodoTillTime"));
				}
				$F->store();
			} else {
				$E->changeA("AufgabeText", "Bericht für Termin '".$this->A("TodoName")."' eintragen");
				
				if($this->hasParsers){
					$E->changeA("AufgabeUntil", Util::CLDateParser($this->A("TodoFromDay"), "store"));
					$E->changeA("AufgabeUhrzeitVon", Util::CLTimeParser($this->A("TodoTillTime"), "store"));
				} else {
					$E->changeA("AufgabeUntil", $this->A("TodoFromDay"));
					$E->changeA("AufgabeUhrzeitVon", $this->A("TodoTillTime"));
				}
				
				if($this->A("TodoDoneTime") > 0){
					$E->changeA("AufgabeStatus", "5");
					$E->changeA("AufgabeDone", time());
				}
				$E->saveMe();
			}
			
		}
		
		
		parent::saveMe($checkUserData, false);
		
		if(Session::isPluginLoaded("mGoogle") AND $this->updateGoogle){
			$KE = mTodoGUI::getCalendarDetails("Todo", $this->getID());
			if($this->A("TodoUserID") == Session::currentUser()->getID())
				if($old->A("TodoUserID") == $this->A("TodoUserID"))
					Google::calendarUpdateEvent(mTodoGUI::getCalendarDetails("Todo", $this->getID()));#"Todo", $this->getID(), $name, $this->A("TodoDescription"), $this->A("TodoLocation"), $fromDay, $fromTime, date("Y-m-d", Util::CLDateParser($this->A("TodoTillDay"), "store")), Util::formatTime("de_DE", Util::CLTimeParser($this->A("TodoTillTime"), "store") + 3600));
				else {
					Google::calendarDeleteEvent($KE);#"Todo", $this->getID());
					Google::calendarCreateEvent($KE);
				}
			else {
				Google::calendarDeleteEvent($KE);#"Todo", $this->getID());
				Google::calendarCreateEvent($KE, $this->A("TodoUserID"));
			}
		}
		
		
	}

	public function getOwnerObject(){
		$c = $this->A("TodoClass")."GUI";
		if($c == "KalenderGUI")
			$O = $this;
		else{
			try {
				$O = new $c($this->A("TodoClassID"));
			} catch (ClassNotFoundException $e){
				$O = $this;	
			}
		}
		return $O;
	}

	public function newMe($checkUserData = true, $output = false) {
		$this->changeA("TodoLastChange", time());
		if(Session::currentUser() != null)
			$this->changeA("TodoCreatorUserID", Session::currentUser()->getID());
		#if($this->A("TodoGUID") == "")
		#	$this->changeA("TodoGUID", uniqid()."-".uniqid());
		
		if($this->A("TodoClass") != "" AND $this->A("TodoClass") != "Kalender" AND $this->A("TodoName") == "")
			$this->changeA("TodoName", $this->getOwnerObject()->getCalendarTitle());
		
		$id = parent::newMe($checkUserData, false);
		
		if($this->AA("TodoTeilnehmer")){
			$t = explode(";:;", $this->AA("TodoTeilnehmer"));

			foreach($t AS $UserID){
				if(trim($UserID) == trim($this->A("TodoUserID")))
					continue;
				
				$ST = new Todo(-1);
				$ST->setA(clone $this->getA());
				$ST->changeA("TodoUserID", $UserID);
				$ST->newMe();
			}
		}
		
		if(Session::isPluginLoaded("mSync") AND ($this->A("TodoExceptionForID") == "0" OR $this->A("TodoExceptionForID") == ""))
			mSync::newGUID("Todo", $id);
		
		#$name = $this->getOwnerObject()->getCalendarTitle();

		if(Session::isPluginLoaded("mGoogle") AND $this->updateGoogle)
			if($this->A("TodoUserID") == Session::currentUser()->getID())
				Google::calendarCreateEvent(mTodoGUI::getCalendarDetails("Todo", $id));
			
		if($this->A("TodoClass") == "DBMail" AND Session::isPluginLoaded("mMail"))
			Mail::assign("Todo", $id, $this->A("TodoClassID"));
		
		
		if(Session::isPluginLoaded("mAufgabe") AND ($this->A("TodoType") == 3 OR $this->A("TodoType") == 4 OR $this->A("TodoType") == 5) AND $this->A("TodoUserID") > 0){
			$F = new Factory("Aufgabe");
			$F->sA("AufgabeByClass", "Todo");
			$F->sA("AufgabeByClassID", $id);
			
			$F->sA("AufgabeUserID", $this->A("TodoUserID"));
			$F->sA("AufgabeText", "Bericht für Termin eintragen");
			$F->sA("AufgabeCreated", time());
			$F->sA("AufgabeUntil", $this->A("TodoFromDay"));
			$F->sA("AufgabeUhrzeitVon", $this->A("TodoTillTime"));
			
			$F->store();
		}
		
			
		return $id;
	}

	public function deleteMe() {
		if(Session::isPluginLoaded("mGoogle"))
			Google::calendarDeleteEvent(mTodoGUI::getCalendarDetails("Todo", $this->getID()));
		
		$AC = anyC::get("Todo", "TodoExceptionForID", $this->getID());
		while($T = $AC->getNextEntry())
			$T->deleteMe();
		
		if($this->A("TodoClass") == "DBMail" AND Session::isPluginLoaded("mMail"))
			Mail::assignRevoke("Todo", $this->getID(), $this->A("TodoClassID"));
		
		
		if(Session::isPluginLoaded("mAufgabe") AND ($this->A("TodoType") == 3 OR $this->A("TodoType") == 4 OR $this->A("TodoType") == 5) AND $this->A("TodoUserID") > 0){
			$AC = anyC::get("Aufgabe", "AufgabeByClass", "Todo");
			$AC->addAssocV3("AufgabeByClassID", "=", $this->getID());
			while($A = $AC->n())
				$A->deleteMe();
		}
		
		parent::deleteMe();
	}

	public function addFile($id){
		mFileGUI::addFile("Todo",$this->ID, $id);
		echo "message:EventMessages.M004";
	}

	public function getCalendarTitle(){
		return trim($this->A("TodoName"));
	}
	
	public static function newFromKalenderEvent(KalenderEvent $KE){
		$T = new Todo(-1);
		$T->loadMeOrEmpty();
		
		$T->changeA("TodoFromDay", Kalender::parseDay($KE->getDay()));
		$T->changeA("TodoFromTime", Kalender::parseTime($KE->getTime()));
		$T->changeA("TodoTillDay", Kalender::parseDay($KE->getEndDay()));
		$T->changeA("TodoTillTime", Kalender::parseTime($KE->getEndTime()));
		$T->changeA("TodoType", "2");
		$T->changeA("TodoName", $KE->title());
		$T->changeA("TodoClass", $KE->ownerClass());
		$T->changeA("TodoClassID", $KE->ownerClassID());
		$T->changeA("TodoUserID", Session::currentUser()->getID());
		$T->changeA("TodoOrganizer", $KE->organizer());
		
		return $T->newMe();
	}
}
?>
