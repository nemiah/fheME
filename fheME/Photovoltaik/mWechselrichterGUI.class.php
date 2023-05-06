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
		
		$B = $gui->addSideButton("Smart Meter", "./fheME/Photovoltaik/chart_curve.png");
		$B->loadPlugin("contentRight", "mSmartMeter");
		
		$B = $gui->addSideButton("Vorhersage", "./images/navi/daytime.svg");
		$B->loadPlugin("contentRight", "mPhotovoltaikForecast");
		
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
			
			/*$empty = array();
			$v = new stdClass();
			$v->data = $json->{"Battery SOC"};
			$v->label = "";
			$v->color = "#f2f2f2";
			$empty[] = $v;
			
			$v = new stdClass();
			$v->data = 100 - $json->{"Battery SOC"};
			$v->label = "leer";
			$v->color = "#FFF";
			$empty[] = $v;*/


			/*$html .= "<div id=\"battChart_".$W->getID()."\" style=\"float:left;width:100px;height:100px;margin-right:10px;margin-top:0px;margin-bottom:10px;\"></div>
				<div id=\"graphLegendContainer\" style=\"font-size:16px;width:100px;position:absolute;margin-left:0px;margin-top:40px;text-align:center;\">".$json->{"Battery SOC"}."%</div>
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
						}
					}
			);
		</script>";*/
				
			$grid = "";
			if($W->A("WechselrichterSmartMeterID")){
				$M = new SmartMeter($W->A("WechselrichterSmartMeterID"));
				
				$dataMeter = shell_exec("python3 ".__DIR__."/kostal_em_query_v02.py ".$M->A("SmartMeterIP")." ".$M->A("SmartMeterPort")." 2>&1");
				$jsonMeter = json_decode($dataMeter);
				#print_r($jsonMeter);
				
				$B = new Button("", "arrow_left", "iconicG");
				$B->style("font-size:16px;margin-left:-5px;");
				
				$BR = new Button("", "arrow_right", "iconicG");
				$BR->style("font-size:16px;margin-left:-5px;");
				
				$BH = new Button("", "home", "iconicG");
				$BH->style("font-size:16px;");
				
				$grid = Util::CLNumberParser($jsonMeter->{"Active power-"})."W $BH$BR";
				if($jsonMeter->{"Active power+"})
					$grid = Util::CLNumberParser($jsonMeter->{"Active power+"})."W $BH$B";
			}
				
			$solar = $json->{"Total DC power Panels"};
			if($solar < 0)
				$solar = 0;
			
			$forecastToday = 0;
			$forecastTomorrow = 0;
			$restToday = 0;
			$AC = anyC::get("PhotovoltaikForecast");
			while($F = $AC->n()){
				if($F->A("PhotovoltaikForecastName") == "Süden")
					continue;
				
				if(trim($F->A("PhotovoltaikForecastData")) == "")
					continue;
				
				$jsonF = json_decode($F->A("PhotovoltaikForecastData"));
				$forecastToday += $jsonF->result->watt_hours_day->{date("Y-m-d")};
				$forecastTomorrow += $jsonF->result->watt_hours_day->{date("Y-m-d", time() + 3600 * 24)};
				
				foreach($jsonF->result->watt_hours_period AS $period => $wh){
					if(strpos($period, date("Y-m-d")) === false)
						continue;
					
					if(strtotime($period) < time() - 60 * 30)
						continue;
					
					$restToday += $wh;
					
					#echo Util::CLDateTimeParser(strtotime($period))."<br>";
				}
				
				$restToday += 0;
			}
			
			$width = "130px";
			
			$html .= "
				<span style=\"font-size:14px;\">
					<span style=\"display:inline-block;width:$width;\">Photovoltaik:</span> ".Util::CLNumberParser($solar)."W<br>
					<!--<span style=\"display:inline-block;width:$width;\">Inverter:</span> ".Util::CLNumberParser($json->{"Inverter power generated"})."W<br>-->
					<span style=\"display:inline-block;width:$width;\">Haus:</span> ".Util::CLNumberParser($json->{"Consumption power Home total"})."W<br>
					<span style=\"display:inline-block;width:$width;\">Netz:</span> $grid<br>
					<!--<span style=\"display:inline-block;width:$width;\">Batterie:</span> ".$json->{"Battery SOC"}."%<br>-->
					<span style=\"display:inline-block;width:$width;\">PV heute:</span> ".Util::CLNumberParserZ(round($json->{"Daily yield"}/1000, 2))."kWh<br>
					<span style=\"display:inline-block;width:$width;\">PV heute Vorhers.:</span> ".Util::CLNumberParserZ(round($forecastToday/1000, 2))."kWh<br>
					<span style=\"display:inline-block;width:$width;\">PV heute Rest:</span> ".Util::CLNumberParserZ(round($restToday/1000, 2))."kWh<br>
					<span style=\"display:inline-block;width:$width;\">PV morgen:</span> ".Util::CLNumberParserZ(round($forecastTomorrow/1000, 2))."kWh<br>
				</span>";
					#".Util::CLNumberParser($json->{"Consumption power Home Battery"})."W
				
		}
		
		$html .= "<div style=\"margin-top:10px;border-right:1px solid #bbb;\">";
		
		$html .= $this->bar("Batterie", $json->{"Battery SOC"});
			
		$AC = anyC::get("Zweirad");
		while($Z = $AC->n())
			$html .= $this->bar($Z->A("ZweiradName"), $Z->A("ZweiradSOC"));
		
		$AC = anyC::get("Heizung");
		while($H = $AC->n()){
			
			$states = $H->getParsedData();
			$WP = round(100 / $H->A("HeizungWaterHotTemp") * $states["sGlobal"]["dhwTemp"]);
			$html .= $this->bar("Wasser", $WP);
		}
			
		$html .= "
		</div>";
		
		$html .= "</div>";
		echo $html;
	}

	private function bar($label, $percent){
		$outside = "";
		$inline = "<span style=\"float:right;\">".$percent."%</span>";
		if($percent < 50){
			$outside = "<div style=\"vertical-align:top;padding:3px;margin-top:3px;display:inline-block;box-sizing:border-box;\">".($percent < 20 ? $label." " : "").$percent."%</div>";
			$inline = "";
			
			if($percent < 20)
				$label = "&nbsp;";
		}
		return "<div style=\"vertical-align:top;display:inline-block;box-sizing:border-box;padding:3px;margin-top:3px;overflow:hidden;white-space: nowrap;background-color:#f2f2f2;width:".($percent > 100 ? 100 : $percent)."%;\">$inline".$label."</div>$outside";
	}
	
	public static function getOverviewPlugin(){
		$P = new overviewPlugin("mWechselrichterGUI", "Photovoltaik", 0);
		$P->updateInterval(60);
		#$P->updateFunction("function(){  }");
		return $P;
	}
}
?>