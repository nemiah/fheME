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

class KalenderViewMonat {
	protected $ansicht;
	protected $current;
	protected $first;
	protected $last;
	protected $date;
	protected $cols;
	protected $rows;
	protected $colorBgSaturday = "#EBEBEB";
	protected $colorBgSunday = "#EBEBEB";
	
	function __construct() {
		if($this->ansicht == null)
			$this->ansicht = "monat";
		
		$display = mUserdata::getUDValueS("KalenderDisplay".ucfirst($this->ansicht), "0");
		
		switch($this->ansicht){
			case "jahr":
				$Date = new Datum(Datum::parseGerDate("1.1.".date("Y")));
				for($i = 0; $i < abs($display); $i++)
					if($display > 0) $Date->addYear();
					else $Date->subYear();

			break;
			
			case "monat":
				$Date = new Datum(Datum::parseGerDate("1.".date("m.Y")));
				for($i = 0; $i < abs($display); $i++)
					if($display > 0) $Date->addMonth();
					else $Date->subMonth();

			break;
			
			case "woche":
				$Date = new Datum(Datum::parseGerDate(date("d.m.Y")));
				for($i = 0; $i < abs($display); $i++)
					if($display > 0) $Date->addWeek(true);
					else $Date->subWeek();
			break;
			
			case "tag":
				$Date = new Datum(Datum::parseGerDate(date("d.m.Y")));
				for($i = 0; $i < abs($display); $i++)
					if($display > 0) $Date->addDay();
					else $Date->subDay();
			break;
		}

		$this->current = clone $Date;
		if($this->ansicht != "tag" AND $this->ansicht != "jahr"){
			while(date("w",$Date->time()) > 1)
				$Date->subDay();

			if(date("w",$Date->time()) == 0)
				$Date->addDay();
		}
		
		$this->date = $Date;
		$this->first = $Date->time();
		
		
		$D = clone $Date;
		$rows = 5;
		if($this->ansicht == "woche")
			$rows = 1;
		
		$cols = 7;
		if($this->ansicht == "tag") {
			$cols = 1;
			$rows = 1;
		}
		if($this->ansicht == "jahr") {
			$cols = 31;
			$rows = 12;
		}
		
		if($this->ansicht != "jahr"){
			for($i = 0; $i < $rows; $i++){
				if($i > 0 AND date("m.Y",$D->time()) != date("m.Y",$this->current->time())) break;

				for($j = 0; $j < 7; $j++)
					$D->addDay();
			}
			$D->subDay();
		}
		
		if($this->ansicht == "jahr"){
			$D->addYear();
			$D->subDay();
		}
		
		$this->rows = $rows;
		$this->cols = $cols;
		$this->last = $D->time();
	}
	
	public function getCurrent(){
		return $this->current;
	}
	
	function getFirst(){
		return $this->first;
	}
	
	function getLast(){
		return $this->last;
	}
	
	public function getTitle(){
		if($this->ansicht == "monat")
			return "Monat ".Util::CLMonthName(date("m",$this->current->time())).date(" Y",$this->current->time());
		
		if($this->ansicht == "woche")
			return date("W",$this->current->time()).". Woche ".date("Y",$this->current->time() + 6 * 24 * 3600);
		
		if($this->ansicht == "tag")
			return Util::CLDateParserL($this->current->time(), "load");
		
		if($this->ansicht == "jahr")
			return "Jahr ".date("Y", $this->current->time());
	}
	
	public function getHeader(){
		return "";
	}
	
	public function getTable($GUI){
		$bps = BPS::getAllProperties("mKalenderGUI");
		
		
		
		$K = $GUI->getData($this->first, $this->last, isset($bps["KID"]) ? $bps["KID"] : Session::currentUser()->getID());
		
		$ansicht = $this->ansicht;
		$cols = $this->cols;
		$rows = $this->rows;
		
		$html = "<table style=\"margin-left:10px;border-spacing: 0px;\" id=\"KalenderTable\">
			<colgroup>";
		
		for($j = 0; $j < $cols -2; $j++)#
			$html .= "
				<col ".($ansicht == "woche" ? "class=\"backgroundColor".($j % 2 + 2)."\" " : "")." style=\"width:".(100 / $cols)."%;\" />";
			
