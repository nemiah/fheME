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
 *  2007 - 2021, open3A GmbH - Support@open3A.de
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
						Menu.setHighLight($('mInstallationMenuEntry'));
					}
					else if($('mCloudMenuEntry')){
						$('contentLeft').update('');
						Popup.closeNonPersistent();
						contentManager.loadFrame('contentRight', 'mCloud', -1, 0, 'mCloudGUI;-');
						Menu.setHighLight($('mCloudMenuEntry'));
					}
				</script>";
			break;
			
			case "2":
				
			break;
		}
	}
	
	public function getOpen3AVersion(){
		$this->currentVersion = $_SESSION["applications"]->getRunningVersion();

		$appName = Applications::activeApplicationLabel();
		
		$hpVersion = null;
		
		$version = Util::httpTestAndLoad("http://www.open3a.de/currentVersion.php?app=".Applications::activeApplication(), 2);
		#var_dump($version);
		$XML = new XMLC();
		$XML->setXML($version["_response"]);
		try {
			$XML->lCV3();
			$t = $XML->getNextEntry();
			if($t != null)
				$hpVersion = $t->getA()->Version;
		} catch (Exception $e){}
		
		$html = "";
		
		if($hpVersion != null AND (Util::versionCheck($hpVersion, $this->currentVersion) OR Util::versionCheck($hpVersion, $this->currentVersion, "<"))) 
			$html .= "
				
					".(Util::versionCheck($hpVersion, $this->currentVersion) ? "Auf der Homepage steht eine neue Version von $appName zur Verfügung ($hpVersion). Sie benutzen Version $this->currentVersion." : (Util::versionCheck($hpVersion, $this->currentVersion, "==") ? "Ihre $appName-Version ist auf dem aktuellen Stand." : "Ihre $appName-Version ist aktueller als die Version auf der Homepage!"))."
				";
		
		$B = new Button("Zur\nRegistrierung", "navigation");
		$B->onclick("window.open('https://www.open3a.de/page-Registrierung', '_blank');");
		$B->style("float:right;margin-top:-27px;");
			
		
		$htmlSub = T::_("Registrieren Sie sich noch heute kostenlos auf open3A.de und Sie erhalten die aktuellsten News zu open3A in Ihr Postfach.");
		$A = Applications::i();
		$versionen = "";
		foreach($A->getVersions() AS $app => $version)
			$versionen .= "<div style=\"display:inline-block;width:33.3333%;vertical-align:top;\">$app $version</div>";
		
		echo "<div style=\"height:calc(187px - 7px - 7px);\">".($html != "" ? "<br><br>" : "").$htmlSub."<br><br>".$versionen."</div>$B";

	}
		
	public function getOffice3aRSS(){ //noch bisschen lassen, falls Leute die Installation nur überschreiben 20200922
		if(Environment::getS("blogShow", "1") == "0")
			return "";
		
		if(function_exists("curl_init")){
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 1);
			curl_setopt($ch, CURLOPT_TIMEOUT, 1); //timeout in seconds
			curl_setopt($ch, CURLOPT_URL, Environment::getS("blogRSSURL", "https://blog.furtmeier.it/feed/"));
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			$data = curl_exec($ch);
			curl_close ($ch);
		} else {
			$ctx = stream_context_create(array('https' => array('timeout' => 1)));
			$data = file_get_contents(Environment::getS("blogRSSURL", "https://blog.furtmeier.it/feed/"), 0, $ctx);
		}
		
		$html = "
			<div style=\"border-bottom:1px solid #DDD;position:relative;\" class=\"desktopButton\" onclick=\"window.open('".Environment::getS("blogURL", "http://blog.furtmeier.it/")."', '_blank');\">
				<h1 style=\"font-size:2.0em;color:#999999;position:absolute;bottom:5px;\">".Environment::getS("blogName", "open<span style=\"color:#A0C100;\">3A</span> blog")."</h1>
			</div>
			<div id=\"blogContainer\" style=\"padding-left:30px;padding-right:30px;overflow:auto;\">";
		
		
		if($data === false)
			return "$html<p style=\"color:grey;\">Blog nicht erreichbar. ☹</p>";
		
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

			$html .= "</div>".OnEvent::script("\$j('#blogContainer').css('height', contentManager.maxHeight() - \$j('.desktopButton').outerHeight() - 20)");

			return $html;
		} catch (Exception $e){
			return "";
		}
	}
	
	public static function dataUpdate(){
		if(function_exists("curl_init")){
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 1);
			curl_setopt($ch, CURLOPT_TIMEOUT, 1); //timeout in seconds
			curl_setopt($ch, CURLOPT_URL, Environment::getS("dataURL", "http://data.open3a.de/?section=dashboard")); //use http, it's faster
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			$data = curl_exec($ch);
			curl_close($ch);
		} else {
			$ctx = stream_context_create(array('https' => array('timeout' => 1)));
			$data = file_get_contents(Environment::getS("dataURL", "http://data.open3a.de/?section=dashboard"), 0, $ctx);
		}
		
		if($data === false)
			$data = "{}";
		
		mUserdata::setUserdataS("dashboardData", $data, "", -1);
		
	}
	
	public static function dataGet(){
		$data = mUserdata::getGlobalSettingValue("dashboardData", null);
		if($data === null){
			
			self::dataUpdate();
			$data = mUserdata::getGlobalSettingValue("dashboardData", "{}");
		}
		
		$json = json_decode($data);
		
		if(isset($json->time) AND time() - $json->time > 3600 * 24 * 3){
			self::dataUpdate();
			json_decode(mUserdata::getGlobalSettingValue("dashboardData", "{}"));
		}
		
		return $json;
	}
}
?>