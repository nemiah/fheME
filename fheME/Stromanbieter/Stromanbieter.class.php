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
class Stromanbieter extends PersistentObject {
	public function usageGet(){
		$jsonLive = '{"query":"{ viewer { homes { consumption(resolution: DAILY, last: '.(date("d") - 1).') { nodes { from to cost unitPrice unitPriceVAT consumption consumptionUnit }}}}}"}';

		# Create a connection
		$ch = curl_init('https://api.tibber.com/v1-beta/gql');
		curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json', 'Authorization: Bearer sPcho87J0CoPsExquRkws97KfKQ7kdyWRFi8XNStpZo')); // Demo token
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonLive);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

		# Get the response
		$response = curl_exec($ch);
		$r = json_decode($response);
		curl_close($ch);
		
		$collector = [];
		
		foreach($r->data->viewer->homes[0]->consumption->nodes AS $consumption){
			#print_r($consumption);
			$day = strtotime($consumption->from);
			if(!isset($collector[date("Ym", $day)]))
				$collector[date("Ym", $day)] = [0, 0];
			
			$collector[date("Ym", $day)][0] += $consumption->cost;
			$collector[date("Ym", $day)][1] += $consumption->consumption;
		}
		
		return $collector;
	}
	
	public function pricesGet(){
		$jsonLive = '{"query":"{ viewer { homes { currentSubscription { priceInfo{ today { total startsAt } tomorrow { total startsAt }}}}}}"}';

		# Create a connection
		$ch = curl_init('https://api.tibber.com/v1-beta/gql');
		curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json', 'Authorization: Bearer sPcho87J0CoPsExquRkws97KfKQ7kdyWRFi8XNStpZo')); // Demo token
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonLive);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

		# Get the response
		$response = curl_exec($ch);
		curl_close($ch);

		
		#echo '<pre>';
		$r = json_decode($response);
				
		$minD1 = 100;
		$maxD1 = 0;
		$minD2 = 100;
		$maxD2 = 0;
		$minD1Time = null;
		$minD2Time = null;
		
		$data = [];
		foreach($r->data->viewer->homes[0]->currentSubscription->priceInfo->today AS $priceInfo){
			$price = $priceInfo->total * 100;
			$data[] = array(strtotime($priceInfo->startsAt) * 1000, $price);
			if($price < $minD1){
				$minD1 = $price;
				$minD1Time = strtotime($priceInfo->startsAt);
			}
			
			if($price > $maxD1)
				$maxD1 = $price;
		}
		
		
		foreach($r->data->viewer->homes[0]->currentSubscription->priceInfo->tomorrow AS $priceInfo){
			$price = $priceInfo->total * 100;
			$data[] = array(strtotime($priceInfo->startsAt) * 1000, $price);
			if($price < $minD2){
				$minD2 = $price;
				$minD2Time = strtotime($priceInfo->startsAt);
			}
			
			if($price > $maxD2)
				$maxD2 = $price;
		}
		
		#echo "<pre style=\"font-size:8px;\">";
		#print_r($data);
		#echo "</pre>";
		
		return [$data, $minD1, $maxD1, $minD1Time, $minD2, $maxD2, $minD2Time];
	}
}
?>