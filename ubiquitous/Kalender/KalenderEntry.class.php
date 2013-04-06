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
class KalenderEntry {
	protected $title;
	protected $summary;
	protected $icon;
	protected $onClick;#"contentManager.rmePCR('mKalender', '-1', 'getInfo', ['%%CLASSNAME%%', '%%CLASSID%%', '%%TIME%%'], function(transport) { Popup.displayNamed('edit', 'Event', transport, 'mKalender'); });";
	#private $duration;
	protected $bgColor;
	public static $bgColors = array("#536ca6", "#d47f1e", "#3c995b", "#7ec225", "#4585a3", "#9643a5", "#8a2d38", "#a59114");
	protected $className;
	protected $classID;
	protected $values = array();
	protected $editable;
	protected $location;
	protected $UID;
	protected $organizer;
	protected $repeat = false;
	protected $repeatInterval;
	protected $repeatWeekOfMonth = 0;
	protected $repeatDayOfWeek = "";

	protected $remind = -1;
	protected $reminded = 0;
	
	function __construct() {
		$this->onClick = OnEvent::popup("Event", "mKalender", -1, "getInfo", array("'%%CLASSNAME%%'", "'%%CLASSID%%'", "'%%TIME%%'"), "", "Kalender.popupOptions");
	}
	
	function ownerClass(){
		return $this->className;
	}

	function ownerClassID(){
		return $this->classID;
	}

	function UID($UID = null){
		if($UID != null)
			$this->UID = $UID;
		
		return $this->UID;
	}

	function organizer($organizerName = null, $organizerEMail = null){
		if($organizerName != null)
			$this->organizer = $organizerName.($organizerEMail != null ? " <$organizerEMail>" : "");
		
		if($organizerName != null AND $organizerEMail != null AND $organizerEMail == $organizerName)
			$this->organizer = $organizerEMail;
		
		return $this->organizer;
	}
	
	/*function __construct($className, $classID, $firstDay, $lastDay, $title) {
		$this->className = $className;
		$this->classID = $classID;

		$this->firstDay = $firstDay;
		$this->lastDay = $lastDay;
		$this->time = 0;
		$this->title = $title;
	}*/

	function remind($minutes = null, $reminded = 0){
		if($minutes != null){
			$this->remind = $minutes;
			$this->reminded = $reminded;
		}
		
		return $this->remind;
	}
	
	function reminded(){
		return $this->reminded;
	}
	
	function location($location = null){
		if($location != null)
			$this->location = $location;

		return $this->location;
	}

	function title(){
		return $this->title;
	}

	function repeat($activate = null, $interval = null, $weekOfMonth = 0, $dayOfWeek = ""){
		if($activate != null){
			$this->repeat = $activate;
			$this->repeatInterval = $interval;
			$this->repeatWeekOfMonth = $weekOfMonth;
			$this->repeatDayOfWeek = $dayOfWeek;
		}

		if($this->repeat === false)
			return false;
		
		return $this->repeatInterval;
	}

	function summary($text = null){
		if($text != null)
			$this->summary = $text;

		return $this->summary;
	}

	function icon($path = null){
		if($path != null)
			$this->icon = $path;
		
		return $this->icon;
	}
	
	function value($name, $value = null){
		if($value != null)
			$this->values[$name] = $value;
		
		if($value == null AND isset($this->values[$name]))
			return $this->values[$name];
		else
			return "";
	}

	function editable($editMethod, $deleteMethod){
		$this->editable = array($editMethod, $deleteMethod);
	}

	function getDayViewHTML(){
		$B = "";
		if($this->icon != null){
			$B = new Button("", $this->icon);
			$B->type("icon");
		}

		$this->onClick = str_replace(array("%%CLASSNAME%%", "%%CLASSID%%", "%%DAY%%"), array($this->className, $this->classID, ""), $this->onClick);

		return "
			<div onclick=\"$this->onClick\" class=\"backgroundColor1\" onmouseover=\"this.className = ' backgroundColor2';\" onmouseout=\"this.className = 'backgroundColor1';\" style=\"position:relative;float:left;padding:5px;cursor:pointer;min-height:30px;margin-right:10px;width:150px;\">
				<span style=\"float:left;margin-right:3px;\">
					$B
				</span>
				<!--<span style=\"display:none;\" id=\"time_$this->className$this->classID$this->time\">$this->time<br /></span>--><b>".$this->formatTime($this->time)."</b><br />$this->title
			</div>";
	}

	function formatTime($time){
		$time .= $time[3];
		$time[3] = $time[2];
		$time[2] = ":";

		$timestamp = Util::parseTime("de_DE", $time);

		return Util::CLTimeParser($timestamp);
	}

	function formatDay($day){
		$timestamp = $this->parseDay($day);

		return Util::CLDateParser($timestamp);
	}

	function parseDay($day){
		return Util::parseDate("de_DE", $day[0].$day[1].".".$day[2].$day[3].".".$day[4].$day[5].$day[6].$day[7]);
	}

	function getInfo($time){
		return "Please overwrite";
	}

	function  getMinimal($time){
		return "Please overwrite";
	}
}
?>