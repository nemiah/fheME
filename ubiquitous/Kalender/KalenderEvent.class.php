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
class KalenderEvent extends KalenderEntry {
	private $day;
	private $time;
	private $allDay = false;
	
	private $endDay;
	private $endTime;

	public static $z = 100;
	public static $stack = array();
	public static $count = 0;
	
	private $canNotify = false;
	private $notified = false;
	private $exception = false;
	public static $displayNr = 0;
	
	private $exceptions = array();
	private $currentWhen;
	private $owner;
	private $topButtons = array();
	
	private $status = 0; //see TodoGUI::getStatus
	
	function __construct($className, $classID, $day, $time, $title) {
		$this->className = $className;
		$this->classID = $classID;
		
		$this->day = $day;
		$this->time = $time;
		$this->title = $title;
		#echo $day."<br />";
		#$cBG = self::$count;
		#if($cBG > count($this->bgColors))
		#	self::$count = 0;
		#$this->bgColor = $this->bgColors[self::$count];
		#self::$count++;
		
		parent::__construct();
	}
	
	function status($status){
		$this->status = $status;
	}
	
	function allDay($allday = null){
		if($allday === null)
			return $this->allDay;
		
		$this->allDay = $allday;
	}
	
	function owner($UserID){
		$this->owner = $UserID;
	}
	
	function currentWhen($currentWhen = null){
		if($currentWhen == null)
			return $this->currentWhen;
		
		$this->currentWhen = $currentWhen;
	}
	
	function exception($starttime, $isDeleted, $forID){
		$this->exception = array($starttime, $isDeleted, $forID);
	}
	
	function getException(){
		return $this->exception;
	}
	
	function makeException($exceptionEvents){
		foreach($exceptionEvents AS $exceptionEvent){
			$exceptionData = $exceptionEvent->getException();
			
			if($this->classID != $exceptionData[2])
				continue;

			$this->exceptions[date("dmYHi", $exceptionData[0])] = true;#$exceptionEvent->getDay().$exceptionEvent->getTime()] = true;
		}
	}
	
	function when($startDay, $endDay){
		$when = array();
		$W = new stdClass();
		$W->day = $this->day;
		$W->endDay = $this->endDay;
		$W->time = $this->time;
		$when[] = $W;

		$pDay = Kalender::parseDay($this->day);
		$peDay = Kalender::parseDay($this->endDay);
		
		if($peDay > $pDay){
			$D = new Datum($pDay);
			while($D->time() <= $peDay){
				$newStamp = Kalender::formatDay($D->time());

				$D->addDay();

				if($D->time() < $startDay OR $D->time() > $endDay + 3600 * 25)
					continue;
				
				#echo $D->time()." &lt; ".$startDay." OR ".$D->time()." &gt; ".$endDay."<br />";
				
				if($newStamp == $this->day)
						continue;

				$W = new stdClass();
				$W->day = $newStamp;
				$W->endDay = $this->endDay;
				$W->time = $this->time;
				$when[] = $W;

			}
		}
		
		$firstDay = Kalender::parseDay($this->day);

		if($this->repeat){
			switch($this->repeatInterval){
				case "weekly":

					$weekDay = date("w", $pDay);
					$D = new Datum($startDay);
					while($D->time() <= $endDay){
						if(date("w", $D->time()) == $weekDay)
							break;
						
						$D->addDay();
					}

					while($D->time() <= $endDay){
						$newDay = Kalender::formatDay($D->time());
						$D->addWeek(true);
						if($newDay == $this->day)
								continue;

						if($D->time() <= $firstDay)
							continue;

						if(isset($this->exceptions[$newDay.$this->time]))
							continue;
						
						$W = new stdClass();
						$W->day = $newDay;
						#echo ";";
						$W->endDay = $newDay;
						#echo "HI!<br />";
						$W->time = $this->time;
						$when[] = $W;

					}
				break;
				
				case "monthly":

					$monthDay = date("d", $pDay);
					$D = new Datum($startDay);
					while($D->time() <= $endDay){
						$newStamp = Kalender::formatDay($D->time());
						$newDay = date("d", $D->time());
						$D->addDay();
						
						if($newStamp == $this->day)
								continue;
						
						if($newDay != $monthDay)
							continue;

						if(isset($this->exceptions[$newDay.$this->time]))
							continue;
						
						$W = new stdClass();
						$W->day = $newStamp;
						$W->endDay = $newStamp;
						$W->time = $this->time;
						$when[] = $W;

					}
				break;
				
				case "yearly":
					#$monthDay = date("d", Kalender::parseDay($this->day));
					$D = new Datum(mktime(0, 1, 0, date("m", $pDay), date("d", $pDay), date("Y")));#$pDay);
					while($D->time() <= $endDay){
						$newStamp = Kalender::formatDay($D->time());
						
						$D->addYear();
						
						if($newStamp == $this->day)
								continue;
						
						if(date("Y", $D->time()) <= date("Y", $endDay))
							continue;
						#echo date("Y", $D->time())." &lt; ".date("Y", $endDay)."<br />";
						if(isset($this->exceptions[$newStamp.$this->time]))
							continue;
						
						$W = new stdClass();
						$W->day = $newStamp;
						$W->endDay = $newStamp;
						$W->time = $this->time;
						$when[] = $W;

					}
				break;
			}
		}

		#echo "<pre style=\"font-size:10px;\">$this->title";
		#print_r($when);
		#echo "</pre>";
		
		return $when;
	}
	
