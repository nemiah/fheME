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
 *  2007 - 2017, Furtmeier Hard- und Software - Support@Furtmeier.IT
 */

class mWechselrichterGUI extends anyC implements iGUIHTMLMP2 {

	public function getHTML($id, $page){
		$this->loadMultiPageMode($id, $page, 0);

		$gui = new HTMLGUIX($this);
		$gui->version("mWechselrichter");
		$gui->screenHeight();

		$gui->name("Wechselrichter");
		
		$gui->attributes(array());
		
		return $gui->getBrowserHTML($id);
	}

	public function getOverviewContent(){
		$html = "<div class=\"touchHeader\"><span class=\"lastUpdate\" id=\"lastUpdatemWechselrichterGUI\"></span><p>Photovoltaik</p></div>
			<div style=\"padding:10px;overflow:auto;\">";
		
		$AC = anyC::get("Wechselrichter");
		
		while($W = $AC->n()){
			$data = shell_exec("python3 ".__DIR__."/kostal_modbusquery.py ".$W->A("WechselrichterIP")." ".$W->A("WechselrichterPort")." 2>&1");
			$json = json_decode($data);
			
			#$x = $json->{"Battery SOC"} / 100;
			#$myColor = array((2.0 * $x > 1 ? 1 : 1), 2.0 * (1 - $x) > 1 ? 1 : 2.0 * (1 - $x), 0);
			
			$empty = array();
			$v = new stdClass();
			$v->data = $json->{"Battery SOC"};
			$v->label = "";
			$v->color = "#ddd";
			$empty[] = $v;
			
			$v = new stdClass();
			$v->data = 100 - $json->{"Battery SOC"};
			$v->label = "leer";
			$v->color = "#FFF";
			$empty[] = $v;


			$html .= "<div id=\"battChart_".$W->getID()."\" style=\"float:left;width:100px;height:100px;margin-right:10px;margin-top:10px;margin-bottom:10px;\"></div>
				<div id=\"graphLegendContainer\" style=\"font-size:16px;width:100px;position:absolute;margin-left:0px;margin-top:50px;text-align:center;\">".$json->{"Battery SOC"}."%</div>
					<script type=\"text/javascript\">
			var plot = \$j.plot(
				\$j('#battChart_".$W->getID()."'), ". json_encode($empty).", 
					{
						series: {
							pie: { 
								show: true, 
								innerRadius: 0.7,
								label: { 
									show: false
								} 
							}
						},
						legend: {
							show: false,
							/*,noColumns: null,
							labelFormatter: null,
							labelBoxBorderColor: null,
							container: \$j('#graphLegendContainer'),
							position: 'ne',
							margin:0,
							backgroundColor: null,
							backgroundOpacity: 0.85*/
						}
					}
			);
		</script>";
				
			$html .= "
				<span style=\"font-size:14px;\">
					<span style=\"display:inline-block;width:100px;\">Erzeugung:</span> ".Util::CLNumberParser($json->{"Total DC power Panels"})."W<br>
					<span style=\"display:inline-block;width:100px;\">Verbrauch:</span> ".Util::CLNumberParser($json->{"Consumption power Home total"})."W<br>
					<span style=\"display:inline-block;width:100px;\">Einspeisen:</span> ".Util::CLNumberParser($json->{"Total Grid power"})."W<br>
					<span style=\"display:inline-block;width:100px;\">Batterie:</span> ".Util::CLNumberParser($json->{"Consumption power Home Battery"})."W
				</span>";
				
		}
		$html .= "</div>";
		echo $html;
	}

	
	public static function getOverviewPlugin(){
		$P = new overviewPlugin("mWechselrichterGUI", "Photovoltaik", 0);
		$P->updateInterval(10);
		#$P->updateFunction("function(){  }");
		return $P;
	}
}
?>