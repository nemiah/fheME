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

class Filelink {
	public static function configured(){
		return mUserdata::getGlobalSettingValue("filelinkPublicPath") != null;
	}
	
	public static function minSize(){
		return mUserdata::getGlobalSettingValue("filelinkMinSize", 1024*1024);
	}
	
	public static function dir($for){
		self::cleanUp();
		$FLPRoot = Util::getRootPath()."../phynxPublic/filelink/";

		if(!file_exists($FLPRoot."index.html"))
			file_put_contents($FLPRoot."index.html", "");
		
		$FLP = $FLPRoot.self::makeDir($for);
		
		if(!file_exists($FLP))
			mkdir($FLP);
		
		return $FLP."/";
	}
	
	public static function publicPath($for){
		$publicPath = mUserdata::getGlobalSettingValue("filelinkPublicPath");
		
		if(substr($publicPath, -1) != "/")
			$publicPath .= "/";
		
		return $publicPath.self::makeDir($for)."/";
	}
	
	private static function makeDir($for){
		return substr(sha1($for), 3, 20);
	}
	
	public static function cleanUp(){
		if(!file_exists(Util::getRootPath()."../phynxPublic/filelink"))
			return;
		
		$delete = array();
		$dir = new DirectoryIterator(Util::getRootPath()."../phynxPublic/filelink/");
		foreach ($dir as $file) {
			if($file->isDot()) continue;
			if(!$file->isDir()) continue;

			if($file->getCTime() < time() - 3600 * 24 * 14)
				$delete[] = $file->getPathname();
		}
		
		foreach($delete AS $dir){
			$D = new DirectoryIterator($dir);
			foreach ($D as $file) {
				if($file->isDot()) continue;
				if($file->isDir()) continue;
				
				unlink($file->getPathname());
			}
			rmdir($dir);
		}
	}
}
?>