	function getDay(){
		return $this->day;
	}

	function getTime(){
		return $this->time;
	}

	function getEndDay(){
		return $this->endDay;
	}

	function getEndTime(){
		return $this->endTime;
	}

	function endDay($day){
		$this->endDay = $day;
	}

	function endTime($time){
		$this->endTime = $time;
	}
	
	function addTopButton(Button $B){
		$this->topButtons[] = $B;
	}
	
	function canNotify($canNotify = true, $notified = false){
		$this->canNotify = $canNotify;
		
		$this->notified = $notified;
	}
	
	function repeatable($callMethod){
		$this->isRepeatable = $callMethod;
		
	}

	public static $multiDayColors = array();
	public static $multiDayStack = array();
	function getWeekViewHTML($time, $multiDayOffset = 0){
		if($this->exception !== false AND $this->exception[1] == "1")
			return "";
		
		self::$displayNr = 3;
		
		
		$B = "";
		if($this->icon != null){
			$B = new Button("", $this->icon, "icon");
			$B->style("margin-top:-4px;margin-left:-2px;");
		}
		
		$this->onClick = str_replace(array("%%CLASSNAME%%", "%%CLASSID%%", "%%TIME%%"), array($this->className, $this->classID, $time != null ? $time : ""), $this->onClick);

		$startTime = /*Kalender::parseDay($this->day) - 60 + */Kalender::parseTime($this->time);
		$endTime = /*Kalender::parseDay($this->currentWhen->endDay) - 60 + */Kalender::parseTime($this->endTime);

		$multiDays = false;
		if($this->day != $this->endDay OR $this->allDay){
			$multiDays = true;
			
			$stackCalcFirst = Kalender::parseDay($this->day);
			$stackCalcLast = Kalender::parseDay($this->currentWhen->endDay);
			$D = new Datum($stackCalcFirst);
			while($D->time() <= $stackCalcLast){
				if(!isset(self::$stack[date("Ymd", $D->time())]))
					self::$stack[date("Ymd", $D->time())] = array();
				
				if(in_array($this->className.$this->classID, self::$stack[date("Ymd", $D->time())])){
					$D->addDay();
					continue;
				}
				
				self::$stack[date("Ymd", $D->time())][] = $this->className.$this->classID;
				$D->addDay();
			}
		}
		#echo "<pre style=\"font-size:10px;\">";
		#print_r(self::$stack);
		#echo "</pre>";
		if(Kalender::parseDay($this->currentWhen->endDay) - 60 + Kalender::parseTime($this->endTime) > $time + 24 * 3600) //OK
			$endTime = 24 * 3600;
		
		if(Kalender::parseDay($this->day) - 60 + Kalender::parseTime($this->time) < $time)
			$startTime = 60 + Kalender::parseTime($this->time);
		
		
		$height = ceil(($endTime - $startTime) / 3600 * 22);
		for($h = $startTime; $h < $endTime; $h += 3600)
			$height -= $h < 3600 * 6 ? 11 : 0;
		
		if($height < 22)
			$height = 22;
		
		
		$top = 0;
		$hours = substr($this->time, 0, 2);
		$minutes = (substr($this->time, 2, 2) / 60);
		for($h = 0; $h < $hours; $h++)
			$top += $h < 6 ? 11 : 22;
		if($hours > 5)
			$top += $minutes * 22;
		
		
		if($multiDays){
			$height = 22;
			if(!isset(self::$multiDayStack[$this->className.$this->classID]))
				self::$multiDayStack[$this->className.$this->classID] = count(self::$multiDayStack);
			#echo date("Ymd", $time).":".count(self::$stack[date("Ymd", $time)])."<br />";
			$top = array_search($this->className.$this->classID, self::$stack[date("Ymd", $time)]) * 22;
			#$top = self::$multiDayStack[$this->className.$this->classID] * 22;
		} else
			$top += $multiDayOffset * 22;
		
		
		$grey = false;
		if($this->owner != null AND $this->owner != -1 AND $this->owner != Session::currentUser()->getID())
			$grey = true;
		
		if($this->status == 2)
			$grey = true;
		
		
		$bgColor = "rgba(126, 194, 37, 0.5)";
		$titleColor = "rgb(126, 194, 37)";
		if($multiDays AND $this->day != date("dmY", $time))
				$titleColor = "rgba(126, 194, 37, 0.5)";
		return "
			<div class=\"weekEventEntry\" onclick=\"$this->onClick\" style=\"background-color:".($grey ? "#DDD" : $bgColor).";padding:0px;cursor:pointer;height:".$height."px;overflow:hidden;position:absolute;margin-top:{$top}px;width:137px;\">
				<div style=\"padding:5px;background-color:$titleColor;\">
					<div style=\"overflow:hidden;\">
					".(!$this->allDay ? "<b>".$this->formatTime($this->time)."</b>&nbsp;" : "")."".str_replace(" ", "&nbsp;", $this->title)."
					</div>
				</div>
			</div>";
	}
	
