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
 *  2007 - 2014, Rainer Furtmeier - Rainer@Furtmeier.IT
 */
class CCKalender extends CCPage implements iCustomContent {
	#private $loggedIn = false;
	function __construct() {
		parent::__construct();
		
		$this->loadPlugin("ubiquitous", "Kalender");
		$this->loadPlugin("ubiquitous", "Todo");
		
		addClassPath(dirname(__FILE__));
				
		
		#$this->loggedIn = Session::currentUser() != null;
	}
	
	function getStyle(){
		return "";
	}
	
	public function getScript(){
		return "var CCKalender = {
			
		};";
	}
	
	function getLabel(){
		return "Kalender";
	}
	
	function getTitle(){
		return $this->getLabel();
	}
	
	function getCMSHTML() {
		if(!$this->loggedIn)
			return $this->formLogin ();

		$D = new Datum();
		$D->normalize();
		
		$DE = clone $D;
		$DE->addDay();
		
		
		$T = new mTodoGUI();
		$K = $T->getCalendarData($D->time(), $DE->time(), Session::currentUser()->getID());
		
		while($DE->time() > $D->time()){
			
			$termine = $K->getEventsOnDay(date("dmY", $D->time()));
			if($termine != null)
				foreach($termine AS $ev)
					foreach($ev AS $v){

							echo get_class($v).": ".$v->title()."<br>";
						}
			
			$D->addDay();
		}
		
	}
}

?>