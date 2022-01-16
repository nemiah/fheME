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
 *  2007 - 2021, open3A GmbH - Support@open3A.de
 */
		
class phimGUI extends phim implements iGUIHTML2 {
	function getHTML($id){
		$gui = new HTMLGUIX($this);
		$gui->name("phim");
	
		$gui->optionsEdit(false, false);
		
		return $gui->getEditHTML();
	}
	
	#function ping(){ //dummy for keeping the session alive
		
	#}
	
	public function communicationPopup($class, $classID){
		$AC = anyC::get("phimGruppe", "phimGruppeClassName", $class);
		$AC->addAssocV3("phimGruppeClassID", "=", $classID);
		$G = $AC->n();
		
		if(!$G){
			$Users = Users::getUsersArray();

			$AC = anyC::get("phimUserHidden");
			$hidden = $AC->toArray("phimUserHiddenUserID");

			$T = new HTMLTable(2);
			$T->setColWidth(1, 20);
			$T->useForSelection(false);
			foreach($Users AS $ID => $U){
				if(in_array($ID, $hidden))
					continue;

				
				$I = new HTMLInput("user_$ID", "checkbox", $ID == Session::currentUser()->getID() ? "1" : "0");
				$I->onchange("if(this.checked) \$j('[name=phimGruppeMembers]').val(\$j('[name=phimGruppeMembers]').val()+';$ID;'); else  \$j('[name=phimGruppeMembers]').val(\$j('[name=phimGruppeMembers]').val().replace(';$ID;', ''));");

				$T->addRow([$I, $U]);
				$T->addCellEvent(2, "click", "\$j('[name=user_$ID]').prop('checked', !\$j('[name=user_$ID]').prop('checked')).trigger('change');");
				if($ID == Session::currentUser()->getID())
					$T->addRowStyle ("display:none;");
			}

			$I = new HTMLInput("phimGruppeMembers", "hidden", ";".Session::currentUser()->getID().";");
			
			$B = new Button("Rückfrage\nerstellen", "bestaetigung");
			$B->style("margin:10px;float:right;");
			$B->rmePCR("phim", "-1", "communicationNewGroup", ["'$class'", "'$classID'", "\$j('[name=phimGruppeMembers]').val()"], OnEvent::reloadPopup("phim"));
			
			echo $T.$I.$B;
			return;
		}
		
		$users = Users::getUsersArray(null, true);
		$AC = anyC::get("phim", "phimphimGruppeID", $G->getID());
		echo "<p>";
		while($M = $AC->n()){
			echo "<strong>".$users[$M->A("phimFromUserID")].":</strong> ".$M->A("phimMessage")."<br>";
			
			if(strpos($M->A("phimReadBy"), ";".Session::currentUser()->getID().":") === false){
				$M->changeA("phimReadBy", ";".Session::currentUser()->getID().":".time().";");
				$M->saveMe();
			}
		}
		echo "</p>";
		
		echo "<div class=\"backgroundColor3\" style=\"border-top:1px solid grey;\">";
		$I = new HTMLInput("newMessage", "textarea");
		$I->style("width:100%;background-color:white;");
		$I->onEnter(OnEvent::rme($this, "sendMessage", ["'g".$G->getID()."'", "\$j('[name=newMessage]').val()"], OnEvent::reloadPopup("phim")));
		
		echo $I."</div>";
		echo OnEvent::script("\$j('#phimButton_{$class}_$classID').removeClass('highlight');");
		if($class == "DBMail")
			echo OnEvent::script ("\$j('#BrowserMain$classID').removeClass('confirm');");
		#contentManager.rmePCR('phim', -1, 'sendMessage', [$j('#channel').val(), $j('[name=newMessage]').val()], function(){ $j('[name=newMessage]').val(''); }, '', 1);
	}
	
	function communicationNewGroup($class, $classID, $members){
		
		$text = "";
		if($class == "DBMail"){
			$M = new DBMail($classID);
			$text = "zu E-Mail von ".($M->A("DBMailFromName") != "" ? $M->A("DBMailFromName") : $M->A("DBMailFromAddress"));
		}
		
		$F = new Factory("phimGruppe");
		$F->sA("phimGruppeName", "Rückfrage $text");
		$F->sA("phimGruppeMasterUserID", Session::currentUser()->getID());
		$F->sA("phimGruppeMembers", $members);
		$F->sA("phimGruppeClassName", $class);
		$F->sA("phimGruppeClassID", $classID);
		
		$F->store();
	}
	
}
?>