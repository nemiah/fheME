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

class DBImageGUI implements iGUIHTML2  {
	protected $image;
	
	public function __construct($image = null) {
		$this->image = $image;
	}
	
	public static function resizeMax($imageData, $max_width, $max_height){
		$image = imagecreatefromstring($imageData);
		
		$width  = $max_width;
		$height = $max_height;
		$width_orig = imagesx($image);
		$height_orig = imagesy($image);
		
		if($width_orig < $width AND $height_orig < $height)
			return $imageData;
		
		$ratio_orig = $width_orig/$height_orig;

		if ($width/$height > $ratio_orig)
		   $width = floor($height*$ratio_orig);
		else
		   $height = floor($width/$ratio_orig);
		
		$tempimg = imagecreatetruecolor($width, $height);
		imagecopyresampled($tempimg, $image, 0, 0, 0, 0, $width, $height, $width_orig, $height_orig);
		
		ob_start();
		imagepng($tempimg);
		$image_contents = ob_get_contents();
        ob_end_clean();

		return $image_contents;
	}
	
	protected function stringify($mimeType, $path){
		return $mimeType.":::".filesize($path).":::".base64_encode(addslashes(file_get_contents($path)));
	}
	
	public static function stringifyS($mimeType, $path, $maxWidth = null, $maxHeight = null){
		$imageData = file_get_contents($path);
		$size = filesize($path);
		
		if($maxWidth != null){
			$mimeType = "image/png";
			$imageData = self::resizeMax($imageData, $maxWidth, $maxHeight);
			$size = strlen($imageData);
		}
		
		return $mimeType.":::".$size.":::".base64_encode(addslashes($imageData));
	}
	
	public static function getData($imageString){
		$data = explode(":::",$imageString);
		
		return stripslashes(base64_decode($data[2]));
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