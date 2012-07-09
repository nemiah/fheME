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
 *  2007 - 2012, Rainer Furtmeier - Rainer@Furtmeier.de
 */

class Fhem_en_US implements iTranslation {
	public function getLabels(){
		return;
	}
	
	public function getMenuEntry(){
		return "";
	}
	
	public function getLabelDescriptions(){
		return array();
	}
	
	public function getFieldDescriptions(){
		return;
	}
	
	public function getText(){
		return ;
	}

	public function getSingular(){
		return "Device";
	}
	
	public function getPlural(){
		return "Devices";
	}

	public function getSearchHelp(){
		return "";
	}
	
	public function getEditCaption(){
		return "edit Device";
	}
	
	public function getSaveButtonLabel(){
		return "save Device";
	}
	
	public function getBrowserCaption(){
		return "Please select a Device";
	}
	
	public function getBrowserNewEntryLabel(){
		return "new Device";
	}
}
?>