<?php
/**
 *  This file is part of open3A.

 *  open3A is free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 3 of the License, or
 *  (at your option) any later version.

 *  open3A is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.

 *  You should have received a copy of the GNU General Public License
 *  along with this program.  If not, see <http://www.gnu.org/licenses></http:>.
 * 
 *  2007 - 2020, open3A GmbH - Support@open3A.de
 */

class mCustomerPageGUI extends UnpersistentClass implements iGUIHTMLMP2 {

	public function getHTML($id, $page){
		
		$T = new HTMLTable(2);
		$T->setTableStyle("width:100%;");
		$T->setColWidth(1, 20);
		#$T->setColWidth(2, 20);
		$T->useForSelection(false);
		$T->weight("light");
		
		$FB = new FileBrowser();
		$FB->addDir(dirname(__FILE__));
		#$FB->addDir(Util::getRootPath()."specifics");
		#$FB->addDir(FileStorage::getFilesDir());

		while($return = Registry::callNext("CustomerPage", "directory"))
			$FB->addDir($return);
		
		$exports = $FB->getAsLabeledArray("iCustomContent",".class.php");
		
		foreach($exports AS $k => $v){
			$c = new $v();
			
			if(!in_array(Applications::activeApplication(), $c->getApps()))
				continue;
			$B = new Button("");
			$B->windowRme("mCustomerPage", -1, "getPage", "$v", "", "tab");
			$action = $B->getAction();
			
			#if($c->isDisabled())
			#	$action = "";
			
			$B = new Button("", "./images/i2/edit.png", "icon");
			$B->className("editButton");
			#$B->onclick("contentManager.loadFrame('contentLeft','$v');");

			$T->addRow(array($B, T::_($k)));
			
			$T->addCellEvent(1, "click", $action);
			$T->addCellEvent(2, "click", $action);
			
			#if($c->isDisabled())
			#	$T->addCellStyle (2, "color:grey;");
		}
		
		return "<p class=\"prettyTitle\">".T::_("Externe Seiten")."</p><div id=\"externFrame\">".$T."</div>".
				OnEvent::script("\$j('#externFrame').css('overflow', 'auto').css('height', contentManager.maxHeight() - \$j('.prettyTitle').outerHeight());");
		
	}

	public function getPage($class){
		if(file_exists(__DIR__."/pages/$class.class.php"))
			header("Location: ../ubiquitous/CustomerPage/?CC=". substr($class, 2));
		else {
			$reflector = new \ReflectionClass($class);
			$sub = str_replace("/$class.class.php", "", str_replace(Util::getRootPath(), "", $reflector->getFileName()));

			header("Location: ../ubiquitous/CustomerPage/?D=$sub");
		}
		#echo $class;
	}
	
	public static function getDir(){
		return __DIR__."/pages";
	}
}
?>