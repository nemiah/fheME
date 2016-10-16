<?php
/**
 *  This file is part of Demo.

 *  Demo is free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 3 of the License, or
 *  (at your option) any later version.

 *  Demo is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.

 *  You should have received a copy of the GNU General Public License
 *  along with this program.  If not, see <http://www.gnu.org/licenses></http:>.
 * 
 *  2007 - 2012, Rainer Furtmeier - Rainer@Furtmeier.de
 */

class mTinkerforgeGUI extends anyC implements iGUIHTMLMP2 {

	public function getHTML($id, $page){
		$this->loadMultiPageMode($id, $page, 0);

		$gui = new HTMLGUIX($this);
		$gui->version("mTinkerforge");

		$gui->name("Tinkerforge");
		
		$gui->attributes(array());
		
		return $gui->getBrowserHTML($id);
	}

	
	public static function getOverviewPlugin(){
		return new overviewPlugin("mTinkerforgeGUI", "Tinkerforge", 100);
	}
	
	public function getOverviewContent(){
		$html = "<div class=\"touchHeader\"><span class=\"lastUpdate\" id=\"lastUpdatemTinkerforgeGUI\"></span><p>Tinkerforge</p></div>
			<div style=\"padding:10px;\">";

		$AC = anyC::get("Tinkerforge");
		
		while($T = $AC->getNextEntry()){
			$ACB = anyC::get("TinkerforgeBricklet", "TinkerforgeBrickletTinkerforgeID", $T->getID());
			
			while($B = $ACB->getNextEntry()){
				$html .= $B->getControl();
			}
			
		}
		
		
		$html .= "</div>";
		echo $html;
	}
	
}
?>