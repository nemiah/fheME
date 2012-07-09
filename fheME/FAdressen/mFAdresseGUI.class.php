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
 *  along with this program.  If not, see <http://www.gnu.org/licenses/>.
 * 
 *  2007 - 2012, Rainer Furtmeier - Rainer@Furtmeier.de
 */
class mFAdresseGUI extends AdressenGUI {
	function __construct(){
		parent::__construct();
		
		$this->gui = new HTMLGUIX();
	}
        
	function getHTML($id, $page){
		$this->filterCategories();
		
		#$gui->VersionCheck("mWAdresse");
		
		$this->loadMultiPageMode($id, $page, 0);

		$gui = $this->gui = new HTMLGUIX($this);
		$gui->options(true, true, true, true);
		$gui->name("Adresse");

		$gui->attributes(array("vorname", "nachname"));
		$gui->activateFeature("reloadOnNew", $this);


		return $gui->getBrowserHTML($id);
	}
        
	public function getCollectionOf(){
		return "FAdresse";
	}


	function getClearClass(){
		return "mFAdresse";
	}

	public static function doSomethingElse(){
		$k = new Kategorien();
		$k->addKategorie("Adressen","1");
	}
	
	public static function getCalendarDetails($className, $classID, $T = null) {
		$K = new Kalender();
		if($T == null)
			$T = new FAdresseGUI($classID);

		$name = $T->A("vorname")." ".$T->A("nachname");

		$day = mktime(8, 0, 0, date("m", $T->A("geb")), date("d", $T->A("geb")), date("Y"));

		$time = $T->A("TodoTillTime");
		
		$KE = new KalenderEvent($className, $classID, $K->formatDay($day),"0900", $name);
		
		#$KE->repeat(true, "yearly");
		
		$KE->icon("./fheME/FAdressen/birthday.png");
		$KE->summary("Geburtstag");
		return $KE;
	}

	public static function getCalendarData($firstDay, $lastDay) {
		$K = new Kalender();

		$AC = new anyC();
		$AC->setCollectionOf("Adresse");
		$AC->addAssocV3("type", "=", "default");
		$AC->addAssocV3("AuftragID", "=", "-1");
		$AC->addAssocV3("geb", ">", "0");
		
		while($t = $AC->getNextEntry())
			$K->addEvent(self::getCalendarDetails("mFAdresseGUI", $t->getID(), $t));

		return $K;
	}
}
?>