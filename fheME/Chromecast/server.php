<?php
require_once(__DIR__."/CastV2inPHP/Chromecast.php");
$ip = msg_get_queue(12340);
echo "Initialized!\n";

function message($status, $data = null){
	$ip = msg_get_queue(12340);
	
	$response = new stdClass();
	$response->status = $status;
	$response->data = $data;
	
	msg_send($ip, 8, $response, true, false, $err);
}

$cc = null;
while(true){
	echo "Waiting…\n";
	
	msg_receive($ip, 0, $msgtype, 5000, $message, true, null, $err);
	#echo "msgtype {$msgtype} data {$message->action}\n";
	var_dump($message);
	
	if(!$message->action)
		continue;
	
	if($message->action == "")
		message("error");
		
	
	if($message->action == "play"){
		echo "Playing…\n";
		$cc = new Chromecast($message->server, $message->port);
		$cc->DMP->play($message->url, "BUFFERED", "video/mp4", true, 0);
		
		message("ok");
	}
	
	if($message->action == "pause"){
		echo "Pausing…\n";
		
		if(!$cc){
			message("error");
			echo "No active stream!\n";
			continue;
		}
		
		$cc->DMP->pause();
		
		message("ok");
	}
	
	if($message->action == "status"){
		echo "Status…\n";
		
		if(!$cc){
			message("error");
			echo "No active stream!\n";
			continue;
		}
		
		$status = $cc->DMP->getStatus();
		#print_r($status);
		
		#$s = new stdClass();
		$s = $status->status[0];
		
		message("ok", $s);
	}
	
	if($message->action == "restart"){
		echo "Restarting…\n";
		
		if(!$cc){
			message("error");
			echo "No active stream!\n";
			continue;
		}
		
		$cc->DMP->restart();
		message("ok");
	}
	
	if($message->action == "stop"){
		echo "Stopping…\n";
		
		if(!$cc){
			message("error");
			echo "No active stream!\n";
			continue;
		}
		
		$cc->DMP->Stop();
		$cc = null;
		
		message("ok");
	}
}
?> 