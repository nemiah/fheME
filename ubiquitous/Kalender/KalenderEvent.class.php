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
class KalenderEvent extends KalenderEntry {
	private $day;
	private $time;
	private $allDay = false;
	
	private $endDay;
	private $endTime;

	public static $z = 100;
	public static $stack = array();
	public static $colsStack = null;
	public static $count = 0;
	
	private $canNotify = false;
	private $notified = false;
	private $exception = false;
	public static $displayNr = 0;
	
	private $exceptions = array();
	private $currentWhen;
	private $owner;
	private $topButtons = array();
	private $isRepeatable;
	private $isMovable;
	private $isCloneable;
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
	
	function timestamp(){
		return Kalender::parseDay($this->day) + Kalender::parseTime($this->time) - 60;
	}
	
	function status($status){
		$this->status = $status;
	}
	
	function allDay($allday = null){
		if($allday === null)
			return $this->allDay;
		
		$this->allDay = $allday;
	}
	
	function owner($UserID = null){
		if($UserID == null)
			return $this->owner;
		else
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
		if($this->exception !== false AND $this->exception[1] == "1")
			return array();
		
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
			switch($this->repeatType){
				case "daily":
					$days = array();
					if($this->repeatDayOfWeek != ""){
						$days = explode (",", $this->repeatDayOfWeek);
						$when = array(); //always reset first day
					}
					
					$D = new Datum($startDay);
					
					while($D->time() <= $endDay){
						$newDay = Kalender::formatDay($D->time());
						$cTime = $D->time();
						$D->addDay();
						#if($newDay == $this->day)
						#s		continue;

						if($D->time() <= $firstDay)
							continue;

						if(isset($this->exceptions[$newDay.$this->time]))
							continue;
						
						if(!in_array(date("w", $cTime), $days))
							continue;
						
						$W = new stdClass();
						$W->day = $newDay;
						$W->endDay = $newDay;
						$W->time = $this->time;
						$when[] = $W;

					}
				break;
				
				case "weekly":
					#echo $this->title().":<br>";
					#echo "Event first day: ".Util::CLDateParser($firstDay)."<br>";
					#echo "Display start day: ".Util::CLDateParser($startDay)."<br>";
					#echo "display end day: ".Util::CLDateParser($endDay)."<br>";
					#echo "Event interval: ".($this->repeatInterval + 1)."<br>";

					$D = new Datum($firstDay);
					while($D->time() <= $endDay){
						#echo $this->weeks($firstDay, $D->time())."<br>";
						if(
							$D->time() >= $startDay 
							AND 
							$this->weeks($firstDay, $D->time()) % ($this->repeatInterval + 1) == 0
						)
							break;
						
						$D->addWeek(true);
						#if($this->weeks($firstDay, $D->time()) % ($this->repeatInterval + 1) == 0)
						#	echo "!";
						#$D->printer();
					}
					#$D->printer();
					
					#echo "------<br>";
					
					$D->subWeek(true);
					#$D->printer();
					#if($startDay < $firstDay)
					#	$D = new Datum ($firstDay);
					#$counter = floor(($D->time() - $firstDay) / (3600 * 24 * 7));
					#echo $counter."<br />";
					while($D->time() <= $endDay){
						
						if($this->repeatUntil > 0  AND $D->time() >= $this->repeatUntil)
							break;
						
						$D->addWeek(true);
						$newDay = Kalender::formatDay($D->time());
						if($newDay == $this->day){
							#$counter++;
							continue;
						}
						
						
						if($this->weeks($firstDay, $D->time()) % ($this->repeatInterval + 1) != 0){
							#$counter++;
							continue;
						}
						
						if($D->time() <= $firstDay)
							continue;

						if(isset($this->exceptions[$newDay.$this->time]))
							continue;
						
						$W = new stdClass();
						$W->day = $newDay;
						$W->endDay = $newDay;
						$W->time = $this->time;
						$when[] = $W;
					}
					#echo "END;<br><br>";
				break;
				
				case "monthly":
					$D = new Datum($startDay);
					#echo $this->repeatWeekOfMonth;
					if($this->repeatWeekOfMonth > 0 AND $this->repeatWeekOfMonth != 127){
						#$c = -1;
						$weekDay = date("w", $pDay);
						
						#if(date("d", $D->time()) > 1 AND date("d", $D->time()) < 20)
							#$c = 0;
						
						while($D->time() <= $endDay){
							$newStamp = Kalender::formatDay($D->time());
							$newDay = date("d", $D->time());
							$newWeekDay = date("w", $D->time());
							
							#if(date("d", $D->time()) == 1)
							#	$c = 0;
							$ts = $D->time();
							$D->addDay();
							
							#if($c < 0)
							#	continue;
							
							
							if($newWeekDay != $weekDay)
								continue;
							
							#$c++;
							
							if($newStamp == $this->day)
								continue;
							#if($c != $this->repeatWeekOfMonth)
							#	continue;
							
							if(isset($this->exceptions[$newDay.$this->time]))
								continue;

							if(!$this->isNth($this->repeatWeekOfMonth, $ts))
								continue;
							
							$W = new stdClass();
							$W->day = $newStamp;
							$W->endDay = $newStamp;
							$W->time = $this->time;
							$when[] = $W;

						}
					} elseif($this->repeatWeekOfMonth == 0) {
						$monthDay = date("d", $pDay);
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
					} elseif($this->repeatWeekOfMonth == 127){
						$when = array(); //always reset first day
						
						$monthDay = date("d", $pDay);
						while($D->time() <= $endDay){
							$newStamp = Kalender::formatDay($D->time());
							$newDay = date("d", $D->time());
							$maxDays = $D->getMaxDaysOfMonth();
							$D->addDay();
							
							if($newDay != $maxDays)
								continue;

							if(isset($this->exceptions[$newDay.$this->time]))
								continue;

							$W = new stdClass();
							$W->day = $newStamp;
							$W->endDay = $newStamp;
							$W->time = $this->time;
							$when[] = $W;

						}
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
	
	function weeks($date1, $date2) {
		if($date1 > $date2) 
			return $this->datediffInWeeks($date2, $date1);
		
		$first = new DateTime("@".$date1);
		$second = new DateTime("@".$date2);
		
		return round($first->diff($second)->days / 7);
	}
	
	private function isNth($nth, $time){
		$D = new Datum($time);
		$D->setToMonth1st();
		
		$w = date("w", $time);
		$c = 0;
		for($i = 1; $i <= date("d", $time); $i++){
			if(date("w", $D->time()) == $w)
				$c++;
			
			$D->addDay();
		}

		return $nth == $c;
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
	
	function movable($callMethod){
		$this->isMovable = $callMethod;
	}
	
	function cloneable($callMethod){
		$this->isCloneable = $callMethod;
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
					<small>".(!$this->allDay ? "<b>".$this->formatTime($this->time)."</b>&nbsp;" : "")."".str_replace(" ", "&nbsp;", $this->title)."</small>
					</div>
				</div>
			</div>";
	}
	
	function getDayViewHTML($time = null){
		if($this->exception !== false AND $this->exception[1] == "1")
			return "";
		
		if(self::$displayNr > count(self::$bgColors) - 1)
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
		
		
		
		if(self::$colsStack == null)
			self::$colsStack = array(array(), array(), array(), array());
		
		$useCol = null;
		for($i = 0; $i < count(self::$colsStack); $i++){
			if(!isset(self::$colsStack[$i][date("Hi", $startTime)])){
				$useCol = $i;
				break;
			}
		}
		
		$minutes = $endTime - $startTime;
		if($minutes < 30 * 60)
			$minutes = 30 * 60;

		$i = 0;
		while($i < $minutes){
			self::$colsStack[$useCol][date("Hi", $startTime + $i)] = true;
			$i += 60;
		}
		
		
		$height = ceil(($endTime - $startTime) / 3600 * 40);
		if($height < 20)
			$height = 20;
		$top = (substr($this->time, 0, 2) + (substr($this->time, 2, 2) / 60)) * 40;
		
		$left = 80 + $useCol * 210;
		if($top + $height > 24 * 40)
			$height = 24 * 40 - $top;
		
		$grey = false;
		if($this->owner != null AND $this->owner != -1 AND $this->owner != Session::currentUser()->getID())
			$grey = true;

		if($this->status == 2)
			$grey = true;
		#echo "$this->title: col $useCol; $left x $top<br />";
		return "
			<div onclick=\"$this->onClick\" style=\"background-color:".($grey ? "#DDD" : self::$bgColors[self::$displayNr++]).";position:absolute;top:{$top}px;left:{$left}px;padding:0px;cursor:pointer;height:".$height."px;margin-right:10px;width:200px;overflow:hidden;z-index:".(self::$z--).";\">
				<div style=\"padding:5px;\">
					<span style=\"float:left;margin-right:3px;\">
						$B
					</span>
				
					<!--<span style=\"display:none;\" id=\"time_$this->className$this->classID$this->time\">$this->time<br /></span>-->".(!$this->allDay ? "<b>".$this->formatTime($this->time)."</b>&nbsp;" : "")."$this->title
				</div>
			</div>";
	}

	function getInfo($time){
		if($time == null)
			$time = Kalender::parseDay($this->day);
		
		$BE = "";
		$BD = "";
		$BDS = "";
		if($this->editable != null){
			if($this->editable[0] != null){
				$BE = new Button("Bearbeiten", "edit", "icon");
				$BE->style("margin:10px;float:right;");
				$BE->popup("", "Kalendereintrag bearbeiten", "mKalender", $this->classID, "editInPopup", array("'".$this->className."'", $this->classID, "'{$this->editable[0]}'"));
			}
			
			$BD = new Button("Dieses Event löschen", "trash", "icon");
			$BD->style("float:right;margin:10px;");
			$BD->doBefore("if(confirm('Löschen?')) %AFTER");
			$BD->rmePCR(str_replace("GUI", "", $this->className), $this->classID, $this->editable[1], $this->classID, "contentManager.reloadFrame('contentScreen'); Popup.close('mKalender', 'edit');");
			
			if($this->repeat() !== false){
				$BD->rmePCR(str_replace("GUI", "", $this->className), $this->classID, $this->editable[1], array($this->classID, $time+Kalender::parseTime($this->time)-60), "contentManager.reloadFrame('contentLeft'); Popup.close('mKalender', 'edit');");
				
				$BDS = new Button("Alle Events Löschen", "./ubiquitous/Kalender/deleteSeries.png", "icon");
				$BDS->style("float:right;margin:10px;");
				$BDS->doBefore("if(confirm('Löschen?')) %AFTER");
				$BDS->rmePCR(str_replace("GUI", "", $this->className), $this->classID, $this->editable[1], $this->classID, "contentManager.reloadFrame('contentLeft'); Popup.close('mKalender', 'edit');");
			
			}
			
		}

		$T = new HTMLTable(2, "Eventdetails");
		$T->setColWidth(1, 120);
		$T->addColStyle(1, "vertical-align:top;");
		
		$T->addLV("Betreff", $this->title);

		$T->addLV("Tag", Util::CLDateParser($time));
		
		if(!$this->allDay)
			$T->addLV("Uhrzeit", $this->formatTime($this->time));
		else
			$T->addLV("Uhrzeit","Ganzer Tag");
		
		$T->addLV("Details", "<div style=\"max-height:300px;overflow:auto;\">".$this->summary."</div>");
		
		if($this->organizer)
			$T->addLV("Organisator", $this->organizer);
		
		if(count($this->values) > 0)
			$T->insertSpaceAbove();
		foreach($this->values AS $label => $value)
			$T->addLV($label, $value);

		$BN = "";
		if($this->canNotify){
			// TODO: Entfernen sobald Einladungen funktionieren
			$BN = new Button("Terminbestätigung", "mail".($this->notified ? "ed" : ""), "icon");
			$BN->style("margin-top:10px;margin-left:10px;");
			$BN->popup("", "Terminbestätigung", "Util", "-1", "EMailPopup", array("'mKalender'", "-1", "'notification::$this->className::$this->classID::$time'", "'function(){ Kalender.refreshInfoPopup(); }'"));
		}
		
		$BR = "";
		if($this->isRepeatable AND $this->getException() === false){
			$BR = new Button("Wiederholungen", "refresh", "icon");
			$BR->style("margin:10px;float:right;");
			$BR->rmePCR("mKalender", "-1", "getRepeatable", array("'$this->className'", "'$this->classID'", "'$this->isRepeatable'"), "function(transport){ \$j('#eventSideContent').html(''); \$j('#editDetailsmKalender').animate({'width':'400px'}, 200, 'swing'); \$j('#eventAdditionalContent').html(transport.responseText).slideDown(); }");
		}
		
		$BI = new Button("Teilnehmer", "./ubiquitous/Kalender/einladungen.png", "icon");
		$BI->style("margin: 10px; float: right;");
		$BI->rmePCR("mKalender", "-1", "getInvitees", array("'$this->className'", "'$this->classID'"), "function(t){ \$j('#eventAdditionalContent').html(''); \$j('#editDetailsmKalender').animate({'width':'800px'}, 200, 'swing', function(){ \$j('#eventSideContent').html(t.responseText).fadeIn(); }); }");
		
		if(!$this->canInvite)
			$BI = "";
		
		$closed = "";
		if($this->closeable){
			$BC = new Button("Termin abschließen", "bestaetigung", "icon");
			$BC->style("margin: 10px; float: right;");
			$BC->rmePCR("mKalender", "-1", "getClose", array("'$this->className'", "'$this->classID'"), "function(t){ \$j('#editDetailsContentmKalender').html(t.responseText); }");
			
			if($this->closed[0]){
				$BC = "";
				$closed = "<p>Termin abgeschlossen am ".Util::CLDateParser($this->closed[0]).($this->closed[1] != "" ? ":<br>".nl2br($this->closed[1]) : "")."</p>";
			}
		}
		
		$topButtons = "";
		foreach($this->topButtons AS $B){
			$B->type("icon");
			$B->style("margin-top:10px;margin-left:10px;");
			$topButtons .= $B;
		}
		
		return "<div style=\"width:400px;\">".$BDS.$BD.$BE.$BN.$topButtons.$BR.$BI.$BC.$closed."</div><div style=\"clear:both;\"></div><div style=\"display:none;\" id=\"eventAdditionalContent\"></div><div style=\"display:none;width:400px;float:right;\" id=\"eventSideContent\"></div><div style=\"width:400px;float:left;\" id=\"eventDefaultContent\"$T</div>";
	}
	
	public function getInviteForm() {
		
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
		
		if($this->allDay)
			$zeit = "";
		
		return "
			<div class=\"".($this->isMovable ? "movable" : "")."\" data-clonecallback=\"".str_replace("GUI", "", $this->ownerClass())."::".$this->isCloneable."\" data-movecallback=\"".str_replace("GUI", "", $this->ownerClass())."::".$this->isMovable."\" data-id=\"".$this->ownerClassID()."\" title=\"".strip_tags($this->title)."\" onclick=\"$onClick\" style=\"".($this->status == 2 ? "color:grey;" : "")."white-space:nowrap;overflow:hidden;height:13px;/*width:60px;*/clear:left;padding:2px;padding-left:4px;cursor:pointer;".($grey ? "color:grey;" : "")."\">
				<small>".(($grey AND isset(mKalenderGUI::$colors[$this->owner])) ? "<div style=\"display:inline-block;margin-right:3px;width:5px;background-color:".mKalenderGUI::$colors[$this->owner].";\">&nbsp;</div>" : "")."$zeit $this->title</small>
			</div>";
	}
	
	public function getAdresse(){
		$C = $this->className;
		$C = new $C(-1); //its a collection (quite sure)
		
		return $C->getAdresse($this->classID);
	}
	
	/**
	 * Gibt die xCal-Repräsentation dieses Objektes zurück.
	 * @return DOMElement
	 */
	public function toXCal() {
		$xCalData = new xCalDataEvent();
		#$dateTime = new DateTime();
		
		$xCalData->setUid($this->UID);
		$xCalData->setSummary($this->title);
		// TODO: Wiederholende Termine über when holen --> Wiederholungen über xCal angeben
		// Parameter vermutlich Timestamp
		
		/*if (is_null($this->time)) {
			$dtStart = $this->day;
			// TODO: Testen ob Timestamp oder nicht
			$xCalData->setDtStartValue(xCalDataEvent::DTVALUE_DATE);
			$xCalData->setDtStart(gmdate("Ymd", $dtStart));
		} else {*/
			$dtStart = Kalender::parseDay($this->day) + Kalender::parseTime($this->time) - 60;
			#echo date("dmY His", $dtStart).": ".$this->title."<br />";
			// TODO: Test des Formats
			// parseTime parseDay Kalender-Klasse
			$xCalData->setDtStartValue(xCalDataEvent::DTVALUE_DATETIME);
			$xCalData->setDtStart(gmdate("Ymd", $dtStart) . "T" . gmdate("His", $dtStart) . "Z");
		#}
		
		/*if (is_null($this->endTime)) {
			$dtEnd = $this->endDay;
			$xCalData->setDtEnd(gmdate("Ymd", $dtEnd));
			$xCalData->setDtEndValue(xCalDataEvent::DTVALUE_DATE);*/
		#} else {
			$dtEnd = Kalender::parseDay($this->endDay) + Kalender::parseTime($this->endTime) - 60;
			$xCalData->setDtEnd(gmdate("Ymd", $dtEnd) . "T" . gmdate("His", $dtEnd) . "Z");
			$xCalData->setDtEndValue(xCalDataEvent::DTVALUE_DATETIME);
		#}
		#$dateTime->setTimestamp($dtStart);
		
		return xCalUtil::getXCalEventByXCalDataEvent($xCalData);
	}
	
}
?>