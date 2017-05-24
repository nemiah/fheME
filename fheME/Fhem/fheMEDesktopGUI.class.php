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
 *  2007 - 2017, Furtmeier Hard- und Software - Support@Furtmeier.IT
 */
class fheMEDesktopGUI extends ADesktopGUI implements iGUIHTML2 {
	
	public function getHTML($id){
		// <editor-fold defaultstate="collapsed" desc="Aspect:jP">
		try {
			$MArgs = func_get_args();
			return Aspect::joinPoint("around", $this, __METHOD__, $MArgs);
		} catch (AOPNoAdviceException $e) {}
		Aspect::joinPoint("before", $this, __METHOD__, $MArgs);
		// </editor-fold>
		
		if($_SESSION["S"]->isUserAdmin()) return parent::getHTML($id);
		
		switch($id){
			case "1":
				//return OnEvent::script('contentManager.loadPlugin("contentScreen", "mfheOverview");');
				
				/*return "
				<script type=\"text/javascript\">
					if($('mfheOverviewMenuEntry')){
						$('contentLeft').update('');
						
						contentManager.loadFrame('contentScreen', 'mfheOverview', -1, 0, 'mfheOverviewGUI;-');
						setHighLight($('mfheOverviewMenuEntry'));
					}
				</script>";
			break;*/
			
			case "2":
			break;
		}

	}
}
?>
