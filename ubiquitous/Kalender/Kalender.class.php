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
class Kalender {
	private $events;
	private $eventsList = array();
	
	private $holidays;
	private $holidaysList = array();
	
	private $startDay;
	private $endDay;

	public function timeRange($startDay, $endDay){
		$this->startDay = $startDay;
		$this->endDay = $endDay;
	}

	/**
	 * Adds event and returns the object KalenderEvent
	 * for further modification
	 *
	 * @param int $day in format DDMMYY
	 * @param int $time in format HHII
	 * @param string $title
	 * @return KalenderEvent
	 */
	public function addEvent(KalenderEvent $KE){#, $classID = null, $day = null, $time = null, $title = null){
		$this->eventsList[] = $KE;

		return $KE;

	}
	public function addHolidays(KalenderHolidays $KH){
		$this->holidaysList[] = $KH;

		return $KH;
	}

	public function hasMultiDayEvents($firstDay, $lastDay){
		#echo date("dmY", $firstDay).";".$lastDay;
		$return = 0;
		$counted = array();
		$stack = array();
		
		foreach($this->events AS $D)
			foreach($D AS $T)
				foreach($T AS $E){
					
					if(($E->getDay() != $E->getEndDay() OR $E->allDay())){# AND !isset($counted[$E->ownerClass().$E->ownerClassID()])){
						
						$stackCalcFirst = Kalender::parseDay($E->currentWhen()->day);#$E->getDay()
						$stackCalcLast = Kalender::parseDay($E->currentWhen()->endDay);
						if($stackCalcFirst < $firstDay AND $stackCalcLast < $firstDay)
							continue;
						#echo $E->title()." (".$E->ownerClassID()."): ".$E->getDay()."/".$E->currentWhen()->day."<br />";
						#if($stackCalcFirst > $lastDay)
						#	continue;
						
						#$return++;
						#$counted[$E->ownerClass().$E->ownerClassID()] = true;
						
						#echo "::".$E->currentWhen()->day."<br />";
						if($stackCalcLast > $lastDay)
							$stackCalcLast = $lastDay;
						
						if($stackCalcFirst < $firstDay)
							$stackCalcFirst = $firstDay;
						
						$D = new Datum($stackCalcFirst);
						while($D->time() <= $stackCalcLast){
							if(!isset($stack[date("Ymd", $D->time())]))
								$stack[date("Ymd", $D->time())] = array();

							if(in_array($E->ownerClass().$E->ownerClassID(), $stack[date("Ymd", $D->time())])){
								$D->addDay();
								continue;
							}

							$stack[date("Ymd", $D->time())][] = $E->ownerClass().$E->ownerClassID();
							$D->addDay();
						}
					}
				}
				
		$max = 0;
		foreach($stack AS $ev)
			if(count($ev) > $max)
				$max = count($ev);
		
		#echo "<pre style=\"font-size:10px;\">";
		#print_r($stack);
		#echo "</pre>";
		return $max;
	}

	public function getEventsOnDay($day){
		if($this->events == null){
			$this->events = array();
			
			foreach($this->eventsList AS $KE){
				$whens = $KE->when($this->startDay, $this->endDay);
				foreach($whens AS $KEWhen){
					$KE2 = clone $KE;
					$KE2->currentWhen($KEWhen);
					$this->events[$KEWhen->day][$KEWhen->time][] = $KE2;
				}
			}
			
			foreach($this->events AS $d => $value)
				ksort($this->events[$d]);
		}

		if(!isset($this->events[$day]))
			return null;

		return $this->events[$day];
	}

	public function getHolidaysOnDay($day){
		if($this->holidays == null){
			$this->holidays = array();
			
			foreach($this->holidaysList AS $KH){
				foreach($KH->when($this->startDay, $this->endDay) AS $KHWhen)
					$this->holidays[$KHWhen->day][$KHWhen->time][] = $KH;
			}
			
			foreach($this->holidays AS $d => $value)
				ksort($this->holidays[$d]);
		}

		if(!isset($this->holidays[$day]))
			return null;

		return $this->holidays[$day];
	}

	public static function formatDay($timestamp){
		return date("dmY", $timestamp);
	}

	public function getEventsList(){
		return $this->eventsList;
	}

	public function getHolidaysList(){
		return $this->holidaysList;
	}

	public static function parseDay($day){
		return Util::parseDate("de_DE", $day[0].$day[1].".".$day[2].$day[3].".".$day[4].$day[5].$day[6].$day[7]);
	}

	public static function parseTime($time){
		return Util::parseTime("de_DE", $time[0].$time[1].":".$time[2].$time[3]);
	}

	public static function formatTime($timestamp){
		return str_replace(":", "", Util::formatTime("de_DE", $timestamp));
	}

	public function exceptions(){
		$exceptions = array();
		foreach($this->eventsList AS $event)
			if($event->getException() !== false)
				$exceptions[] = $event;
			
		foreach($this->eventsList AS $event){
			if($event->getException() !== false)
				continue;
			
			$event->makeException($exceptions);
		}
	}
	
	public function merge(Kalender $K){
		$this->eventsList = array_merge($this->eventsList, $K->getEventsList());
		$this->holidaysList = array_merge($this->holidaysList, $K->getHolidaysList());
	}
}
?>
