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
	
	public function pricesGet(){
		$jsonLive = '{"query":"{ viewer {homes {currentSubscription{priceInfo{today {total startsAt } tomorrow { total startsAt }}}}}}"}';

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

		$data = [];
		foreach($r->data->viewer->homes[0]->currentSubscription->priceInfo->today AS $priceInfo)
			$data[] = array(strtotime($priceInfo->startsAt) * 1000, $priceInfo->total);
		
		foreach($r->data->viewer->homes[0]->currentSubscription->priceInfo->tomorrow AS $priceInfo)
			$data[] = array(strtotime($priceInfo->startsAt) * 1000, $priceInfo->total);
		
		return $data;
	}
}
?>