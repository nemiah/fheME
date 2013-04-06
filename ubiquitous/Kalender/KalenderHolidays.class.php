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
class KalenderHolidays extends KalenderEntry {
	private $firstDay;
	private $lastDay;

	public static $count = 0;

	function __construct($className, $classID, $firstDay, $lastDay, $title) {
		$this->className = $className;
		$this->classID = $classID;

		$this->firstDay = $firstDay;
		$this->lastDay = $lastDay;
		$this->title = $title;
		$this->bgColor = self::$bgColors[self::$count];
		self::$count++;
		
		parent::__construct();
	}

	function when($startDay, $endDay){
		$when = array();
		
		$start = Kalender::parseDay($this->firstDay);
		$end = Kalender:: parseDay($this->lastDay);
		
		$D = new Datum($start);
		
		while($D->time() <= $end){
			$W = new stdClass();
			$W->day = Kalender::formatDay($D->time());
			$W->time = "0800";
			$when[] = $W;
			
			$D->addDay();
		}
		return $when;
	}
	
	function getFirstDay(){
		return $this->firstDay;
	}

	function getLastDay(){
		return $this->lastDay;
	}

	function getDayViewHTML(){
		$B = "";
		if($this->icon != null){
			$B = new Button("", $this->icon);
			$B->type("icon");
		}

		$this->onClick = str_replace(array("%%CLASSNAME%%", "%%CLASSID%%"), array($this->className, $this->classID), $this->onClick);

		return "
			<div onclick=\"$this->onClick\" class=\"backgroundColor1\" onmouseover=\"this.className = ' backgroundColor2';\" onmouseout=\"this.className = 'backgroundColor1';\" style=\"position:relative;float:left;padding:5px;cursor:pointer;min-height:30px;margin-right:10px;width:150px;\">
				<span style=\"float:left;margin-right:3px;\">
					$B
				</span>
				<!--<span style=\"display:none;\" id=\"time_$this->className$this->classID$this->time\">$this->time<br /></span>--><b>".$this->formatTime($this->time)."</b><br />$this->title
			</div>";
	}

	function getInfo($time){
		$T = new HTMLTable(2);
		$T->setColWidth(1, 120);

		$T->addLV("Name", $this->title);

		$T->addLV("Erster Tag", $this->formatDay($this->firstDay));
		$T->addLV("Letzter Tag", $this->formatDay($this->lastDay));
		$T->addLV("Beschreibung", $this->summary);
		
		if(count($this->values) > 0)
			$T->insertSpaceAbove();
		foreach($this->values AS $label => $value)
			$T->addLV($label, $value);

		return $T;
	}

	function getMinimal($time){
		$B = "";
		if($this->icon != null){
			$B = new Button("", $this->icon);
			$B->type("icon");
		}
		
		$this->onClick = str_replace(array("%%CLASSNAME%%", "%%CLASSID%%"), array($this->className, $this->classID), $this->onClick);

		#$('time_$this->className$this->classID$this->time').style.display = ''; 
		return "
			<div onclick=\"$this->onClick\" style=\"clear:left;padding:2px;cursor:pointer;overflow:hidden;height:13px;".($this->bgColor != null ? "color:white;background-color:$this->bgColor" : "")."\">
				$this->title
			</div>";
	}
}
?>