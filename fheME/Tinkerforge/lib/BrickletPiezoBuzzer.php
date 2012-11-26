<?php

/* ***********************************************************
 * This file was automatically generated on 2012-10-01.      *
 *                                                           *
 * If you have a bugfix for this file and want to commit it, *
 * please fix the bug in the generator. You can find a link  *
 * to the generator git on tinkerforge.com                   *
 *************************************************************/

namespace Tinkerforge;

require_once(__DIR__ . '/IPConnection.php');

/**
 * Device for controlling a piezo buzzer
 */
class BrickletPiezoBuzzer extends Device
{

    /**
     * This callback is triggered if a beep set by BrickletPiezoBuzzer::beep() is finished
     */
    const CALLBACK_BEEP_FINISHED = 3;

    /**
     * This callback is triggered if the playback of the morse code set by
     * BrickletPiezoBuzzer::morseCode() is finished.
     */
    const CALLBACK_MORSE_CODE_FINISHED = 4;


    /**
     * @internal
     */
    const FUNCTION_BEEP = 1;

    /**
     * @internal
     */
    const FUNCTION_MORSE_CODE = 2;

    /**
     * Creates an object with the unique device ID $uid. This object can
     * then be added to the IP connection.
     *
     * @param string $uid
     */
    public function __construct($uid)
    {
        parent::__construct($uid);

        $this->expectedName = 'Piezo Buzzer Bricklet';

        $this->bindingVersion = array(1, 0, 0);

        $this->callbackWrappers[self::CALLBACK_BEEP_FINISHED] = 'callbackWrapperBeepFinished';
        $this->callbackWrappers[self::CALLBACK_MORSE_CODE_FINISHED] = 'callbackWrapperMorseCodeFinished';
    }

    /**
     * @internal
     * @param string $header
     * @param string $data
     */
    public function handleCallback($header, $data)
    {
        call_user_func(array($this, $this->callbackWrappers[$header['functionID']]), $data);
    }

    /**
     * Beeps with the duration in ms. For example: If you set a value of 1000,
     * the piezo buzzer will beep for one second.
     * 
     * @param int $duration
     * 
     * @return void
     */
    public function beep($duration)
    {
        $payload = '';
        $payload .= pack('V', $duration);

        $this->sendRequestNoResponse(self::FUNCTION_BEEP, $payload);
    }

    /**
     * Sets morse code that will be played by the piezo buzzer. The morse code
     * is given as a string consisting of "." (dot), "-" (minus) and " " (space)
     * for *dits*, *dahs* and *pauses*. Every other character is ignored.
     * 
     * For example: If you set the string "...---...", the piezo buzzer will beep
     * nine times with the durations "short short short long long long short 
     * short short".
     * 
     * The maximum string size is 60.
     * 
     * @param string $morse
     * 
     * @return void
     */
    public function morseCode($morse)
    {
        $payload = '';
        for ($i = 0; $i < strlen($morse) && $i < 60; $i++) {
            $payload .= pack('c', ord($morse[$i]));
        }
        for ($i = strlen($morse); $i < 60; $i++) {
            $payload .= pack('c', 0);
        }

        $this->sendRequestNoResponse(self::FUNCTION_MORSE_CODE, $payload);
    }

    /**
     * Registers a callback with ID $id to the callable $callback.
     *
     * @param int $id
     * @param callable $callback
     *
     * @return void
     */
    public function registerCallback($id, $callback)
    {
        $this->registeredCallbacks[$id] = $callback;
    }

    /**
     * @internal
     * @param string $data
     */
    public function callbackWrapperBeepFinished($data)
    {
        $result = array();




        call_user_func_array($this->registeredCallbacks[self::CALLBACK_BEEP_FINISHED], $result);
    }

    /**
     * @internal
     * @param string $data
     */
    public function callbackWrapperMorseCodeFinished($data)
    {
        $result = array();




        call_user_func_array($this->registeredCallbacks[self::CALLBACK_MORSE_CODE_FINISHED], $result);
    }
}

?>
