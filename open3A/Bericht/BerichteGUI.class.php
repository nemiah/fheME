<?php
/*
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
 *  along with this program.  If not, see <http://www.gnu.org/licenses/>.
 * 
 *  2007 - 2024, open3A GmbH - Support@open3A.de
 */
#namespace open3A;
class BerichteGUI extends Berichte implements iGUIHTML2 {
	public function __construct() {
		parent::__construct();
		
		$this->customize();
	}
	
	public function getHTML($id){
		T::load(__DIR__, "Berichte");
		$gui = new HTMLGUIX($this);
		$gui->version("Berichte");

		$ps = mUserdata::getPluginSpecificData("Berichte");
		#print_r($ps);
		
		$files = $this->getFiles();
		
		
		$categories = array();
		
		foreach($files as $key => $value){
			if(isset($ps["pluginSpecificH".str_replace("Bericht_", "", $value[0])]))
				continue;

			if($value[1] == null)
				$value[1] = "Weitere";
			
			if(!isset($categories[$value[1]]))
				$categories[$value[1]] = array();

			$categories[$value[1]][$key] = $value[0];
		}
		
		ksort($categories);
		
		$divs = "";
		foreach($categories AS $cat => $entries){
			
			$T = new HTMLTable(3);
			$T->setTableStyle("width:100%;");
			$T->setColWidth(1, 20);
			$T->setColWidth(2, 20);
			$T->useForSelection(false);
			$T->weight("light");
		
			foreach($entries AS $key => $value){
				#echo "pluginSpecificH".str_replace("Bericht_", "", $key)."<br>";
				$B = new Button("Bericht anzeigen", "./images/i2/bericht.png", "icon");
				
				$BPDF = new Button("PDF anzeigen", "./images/i2/pdf.gif", "icon");
				$BPDF->doBefore("contentManager.selectRow(this); %AFTER");
				$BPDF->windowRme(str_replace("GUI","",$value), "-1", "getPDF");

				$c = new $value();


				$action = "contentManager.selectRow(this);".OnEvent::popup("Bericht anzeigen", "Berichte", "-1", "chainload", array("'$value'"), "", "{right:450}");

				$sb = $c->hasSettings();
				if(!$sb == true){
					$action = "";
					if(is_object($BPDF)){
						$action = $BPDF->getAction();
						$BPDF->onclick($action);
					}
					$B = "";
				}

				$cb = $c->quickButton();

				if($cb !== false){
					$BPDF = $cb;
					if(is_array($cb)){
						$BPDF = $cb[0];
						$action = $cb[1];
					}
				}

				$T->addRow(array($B, $BPDF, T::_($key)));

				$T->addCellEvent(1, "click", $action);
				$T->addCellEvent(3, "click", $action);
			}
			
			$B = new Button($key, "bericht", "icon");
			$B->style("float:left;margin-right:10px;margin-top:-7px;margin-left:-5px;");
			
			$divs .= "<div style=\"\" class=\"SpellbookSpell\">
				<div style=\"margin:10px;border-radius:5px;\" class=\"borderColor1 spell\">
					<div class=\"backgroundColor2\" style=\"padding:10px;padding-bottom:5px;\">
						$B<h2 style=\"margin-bottom:0px;margin-top:0px;\">$cat</h2>
					</div>
					<div style=\"padding:7px;height:190px;overflow:auto;\" class=\"SpellbookDescription\">$T</div>
				</div>
			</div>";
			
			
			$divs .=  "</div>";
		}
		
		return "<p class=\"prettyTitle\">".T::_("Berichte")."</p>$divs";#<div id=\"berichteFrame\">".$T."</div>".HTMLGUIX::tipJS("Berichte").OnEvent::script("\$j('#berichteFrame').css('overflow', 'auto').css('height', contentManager.maxHeight() - \$j('.prettyTitle').outerHeight());");
	}
	
	public function chainload($target){
		if(!PMReflector::implementsInterface($target, "iBerichtDescriptor"))
			return;
		
		$c = new $target();
		echo $c->getHTML(-1)."<div style=\"height:40px;\"></div>";
	}
}
?>