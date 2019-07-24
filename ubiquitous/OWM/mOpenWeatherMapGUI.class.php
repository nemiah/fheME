<?php
/**
 *  This file is part of ubiquitous.

 *  ubiquitous is free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 3 of the License, or
 *  (at your option) any later version.

 *  ubiquitous is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.

 *  You should have received a copy of the GNU General Public License
 *  along with this program.  If not, see <http://www.gnu.org/licenses></http:>.
 * 
 *  2007 - 2019, open3A GmbH - Support@open3A.de
 */

class mOpenWeatherMapGUI extends anyC implements iGUIHTMLMP2 {

	public function getHTML($id, $page){
		$this->loadMultiPageMode($id, $page, 0);

		$gui = new HTMLGUIX($this);
		$gui->version("mOpenWeatherMap");

		$gui->name("Wetter");
		
		$gui->attributes(array("OpenWeatherMapName"));
		
		$B = $gui->addSideButton("Einstellungen", "system");
		$B->popup("", "Allgemeine Werte", "mOpenWeatherMap", "-1", "getPopupEinstellungen");
		
		return $gui->getBrowserHTML($id);
	}

	
	public function saveEinstellungen($apiKey){
		mUserdata::setUserdataS("OWMApiKey", $apiKey, "", -1);
	}
	
	public function getPopupEinstellungen(){
		$F = new HTMLForm("allgemeineWerte", array("OWMApiKey"));
		$F->getTable()->setColWidth(1, 120);
		
		$F->setValue("OWMApiKey", mUserdata::getGlobalSettingValue("OWMApiKey", ""));
		
		
		$F->setSaveRMEPCR("Speichern", "", "mOpenWeatherMap", "-1", "saveEinstellungen", OnEvent::closePopup("mOpenWeatherMap"));
		
		echo $F;
	}
	
	public function getACData($attributeName, $query){
		$this->setSearchStringV3($query);
		$this->setSearchFieldsV3(array("OpenWeatherMapName"));
		
		$this->setFieldsV3(array("OpenWeatherMapName AS label", "OpenWeatherMapID AS value"));
		
		$this->setLimitV3("10");
		
		Aspect::joinPoint("query", $this, __METHOD__, $this);
		
		echo $this->asJSON();
	}

	public function getOverviewContent($echo = true){
		$html = "<div class=\"touchHeader\"><span class=\"lastUpdate\" id=\"lastUpdatemOpenWeatherMapGUI\"></span><p>Wetter</p></div><div style=\"padding:10px;height:249px;overflow:auto;\">";
		while($W = $this->getNextEntry()){
			#$W->download();
			$dataCurrent = json_decode($W->A("OpenWeatherMapDataCurrent"));
			$dataForecastDaily = json_decode($W->A("OpenWeatherMapDataForecastDaily"));
			
			$icon = OpenWeatherMap::iconPng($dataCurrent->weather[0]->icon);
			$B = new Button("", "./ubiquitous/OWM/icons48/".$icon.".png", "icon");
			$B->style("float:left;");
			
			$BT = "";
			if(isset($dataForecastDaily->list[6]) AND isset($dataForecastDaily->list[5]) AND isset($dataForecastDaily->list[4])){
				
				$total = 0;
				$count = 0;
				for($i = 4; $i < 7; $i++){
					$total += $dataForecastDaily->list[$i]->temp->max;
					$count++;
				}
				
				$avg = $total / $count;
				$diff = $avg - $dataCurrent->main->temp_max;
				$grad = ($diff / 5) * -90;
				
				if($grad > 90)
					$grad = 90;
				
				if($grad < -90)
					$grad = -90;
				
				$BT = new Button("Tendenz", "arrow_right", "iconicL");
				$BT->style("margin-left:10px;transform:rotate({$grad}deg);");
			}
			
			$html .= $B."<div style=\"margin-left:70px;margin-top:5px;\">
				<b style=\"font-size:30px;font-weight:bold;color:#555;\">".round($dataCurrent->main->temp)." °C</b>$BT
				</div>";
			
			$D = new Datum();
			$D->normalize();
			
			$c = 0;
			$html .= "<div style=\"clear:both;\">";
			for($i = 0; $i < 5; $i++){
				if($c == 2)
					break;
				
				$time = $dataForecastDaily->list[$i]->dt;
				if($time < $D->time())
					continue;
				
				#$icon = OpenWeatherMap::iconPng($dataForecastDaily->list[$i]->weather[0]->icon);
				#$B = new Button("", "./ubiquitous/OWM/icons48/".$icon.".png", "icon");
				#$B->popup("", "Vorhersage", "mOpenWeatherMap", "-1", "all");
				
				#$html .= "<div style=\"float:left;width:49%;margin-top:20px;\"><small style=\"color:grey;\">".($i == 0 ? "Heute" : "Morgen")." (".Datum::getGerWeekArray(date("w", $time)).")</small><br>
				#	<b style=\"font-size:15px;font-weight:bold;color:#555;\">".round($dataForecastDaily->list[$i]->temp->min)." - ".round($dataForecastDaily->list[$i]->temp->max)." °C<br>".$B."
				#	</b>
				#	</div>";
				
				$html .= $this->toIcon(true, $dataForecastDaily->list[$i]);
				
				$c++;
			}
			
			$html .= "</div>";
		}
		
		
		
		$html .= "</div>";
		
		
		if($echo)
			echo $html;
		return $html;
	}
	
	private function toIcon($clickable, $data){
		$icon = OpenWeatherMap::iconPng($data->weather[0]->icon);
		$B = new Button("", "./ubiquitous/OWM/icons48/".$icon.".png", "icon");
		if($clickable)
			$B->popup("", "Vorhersage", "mOpenWeatherMap", "-1", "all");
		#".($i == 0 ? "Heute" : "Morgen")."
		return "<div style=\"display:inline-block;vertical-align:top;width:49%;margin-top:20px;\"><small style=\"color:grey;\">".Datum::getGerWeekArray(date("w", $data->dt)).", ".date("d", $data->dt).".</small><br>
			<b style=\"font-size:15px;font-weight:bold;color:#555;\">".round($data->temp->min)." - ".round($data->temp->max)." °C<br>".$B."
			</b>
			</div>";
	}
	
	public function all(){
		$D = new Datum();
		$D->normalize();
			
		$html = "<div style=\"margin-left:2%;\">";
		while($W = $this->n()){
			
			$data = json_decode($W->A("OpenWeatherMapDataForecastDaily"));
			foreach($data->list AS $value){
				
				$time = $value->dt;
				if($time < $D->time())
					continue;
				
				$html .= $this->toIcon(false, $value);
			}
		}
		
		echo $html."</div>";
	}
	
	public static function getOverviewPlugin(){
		$P = new overviewPlugin("mOpenWeatherMapGUI", "Wetter", 249);
		$P->updateInterval(1800);
		
		return $P;
	}
}
?>