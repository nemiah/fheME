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
 * Device for output of voltage between 0 and 5V
 */
class BrickletAnalogOut extends Device
{


    /**
     * @internal
     */
    const FUNCTION_SET_VOLTAGE = 1;

    /**
     * @internal
     */
    const FUNCTION_GET_VOLTAGE = 2;

    /**
     * @internal
     */
    const FUNCTION_SET_MODE = 3;

    /**
     * @internal
     */
    const FUNCTION_GET_MODE = 4;

    /**
     * Creates an object with the unique device ID $uid. This object can
     * then be added to the IP connection.
     *
     * @param string $uid
     */
    public function __construct($uid)
    {
        parent::__construct($uid);

        $this->expectedName = 'Analog Out Bricklet';

        $this->bindingVersion = array(1, 0, 0);

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
     * Sets the voltage in mV. The possible range is 0V to 5V (0-5000).
     * Calling this function will set the mode to 0 (see `:func:SetMode`).
     * 
     * The default value is 0 (with mode 1).
     * 
     * @param int $voltage
     * 
     * @return void
     */
    public function setVoltage($voltage)
    {
        $payload = '';
        $payload .= pack('v', $voltage);

        $this->sendRequestNoResponse(self::FUNCTION_SET_VOLTAGE, $payload);
    }

    /**
     * Returns the voltage as set by BrickletAnalogOut::setVoltage().
     * 
     * 
     * @return int
     */
    public function getVoltage()
    {
        $payload = '';

        $data = $this->sendRequestExpectResponse(self::FUNCTION_GET_VOLTAGE, $payload, 2);

        $payload = unpack('v1voltage', $data);

        return $payload['voltage'];
    }

    /**
     * Sets the mode of the analog value. Possible modes:
     * 
     * * 0: Normal Mode (Analog value as set by BrickletAnalogOut::setVoltage() is applied
     * * 1: 1k Ohm resistor to ground
     * * 2: 100k Ohm resistor to ground
     * * 3: 500k Ohm resistor to ground
     * 
     * Setting the mode to 0 will result in an output voltage of 0. You can jump
     * to a higher output voltage directly by calling BrickletAnalogOut::setVoltage().
     * 
     * The default mode is 1.
     * 
     * @param int $mode
     * 
     * @return void
     */
    public function setMode($mode)
    {
        $payload = '';
        $payload .= pack('C', $mode);

        $this->sendRequestNoResponse(self::FUNCTION_SET_MODE, $payload);
    }

    /**
     * Returns the mode as set by BrickletAnalogOut::setMode().
     * 
     * 
     * @return int
     */
    public function getMode()
    {
        $payload = '';

        $data = $this->sendRequestExpectResponse(self::FUNCTION_GET_MODE, $payload, 1);

        $payload = unpack('C1mode', $data);

        return $payload['mode'];
    }
}

?>
