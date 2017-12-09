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
 *  2007 - 2017, Furtmeier Hard- und Software - Support@Furtmeier.IT
 */
class phynxMailer {
	private $exceptions = false;
	private $to = array();
	#private $cc = array();
	#private $bcc = array();
	private $from = array();
	private $subject = "";
	private $body = "";
	private $attachments = array();
	
	function __construct($to, $subject, $body) {
		$this->to($to);
		$this->subject($subject);
		$this->body($body);
	}
	
	function body($body){
		$this->body = $body;
	}
	
	function subject($subject){
		$this->subject = $subject;
	}
	
	function to($email, $name = ""){
		$this->to[] = array($email, $name);
	}
	
	function from($email, $name, $sender = null){
		$this->from = array($email, $name, $sender);
	}
	
	function attach($filename){
		$this->attachments[] = $filename;
	}
	
	function send(){
		if(!count($this->from) AND Session::currentUser())
			$this->from(Session::currentUser()->A("UserEmail"), Session::currentUser()->A("name"));
		
		$from = $this->from;
		
		$mimeMail2 = new PHPMailer($this->exceptions, substr($from[0], stripos($from[0], "@") + 1));

		$mimeMail2->CharSet = "UTF-8";
		$mimeMail2->Subject = $this->subject;
		
		$mimeMail2->From = $from[0];
		$mimeMail2->Sender = $from[2];
		$mimeMail2->FromName = $from[1];
		
		$mimeMail2->IsHTML();
		
		$mimeMail2->Body = $this->body;
		foreach($this->to AS $to)
			$mimeMail2->AddAddress($to[0], $to[1]);
		
		$mimeMail2->SMTPOptions = array(
			'ssl' => array(
				'verify_peer' => false,
				'verify_peer_name' => false,
				'allow_self_signed' => true
			)
		);
		
		foreach($this->attachments AS $attachment)
			$mimeMail2->AddAttachment($attachment);
		
		#$mimeMail2->SMTPDebug = 2;
		
		if(!$mimeMail2->Send())
			throw new Exception("E-Mail: Failed to send e-mail! ".$mimeMail2->ErrorInfo);
	}
}
?>