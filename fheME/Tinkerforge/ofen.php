<?php
require_once(__DIR__.'/lib/IPConnection.php');
require_once(__DIR__.'/lib/BrickletTemperatureIR.php');
require_once(__DIR__.'/lib/BrickletAnalogOut.php');
require_once(__DIR__.'/lib/BrickletIO4.php');

declare(ticks = 1);
pcntl_signal(SIGINT, "signal_handler");
pcntl_signal(SIGTERM, 'signal_handler');

function signal_handler($signal) {
	global $BLight, $CTemp, $CLight, $BIO4;
	
	switch($signal) {
		case SIGINT:
		case SIGTERM:
			print "Exiting...\n";
			
			if(isset($BLight))
				$BLight->setVoltage(0);
			
			if(isset($CTemp))
				$CTemp->destroy();
			
			if(isset($BIO4))
				$BIO4->setConfiguration(1 << 3, 'o', false);
			
			$CLight->destroy();
			
			exit();
	}
}

function cb_temp($temperature) {
	global $BLight;

	$shmid = shmop_open(1, "a", 0644, "1");
	$onOff = shmop_read($shmid, 0, 1);
	shmop_close($shmid);
	
	echo floor($temperature / 10.0)." °C\n";
	if(floor($temperature) / 10 > 80)
		$volt = 0;
	else
		$volt = 5000;
	
	
	$shmid = shmop_open(0xff3, "w", 0644, 4);
	shmop_write($shmid, $volt, 0);
	shmop_close($shmid);
	
	if($onOff == "0")
		$volt = 0;
	
	$BLight->setVoltage($volt);
}

function cb_trigger($interruptMask, $valueMask) {
	if(decbin($interruptMask) != 1)
		return;
	
	global $BIO4, $BLight;
	
	$trigger = substr(decbin($valueMask), -1, 1);
	
	$BIO4->setConfiguration(1 << 3, 'o', $trigger == "1");
	
	$shmid = shmop_open(0xff3, "w", 0644, 4);
	$lastVolt = shmop_read($shmid, 0, 4);
	shmop_close($shmid);
		
	$BLight->setVoltage(($trigger == "1" AND $lastVolt !== false) ? $lastVolt : 0);
	
	$shmid = shmop_open(1, "w", 0644, 1);
	shmop_write($shmid, substr(decbin($valueMask), -1, 1), 0);
	shmop_close($shmid);
}

use Tinkerforge\IPConnection;
use Tinkerforge\BrickletTemperatureIR;
use Tinkerforge\BrickletAnalogOut;
use Tinkerforge\BrickletIO4;

$pid = pcntl_fork();

$shmid = shmop_open(0xff3, "c", 0644, 4);
shmop_write($shmid, "0000", 0);
shmop_close($shmid);
		
switch($pid) {
	case -1:
		print "Could not fork!\n";
		exit;
	
	case 0:
		$CLight = new IPConnection("192.168.7.77", 4223);
		
		$BIO4 = new BrickletIO4("5PJ");
		$CLight->addDevice($BIO4);
		
		$BLight = new BrickletAnalogOut("bvo");
		$CLight->addDevice($BLight);
		
		$BIO4->registerCallback(BrickletIO4::CALLBACK_INTERRUPT, 'cb_trigger');
		$BIO4->setDebouncePeriod(500);
		$BIO4->setInterrupt(1 << 0);
		
		$trigger = substr(decbin($BIO4->getValue()), -1, 1);
		
		$BIO4->setConfiguration(1 << 3, 'o', $trigger == "1");
		
		$shmid = shmop_open(1, "c", 0644, 1);
		shmop_write($shmid, $trigger, 0);
		shmop_close($shmid);

		$CLight->dispatchCallbacks(-1);
		exit;
	break;

	default:

		$CTemp = new IPConnection("192.168.7.228", 4223);
		$CLight = new IPConnection("192.168.7.77", 4223);

		$BTemp = new BrickletTemperatureIR("9nA");
		$BLight = new BrickletAnalogOut("bvo");

		$CTemp->addDevice($BTemp);
		$CLight->addDevice($BLight);
		
		$BLight->setVoltage(5000);
		sleep(1);

		$BTemp->setDebouncePeriod(60000);

		$BTemp->registerCallback(BrickletTemperatureIR::CALLBACK_OBJECT_TEMPERATURE_REACHED, 'cb_temp');
		$BTemp->setObjectTemperatureCallbackThreshold('>', 50*10, 0);

		$CTemp->dispatchCallbacks(-1);
}
?>