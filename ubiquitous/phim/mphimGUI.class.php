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

class mphimGUI extends anyC implements iGUIHTMLMP2 {

	public function getHTML($id, $page){
		$this->loadMultiPageMode($id, $page, 0);

		$gui = new HTMLGUIX($this);
		$gui->version("mphim");

		$gui->name("phim");
		
		$gui->attributes(array());
		
		return $gui->getBrowserHTML($id);
	}

	public function getInit(){
		/*$I = new HTMLInput("phimMessage");
		$I->onEnter("phim.send(this.value);");
		echo $I;*/
		$U = Users::getUsers();
		# = array();
		#while($us = $U->getNextEntry())
		#	$users[$us->getID()] = $us->A("name");
		
		$L = new HTMLList();
		$L->addListStyle("list-style-type:none;margin-bottom:10px;");
		#$AC = anyC::get("phim", "phimUserID", Session::currentUser()->getID());
		#while($p = $AC->getNextEntry()){
		while($us = $U->getNextEntry()){
			if($us->getID() == Session::currentUser()->getID())
				continue;
			
			$B = new Button("Status", "./ubiquitous/phim/userOffline.png", "icon");
			$B->style("float:left;margin-right:5px;margin-top:-2px;margin-left:-15px;");
			$B->className("phimUserStatus");
			$B->id("phimUserStatus".$us->getID());
				
			$L->addItem($B.$us->A("name")." <span id=\"phimUserUnread".$us->getID()."\"></span>");
			$L->addItemEvent("onclick", "phim.getChatWindow(".$us->getID().", '".$this->user2id($us->getID())."');");
			$L->addItemStyle("cursor:pointer;");
		}
		
		echo "<div class=\"\" style=\"width:180px;margin-left:0px;margin-top:20px;\">".$L.OnEvent::script("phim.currentUser = ".Session::currentUser()->getID())."</div>";
	}
	
	public function user2id($id){
		$U = new User($id);
		return $U->A("name");
	}
	
	public function getChatWindow($phimTargetUserID){
		$I = new HTMLInput("phimSendTo$phimTargetUserID", "text");
		$I->style("width:99%;");
		$I->onEnter("{ phim.send(this.value, '".Session::currentUser()->getID()."', $phimTargetUserID); this.value = ''; }");
		$I->id("phimSendTo$phimTargetUserID");
		
		$other = new User($phimTargetUserID);
		
		$AC = anyC::get("phim");
		$AC->addAssocV3("phimFromUserID", "=", Session::currentUser()->getID(), "AND", "1");
		$AC->addAssocV3("phimToUserID", "=", $phimTargetUserID, "AND", "1");
		
		$AC->addAssocV3("phimFromUserID", "=", $phimTargetUserID, "OR", "2");
		$AC->addAssocV3("phimToUserID", "=", Session::currentUser()->getID(), "AND", "2");
		
		$AC->setOrderV3("phimTime", "DESC");
		$AC->setLimitV3("30");
		
		$messages = array();
		while($M = $AC->getNextEntry()){
			$from = $M->A("phimFromUserID");
			if($from == $phimTargetUserID)
				$from = $other->A("name");
			else
				$from = Session::currentUser()->A("name");
			
			$messages[] = "<p style=\"padding:3px;line-height:1.5;\"><span style=\"color:grey;\">(".Util::CLDateTimeParser($M->A("phimTime")).")</span> <b>$from:</b> ".$M->A("phimMessage")."</p>";
		}
		
		sort($messages);
		
		$html = "<div id=\"phimMessages$phimTargetUserID\" class=\"borderColor1\" style=\"border-bottom-style:solid;border-bottom-width:3px;height:200px;overflow:auto;\">".  implode("", $messages)."</div>$I";
		
		echo $html.OnEvent::script("setTimeout(function(){ $('phimMessages$phimTargetUserID').scrollTop = $('phimMessages$phimTargetUserID').scrollHeight; \$j('#phimSendTo$phimTargetUserID').trigger('focus'); },100); phim.ids2users['$phimTargetUserID'] = '".$this->user2id($phimTargetUserID)."'; phim.ids2users['".Session::currentUser()->getID()."'] = '".Session::currentUser()->A("name")."';");
	}
}
?>