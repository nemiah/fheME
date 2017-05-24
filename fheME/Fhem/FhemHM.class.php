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
 *  2007 - 2017, Furtmeier Hard- und Software - Support@Furtmeier.IT
 */

class FhemHM {
	public static function status($data, Fhem $F){
		
		switch($F->A("FhemHMModel")){
			case "HM-Sec-RHS":
				#return $data->attributes()->state;
			break;
		}
		
		return $data->attributes()->state;
	}
	
	public static function icon($data, Fhem $F, Button $B){
		switch($F->A("FhemHMModel")){
			case "HM-Sec-RHS":
				if($data->attributes()->state == "open")
					$B->image ("warning");
				
				if($data->attributes()->state == "closed")
					$B->image ("bestaetigung");
				
				if($data->attributes()->state == "tilted")
					$B->image ("notice");
				
			break;
		}
	}
}
?>