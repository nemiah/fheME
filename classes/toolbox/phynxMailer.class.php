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
class phynxMailer {
	private $exceptions = false;
	private $to = array();
	private $cc = array();
	private $bcc = array();
	private $from = array();
	private $subject = "";
	private $body = "";
	private $attachments = array();
	private $stringAttachments = [];
	private $embedded = [];
	private $skipOwn = false;
	private $smtpData = [];
	
	function __construct($to, $subject, $body) {
		$this->to($to);
		$this->subject($subject);
		$this->body($body);
	}
	
	function SMTPData($host, $user, $password){
		$this->smtpData = ["host" => $host, "user" => $user, "pass" => $password];
	}
	
	function bcc($bcc){
		$this->bcc[] = $bcc;
	}
	
	function cc($cc){
		$this->cc[] = $cc;
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
	
	function attach($filename, $attachmentName = ""){
		$this->attachments[] = [$filename, $attachmentName];
	}
	
	function attachString($data, $attachmentName = ""){
		$this->stringAttachments[] = [$data, $attachmentName];
	}
	
	function embed($filename, $cid){
		$this->embedded[] = [$filename, $cid];
	}
	
	function skipOwnServer($bool){
		$this->skipOwn = $bool;
	}
	
	function send(){
		if(!count($this->from) AND Session::currentUser())
			$this->from(Session::currentUser()->A("UserEmail"), Session::currentUser()->A("name"));
		
		if(!count($this->from) AND !Session::currentUser())
			$this->from("support@open3A.de", "open3A");
		
		$from = $this->from;
		
		$mimeMail2 = new PHPMailer($this->exceptions, substr($from[0], stripos($from[0], "@") + 1), $this->skipOwn);

		if(count($this->smtpData)){
			$mimeMail2->IsSMTP();

			$mimeMail2->Host = $this->smtpData["host"];
			$mimeMail2->SMTPAuth = $this->smtpData["user"] != "";
			$mimeMail2->Username = $this->smtpData["user"];
			$mimeMail2->Password = $this->smtpData["pass"];
		}
		
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
		
		foreach($this->bcc AS $bcc)
			$mimeMail2->addBCC($bcc);
		
		foreach($this->cc AS $cc)
			$mimeMail2->addCC($cc);
		
		foreach($this->attachments AS $attachment)
			$mimeMail2->AddAttachment($attachment[0], $attachment[1]);
		
		foreach($this->stringAttachments AS $attachment)
			$mimeMail2->addStringAttachment($attachment[0], $attachment[1]);
		
		foreach($this->embedded AS $embedded)
			$mimeMail2->addEmbeddedImage($embedded[0], $embedded[1]);
		
		#$mimeMail2->SMTPDebug = 2;
		
		if(!$mimeMail2->Send())
			throw new Exception("E-Mail: Failed to send e-mail! ".$mimeMail2->ErrorInfo);
		
		return true;
	}
}
?>