	function getDayViewHTML($time = null){
		if($this->exception !== false AND $this->exception[1] == "1")
			return "";
		
		if(self::$displayNr > count($this->bgColors))
			self::$displayNr = 0;
		
		
		$B = "";
		if($this->icon != null){
			$B = new Button("", $this->icon, "icon");
			$B->style("margin-top:-4px;margin-left:-2px;");
		}
		
		$this->onClick = str_replace(array("%%CLASSNAME%%", "%%CLASSID%%", "%%TIME%%"), array($this->className, $this->classID, $time != null ? $time : ""), $this->onClick);

		$startTime = Kalender::parseDay($this->day) - 60 + Kalender::parseTime($this->time);
		$endTime = Kalender::parseDay($this->currentWhen->endDay) - 60 + Kalender::parseTime($this->endTime);

		
		if($endTime > $time + 24 * 3600 OR $this->allDay) //OK
			$endTime = $time + 24 * 3600 - 61;
		
		if($startTime < $time)
			$startTime = $time - 60 + Kalender::parseTime($this->time);
		
		$i = 0;
		while($startTime + $i < $endTime){
			self::$stack[date("Hi", $startTime + $i)][] = true;
			$i += 60;
		}
		
		$height = ceil(($endTime - $startTime) / 3600 * 40);
		if($height < 20)
			$height = 20;
		$top = (substr($this->time, 0, 2) + (substr($this->time, 2, 2) / 60)) * 40;
		
		$left = 80 + (count(self::$stack[$this->time]) - 1) * 210;
		if($top + $height > 24 * 40)
			$height = 24 * 40 - $top;
		
		$grey = false;
		if($this->owner != null AND $this->owner != -1 AND $this->owner != Session::currentUser()->getID())
			$grey = true;
		
		if($this->status == 2)
			$grey = true;
		
		return "
			<div onclick=\"$this->onClick\" style=\"background-color:".($grey ? "#DDD" : $this->bgColors[self::$displayNr++]).";position:absolute;top:{$top}px;left:{$left}px;padding:0px;cursor:pointer;height:".$height."px;margin-right:10px;width:200px;overflow:hidden;z-index:".(self::$z--).";\">
				<div style=\"padding:5px;\">
					<span style=\"float:left;margin-right:3px;\">
						$B
					</span>
				
					<!--<span style=\"display:none;\" id=\"time_$this->className$this->classID$this->time\">$this->time<br /></span>-->".(!$this->allDay ? "<b>".$this->formatTime($this->time)."</b>&nbsp;" : "")."$this->title
				</div>
			</div>";
	}

