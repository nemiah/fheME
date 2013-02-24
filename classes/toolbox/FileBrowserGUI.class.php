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
 *  2007 - 2013, Rainer Furtmeier - Rainer@Furtmeier.IT
 */
class FileBrowserGUI extends UnpersistentClass implements iGUIHTML2 {
	
	protected $tableLabel;
	protected $directory;
	
	public function getHTML($id){
		$gui = new HTMLGUI();
		$gui->VersionCheck($this->getClearClass());
		
		$t = new HTMLTable(2,$this->tableLabel);
		$t->addColStyle(1,"width:20px;");
		
		$FB = new FileBrowser();
		$FB->addDir($this->directory);
		$w = $FB->getAsLabeledArray("iFileBrowser",".class.php");
		
		foreach($w as $k => $v) {
			$B = new Button("","./images/i2/edit.png");
			$B->type("icon");
			$B->onclick("contentManager.loadFrame('contentLeft','$v');");
			
			$t->addRow(array($B,"$k"));
		}
		return $t->getHTML();
	}
}

?>