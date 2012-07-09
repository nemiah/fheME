<?php
/**
 * Telnet class
 * 
 * Used to execute remote commands via telnet connection 
 * Usess sockets functions and fgetc() to process result
 * 
 * All methods throw Exceptions on error
 * 
 * Written by Dalibor Andzakovic <dali@swerve.co.nz>
 * Based on the code originally written by Marc Ennaji and extended by 
 * Matthias Blaser <mb@adfinis.ch>
 */

class Telnet {
	private $host;
	private $port;
	private $timeout;
	
	private $errno;
	private $errstr;
	
	private $socket;
	private $buffer;
	private $prompt;
	
	
	private $NULL;
	private $DC1;
	private $WILL;
	private $WONT;
	private $DO;
	private $DONT;
	private $IAC;
	
	public function __construct($host = '127.0.0.1', $port = '23', $timeout = 10){
		$this->host = $host;
		$this->port = $port;
		$this->timeout = $timeout;
		
    	$this->NULL = chr(0);
    	$this->DC1 = chr(17);
    	$this->WILL = chr(251);
	    $this->WONT = chr(252);
	    $this->DO = chr(253);
	    $this->DONT = chr(254);
	    $this->IAC = chr(255);

		$this->connect();
	}
	
	public function setPrompt($s = '$'){
        $this->prompt = $s;
        return true;
    }
	
	public function __destruct() {
		$this->disconnect();
		$this->buffer = NULL;
	}
	
	
	public function connect(){
		if (!preg_match('/([0-9]{1,3}\\.){3,3}[0-9]{1,3}/', $this->host)) {
			$ip = gethostbyname($this->host);

			if($this->host == $ip)
				throw new Exception("Cannot resolve $this->host");
			else
				$this->host = $ip; 
		}
		
		$this->socket = fsockopen($this->host, $this->port, $this->errno, $this->errstr, $this->timeout);
		
		if (!$this->socket)
			throw new NoServerConnectionException;#Exception("Cannot connect to $this->host on port $this->port");
	}
	
	public function disconnect(){
		if ($this->socket){
			if (!fclose($this->socket))
				throw new Exception("Error while closing telnet socket");                

			$this->socket = null;
		}
    }
    
	public function fireAndForget($command) {
		$this->write($command);
		#$this->waitPrompt();
		#return $this->getBuffer();
	}
	
	public function fireAndGet($command) {
		$this->write($command);
		$this->waitPrompt();
		return $this->getBuffer();
	}
	
	private function write($buffer, $addNewLine = true){
		if (!$this->socket)
			throw new Exception("Telnet connection closed");            
		
		$this->clearBuffer();
		
		if ($addNewLine == true)
			$buffer .= "\n";
		
		
		if(!fwrite($this->socket, $buffer) < 0)
			throw new Exception("Error writing to socket");            
	}
	
	private function getBuffer(){
		$buf = explode("\n", $this->buffer);
		unset($buf[count($buf)-1]);
		
		$buf = implode("\n",$buf);
		return trim($buf);
    }
    
    private function readTo($prompt){

        if (!$this->socket)
            throw new Exception("Telnet connection closed");            

		$this->clearBuffer();

		do {
			$c = $this->getc();
			if ($c === false)
				throw new Exception("Couldn't find the requested : '" . $prompt . "', it was not in the data returned from server : '" . $buf . "'");                

            if ($c == $this->IAC AND $this->negotiateTelnetOptions())
				continue;
       		
			$this->buffer .= $c;
            
			if ((substr($this->buffer, strlen($this->buffer) - strlen($prompt))) == $prompt)
				return;                

		} while($c != $this->NULL || $c != $this->DC1);
    }
    
	private function negotiateTelnetOptions(){
		$c = $this->getc();

		if ($c != $this->IAC){
			
			if (($c == $this->DO) || ($c == $this->DONT)){
				$opt = $this->getc();
				fwrite($this->socket, $this->IAC . $this->WONT . $opt);
	        } else if (($c == $this->WILL) || ($c == $this->WONT)) {
				$opt = $this->getc();            
				fwrite($this->socket, $this->IAC . $this->DONT . $opt);
			} else
				throw new Exception('Error: unknown control character ' . ord($c ));   
				         
		} else
        	throw new Exception('Error: Something Wicked Happened');        	

		return true;
	}
    
    private function getc() {
    	return fgetc($this->socket); 
    }
    
	private function waitPrompt(){
		return $this->readTo($this->prompt);
	}
	
    private function clearBuffer() {
		$this->buffer = '';
	}
	
}
?>