	function getInfo($time){
		$BE = "";
		$BD = "";
		$BDS = "";
		if($this->editable != null){
			$BE = new Button("Bearbeiten", "edit", "icon");
			$BE->style("margin:10px;float:right;");
			$BE->popup("", "Kalendereintrag bearbeiten", "mKalender", $this->classID, "editInPopup", array("'".$this->className."'", $this->classID, "'{$this->editable[0]}'"));

			$BD = new Button("Dieses Event Löschen", "trash", "icon");
			$BD->style("float:right;margin:10px;");
			$BD->onclick("if(confirm('Löschen?')) ");
			$BD->rmePCR(str_replace("GUI", "", $this->className), $this->classID, $this->editable[1], $this->classID, "contentManager.reloadFrame('contentLeft'); Popup.close('mKalender', 'edit');");
			
			if($this->repeat() !== false){
				$BD->rmePCR(str_replace("GUI", "", $this->className), $this->classID, $this->editable[1], array($this->classID, $time+Kalender::parseTime($this->time)-60), "contentManager.reloadFrame('contentLeft'); Popup.close('mKalender', 'edit');");
				
				$BDS = new Button("Alle Events Löschen", "./ubiquitous/Kalender/deleteSeries.png", "icon");
				$BDS->style("float:right;margin:10px;");
				$BDS->onclick("if(confirm('Löschen?')) ");
				$BDS->rmePCR(str_replace("GUI", "", $this->className), $this->classID, $this->editable[1], $this->classID, "contentManager.reloadFrame('contentLeft'); Popup.close('mKalender', 'edit');");
			
			}
			
		}

		$T = new HTMLTable(2, "Eventdetails");
		$T->setColWidth(1, 120);
		
		$T->addLV("Betreff", $this->title);

		$T->addLV("Tag", Util::CLDateParser($time));
		
		if(!$this->allDay)
			$T->addLV("Uhrzeit", $this->formatTime($this->time));
		else
			$T->addLV("Uhrzeit","Ganzer Tag");
		
		$T->addLV("Details", $this->summary);
		
		if(count($this->values) > 0)
			$T->insertSpaceAbove();
		foreach($this->values AS $label => $value)
			$T->addLV($label, $value);

		$BN = "";
		if($this->canNotify){
			$BN = new Button("Termin-\nbestätigung", "mail".($this->notified ? "ed" : ""), "icon");
			$BN->style("margin-top:10px;margin-left:10px;");
			$BN->popup("", "Terminbestätigung", "Util", "-1", "EMailPopup", array("'mKalender'", "-1", "'notification::$this->className::$this->classID::$time'", "'function(){ Kalender.refreshInfoPopup(); }'"));
		}
		
		$BR = "";
		if($this->isRepeatable AND $this->getException() === false){
			$BR = new Button("Wiederholungen", "refresh", "icon");
			$BR->style("margin:10px;float:right;");
			$BR->rmePCR("mKalender", "-1", "getRepeatable", array("'$this->className'", "'$this->classID'", "'$this->isRepeatable'"), "function(transport){ \$j('#eventAdditionalContent').html(transport.responseText).slideDown(); }");
		}
		
		$topButtons = "";
		foreach($this->topButtons AS $B){
			$B->type("icon");
			$B->style("margin-top:10px;margin-left:10px;");
			$topButtons .= $B;
		}
		
		return $BDS.$BD.$BE.$BN.$topButtons.$BR."<div style=\"clear:both;\"></div><div style=\"display:none;\" id=\"eventAdditionalContent\"></div>".$T;
	}

	function getMinimal($time){
		if($this->exception !== false AND $this->exception[1] == "1")
			return "";
		
		$B = "";
		if($this->icon != null){
			$B = new Button("", $this->icon);
			$B->type("icon");
		}
		
		$onClick = str_replace(array("%%CLASSNAME%%", "%%CLASSID%%", "%%TIME%%"), array($this->className, $this->classID, $time), $this->onClick);
		
		$startTime = Kalender::parseDay($this->day) - 60 + Kalender::parseTime($this->time);

		$grey = false;
		if($this->owner != null AND $this->owner != -1 AND $this->owner != Session::currentUser()->getID())
			$grey = true;
		
		#$('time_$this->className$this->classID$this->time').style.display = ''; 
		$zeit = "<b>".$this->formatTime($this->time)."</b>";
		if($startTime < $time AND $this->repeat == "0")
			$zeit = "";
		return "
			<div onclick=\"$onClick\" style=\"".($this->status == 2 ? "color:grey;" : "")."clear:left;padding:2px;padding-left:4px;cursor:pointer;".($grey ? "color:grey;" : "")."\">
				$zeit&nbsp;$this->title
			</div>";
	}
	
	public function getAdresse(){
		$C = $this->className;
		$C = new $C(-1); //its a collection (quite sure)
		
		return $C->getAdresse($this->classID);
	}
}
?>