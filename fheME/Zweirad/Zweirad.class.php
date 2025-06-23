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
class Zweirad extends PersistentObject {
	public static function update($data){
		if(!isset($data["ZweiradID"]))
			return;
		
		$Z = new Zweirad($data["ZweiradID"]);
		$Z->loadMe();
		unset($data["ZweiradID"]);
		#print_r($data);
		foreach($data AS $k => $v)
			$Z->changeA($k, $v);
		
		$Z->saveMe();
	}
	
	public static function APIUpdate(){
		$AC = anyC::get("Zweirad", "ZweiradAPIUpdate", "1");
		while($Z = $AC->n())
			$Z->updateFromAPI();
		
	}
	
	public function updateFromAPI(){
		switch($this->A("ZweiradAPI")){
			case "silence":
				Util::alienAutloaderLoad(__DIR__."/vendor/autoload.php");
				
				$api = new nemiah\phpSilence\api($this->A("ZweiradAPIUser"), $this->A("ZweiradAPIPassword"));
				$scooters = $api->getData();
				Util::alienAutloaderUnload();
				
				foreach($scooters AS $scooter){
					if($scooter->frameNo != $this->A("ZweiradAPIID"))
						continue;
					
					$this->changeA("ZweiradSOC", $scooter->batterySoc);
					$this->changeA("ZweiradCharging", $scooter->charging ? "1" : "0");
					$this->changeA("ZweiradLastUpdate", strtotime($scooter->lastReportTime));
					$this->saveMe();
				}
				
			break;
			
			case "skoda":
				$data = shell_exec("ssh 192.168.122.69 -i /var/www/fheME/specifics/id_rsa \".local/bin/myskoda --format json --user '".$this->A("ZweiradAPIUser")."' --password '".$this->A("ZweiradAPIPassword")."' charging ".$this->A("ZweiradAPIID")." | ansi2txt\"");
				#echo $data;
				#$data = preg_replace('/\e[[][A-Za-z0-9];?[0-9]*m?/', '', $data);
				#var_dump(trim($data));
				$data = json_decode(mb_convert_encoding($data, 'UTF-8', 'UTF-8'), false, 512, JSON_THROW_ON_ERROR);
				if($data){
					$this->changeA("ZweiradSOC", $data->status->battery->state_of_charge_in_percent);
					$this->changeA("ZweiradCharging", "0");
					$this->changeA("ZweiradLastUpdate", strtotime($data->timestamp));
					$this->changeA("ZweiradStatus", "OK");
				} else {
					$this->changeA("ZweiradStatus", "ERROR");
				}
				
				$this->saveMe();
			break;
		}
	}
}
?>