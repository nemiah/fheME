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

class DBSoundGUI implements iGUIHTML2  {
	
	public static function link($className, $classID, $classMethod, $classMethodAttribute, $inWindow = false, $randomize = false){
		return ($inWindow ? "." : "")."./interface/loadFrame.php?p=DBSound&id=$className:::$classID:::$classMethod:::$classMethodAttribute".($randomize ? "&r=".rand(1, 99999999) : "").(isset($_GET["physion"]) ? "&physion=".$_GET["physion"] : "");
	}

	function getHTML($id){
		if($id == "" OR $id == -1)
			return;

		$d = explode(":::",$id);
		#print_r($d);
		$C = $d[0];
		$C = new $C($d[1]);
		$C->loadMe();
		$a = $d[2];
		if(isset($d[3]))
			$i = $C->$a($d[3]);
		else
			$i = $C->$a();
	}

}
?>