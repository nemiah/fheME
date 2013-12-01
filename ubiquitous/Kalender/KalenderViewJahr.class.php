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
			
		
		$html .= "";

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