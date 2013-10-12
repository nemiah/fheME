<?php

/* ***********************************************************
 * This file was automatically generated on 2013-09-11.      *
 *                                                           *
 * Bindings Version 2.0.10                                    *
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
     * @internal
     */
    const FUNCTION_GET_IDENTITY = 255;

    const MODE_ANALOG_VALUE = 0;
    const MODE_1K_TO_GROUND = 1;
    const MODE_100K_TO_GROUND = 2;
    const MODE_500K_TO_GROUND = 3;

    const DEVICE_IDENTIFIER = 220;

    /**
     * Creates an object with the unique device ID $uid. This object can
     * then be added to the IP connection.
     *
     * @param string $uid
     */
    public function __construct($uid, $ipcon)
    {
        parent::__construct($uid, $ipcon);

        $this->apiVersion = array(2, 0, 0);

        $this->responseExpected[self::FUNCTION_SET_VOLTAGE] = self::RESPONSE_EXPECTED_FALSE;
        $this->responseExpected[self::FUNCTION_GET_VOLTAGE] = self::RESPONSE_EXPECTED_ALWAYS_TRUE;
        $this->responseExpected[self::FUNCTION_SET_MODE] = self::RESPONSE_EXPECTED_FALSE;
        $this->responseExpected[self::FUNCTION_GET_MODE] = self::RESPONSE_EXPECTED_ALWAYS_TRUE;
        $this->responseExpected[self::FUNCTION_GET_IDENTITY] = self::RESPONSE_EXPECTED_ALWAYS_TRUE;

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
     * Calling this function will set the mode to 0 (see BrickletAnalogOut::setMode()).
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

        $this->sendRequest(self::FUNCTION_SET_VOLTAGE, $payload);
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

        $data = $this->sendRequest(self::FUNCTION_GET_VOLTAGE, $payload);

        $payload = unpack('v1voltage', $data);

        return $payload['voltage'];
    }

    /**
     * Sets the mode of the analog value. Possible modes:
     * 
     * * 0: Normal Mode (Analog value as set by BrickletAnalogOut::setVoltage() is applied)
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

        $this->sendRequest(self::FUNCTION_SET_MODE, $payload);
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

        $data = $this->sendRequest(self::FUNCTION_GET_MODE, $payload);

        $payload = unpack('C1mode', $data);

        return $payload['mode'];
    }

    /**
     * Returns the UID, the UID where the Bricklet is connected to, 
     * the position, the hardware and firmware version as well as the
     * device identifier.
     * 
     * The position can be 'a', 'b', 'c' or 'd'.
     * 
     * The device identifiers can be found :ref:`here <device_identifier>`.
     * 
     * .. versionadded:: 2.0.0~(Plugin)
     * 
     * 
     * @return array
     */
    public function getIdentity()
    {
        $result = array();

        $payload = '';

        $data = $this->sendRequest(self::FUNCTION_GET_IDENTITY, $payload);

        $payload = unpack('c8uid/c8connected_uid/c1position/C3hardware_version/C3firmware_version/v1device_identifier', $data);

        $result['uid'] = IPConnection::implodeUnpackedString($payload, 'uid', 8);
        $result['connected_uid'] = IPConnection::implodeUnpackedString($payload, 'connected_uid', 8);
        $result['position'] = chr($payload['position']);
        $result['hardware_version'] = IPConnection::collectUnpackedArray($payload, 'hardware_version', 3);
        $result['firmware_version'] = IPConnection::collectUnpackedArray($payload, 'firmware_version', 3);
        $result['device_identifier'] = $payload['device_identifier'];

        return $result;
    }
}

?>
