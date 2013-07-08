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
class Datum {

	private $timestamp;

	function __construct($timestamp = null) {
		if($timestamp == null) $timestamp = time();
		
		$this->timestamp = $timestamp;
	}
	
	function getNthDayOfMonth(){
		$endTime = $this->timestamp;
		$this->setToMonth1st();
		
		$c = 0;
		while($this->timestamp <= $endTime){
			if(date("w", $this->timestamp) === date("w", $endTime))
				$c++;
			$this->addDay();
		}
		
		return $c;
	}
	
	function setToJan1st($jahr){
		$this->timestamp = $this->parseGerDate("01.01.$jahr");
	}
	
	function setToMonth1st(){
		$this->timestamp = mktime(0, 1, 0, date("m", $this->timestamp)  , 1, date("Y", $this->timestamp));
	}
	
	function setToMonthLast(){
		$this->timestamp = mktime(0, 1, 0, date("m", $this->timestamp)  , $this->getMaxDaysOfMonth(), date("Y", $this->timestamp));
	}
	
	function addDay(){
		$this->timestamp = mktime(0, 1, 0, date("m", $this->timestamp)  , date("d", $this->timestamp)+1, date("Y", $this->timestamp));
	}	
	
	function subDay(){
		$this->timestamp = mktime(0, 1, 0, date("m", $this->timestamp)  , date("d", $this->timestamp)-1, date("Y", $this->timestamp));
	}

	function subYear(){
		$this->timestamp = mktime(0, 1, 0, date("m", $this->timestamp)  , date("d", $this->timestamp), date("Y", $this->timestamp)-1);
	}
	
	function addWeek($fixDST = false){
		$oldTime = $this->timestamp;
		$this->timestamp += 7 * 24 * 3600;
		
		if($fixDST AND date("I", $oldTime) != date("I", $this->timestamp))
			$this->timestamp += 3600 * (!date("I", $this->timestamp) ? 1 : -1);
	}
	
	function subWeek(){
		$this->timestamp -= 7 * 24 * 3600;
	}
	
	function addMonth(){
		$this->timestamp = mktime(0, 1, 0, date("m", $this->timestamp)+1  , date("d", $this->timestamp), date("Y", $this->timestamp));
		return $this;
	}
	
	function subMonth(){
		$this->timestamp = mktime(0, 1, 0, date("m", $this->timestamp)-1  , date("d", $this->timestamp), date("Y", $this->timestamp));
		return $this;
	}

	function addYear(){
		$this->timestamp = mktime(0, 1, 0, date("m", $this->timestamp), date("d", $this->timestamp), date("Y", $this->timestamp)+1);
	}
	
	function printer(){
		echo date("d m Y H i s",$this->timestamp)."<br />";
	}
	function time(){
		return $this->timestamp;
	}
	
	function getMaxDaysOfMonth(){
		$d = date("m",$this->timestamp);
		switch($d){
			case 1:
			case 3:
			case 5:
			case 7:
			case 8:
			case 10:
			case 12:
				return 31;
			break;

			case 4:
			case 6:
			case 9:
			case 11:
				return 30;
			break;

			case 2:
				if(date("L",$this->timestamp) == 0) return 28;
				else return 29;
			break;
		}
	}
	
	static function parseGerDate($date_text,$mode = "store"){
		if($mode == "load") return date("d.m.Y",$date_text * 1);
		
		$split = explode(".",$date_text);
		if(count($split) != 3) return -1;
		if($split[0] < 1 OR $split[0] > 31) return -1;
		if($split[1] < 1 OR $split[1] > 12) return -1;

		$monat = $split[1];
		$tag = $split[0];
		$jahr = $split[2];
		return mktime(0,1,0,$monat,$tag,$jahr);
	}
	
	static function getGerMonthArray($month = null){
		if($month != null) $month *= 1;
		
		$monate = array();
		$monate[1] = "Januar";
		$monate[2] = "Februar";
		$monate[3] = "März";
		$monate[4] = "April";
		$monate[5] = "Mai";
		$monate[6] = "Juni";
		$monate[7] = "Juli";
		$monate[8] = "August";
		$monate[9] = "September";
		$monate[10] = "Oktober";
		$monate[11] = "November";
		$monate[12] = "Dezember";

		if($month != null) return $monate[$month];
		return $monate;
	}
	
	static function getGerWeekArray($day = null){
		$woche = array();
		$woche[0] = "Sonntag";
		$woche[1] = "Montag";
		$woche[2] = "Dienstag";
		$woche[3] = "Mittwoch";
		$woche[4] = "Donnerstag";
		$woche[5] = "Freitag";
		$woche[6] = "Samstag";

		if($day !== null) return $woche[$day];
		return $woche;
	}
	static function getMonthOptions($month){
		$html = "";
	
		for($i=1;$i<13;$i++)
			$html .= "<option value=\"$i\" ".(date("m") == $i ? "selected=\"selected\"" : "").">".$month[$i]."</option>";
		
		return $html;
	}
	
	static function getWeekOptions($woche){
		$html = "";
	
		for($i=0;$i<7;$i++)
			$html .= "<option value=\"$i\" ".(date("w") == $i ? "selected=\"selected\"" : "").">".$woche."</option>";
		
		return $html;
	}
}


?>
