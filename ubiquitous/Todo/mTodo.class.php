<?php
/*
 *  This file is part of phynx.

 *  phynx is free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 3 of the License, or
 *  (at your option) any later version.

 *  phynx is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.

 *  You should have received a copy of the GNU General Public License
 *  along with this program.  If not, see <http://www.gnu.org/licenses/>.
 * 
 *  2007 - 2021, open3A GmbH - Support@open3A.de
 */
class mTodo extends anyC {
	public static function getHistorieData($ownerClass, $ownerClassID, HistorieTable $Tab){
		$AC = anyC::get("Todo", "TodoClass", $ownerClass);
		$AC->addAssocV3("TodoClassID", "=", $ownerClassID);
		$AC->addOrderV3("TodoFromDay", "DESC");
		$AC->setLimitV3("10");
		
		while($D = $AC->getNextEntry()){
			$B = new Button("Aktivität anzeigen", "./ubiquitous/Todo/Todo.png", "icon");
			$B->popup("", "Event", "mKalender", "-1", "getInfo", array("'mTodoGUI'", $D->getID(), $D->A("TodoFromDay")));

			$Tab->addHistorie("Aktivität", "./ubiquitous/Todo/Todo.png", $D->A("TodoFromDay"), $D->getOwnerObject()->getCalendarTitle(), $B, $D->A("TodoDescription"), $D->A("TodoCreatorUserID"));
		}
		return true;
	}
}

?>
