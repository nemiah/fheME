<?php

/**
 * This file is part of the htmlMimeMail5 package (http://www.phpguru.org/)
 *
 * htmlMimeMail5 is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * htmlMimeMail5 is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with htmlMimeMail5; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 * 
 * � Copyright 2005 Richard Heyes
 */
define('SMTP_STATUS_NOT_CONNECTED', 1);
define('SMTP_STATUS_CONNECTED', 2);

class hmmsmtp {

	private $authenticated;
	private $connection;
	private $recipients;
	private $headers;
	private $timeout;
	private $errors;
	private $status;
	private $body;
	private $from;
	private $host;
	private $port;
	private $helo;
	private $auth;
	private $user;
	private $pass;
	private $dsn;
	private $useStarttls = false;
	private $useDSN = false;
	private $log = "";
	private $logSkipNext = false;
	
	/**
	 * Constructor function. Arguments:
	 * $params - An assoc array of parameters:
	 *
	 *   host    - The hostname of the smtp server		Default: localhost
	 *   port    - The port the smtp server runs on		Default: 25
	 *   helo    - What to send as the HELO command		Default: localhost
	 *             (typically the hostname of the
	 *             machine this script runs on)
	 *   auth    - Whether to use basic authentication	Default: FALSE
	 *   user    - Username for authentication			Default: <blank>
	 *   pass    - Password for authentication			Default: <blank>
	 *   timeout - The timeout in seconds for the call	Default: 5
	 *             to fsockopen()
	 */
	public function __construct($params = array()) {

		if (!defined('CRLF'))
			define('CRLF', "\r\n");

		$this->authenticated = FALSE;
		$this->timeout = 5;
		$this->status = SMTP_STATUS_NOT_CONNECTED;
		$this->host = 'localhost';
		$this->port = 25;
		$this->helo = 'localhost';
		$this->auth = FALSE;
		$this->user = '';
		$this->pass = '';
		$this->errors = array();

		foreach ($params as $key => $value) {
			$this->$key = $value;
		}
	}

	/**
	 * Connect function. This will, when called
	 * statically, create a new smtp object, 
	 * call the connect function (ie this function)
	 * and return it. When not called statically,
	 * it will connect to the server and send
	 * the HELO command.
	 */
	public function connect($params = array()) {
		foreach ($params as $key => $value) {
			$this->$key = $value;
		}
		/* if (!isset($this->status)) {
		  $obj = new smtp($params);
		  if($obj->connect()){
		  $obj->status = SMTP_STATUS_CONNECTED;
		  }

		  return $obj;

		  } else { */
		
		$socket_context = stream_context_create(array(
			'ssl' => array(
				'verify_peer' => false,
				'verify_peer_name' => false,
				'allow_self_signed' => true
			)));
		#$this->connection = fsockopen($this->host, $this->port, $errno, $errstr, $this->timeout);
		$this->connection = stream_socket_client(
                $this->host.":".$this->port,
                $errno,
                $errstr,
                $this->timeout,
                STREAM_CLIENT_CONNECT,
                $socket_context
            );
		
		if (function_exists('socket_set_timeout')) 
			@socket_set_timeout($this->connection, 5, 0);
		

		$greeting = $this->get_data();
		if (is_resource($this->connection)) {
			$this->status = SMTP_STATUS_CONNECTED;
			return $this->auth ? $this->ehlo() : $this->helo();
		} else {
			$this->errors[] = 'Failed to connect to server: ' . $errstr;
			return FALSE;
		}
		#}
	}

	public function dsn($Success, $Delay, $Failure){
		$this->dsn = array($Success, $Delay, $Failure);
	}
	
