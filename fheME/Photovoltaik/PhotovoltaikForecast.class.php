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
class PhotovoltaikForecast extends PersistentObject {
	public static function update(){
		sleep(3);
		$AC = anyC::get("PhotovoltaikForecast");
		$AC->setLimitV3(1);
		$AC->addAssocV3("PhotovoltaikForecastUpdate", "<", time() - 3600);
		while($F = $AC->n()){
			$data = file_get_contents($F->A("PhotovoltaikForecastURL"));
			$F->changeA("PhotovoltaikForecastData", $data);
			$F->changeA("PhotovoltaikForecastUpdate", time());
			
			$F->saveMe();
		}
	}
}
?>