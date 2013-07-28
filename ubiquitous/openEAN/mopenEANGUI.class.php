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
 *  along with this program.  If not, see <http://www.gnu.org/licenses/>.
 * 
 *  2007 - 2013, Rainer Furtmeier - Rainer@Furtmeier.IT
 */

class mopenEANGUI extends UnpersistentClass implements iGUIHTMLMP2 {

	public function getHTML($id, $page){
	}

	public function startSeach($EAN){
		$Tidy = new openEANTidy("http://openean.kaufkauf.net/index.php?cmd=ean1&ean=$EAN&sq=1");
		#print_r($Tidy);
		$HS = new HTMLSlicer($Tidy);

		$artikel = array();
		$name = $HS->getTag("//input[@name='name']");
		if(count($name) == 0)
			return $artikel;
		$artikel["name"] = $name[0]->attributes()->value."";
		
		$name = $HS->getTag("//input[@name='fullname']");
		$artikel["fullname"] = $name[0]->attributes()->value."";
		
		return $artikel;
	}

	/*public function getOpenEANHTML($EAN){
		$B = new Button("HTML\nanzeigen", "empty");
		$B->windowRme("mopenEAN", "-1", "getOpenEANHTML", array("'$EAN'"));

		echo $B;
	}*/
}



class openEANTidy extends HTMLTidy {
	function cleanUp(){
		$this->removeTag("script");
		$this->removeTag("link");
		$this->removeTag("meta");
		$this->removeTag("iframe");
		$this->removeComments();
	}
}
?>