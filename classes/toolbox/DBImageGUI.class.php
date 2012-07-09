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
 *  2007 - 2012, Rainer Furtmeier - Rainer@Furtmeier.de
 */

class DBImageGUI implements iGUIHTML2  {
	protected $image;
	
	public function __construct($image = null) {
		$this->image = $image;
	}
	
	protected function stringify($mimeType, $path){
		return $mimeType.":::".filesize($path).":::".base64_encode(addslashes(file_get_contents($path)));
	}
	
	public static function stringifyS($mimeType, $path){
		return $mimeType.":::".filesize($path).":::".base64_encode(addslashes(file_get_contents($path)));
	}
	
	public function loadMe(){
		
	}
	
	public function A($val){
		
	}
	
	public static function imageLink($className, $classID, $classAttribute, $inWindow = false, $randomize = false){
		return ($inWindow ? "." : "")."./interface/loadFrame.php?p=DBImage&id=$className:::$classID:::$classAttribute".($randomize ? "&r=".rand(1, 99999999) : "").(isset($_GET["physion"]) ? "&physion=".$_GET["physion"] : "");
	}

	protected function showError($message){
			header("Content-type: image/png");
			$img = imagecreatetruecolor(398, 300);
			imagestring($img, 2, 5, 5,  $message, imagecolorallocate($img, 255, 0, 0));

			imagepng($img);
			imagedestroy($img);
			exit;
	}

	function getHTML($id){
		if($id == "" OR $id == -1)
			$this->showError("No data available!");

		$d = explode(":::",$id);

		$C = $d[0];
		$C = new $C($d[1]);
		$C->loadMe();
		$a = $d[2];
		$i = $C->A($a);

		$i = explode(":::",$i);
		if(!isset($i[0])) return;
		if(!isset($i[1])) return;
		if(!isset($i[2])) return;
		
		header("Content-type: $i[0]");
		header("Content-length: $i[1]");
		echo stripslashes(base64_decode($i[2]));
	}

}
?>