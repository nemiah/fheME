<?php
/**
 *  This file is part of fheME.

 *  fheME is free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 3 of the License, or
 *  (at your option) any later version.

 *  fheME is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.

 *  You should have received a copy of the GNU General Public License
 *  along with this program.  If not, see <http://www.gnu.org/licenses></http:>.
 * 
 *  2007 - 2022, open3A GmbH - Support@open3A.de
 */

class mZweiradGUI extends anyC implements iGUIHTMLMP2 {

	public function getHTML($id, $page){
		$this->loadMultiPageMode($id, $page, 0);

		$gui = new HTMLGUIX($this);
		$gui->version("mZweirad");
		$gui->screenHeight();

		$gui->name("Zweirad");
		
		$gui->attributes(array("ZweiradName"));
		
		return $gui->getBrowserHTML($id);
	}
	public function getOverviewContent(){
		$html = "<div class=\"touchHeader\"><span class=\"lastUpdate\" id=\"lastUpdatemZweiradGUI\"></span><p>Zweiräder</p></div>
			<div style=\"padding:10px;overflow:auto;\">";

		$AC = anyC::get("Zweirad", "ZweiradInOverview", "1");
		
		while($Z = $AC->n()){
			
			$B = new Button("Zweirad", "./fheME/Zweirad/chevrons-right.svg", "icon");
			$B->style("float:left;margin-right:5px;width:32px;");
			
			$BC = new Button("Lädt", "./fheME/Zweirad/zap.svg", "icon");
			$BC->style("float:right;width:32px;margin-left:5px;");
			
			if($Z->A("ZweiradCharging") != 1)
				$BC = "";
			
			$onclick = "Touchy.wheelOnFire(event, {
				data: {'30': '30%', '40': '40%', '50': '50%', '60': '60%', '70': '70%', '80': '80%', '90': '90%', '100': '100%'},
				selection: function(value){
					".OnEvent::rme($Z, "setSOCTarget", array("value"), "function(){ fheOverview.loadContent('mZweiradGUI::getOverviewContent'); }")."
				},
				value: function(){
					return ".$Z->A("ZweiradSOCTarget").";
				}
			})";
					
			$html .= "
			<div onclick=\"$onclick\" class=\"touchButton ".(stripos($Z->A("ZweiradStatus"), "ERROR") !== false ? "error" : "")."\">
				$BC".$B."
				<div class=\"label\" style=\"padding-top:0;\">
					".$Z->A("ZweiradName")." ".$Z->A("ZweiradSOC")."%/".$Z->A("ZweiradSOCTarget")."%<br>
					<small style=\"color:grey;\">".Util::CLDateTimeParser($Z->A("ZweiradLastUpdate"))." ".$Z->A("ZweiradStatus")."</small>
				</div>
				<div style=\"clear:both;\"></div>
			</div>";
		}
		
		if($AC->numLoaded() == 0){
			echo "";
			return;
		}
		
		$html .= "</div>";
		
		echo $html;
	}
	
	public static function getOverviewPlugin(){
		$P = new overviewPlugin("mZweiradGUI", "Zweiräder", 0);
		$P->updateInterval(180);
		
		return $P;
	}


}
?>