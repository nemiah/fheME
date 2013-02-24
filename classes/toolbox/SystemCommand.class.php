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
class SystemCommand {
	private $command = "";
	private $attributes = array();
	private $values = array();
	
	private $attributeSeparator = " ";
	private $attributeInitiator = "--";
	private $attributeOperator = " ";
	
	private $output = "";
	private $pipe = null;
	
	private $username = "";
	private $password = "";
	
	public function runAsUser($username, $password){
		$this->username = $username;
		$this->password = $password; 
	}
	
	public function setCommand($command){
		$this->command = $command;
	}
	
	public function addValue($value){
		$this->values[] = $value;
	}
	
	public function setPipe(SystemCommand $sc){
		$this->pipe = $sc;
	}
	
	public function setAttributeInitiator($i){
		$this->attributeInitiator = $i;
	}
	
	public function addAttribute($attribute,$value = ""){
		$this->attributes[$attribute] = $value;
	}
	
	public function getPipeCommand(){
		return $this->execute(false);
	}
	
	public function execute($do = true){
		$cmd = $this->command;
		
		for($i=0;$i<count($this->values);$i++)
			$cmd .= $this->attributeSeparator.$this->values[$i];
		
		foreach($this->attributes as $key => $value)
			$cmd .= $this->attributeSeparator.$this->attributeInitiator.$key.($value != "" ? $this->attributeOperator.$value : "");

		$cmd = $cmd.($this->pipe != null ? " | ".$this->pipe->getPipeCommand() : "");
				
		if($do) {
			if($this->username == "") $this->output = shell_exec($cmd);
			else {
				$c = '/bin/su '.$this->username.' -c "'.$cmd.'" 2>&1';
				$handle = popen($c,"r");
				$read = fread($handle, 2096);
				pclose($handle);
				
				if(trim($read) == "su: must be run from a terminal"){
					die("su has been disabled for the user of your webserver. Maybe you want to try sudo...");
				} else {
					
					$_SESSION["messages"]->addMessage("System: $c","System");
					$fp = popen($c,"w");
					fputs($fp,$this->password);
					pclose($fp);
					
				}
				$this->output = "No output available in su-mode";
			}
		}
		else return $cmd;
	}
	public function getOutput(){
		return $this->output;
	}
}
?>
