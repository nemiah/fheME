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
 *  2007 - 2016, Rainer Furtmeier - Rainer@Furtmeier.IT
 */
class OpenWeatherMapGUI extends OpenWeatherMap implements iGUIHTML2 {
	function __construct($ID) {
		parent::__construct($ID);
		
		$this->setParser("OpenWeatherMapLastUpdate", "Util::CLDateTimeParser");
	}
	
	function getHTML($id){
		$gui = new HTMLGUIX($this);
		$gui->name("Wetter");
		
		#$gui->type("OpenWeatherMapDataCurrent", "textarea");
		#$gui->type("OpenWeatherMapDataForecast", "textarea");
		
		$gui->space("OpenWeatherMapLastUpdate");
		$gui->space("OpenWeatherMapDataCurrent");
		
		$gui->parser("OpenWeatherMapDataCurrent", "parserJSON");
		$gui->parser("OpenWeatherMapDataForecastDaily", "parserJSON");
		$gui->parser("OpenWeatherMapDataForecast", "parserJSONForecast");
		
		$gui->descriptionField("OpenWeatherMapUpdateInterval", "In Minuten");
		
		$B = $gui->addSideButton("Suche nach\nStadt", "application");
		$B->popup("", "Suche", "OpenWeatherMap", "-1", "popupSearchCity");
		
		$B = $gui->addSideButton("Update", "down");
		$B->rmePCR("OpenWeatherMap", $this->getID(), "download","", OnEvent::reload("Left"));
		
		return $gui->getEditHTML();
	}
	
	public static function parserJSON($w){
		$data = json_decode($w);
		
		return "<pre style=\"font-size:10px;height:200px;overflow:auto;width:100%;\">".print_r($data, true)."</pre>";
	}
	
	public static function parserJSONForecast($w){
		$data = json_decode($w);
		
		$T = new HTMLTable(1);
		$T->setTableStyle("color:grey;font-size:10px;");
		$T->maxHeight(100);
		foreach($data->list AS $I){
			$T->addRow(array(Util::CLDateTimeParser($I->dt).": ".$I->main->temp."°"));
		}
		return $T;
	}
	
	public function popupSearchCity(){
		$I = new HTMLInput("city");
		$I->onEnter(OnEvent::rme($this, "searchCity", array("this.value"), "function(t){ \$j('#searchCityResults').html(t.responseText); }"));
		
		echo $I."<div id=\"searchCityResults\"></div>";
	}
	
	public function searchCity($name){
		$data = file_get_contents("http://api.openweathermap.org/data/2.5/find?q=".urlencode($name).",de&type=like&mode=json&APPID=".$this->apiKey());
		$data = json_decode($data);
		if($data->count == 0)
			die("<p>Kein Ergebnis</p>");
		
		$T = new HTMLTable(2);
		$T->weight("light");
		$T->useForSelection();
		$T->setColWidth(1, 20);
		foreach($data->list AS $I){
			$B = new Button("Übernehmen", "arrow_left", "iconic");
			
			$T->addRow(array($B, $I->name));
			$T->addRowEvent("click", "\$j('[name=OpenWeatherMapCityID]').val('$I->id');");
		}
		
		echo $T;
		#echo "<pre>";
		#print_r($data);
		#echo "</pre>";
	}
	
	public function ACLabel(){
		if($this->getID() == "0")
			return "";
		
		return $this->A("OpenWeatherMapName");
	}
}
?>