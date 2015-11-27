<?php
require_once(__DIR__.'/lib/IPConnection.php');
require_once(__DIR__.'/lib/BrickletTemperatureIR.php');
require_once(__DIR__.'/lib/BrickletAnalogOut.php');
require_once(__DIR__.'/lib/BrickletIO4.php');

class Colors {
	private $foreground_colors = array();
	private $background_colors = array();
 
	public function __construct() {
		// Set up shell colors
		$this->foreground_colors['black'] = '0;30';
		$this->foreground_colors['dark_gray'] = '1;30';
		$this->foreground_colors['blue'] = '0;34';
		$this->foreground_colors['light_blue'] = '1;34';
		$this->foreground_colors['green'] = '0;32';
		$this->foreground_colors['light_green'] = '1;32';
		$this->foreground_colors['cyan'] = '0;36';
		$this->foreground_colors['light_cyan'] = '1;36';
		$this->foreground_colors['red'] = '0;31';
		$this->foreground_colors['light_red'] = '1;31';
		$this->foreground_colors['purple'] = '0;35';
		$this->foreground_colors['light_purple'] = '1;35';
		$this->foreground_colors['brown'] = '0;33';
		$this->foreground_colors['yellow'] = '1;33';
		$this->foreground_colors['light_gray'] = '0;37';
		$this->foreground_colors['white'] = '1;37';
	 
		$this->background_colors['black'] = '40';
		$this->background_colors['red'] = '41';
		$this->background_colors['green'] = '42';
		$this->background_colors['yellow'] = '43';
		$this->background_colors['blue'] = '44';
		$this->background_colors['magenta'] = '45';
		$this->background_colors['cyan'] = '46';
		$this->background_colors['light_gray'] = '47';
	}
	 
	// Returns colored string
	public function getColoredString($string, $foreground_color = null, $background_color = null) {
		$colored_string = "";
	 
		// Check if given foreground color found
		if (isset($this->foreground_colors[$foreground_color])) {
			$colored_string .= "\033[" . $this->foreground_colors[$foreground_color] . "m";
		}
		
		// Check if given background color found
		if (isset($this->background_colors[$background_color])) {
			$colored_string .= "\033[" . $this->background_colors[$background_color] . "m";
		}
	 
		// Add string and end coloring
		$colored_string .=  $string . "\033[0m";
	 
		return $colored_string;
	}
}
	
declare(ticks = 1);
/*pcntl_signal(SIGINT, "signal_handler");
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
}*/

function leastSquareFit(array $values) {
    $x_sum = array_sum(array_keys($values));
    $y_sum = array_sum($values);
    $meanX = $x_sum / count($values);
    $meanY = $y_sum / count($values);
    // calculate sums
    $mBase = $mDivisor = 0.0;
    foreach($values as $i => $value) {
        $mBase += ($i - $meanX) * ($value - $meanY);
        $mDivisor += ($i - $meanX) * ($i - $meanX);
    }

    // calculate slope
    $slope = $mBase / $mDivisor;
    return $slope;
}   //  function leastSquareFit()


function cb_temp($temperature) {
	$temperature /= 10.0;
	static $temps = array();
	static $times = array();
	static $bingsBad = 0;
	static $bingsGood = 0;
	
	$temps[] = $temperature;
	$times[] = time();
	if(count($temps) > 600){
		array_shift($temps);
		array_shift($times);
	}
	
	$maxK = null;
	$maxV = 0;
	foreach($temps AS $k => $v){
		if($v > $maxV){
			$maxK = $k;
			$maxV = $v;
		}
	}
	
	$temperature = floor($temperature);
	
	system('clear');
	
	
	$trend = "▬";
	if(count($temps) > 3){
		$v = leastSquareFit(array_slice($temps, -30));
		if($v > 0)
			$trend = "▲";
		if($v <= 0)
			$trend = "▼";
	}
	
	$t = " ".date("H:i:s").": ".str_pad($temperature, 5, " ", STR_PAD_LEFT)." °C $trend ";
	
	$C = new Colors();
	if($temperature < 80){
		echo $C->getColoredString($t, "white", "red");
		
		if($bingsBad < 3)
			exec('play /usr/share/sounds/KDE-Sys-Log-Out.ogg > /dev/null 2>&1');
			
		$bingsGood = 0;
		$bingsBad++;
	} else {
		echo $C->getColoredString($t, "green", "black");
		
		if($bingsGood < 1)
			exec('play /usr/share/sounds/KDE-Sys-App-Positive.ogg > /dev/null 2>&1');
			
		$bingsBad = 0;
		$bingsGood++;
	}
	
	echo "\n Max: ".date("H:i:s", $times[$maxK]).": ".$maxV." °C";
	
}
/*
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
}*/

use Tinkerforge\IPConnection;
use Tinkerforge\BrickletTemperatureIR;
#use Tinkerforge\BrickletAnalogOut;
#use Tinkerforge\BrickletIO4;

$host = '192.168.7.227';
$port = 4223;
$uid = '9nA';

$ipcon = new IPConnection();
$BTemp = new BrickletTemperatureIR($uid, $ipcon);

$ipcon->connect($host, $port);

#$BTemp->setDebouncePeriod(60000);

$BTemp->setObjectTemperatureCallbackPeriod(20000);
#$tir->setAmbientTemperatureCallbackPeriod(1000);

$BTemp->registerCallback(BrickletTemperatureIR::CALLBACK_OBJECT_TEMPERATURE, 'cb_temp');
#$BTemp->setObjectTemperatureCallbackThreshold('>', 30*10, 0);

$ipcon->dispatchCallbacks(-1);

$ipcon->disconnect();

/*
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
}*/
?>