	/**
	 * Function which handles sending the mail.
	 * Arguments:
	 * $params	- Optional assoc array of parameters.
	 *            Can contain:
	 *              recipients - Indexed array of recipients
	 *              from       - The from address. (used in MAIL FROM:),
	 *                           this will be the return path
	 *              headers    - Indexed array of headers, one header per array entry
	 *              body       - The body of the email
	 *            It can also contain any of the parameters from the connect()
	 *            function
	 */
	public function send($params = array()) {
		foreach ($params as $key => $value) {
			$this->set($key, $value);
		}

		if ($this->is_connected()) {

			// Do we auth or not? Note the distinction between the auth variable and auth() function
			if ($this->auth AND !$this->authenticated) {
				if (!$this->auth())
					return false;
			}

			$this->mail($this->from);

			if (is_array($this->recipients)) {
				foreach ($this->recipients as $value) {
					$this->rcpt($value);
				}
			} else {
				$this->rcpt($this->recipients);
			}

			if (!$this->data()) {
				return false;
			}

			// Transparency
			$headers = str_replace(CRLF . '.', CRLF . '..', trim(implode(CRLF, $this->headers)));
			$body = str_replace(CRLF . '.', CRLF . '..', $this->body);
			$body = substr($body, 0, 1) == '.' ? '.' . $body : $body;

			$body = preg_replace("#(?<!\r)\n#si", "\r\n", $body);
			$headers = preg_replace('#(?<!\r)\n#si', "\r\n", $headers); 
			
			$this->send_data($headers);
			$this->send_data('');
			$this->send_data($body, false);
			$this->send_data('.');

			$result = (substr(trim($this->get_data()), 0, 3) === '250');
			//$this->rset();
			return $result;
		} else {
			$this->errors[] = 'Not connected!';
			return FALSE;
		}
	}

	/**
	 * Function to implement HELO cmd
	 */
	private function helo() {
		if (is_resource($this->connection)
				AND $this->send_data('HELO ' . $this->helo)
				AND substr(trim($error = $this->get_data()), 0, 3) === '250') {

			return true;
		} else {
			$this->errors[] = 'HELO command failed, output: ' . trim(substr(trim($error), 3));
			return false;
		}
	}

	/**
	 * Function to implement EHLO cmd
	 */
	private function ehlo() {
		if (is_resource($this->connection)
				AND $this->send_data('EHLO ' . $this->helo)
				AND substr(trim($error = $this->get_data()), 0, 3) === '250') {
			
			if(strpos($error, "STARTTLS") > 0)
				$this->useStarttls = true;
			
			if(strpos($error, "250 DSN") > 0 OR strpos($error, "250-DSN") > 0)
				$this->useDSN = true;
			
			return true;
		} else {
			$this->errors[] = 'EHLO command failed, output: ' . trim(substr(trim($error), 3));
			return false;
		}
	}

	/**
	 * Function to implement RSET cmd
	 */
	private function rset() {
		if (is_resource($this->connection)
				AND $this->send_data('RSET')
				AND substr(trim($error = $this->get_data()), 0, 3) === '250') {

			return true;
		} else {
			$this->errors[] = 'RSET command failed, output: ' . trim(substr(trim($error), 3));
			return false;
		}
	}

	/**
	 * Function to implement QUIT cmd
	 */
	private function quit() {
		if (is_resource($this->connection)
				AND $this->send_data('QUIT')
				AND substr(trim($error = $this->get_data()), 0, 3) === '221') {

			fclose($this->connection);
			$this->status = SMTP_STATUS_NOT_CONNECTED;
			return true;
		} else {
			$this->errors[] = 'QUIT command failed, output: ' . trim(substr(trim($error), 3));
			return false;
		}
	}

	/**
	 * Function to implement AUTH cmd
	 */
	private function auth() {
		if($this->useStarttls)
			$this->starttls();
		
		if (is_resource($this->connection)
				AND $this->send_data('AUTH LOGIN')
				AND substr(trim($error = $this->get_data()), 0, 3) === '334'
				AND $this->send_data(base64_encode($this->user), false)   // Send username
				AND substr(trim($error = $this->get_data()), 0, 3) === '334'
				AND $this->send_data(base64_encode($this->pass), false)   // Send password
				AND substr(trim($error = $this->get_data()), 0, 3) === '235') {

			$this->authenticated = true;
			return true;
		} else {
			$this->errors[] = 'AUTH command failed: ' . trim(substr(trim($error), 3));
			return false;
		}
	}

