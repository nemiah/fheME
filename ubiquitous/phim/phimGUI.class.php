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
 *  2007 - 2019, open3A GmbH - Support@open3A.de
 */
		
class phimGUI extends phim implements iGUIHTML2 {
	function getHTML($id){
		$gui = new HTMLGUIX($this);
		$gui->name("phim");
	
		$gui->optionsEdit(false, false);
		
		return $gui->getEditHTML();
	}
	
	function ping(){ //dummy for keeping the session alive
		
	}
	
	function offline(){
		$message = new stdClass();
		$message->method = "offline";
		$message->user = Session::currentUser()->getID();
		
		$this->go($message, 0);
	}
	
	function online(){
		$message = new stdClass();
		$message->method = "online";
		$message->user = Session::currentUser()->getID();
		
		$this->go($message, 0);
	}
	
	function setRead($fromUserID){
		$AC = anyC::get("phim");
		$AC->addAssocV3("phimFromUserID", "=", $fromUserID);
		$AC->addAssocV3("phimToUserID", "=", Session::currentUser()->getID());
		$AC->addAssocV3("phimRead", "=", "0");
		while($P = $AC->n()){
			echo $P->getID()."\n";
			$P->changeA("phimRead", "1");
			$P->saveMe();
		}
		
		$message = new stdClass();
		$message->method = "read";
		#$message->content = $text;
		$message->from = $fromUserID;
		#$message->fromUser = Session::currentUser()->A("name");
		$message->to = Session::currentUser()->getID();
		$message->time = time();
		
		#$F->store();
		
		$this->go($message, Session::currentUser()->getID());
	}
}
?>