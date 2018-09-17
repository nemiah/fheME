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
 *  2007 - 2018, Furtmeier Hard- und Software - Support@Furtmeier.IT
 */

class mphimGruppeGUI extends anyC implements iGUIHTMLMP2 {

	public function getHTML($id, $page){
		$this->addAssocV3("phimGruppeMasterUserID", "=", Session::currentUser()->getID());
		$this->loadMultiPageMode($id, $page, 0);

		$gui = new HTMLGUIX($this);
		$gui->version("mphim");

		$gui->name("Gruppe");
		
		$B = $gui->addSideButton("Zurück", "back");
		$B->loadPlugin("contentRight", "mphim");
		
		$gui->attributes(array("phimGruppeName"));
		
		return $gui->getBrowserHTML($id);
	}


}
?>