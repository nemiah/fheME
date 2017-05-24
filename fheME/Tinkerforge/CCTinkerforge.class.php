<?php
/**
 *  This file is part of open3A.

 *  open3A is free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 3 of the License, or
 *  (at your option) any later version.

 *  open3A is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.

 *  You should have received a copy of the GNU General Public License
 *  along with this program.  If not, see <http://www.gnu.org/licenses></http:>.
 * 
 *  2007 - 2017, Furtmeier Hard- und Software - Support@Furtmeier.IT
 */
class CCTinkerforge extends CCPage implements iCustomContent {
	private $customer;
	
	function __construct() {
		parent::__construct();
		
		#$this->loadPlugin("open3A", "Adressen");
	}
	
	function getLabel(){
		return "Tinkerforge";
	}
	
	function getTitle(){
		return $this->getLabel();
	}
	
	function getStyle(){
		return "body { background-color:black;cursor:none; }";
	}
	
	function getCMSHTML() {
		$html = "";
		$AC = anyC::get("Tinkerforge");
		
		while($T = $AC->getNextEntry()){
			$ACB = anyC::get("TinkerforgeBricklet", "TinkerforgeBrickletTinkerforgeID", $T->getID());
			
			while($B = $ACB->getNextEntry())
				$html .= $this->getControl($B);
			
			
		}
		
		return $html;
	}

	private function getControl($TFB){
		
		return "<div id=\"brickletPlot".$TFB->A("TinkerforgeBrickletUID")."\" style=\"width:100%;height:450px;\"></div>".OnEvent::script("

var Tinkerforge = {
	data: {},
	plot: {},
}
	
\$j(function () {
    Tinkerforge.plot.BID".$TFB->A("TinkerforgeBrickletUID")." = \$j.plot(\$j('#brickletPlot".$TFB->A("TinkerforgeBrickletUID")."'), [], {
		xaxis: { 
			mode: 'time',
			timezone: 'browser',
			timeformat: '%H:%M',
			tickLength: 0,
			color:'#EEEEEE',
			tickColor:'#EEEEEE',
			tickLength: 0
		},
		
		yaxis: {
			color:'#EEEEEE',
			tickColor:'#EEEEEE',
			tickLength: 0
		},
		
		grid: {
			borderWidth: 0,
			borderColor: '#AAAAAA'
		}
		});
});

function update".$TFB->getID()."(){
	CustomerPage.rme('update', ['".$TFB->getID()."'], function(data){ 
		data = jQuery.parseJSON(data);
		
		var d = new Date()
		var n = d.getTimezoneOffset();

		var newTime = data.time * 1000;// - n * 60 * 1000;
		var newTemp = data.value;

		if(\$j('#brickletPlot'+data.bricklet).length === 0 || !Tinkerforge.plot['BID'+data.bricklet])
			return;

		if(!Tinkerforge.data['BID'+data.bricklet])
			Tinkerforge.data['BID'+data.bricklet] = [];

		if(Tinkerforge.data['BID'+data.bricklet].length > 180)
			Tinkerforge.data['BID'+data.bricklet].shift();

		Tinkerforge.data['BID'+data.bricklet].push([newTime, newTemp]);
		console.log(Tinkerforge.data['BID'+data.bricklet]);

		Tinkerforge.plot['BID'+data.bricklet].setData([{ 
			data: Tinkerforge.data['BID'+data.bricklet], 
			color: 'rgb(149, 0, 0)', 
			shadowSize: 0, 
			threshold: { 
				below: 80, 
				color: 'rgb(180, 180, 180)'} 
			}]);
		Tinkerforge.plot['BID'+data.bricklet].setupGrid();
		Tinkerforge.plot['BID'+data.bricklet].draw();
	});
}

update".$TFB->getID()."();

window.setInterval(function(){
	update".$TFB->getID()."();
}, 60000);");
		
	}
	
	public function update($data){
		$B = new TinkerforgeBricklet($data["P0"]);
		
		$rdata = $B->getData();
		$rdata->bricklet = $B->A("TinkerforgeBrickletUID");
		$rdata->value = $rdata->value1;
		
		echo json_encode($rdata);
	}
	
	public function getScriptFiles(){
		return array(
			"../../libraries/flot/jquery.flot.js",
			"../../libraries/flot/jquery.flot.time.js",
			"../../libraries/flot/jquery.flot.threshold.js",
			"../../libraries/flot/jquery.flot.pie.js",
			"../../libraries/flot/jquery.flot.selection.js",
			"../../fheME/Tinkerforge/Tinkerforge.js");
	}
	
	public function handleForm($values){
		switch($values["action"]){

		}
		
		parent::handleForm($values);
	}
}

?>