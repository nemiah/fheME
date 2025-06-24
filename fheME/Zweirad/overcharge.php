<?php
/*
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
 *  along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 *  2007 - 2022, open3A GmbH - Support@open3A.de
 */
set_time_limit(0);

if(isset($argv[1]))
	$_GET["cloud"] = $argv[1];

if(isset($argv[2]))
	$_SERVER["HTTP_HOST"] = $argv[2];

session_name("ExtConnPV");

require_once realpath(dirname(__FILE__)."/../../system/connect.php");


$absolutePathToPhynx = realpath(dirname(__FILE__)."/../../")."/";

$e = new ExtConn($absolutePathToPhynx);
#$e->forbidCustomizers();

$e->addClassPath(FileStorage::getFilesDir());
$e->loadPlugin("fheME", "Zweirad");
$e->loadPlugin("fheME", "Photovoltaik");

$e->useDefaultMySQLData();

$e->useUser();


#echo "<pre>";
try {
	while(true){
		#echo "running…\n";
		
		$M = new SmartMeter(1);

		$dataMeter = shell_exec("python3 ".Util::getRootPath()."/fheME/Photovoltaik/kostal_em_query_v02.py ".$M->A("SmartMeterIP")." ".$M->A("SmartMeterPort")." 2>&1");
		#var_dump($dataMeter);
		$jsonMeter = json_decode($dataMeter);
		#var_dump($jsonMeter);
		#if($jsonMeter->{"Active power-"} > 0){
		$powerToGrid = $jsonMeter->{"Active power-"};
		#echo "Power to grid: ".$powerToGrid."\n";
		#}
		
		#if($this->A("NenaHomeWechselrichterID")){
		$W = new Wechselrichter(1);
		$jsonInverter = json_decode($W->getData());
		#if($jsonInverter->{"Battery SOC"} > 0){
		#echo "Battery SOC: ".$jsonInverter->{"Battery SOC"}."\n";
		#}
		#}
		
		$AC = anyC::get("Zweirad");
		$AC->addAssocV3("ZweiradFhemID", "!=", "0");
		$AC->addAssocV3("ZweiradChargeAPI", "!=", "", "OR");
		while($Z = $AC->n()){
			#echo "\n".$Z->A("ZweiradName")." start -----------------\n";
			#echo "Current SOC: ".$Z->A("ZweiradSOC")."\n";
			#echo "Target SOC: ".$Z->A("ZweiradSOCTarget")."\n";
			
			
			if($Z->A("ZweiradChargeAPI") == "go-e"){
				$ch = curl_init('http://192.168.7.38/api/status?filter=ama,amp,psm,tpa,frc,alw,wh,nrg');
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
				curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 1); 
				curl_setopt($ch, CURLOPT_TIMEOUT, 1);

				$jsonCharger = json_decode(curl_exec($ch));
				curl_close($ch);
				#$powerToGrid += 3000;
				#$phase = 1;
				#$amps = floor(($powerToGrid + $jsonCharger->nrg[11]) / 230);
				#if($amps > 16)
				#	$amps = 16;
				#	$phase = 3;
				#}
				#echo "Mögliche Phasen: $phase\n";
				#echo "Mögliche Ampere: $amps\n";
				
				$request = 'http://192.168.7.38/api/set?ids='. urlencode('{"pGrid": '.floor($powerToGrid * -1).'., "pAkku": '.floor($jsonInverter->{"Actual battery charge-discharge power"}).'., "pPv": '.floor($jsonInverter->{$W->A("WechselrichterUsePVValue")}).'.}');
				#echo $request."\n";
				$ch = curl_init($request);
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
				curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 1); 
				curl_setopt($ch, CURLOPT_TIMEOUT, 1);
				$response = curl_exec($ch);
				#echo "WB Antwort: ".$response."\n";
				curl_close($ch);
				
				#print_r($jsonCharger);
				#echo "Aktuelle Ladeleistung: ".$jsonCharger->nrg[11]."\n";
				#echo "Aktuelle Phasen: ".$jsonCharger->psm."\n";
			}
			
			#if($Z->A("ZweiradSOC") > $Z->A("ZweiradSOCTarget")){
				#echo "target SOC reached!\n";
				#echo "".$Z->A("ZweiradName")." finished -----------------\n\n";
				#continue;
			#}
			
			#echo "".$Z->A("ZweiradName")." finished -----------------\n\n";
		}
		#echo "sleep 5s…\n";
		sleep(5);
	}
} catch (FieldDoesNotExistException $e){
	die($e->getField()." does not exist!\n");
}
$e->cleanUp();

#echo "</pre>";
?>