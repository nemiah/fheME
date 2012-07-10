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
class mKalender extends UnpersistentClass {
	public function getEMailData($parameters){
		$parameters = explode("::", $parameters);
		
		$className = $parameters[1];
		$classID = $parameters[2];
		$C = new $className($classID);
		$data = $C->getCalendarDetails($className, $classID);
		
		$adresse = $data->getAdresse();
		$emailData = $adresse->getEMailData();
		
		$emailData["body"] = "{Anrede},

hiermit bestätige ich Ihnen unseren Termin:

Start: ".Util::CLDateParser(Kalender::parseDay($data->getDay()))." um ".Util::CLTimeParser(Kalender::parseTime($data->getTime()))." Uhr".($data->getEndDay() > 0 ? "
Ende:  ".Util::CLDateParser(Kalender::parseDay($data->getEndDay()))." um ".Util::CLTimeParser(Kalender::parseTime($data->getEndTime()))." Uhr" :"")."

Beschreibung: ".str_replace("<br />\n", "\n", $data->summary())."

Freundliche Grüße
".Session::currentUser()->A("name");
		
		$emailData["subject"] = "Termininformation";
		
		return $emailData;
	}
	
	public function sendEmail($subject, $body, $recipientID, $parameters){
		$emailData = $this->getEMailData($parameters);
		
		$parameters = explode("::", $parameters);
		
		$className = $parameters[1];
		$classID = $parameters[2];
		$time = $parameters[3];
		
		$C = new $className($classID);
		$data = $C->getCalendarDetails($className, $classID);
		
		$adresse = $data->getAdresse();
		
		$fromName = Session::currentUser()->A("name");
		$from = Session::currentUser()->A("UserEmail");
		
		$mail = new htmlMimeMail5();
	    $mail->setFrom(utf8_decode($fromName." <".$from.">"));
	    if(!ini_get('safe_mode')) $mail->setReturnPath($from);
	    $mail->setSubject(utf8_decode($subject));
		/*
		$ics = "BEGIN:VCALENDAR
VERSION:2.0
PRODID:http://www.furtmeier.it
METHOD:PUBLISH
BEGIN:VTIMEZONE
TZID:Europe/Berlin
X-LIC-LOCATION:Europe/Berlin
BEGIN:DAYLIGHT
TZOFFSETFROM:+0100
TZOFFSETTO:+0200
TZNAME:CEST
DTSTART:19700329T020000
RRULE:FREQ=YEARLY;INTERVAL=1;BYDAY=-1SU;BYMONTH=3
END:DAYLIGHT
BEGIN:STANDARD
TZOFFSETFROM:+0200
TZOFFSETTO:+0100
TZNAME:CET
DTSTART:19701025T030000
RRULE:FREQ=YEARLY;INTERVAL=1;BYDAY=-1SU;BYMONTH=10
END:STANDARD
END:VTIMEZONE
BEGIN:VEVENT
UID:".$data->UID()."
ORGANIZER;CN=\"$fromName\":MAILTO:$from
SUMMARY:".$data->title()."
DESCRIPTION:".str_replace("<br />\n", "\n  ", $data->summary())."
CLASS:PUBLIC
DTSTART;TZID=Europe/Berlin:".date("Ymd", Kalender::parseDay($data->getDay()))."T".$data->getTime()."00
DTEND;TZID=Europe/Berlin:".date("Ymd", Kalender::parseDay($data->getEndDay()))."T".$data->getEndTime()."00
DTSTAMP:".date("Ymd")."T".date("His")."Z
END:VEVENT
END:VCALENDAR";*/
		$ics = "BEGIN:VCALENDAR
PRODID:-//lightCRM Kalender
VERSION:2.0
METHOD:REQUEST
BEGIN:VTIMEZONE
TZID:Europe/Berlin
X-LIC-LOCATION:Europe/Berlin
BEGIN:DAYLIGHT
TZOFFSETFROM:+0100
TZOFFSETTO:+0200
TZNAME:CEST
DTSTART:19700329T020000
RRULE:FREQ=YEARLY;BYDAY=-1SU;BYMONTH=3
END:DAYLIGHT
BEGIN:STANDARD
TZOFFSETFROM:+0200
TZOFFSETTO:+0100
TZNAME:CET
DTSTART:19701025T030000
RRULE:FREQ=YEARLY;BYDAY=-1SU;BYMONTH=10
END:STANDARD
END:VTIMEZONE
BEGIN:VEVENT
LAST-MODIFIED:20120629T094018Z
DTSTAMP:".date("Ymd")."T".date("His")."Z
UID:".$data->UID()."
SUMMARY:Test3
ORGANIZER;CN=\"$fromName\":MAILTO:$from
DTSTART;TZID=Europe/Berlin:".date("Ymd", Kalender::parseDay($data->getDay()))."T".$data->getTime()."00
DTEND;TZID=Europe/Berlin:".date("Ymd", Kalender::parseDay($data->getEndDay()))."T".$data->getEndTime()."00
END:VEVENT
END:VCALENDAR";
		
		$mail->addAttachment(
	    	new stringAttachment(
				$ics,
	    		"event.ics",
	    		'application/ics')
	    );
		
		$adresse->replaceByAnsprechpartner($recipientID);
		$body = str_replace("{Anrede}", Util::formatAnrede($_SESSION["S"]->getUserLanguage(), $adresse), $body);
		
		$mail->setCalendar($ics, "REQUEST");
		$mail->setTextCharset("UTF-8");
		$mail->setCalendarCharset("UTF-8");
		$mail->setText($body);
		
		#print_r($mail->getRFC822(array("Rainer@Furtmeier.de")));
		#die();
		
		$mail->send(array($emailData["recipients"][$recipientID][1]));
		
		$C->setNotified($className, $classID);
	}
	
	public function getData($firstDay, $lastDay){
		$K = new Kalender();
		$K->timeRange($firstDay, $lastDay);
		while($return = Registry::callNext("Kalender", "events", array($firstDay, $lastDay)))
			$K->merge($return);

		while($return = Registry::callNext("Kalender", "holidays", array($firstDay, $lastDay)))
			$K->merge($return);
		
		$K->exceptions();
		
		return $K;
	}
}

?>