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
class SysMessages {
	private $messages = array();
	private $categories = array();
	#private $levels = array();
	
	private $rtecho = false;
	private $logging = false;
	private $messageLimit = 30;
	private $starter = 0;
	private $lastMessage = 0;
	public static $variable = "messages";
	
	/**
	 * @return SysMessages 
	 */
	public static function i(){
		return $_SESSION[self::$variable];
	}
	
	public static function init(){
		$_SESSION[self::$variable] = new SysMessages();
	}
	
	public function __construct(){	
		$this->addMessage("--- System Messages initialized ---");
		if(!$this->logging) $this->messages[] = "--- Logging has been disabled ---";
	}

	public static function log($m, $category = "all", $level = 10){
		if(!isset($_SESSION[self::$variable]))
			$_SESSION[self::$variable] = new SysMessages ();
		
		$_SESSION[self::$variable]->addMessage($m, $category, $level);
	}

	public function startLogging(){
		$this->logging = true;
		$this->addMessage("--- Logging enabled ---");
	}
	
	public function stopLogging(){
		$this->logging = false;
		$this->addMessage("--- Logging disabled ---");
	}

	public static function countCategoryS($category){
		return $_SESSION[self::$variable]->countCategory($category);
	}

	public function countCategory($category){
		$c = 0;
		foreach($this->categories AS $k => $v){
			if($v == $category)
				$c++;
		}

		return $c;
	}

	public function clearMessages(){
		$this->lastMessage = 0;
		$this->starter = 0;
		$this->messages = array();
		#$this->levels = array();
		$this->categories = array();
		
		$this->addMessage("--- System Messages cleared ---");
	}
	
	public function addMessage($m, $category = "all", $level = 10){
		if($this->logging) {
			$this->messages[$this->lastMessage] = date("d.m.Y H:i:s")."# ".$m;
			$this->categories[$this->lastMessage++] = $category;
			#$this->levels[$this->lastMessage++] = $level;
			
			if(count($this->messages) > $this->messageLimit) {
				#unset($this->levels[$this->lastMessage - $this->messageLimit - 1]);
				unset($this->categories[$this->lastMessage - $this->messageLimit - 1]);
				unset($this->messages[$this->lastMessage - $this->messageLimit - 1]);
				$this->starter++;
			}
		}
		if($this->rtecho) echo $this->messages[$this->lastMessage-1]."<br />";
	}
	
	public function getMessages(){
		return $this->messages;
	}
	
	public function startMessage($m, $category = "all", $level = 10){
		$this->addMessage($m, $category, $level);
		#if($this->logging) $this->messages[] = date("d.m.Y H:i:s")."# ".$m;
		#if($this->rtecho) echo $this->messages[count($this->messages)-1]."<br />";
	}
	
	public function endMessage($m){
		if($this->logging) $this->messages[$this->lastMessage-1] .= $m;
		if($this->rtecho) echo $this->messages[$this->lastMessage-1]."<br />";
	}
	
	public function echoMessages(){
		for($i=$this->starter;$i<$this->lastMessage;$i++)
			echo (strstr($this->messages[$i],"Exception") ? "<span style=\"color:white;background-color:red;\">" : "").$this->messages[$i].(strstr($this->messages[$i],"Exception") ? "</span>" : "")."<br />";
	}

	public function echoMessagesReverse(){
		
		if(isset($_SESSION["BPS"]) AND $_SESSION["BPS"]->isPropertySet("SysMessages","displayCategory"))
			$bps = $_SESSION["BPS"]->getProperty("SysMessages","displayCategory");

		$category = (isset($bps) ? $bps : "all");
		
		
		echo "Displaying category: ".$category."<br />";
		
		#echo "<span style=\"font-weight:bold\">";
		#
		/*
		for($i=$this->lastMessage-1;$i>=$this->starter;$i--){
			#if($this->categories[$i] != $category AND $category != "all")
			#	continue;
				
			#if($this->levels[$i] > $maxLevel)
			#	continue;
			
			if($i == $this->lastMessage-1)
				$s = explode("#",$this->messages[$i]);

			$e = explode("#",$this->messages[$i]);
			if($e[0] != $s[0]) echo "</span>";
			echo (($j<10 ? "0" : "").$j++)." ".(strstr($this->messages[$i],"Exception") ? "<span style=\"color:white;background-color:red;\">" : "").$this->messages[$i].(strstr($this->messages[$i],"Exception") ? "</span>" : "")."<br />";
			
		}*/
		$j = 1;
		$messages = $this->messages;
		#$levels = $this->levels;
		$cats = $this->categories;
		
		krsort($messages);
		#krsort($levels);
		krsort($cats);
		
		echo "<span style=\"font-weight:bold\">";
		$flipped = false;
		
		foreach($messages as $key => $value){
			$e = explode("#",$messages[$key]);
			
			if(!isset($oldTime)) $oldTime = $e[0];
			
			if($cats[$key] != $category AND $category != "all")
				continue;
				
			#if($levels[$key] > $maxLevel)
			#	continue;

			if($e[0] != $oldTime AND !$flipped) {
				echo "</span>";
				$flipped = true;
			}
			
			echo (($j<10 ? "0" : "").$j++)." ".(strstr($messages[$key],"Exception") ? "<span style=\"color:white;background-color:red;\">" : "").$messages[$key].(strstr($messages[$key],"Exception") ? "</span>" : "")."<br />";

			$oldTime = $e[0];
			echo "\n";
		}
	}

}
?>
