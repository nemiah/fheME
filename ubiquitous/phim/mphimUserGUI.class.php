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

class mphimUserGUI extends anyC implements iGUIHTMLMP2 {

	public function getHTML($id, $page){
		$this->loadMultiPageMode($id, $page, 0);

		$gui = new HTMLGUIX($this);
		$gui->colWidth("phimUserActive", 20);
		
		$gui->name("Benutzer");
		
		$B = $gui->addSideButton("Zurück", "back");
		$B->loadPlugin("contentRight", "mphim");
		
		$gui->attributes(array("phimUserActive", "phimUserUserID"));
		
		$gui->parser("phimUserActive", "parserActive");
		$gui->parser("phimUserUserID", "parserUser");
		
		return $gui->getBrowserHTML($id);
	}
	
	public static function parserActive($w){
		return Util::catchParser($w);
	}

	public static function parserUser($w){
		$AC = Users::getUsersArray();
		
		return $AC[$w];
	}

}
?>