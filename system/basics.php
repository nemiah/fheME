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

function addClassPath($path){
	if(!isset($_SESSION["phynx_addClassPaths"]))
		$_SESSION["phynx_addClassPaths"] = array();
	
	if($path{strlen($path) - 1} != "/")
		$path .= "/";
	
	if(!in_array($path, $_SESSION["phynx_addClassPaths"]))
		$_SESSION["phynx_addClassPaths"][] = $path;
}

function registerClassPath($className, $classPath){
	$_SESSION["classPaths"][$className] = $classPath;
}

function phynxParseStr($query){ //fools mod_security
	$return = array();

	$ex = explode("&", $query);

	foreach($ex AS $parameter){
		$ex2 = explode("=", $parameter);
		$name = $ex2[0];
		unset($ex2[0]);

		$return[$name] = implode("=", $ex2);
	}

	return $return;
}

function findClass($class_name){
	if(strpos($class_name, 'PHPExcel') === 0)
		return false;
	
	$root = str_replace("system".DIRECTORY_SEPARATOR."basics.php", "", __FILE__);

	if(isset($_SESSION["classPaths"][$class_name])) {
		$path = $_SESSION["classPaths"][$class_name];

		if(file_exists($path))
			require_once($path);
		else
			throw new ClassNotFoundException($class_name);
		return 1;
	}

	$standardPaths = array();
	$standardPaths[] = $root."classes/backend/";
	$standardPaths[] = $root."classes/frontend/";
	$standardPaths[] = $root."classes/toolbox/";
	$standardPaths[] = $root."classes/interfaces/";
	$standardPaths[] = $root."libraries/";
	$standardPaths[] = $root."libraries/fpdf/";
	$standardPaths[] = $root."libraries/iban/";
	$standardPaths[] = $root."specifics/";
	$standardPaths[] = $root."classes/exceptions/";
	$standardPaths[] = $root."libraries/geshi/";
	if(isset($_SESSION["phynx_addClassPaths"]))
		$standardPaths = array_merge($standardPaths, $_SESSION["phynx_addClassPaths"]);

	foreach($standardPaths as $k => $v){
		$path = "$v".$class_name.'.class.php';

		if(is_file($path)) {
			require_once $path;
			registerClassPath($class_name, $path);
			return 1;
		}
	}
	if(isset($_SESSION["CurrentAppPlugins"]) AND count($_SESSION["CurrentAppPlugins"]->getFolders()) > 0) {

		foreach($_SESSION["CurrentAppPlugins"]->getFolders() as $key => $value){
			$path = $root."plugins/$value/$class_name.class.php";

			if(is_file($path)){
				require_once $path;
				registerClassPath($class_name, $path);
				return 1;
			}

			if($_SESSION["applications"]->getActiveApplication() != "nil"){
				$path = $root."".$_SESSION["applications"]->getActiveApplication()."/$value/$class_name.class.php";

				if(is_file($path)){
					require_once $path;
					registerClassPath($class_name, $path);
					return 1;
				}
			}
		}
	} else {
		$fp = opendir($root."plugins/");
		while(($file = readdir($fp)) !== false) {
			if($file == "." OR $file == "..")
				continue;
			
			if(is_dir($root."plugins/$file")) {

				$fp2 = opendir($root."plugins/$file/");
				while(($file2 = readdir($fp2)) !== false) {
					if(!is_dir($root."plugins/$file/$file2"))
						continue;
					
					$path = $root."plugins/$file/$file2/$class_name.class.php";
					if(is_file($path)){
						require_once $path;
						registerClassPath($class_name, $path);
						return 1;
					}
				}

			}
		}
	}

	if($class_name == "FPDF"){
		require_once $root."libraries/fpdf/fpdf.php";
		return 1;
	}

	if($class_name == "htmlMimeMail5"){
		require_once $root."libraries/mailer/htmlMimeMail5.php";
		return 1;
	}

	if($class_name == "PHPMailer"){
		require_once $root."libraries/mailer/PHPMailer.class.php";
		return 1;
	}

	if(preg_match("/^i[A-Z].*/", $class_name)) {
		$_SESSION["messages"]->addMessage("Warning: Creating interface $class_name");
		eval('interface '.$class_name.' { } ');
	} else
	 eval('class '.$class_name.' { '.
		'    public function __construct() { '.
		'        throw new ClassNotFoundException("'.$class_name.'"); '.
		'    } '.
		'} ');

}

function phynx_mb_str_pad($input, $pad_length, $pad_string = " ", $pad_style = STR_PAD_RIGHT, $encoding="UTF-8") {
   return str_pad($input, strlen($input)-mb_strlen($input, $encoding)+$pad_length, $pad_string, $pad_style);
} 

function emoFatalError($excuse, $message, $title, $showErrors = true, $mode = "warning") {
	$errors = "";

	if(isset($_SESSION["phynx_errors"]) AND count($_SESSION["phynx_errors"]) > 0){
		$errors .= "<h2>Es sind PHP-Fehler aufgetreten:</h2><ol class=\"errors\">";
		foreach($_SESSION["phynx_errors"] AS $error)
			$errors .= "<li>".$error[0].": ".$error[1]."</li>";
		
		$errors .= "</ol>";
	}
	
	$image = "";
	if($mode == "warning")
		$image = '<img src="data:image/png;base64,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" style="float:left;margin-right:15px;" />';
	
	if($mode == "ok")
		$image = '<img src="data:image/png;base64,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" style="float:left;margin-right:15px;" />';
	
	
	
	if(!$showErrors)
		$errors = "";

		die('<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
		<title>'.$title.'</title>
		<style type="text/css">
			* {
				padding:0px;
				margin:0px;
			}

			body {
				font-size:14px;
				font-family:sans-serif;
				background-color:#d8d8d8;
				color:black;
			}

			p {
				padding:5px;
				line-height:1.5;
			}

			div {
				padding:10px;
			}

			li {
				margin-left:20px;
				margin-top:10px;
				line-height:1.5;
			}

			.errors li {
				font-size:0.8em;
			}

			ol {
				margin-left:10px;
			}

			h1 {
				margin-bottom:5px;
			}

			h2 {
				margin-bottom:5px;
				margin-top:20px;
				clear:both;
			}

			pre {
				font-size:10px;
			}

			.backgroundColor0 {
				background-color:white;
			}
		</style>

	</head>
	<body>
		<div class="backgroundColor0">
			
			'.$image.'

			<h1>'.$excuse.'</h1>
			<p style="margin-left:80px;">'.$message.'</p>
			'.$errors.'
		</div>
	</body>
</html>');
}
/*
if(isset($_GET["getEmoWarningSymbol"])){
	header('Content-Type: image/png');
	echo stripslashes(base64_decode("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"));
}*/

?>