			$html .= "
				<col style=\"background-color:$this->colorBgSaturday;width:".(100 / $cols)."%;\" />
				<col style=\"background-color:$this->colorBgSunday;width:".(100 / $cols)."%;\" />
			</colgroup>";

		if($ansicht != "tag"){
			$html .= "
				<tr>";

			$D2 = clone $this->date;
				for($j = 0; $j < $cols; $j++) {
					$html .= "
						<th style=\"border-bottom-width:1px;border-bottom-style:solid;border-bottom-color:#EEE;padding-top:10px;\" class=\"backgroundColor0\">".Util::CLWeekdayName(date("w",$D2->time()))."</th>";
					$D2->addDay();
				}
			unset($D2);

			$html .= "
			</tr>";
		}
		$D = clone $this->date;
		for($i = 0; $i < $rows; $i++){
			$html .= "
			<tr class=\"cellHeight noHover\">";
			for($j = 0; $j < $cols; $j++){
				
				$entry = "";
				
				$events = $K->getEventsOnDay(date("dmY", $D->time()));
				$holidays = $K->getHolidaysOnDay(date("dmY", $D->time()));
				
				$hasMultiDay = $K->hasMultiDayEvents($this->first, $this->last);
				
				if($ansicht == "tag"){
					#$dayContent = "";

					$dayDivs = "";
					for($i = 0; $i < 24; $i++){
						$dayDivs .= "
						<div style=\"height:40px;z-index:10;\" class=\"backgroundColor".($i % 2 == 0 ? "3" : "2")."\">
							";
						
						$BN = "";
						if(Session::isPluginLoaded("mTodo")){
							$BN = new Button("Neuer Termin", "./ubiquitous/Kalender/addToDo.png", "icon");
							$BN->className("KalenderButton");
							$BN->popup("", "Neuer Termin", "mKalender", "-1", "newTodo", array("-1", $D->time(), "'Kalender'", "-1", $i), "", "Kalender.popupOptions");
							$BN->style("float:left;margin-left:5px;margin-top:5px;");
							
						}
						
						$dayDivs .= "
							
							<div class=\"borderColor1\" style=\"height:35px;border-right-width:1px;border-right-style:dotted;float:left;width:40px;padding-top:5px;padding-right:5px;font-weight:bold;text-align:right;color:grey;\">".($i < 10 ? "0" : "")."$i:00</div>
						$BN</div>";
					}
					
					$eventsDiv = "";
					for($i = 0; $i < 24; $i++){
						if(count($events) > 0)
							foreach($events AS $time => $ev){
								if(substr($time, 0, 2) * 1 != $i)
									continue;
								
								foreach($ev AS $KE)
									$eventsDiv .= $KE->getDayViewHTML($D->time());
							}

					}
					$entry = "
						<div class=\"cellHeight\" style=\"overflow:auto;width:961px;\" id=\"tagDiv\">
							<div style=\"height:961px;\">
							$dayDivs
							</div>
							<div style=\"margin-top:-961px;\">
								$eventsDiv
							</div>
						</div>";

				} elseif($ansicht == "woche"){
					$dayDivs = "";
					
					for($k = 0; $k < $hasMultiDay; $k++)
						$dayDivs .= "<div style=\"height:22px;z-index:10;\" class=\"borderColor0\"></div>";

					for($k = 0; $k < 24; $k++){
						$bgColor = "";
						if($k < 7 OR $k > 19)
							$bgColor = "background-color:rgba(255, 255, 255, 0.3)";
						
						$border = "border-top:1px dotted white;";
						if($k == 23)
							$border .= "border-bottom:1px dotted white;";
						
						if($k == 12)
							$border = "border-top:1px solid white;";
						
						$dayDivs .= "
						<div style=\"height:".($k < 6 ? "10" : "21")."px;z-index:10;$border$bgColor\" class=\"borderColor0\">
							";
						if($k > 5 AND $k < 21 AND $k % 2 == 0 AND $j % 2 == 1)
							$dayDivs .= "
								<div class=\"borderColor1\" style=\"color:#777;padding-left:3px;\">".($k < 10 ? "0" : "")."$k</div>";
						
						$dayDivs .= "
							
						</div>";
					}
					
					$eventsDiv = "";
					for($i = 0; $i < 24; $i++){
						if(count($events) > 0)
							foreach($events AS $time => $ev){
								if(substr($time, 0, 2) * 1 != $i)
									continue;
								
								foreach($ev AS $KE)
									$eventsDiv .= $KE->getWeekViewHTML($D->time(), $hasMultiDay);
							}

					}
					
					$entry = "
						<div style=\"overflow:auto;height:".(11 * 6 + 22 * 18 + 1 + $hasMultiDay * 22)."px;width:100%;\">
							<div style=\"height:".(11 * 6 + 22 * 18 + 1)."px;\">
								$dayDivs
							</div>
							<div style=\"margin-top:-".(11 * 6 + 22 * 18 + 1)."px;\">
								$eventsDiv
							</div>
						</div>";
				} else {
					if($events != null)
						foreach($events AS $time => $ev){
							foreach($ev AS $v)
								$entry .= $v->getMinimal($D->time());
						}

					if($holidays != null)
						foreach($holidays AS $ev)
							foreach($ev AS $v)
								$entry .= $v->getMinimal($D->time());
						
				}

				$BD = new Button("Tagesansicht", "./ubiquitous/Kalender/showDetails.png");
				$BD->type("icon");
				$BD->rmePCR("mKalender", "-1", "setView", array("'tag'","'".$D->time()."'"), "contentManager.loadFrame('contentScreen','mKalender');");
				$BD->style("float:left;");

				$BN = "";
				if(Session::isPluginLoaded("mTodo")){
					$BN = new Button("Neuer Termin", "./ubiquitous/Kalender/addToDo.png");
					$BN->type("icon");
					$BN->popup("", "Neuer Termin", "mKalender", "-1", "newTodo", array("-1", $D->time(), "'Kalender'", "-1"), "", "Kalender.popupOptions");
					$BN->style("float:left;margin-left:5px;");
				}
				#".((date("m.Y",$D->time()) != date("m.Y",$currentMonth->time())) ? "color:grey;" : "")."
				if($j < $cols) $html .= "
				<td
					style=\"vertical-align:top;padding:0px;\"
					class=\"".((date("d.m.Y",$D->time()) == date("d.m.Y") AND $ansicht != "tag")? "backgroundColor1" : "")." Day borderColor1\" data-day=\"".$D->time()."\">
					<div
						style=\"".($ansicht == "tag" ? "display:none;" : "")."height:21px;padding-top:2px;padding-left:5px;text-align:right;padding-right:5px;\"
						class=\"innerCellTitle\">
						".($ansicht != "tag" ? "<span class=\"dayOptions\">$BD$BN</span>" : "")."
						<span
							style=\"color:grey;\">
							".($ansicht != "tag" ? date("d",$D->time()) : "&nbsp;")."
						</span>
					</div>
					<div style=\"overflow:auto;".($ansicht == "monat" ? "margin-top:0px;width:100%;" : "")."\" class=\"".($ansicht == "monat" ? "innerCellHeight" : "")."\">$entry</div>
				</td>";
				$D->addDay();
			}
			
			for($j = 0; $j < 7 - $cols; $j++)
				$D->addDay();
			
			$html .= "
			</tr>";
		}
		$html .= "
		</table>";
		
		return $html;
	}
}

?>