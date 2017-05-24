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
 *  2007 - 2017, Furtmeier Hard- und Software - Support@Furtmeier.IT
 */
class iCalUtil {
	/**
	 * @param string $iCal
	 * @param string $myEmail
	 * @param int $answer possible values: accept: 1, maybe: 2, decline: 3
	 */
	public static function answerInvitation(string $iCal, string $myEmail, int $answer){
		
		if($answer == "1")
			$text = "akzeptiert";

		if($answer == "2")
			$text = "vorläufig akzeptiert";

		if($answer == "3")
			$text = "abgelehnt";

		$VC = new vcalendar();
		$VC->parse($iCal);
		$event = $VC->getComponent("vevent");
		
		$targetAttendee = "MAILTO:".$myEmail;
		
		$i = 1;
		while($valueOccur = $event->getProperty("ATTENDEE", $i, true)){
			if(stripos($valueOccur["value"], $targetAttendee) === false){
				$i++;
				continue;
			}
			
			$params = $valueOccur["params"];
			if($answer == "1")
				$params["PARTSTAT"] = "ACCEPTED";
			
			if($answer == "2")
				$params["PARTSTAT"] = "TENTATIVE";
			
			if($answer == "3")
				$params["PARTSTAT"] = "DECLINED";
			
			if(isset($params["RSVP"]))
				unset($params["RSVP"]);
			
			$event->setAttendee(
				$targetAttendee,
				$params, $i);
				
			$i++;
		}
		
		$i = 1;
		while($valueOccur = $event->getProperty("ATTENDEE", $i, true)){
			if(stripos($valueOccur["value"], $targetAttendee) === false){
				$i++;
				$event->deleteProperty("ATTENDEE");
				continue;
			}
			$i++;
		}
		
		$event->setProperty("DTSTAMP", gmdate("Ymd")."T".gmdate("His")."Z");
		$event->setProperty("LAST-MODIFIED", gmdate("Ymd")."T".gmdate("His")."Z");
		
		
		$VC->deleteComponent("vevent");
		$VC->setComponent($event);

		$VC->setMethod("REPLY");
		
		$ics = $VC->createCalendar();
		
		$fromName = Session::currentUser()->A("name");
		$from = Session::currentUser()->A("UserEmail");
		
		$mail = new htmlMimeMail5();
	    $mail->setFrom(utf8_decode($fromName." <".$from.">"));
	    if(!ini_get('safe_mode')) $mail->setReturnPath($from);
	    $mail->setSubject(utf8_decode("Antwort Termineinladung (".ucfirst($text)."): ".$event->getProperty("SUMMARY")));
		
		$mail->addAttachment(
	    	new stringAttachment(
				$ics,
	    		"invite.ics",
	    		'application/ics')
	    );
		
		$mail->setCalendar($ics, "REPLY");
		$mail->setCalendarCharset("UTF-8");
		
		$mail->setTextCharset("UTF-8");
		$mail->setText("$fromName hat diesen Termin $text");
		
		$organizer = str_replace("MAILTO:", "", $event->getProperty("ORGANIZER"));
		
		return $mail->send(array($organizer));
	}
	
	public static function toKalenderEvent($iCal, $ownerClass = "iCal", $ownerClassID = "-1"){
		$VC = new vcalendar();
		$VC->parse($iCal);
		$event = $VC->getComponent("vevent");
		
		$dayStart = $event->getProperty("DTSTART");
		$dayEnd = $event->getProperty("DTEND");
		
		$dayStartTS = strtotime(implode("", $dayStart));
		$dayEndTS = strtotime(implode("", $dayEnd));
		
		$KE = new KalenderEvent($ownerClass, $ownerClassID, Kalender::formatDay($dayStartTS), Kalender::formatTime($dayStartTS), $event->getProperty("SUMMARY"));
		
		$KE->UID($event->getProperty("UID"));
		$organizer = $event->getProperty("ORGANIZER", 0, true);
		
		$organizer["value"] = str_replace("MAILTO:", "", $organizer["value"]);
		$ON = $organizer["value"];
		if(isset($organizer["params"]["CN"]))
			$ON = $organizer["params"]["CN"];
		
		$OE = $organizer["value"];
		$KE->organizer($ON, $OE);
		
		if($dayStart["hour"].$dayStart["min"] == "" AND $dayEnd["hour"].$dayEnd["min"] == ""){
			$KE->allDay(true);
		} else {
			$KE->endDay(Kalender::formatDay($dayEndTS));
			$KE->endTime(Kalender::formatTime($dayEndTS));
		}
		
		return $KE;
	}
}
?>