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

class KalenderViewJahr extends KalenderViewMonat {
	function __construct() {
		$this->ansicht = "jahr";
		
		parent::__construct();
	}
	
	public function getHeader(){
		return "";
	}
	
	public function getTable($GUI){
		$bps = BPS::getAllProperties("mKalenderGUI");
		
		$K = $GUI->getData($this->first, $this->last, isset($bps["KID"]) ? $bps["KID"] : Session::currentUser()->getID());
		
		$cols = $this->cols;
		$rows = $this->rows;
		
			
		$html = "<div style=\"border-top:1px solid #DDD;width:".($cols * 65 + 80)."px;\">";
		$D = clone $this->date;
		for($i = 0; $i < $rows; $i++){
			#if($i == 0){
				$html .= "<div style=\"width:".($cols * 65 + 80)."px;\">".
					"<div class=\"backgroundColor3\" style=\"vertical-align:top;display:inline-block;width:76px;padding:2px;\">".Util::CLMonthName($i + 1)."</div>";
				for($j = 0; $j < $cols; $j++){
					
					$Max = $D->getMaxDaysOfMonth();

					$day = "&nbsp;";
					if($j < $Max)
						$day = $j + 1;
					
					$html .= "<div class=\"MonthDay MonthDayHeader backgroundColor3\" style=\"vertical-align:top;display:inline-block;width:60px;padding:2px;text-align:right;color:grey;\"><small>$day</small></div>";
				}
				$html .= "</div>";
			#}
			
			$html .= "<div style=\"width:".($cols * 65 + 80)."px;height:300px;min-height:70px;border-bottom:0px;\" class=\"Month\">".
				"<div style=\"vertical-align:top;display:inline-block;width:80px;\"></div>";
			
			$DM = clone $D;
			$monthFirst = $DM->time();
			$DM->setToMonthLast();
			$monthLast = $DM->time();
			
			$K->getEventsOnDay(date("dmY", $DM->time())); //initializes data
			#echo Util::CLDateParser($monthFirst)."-".Util::CLDateParser($monthLast).":";
			$hasMultiDay = $K->hasMultiDayEvents($monthFirst, $monthLast);
			
			for($j = 0; $j < $cols; $j++){
				
				$bgColor = "none";
				if(date("w", $D->time()) == 6)
						$bgColor = $this->colorBgSaturday;
				
				if(date("w", $D->time()) == 0)
						$bgColor = $this->colorBgSunday;
				
				if($j >= $D->getMaxDaysOfMonth())
					$bgColor = "none";
				
				$html .= "<div class=\"MonthDay\" style=\"background-Color:$bgColor;position:relative;min-height:70px;display:inline-block;width:64px;vertical-align:top;\">";
				
				if($j < $D->getMaxDaysOfMonth()){
					$entry = "";

					$events = $K->getEventsOnDay(date("dmY", $D->time()));
					$holidays = $K->getHolidaysOnDay(date("dmY", $D->time()));

					$entry .= "<div class=\"MonthMultiDay backgroundColor4\" style=\"min-height:".($hasMultiDay * 17)."px;\">";
					if($events != null)
						foreach($events AS $ev){
							foreach($ev AS $v)
								$entry .= $v->getMinimal($D->time());
						}
					$entry .= "</div>";
						
					if($holidays != null)
						foreach($holidays AS $ev)
							foreach($ev AS $v)
								$entry .= $v->getMinimal($D->time());

				
					$html .= $entry;
				}
				
				$html .= "</div>";
				
				if($D->d() >= $D->getMaxDaysOfMonth())
					continue;
				
				$D->addDay();
			}
			$D->addDay();
			$html .= "</div>";
		}
		
		$html .= "</div>";
		
		return $html.OnEvent::script("\$j('.Month').each(function(k, v){ var max = 0; max += \$j(this).find('.MonthMultiDay').outerHeight(); \$j(this).find('.MonthDay').each(function(k, v){ var l =\$j(this).find('div').each(function(){ var mt = parseInt(\$j(this).css('margin-top')); if(mt > max) max = mt + \$j(this).outerHeight(); }); }); \$j(this).css('height', max + \$j('.MonthDayHeader').outerHeight()).find('.MonthDay').css('height', max + \$j('.MonthDayHeader').outerHeight()); })");
		
		#"<table id=\"KalenderHeader\" style=\"position:absolute;margin-left:10px;border-spacing: 0px;width:auto;\" class=\"backgroundColor0\">
		$tableHeader = "
			<thead>
			<tr>
				<th style=\"border-bottom-width:1px;border-bottom-style:solid;border-bottom-color:#EEE;padding-top:10px;text-align:right;\" class=\"backgroundColor0\">Monat</th>";
		
		for($j = 0; $j < $this->cols; $j++)
			$tableHeader .= "
				<th style=\"border-bottom-width:1px;border-bottom-style:solid;border-bottom-color:#EEE;padding-top:10px;\" class=\"backgroundColor0\">".($j + 1)."</th>";


		$tableHeader .= "
		</tr>
		</thead>
		";
		#</table>
		$html = "
			
		<div style=\"\">
		<!--<table id=\"KalenderHeader\" style=\"position:absolute;margin-left:10px;border-spacing: 0px;\">
			$tableHeader
		</table>-->
		<table style=\"margin-left:10px;border-spacing: 0px;\" id=\"KalenderTable\">
			<colgroup>
				<col></col>";
		
		for($j = 0; $j < $cols; $j++)#
			$html .= "
				<col style=\"width:".(100 / $cols)."%;\" />";
			
		
		$html .= "
		$tableHeader
		<tbody>";
		$D = clone $this->date;
		for($i = 0; $i < $rows; $i++){
			$html .= "
			<tr class=\"cellHeight\">
				<td class=\"Day borderColor1 backgroundColor3\" style=\"text-align:right;\">".Util::CLMonthName($i + 1)."</td>";
			
			for($j = 0; $j < $cols; $j++){
				
				$entry = "";
				
				$events = $K->getEventsOnDay(date("dmY", $D->time()));
				$holidays = $K->getHolidaysOnDay(date("dmY", $D->time()));
				
				#$hasMultiDay = $K->hasMultiDayEvents($this->first, $this->last);
				

				if($events != null)
					foreach($events AS $ev){
						foreach($ev AS $v)
							$entry .= $v->getMinimal($D->time());
					}

				if($holidays != null)
					foreach($holidays AS $ev)
						foreach($ev AS $v)
							$entry .= $v->getMinimal($D->time());
				
				
				$bgColor = "none";
				if(date("w", $D->time()) == 6)
						$bgColor = $this->colorBgSaturday;
				
				if(date("w", $D->time()) == 0)
						$bgColor = $this->colorBgSunday;

				#".((date("m.Y",$D->time()) != date("m.Y",$currentMonth->time())) ? "color:grey;" : "")."
				if($j < $cols) $html .= "
				<td
					style=\"vertical-align:top;padding:0px;background-color:$bgColor;overflow:auto;\"
					class=\"".(date("d.m.Y",$D->time()) == date("d.m.Y")? "backgroundColor1" : "")." Day borderColor1\">
					<div style=\"font-size:10px;\">$entry</div>
				</td>";
				$D->addDay();
				
				#if(date("d", $D->time()) == 1){
					#for(;$j < $cols;$j++)
					#	$html .= "<td class=\"Day borderColor1\"></td>";
				#}
			}
			
			
			
			$html .= "
			</tr>";
		}
		$html .= "
			</tbody>
		</table>
		</div>";#.OnEvent::script("\$j('#KalenderTable thead th').each(function(k, v){ \$j(\$j('#KalenderHeader thead th').get(k)).css('width', \$j(v).width()); console.log(\$j(v).width()); }); \$j('#KalenderHeader').css('width', \$j('#KalenderTable').width())");#.OnEvent::script("\$j('#KalenderTable').fixedHeaderTable({ footer: false, cloneHeadToFoot: false, fixedColumn: true });");
		
		return $html;
	}
}

?>