	private function starttls(){
		$this->send_data('STARTTLS');
		
		$rply = $this->get_data();
		$code = substr($rply,0,3);
		
		if($code != 220) {
			$this->errors[] = 'STARTTLS not accepted from server: ' . trim(substr(trim($rply), 3));
			return false;
		}
		
		#stream_set_blocking($this->connection, true);
		if(!stream_socket_enable_crypto($this->connection, true, STREAM_CRYPTO_METHOD_TLS_CLIENT)) {
			$error = error_get_last();
			$this->errors[] = 'STARTTLS failed: ' . $error["message"];
			#print_r(stream_get_transports());
			return false;
		}
		#stream_set_blocking($this->connection, false);
		
		$this->ehlo();

		return true;
	}
	
	/**
	 * Function that handles the MAIL FROM: cmd
	 */
	private function mail($from) {
		if ($this->is_connected()
				AND $this->send_data('MAIL FROM:<' . $from . '>')
				AND substr(trim($this->get_data()), 0, 2) === '250') {

			return true;
		} else {
			return false;
		}
	}

	/**
	 * Function that handles the RCPT TO: cmd
	 */
	private function rcpt($to) {
		$DSN = "";
		if($this->dsn !== null AND $this->useDSN){
			if($this->dsn[0])
				$DSN .= "SUCCESS";
			
			if($this->dsn[1])
				$DSN .= ($DSN != "" ? "," : "")."DELAY";
			
			if($this->dsn[2])
				$DSN .= ($DSN != "" ? "," : "")."FAILURE";
			
			$DSN = " NOTIFY=$DSN ORCPT=rfc822;$to";
		}
		
		if ($this->is_connected()
				AND $this->send_data('RCPT TO:<' . $to . '>'.$DSN)
				AND substr(trim($error = $this->get_data()), 0, 2) === '25') {

			return true;
		} else {
			$this->errors[] = trim(substr(trim($error), 3));
			return false;
		}
	}

	/**
	 * Function that sends the DATA cmd
	 */
	private function data() {
		if ($this->is_connected()
				AND $this->send_data('DATA')
				AND substr(trim($error = $this->get_data()), 0, 3) === '354') {

			return true;
		} else {
			$this->errors[] = trim(substr(trim($error), 3));
			return false;
		}
	}

	/**
	 * Function to determine if this object
	 * is connected to the server or not.
	 */
	private function is_connected() {
		return (is_resource($this->connection) AND ($this->status === SMTP_STATUS_CONNECTED));
	}

	/**
	 * Function to send a bit of data
	 */
	private function send_data($data, $log = true) {
		if (is_resource($this->connection)) {
			if($log)
				$this->log .= trim($data)."\n";
			else
				$this->log .= "...\n";
			
			return fwrite($this->connection, $data . CRLF, strlen($data) + 2);
		} else {
			return false;
		}
	}

	/**
	 * Function to get data.
	 */
	private function get_data() {
		$return = '';
		$line = '';
		$loops = 0;

		if (is_resource($this->connection)) {
			while ((strpos($return, CRLF) === FALSE OR substr($line, 3, 1) !== ' ') AND $loops < 100) {
				$line = fgets($this->connection, 512);
				$return .= $line;
				$loops++;
			}
			$this->log .= trim($return)."\n";
			return $return;
		}else
			return false;
	}

	/**
	 * Sets a variable
	 */
	public function set($var, $value) {
		$this->$var = $value;
		return true;
	}

	/**
	 * Function to return the errors array
	 */
	public function getErrors() {
		return $this->errors;
	}
	
	public function getLog(){
		return $this->log;
	}

}

// End of class
?>