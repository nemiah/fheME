<?php
/**
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
class ADesktopGUI extends UnpersistentClass implements iGUIHTML2 {
	function  __construct() {
		parent::__construct();
		$this->customize();
	}
	public function getHTML($id){
		switch($id){
			case "1":
				return "
				<script type=\"text/javascript\">
					if($('mInstallationMenuEntry')){
						$('contentLeft').update('');
						Popup.closeNonPersistent();
						contentManager.loadFrame('contentRight', 'mInstallation', -1, 0, 'mInstallationGUI;-');
						setHighLight($('mInstallationMenuEntry'));
					}
					else if($('mCloudMenuEntry')){
						$('contentLeft').update('');
						Popup.closeNonPersistent();
						contentManager.loadFrame('contentRight', 'mCloud', -1, 0, 'mCloudGUI;-');
						setHighLight($('mCloudMenuEntry'));
					}
				</script>";
			break;
			
			case "2":
				
			break;
		}
	}
	
	public function getOffice3aRSS(){
		if(Environment::getS("blogShow", "1") == "0")
			return "";
		
		$data = file_get_contents(Environment::getS("blogRSSURL", "http://blog.office3a.eu/feed/"));
		
		if($data === false)
			return "";
		
		$html = "
			<div style=\"border-bottom:1px solid #DDD;position:relative;\" class=\"desktopButton\" onclick=\"window.open('".Environment::getS("blogURL", "http://blog.office3a.eu/")."', '_blank');\">
				<h1 style=\"font-size:2.0em;color:#999999;position:absolute;bottom:5px;\">".Environment::getS("blogName", "office<span style=\"color:#A0C100;\">3a</span> blog")."</h1>
			</div>
			<div style=\"padding-left:30px;padding-right:30px;\">";
		try {
			$XML = new SimpleXMLElement($data);

			$i = 0;
			foreach($XML->channel->item AS $item){
				$html .= "<h2 style=\"color:#999999;".($i > 0 ? "margin-top:30px;" : "")."margin-bottom:0px;\">".$item->title."</h2>";

				$html .= "<p style=\"color:#999999;margin-top:10px;\">".$item->description."<br />
					<small style=\"color:#AAA;\">".Util::CLFormatDate(strtotime($item->pubDate), true)."</small> <a style=\"float:right;color:#444;\" href=\"$item->link\">mehr...</a></p>";
				#print_r($item);

				$i++;

				if($i == 4)
					break;
			}

			$html .= "</div>";

			return $html;
		} catch (Exception $e){
			return "";
		}
	}
}
?>