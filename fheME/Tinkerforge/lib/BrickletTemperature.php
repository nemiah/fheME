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
 * Device for sensing Temperature
 */
class BrickletTemperature extends Device
{

    /**
     * This callback is triggered periodically with the period that is set by
     * BrickletTemperature::setTemperatureCallbackPeriod(). The parameter is the temperature
     * of the sensor.
     * 
     * BrickletTemperature::CALLBACK_TEMPERATURE is only triggered if the temperature has changed since the
     * last triggering.
     */
    const CALLBACK_TEMPERATURE = 8;

    /**
     * This callback is triggered when the threshold as set by
     * BrickletTemperature::setTemperatureCallbackThreshold() is reached.
     * The parameter is the temperature of the sensor.
     * 
     * If the threshold keeps being reached, the callback is triggered periodically
     * with the period as set by BrickletTemperature::setDebouncePeriod().
     */
    const CALLBACK_TEMPERATURE_REACHED = 9;


    /**
     * @internal
     */
    const FUNCTION_GET_TEMPERATURE = 1;

    /**
     * @internal
     */
    const FUNCTION_SET_TEMPERATURE_CALLBACK_PERIOD = 2;

    /**
     * @internal
     */
    const FUNCTION_GET_TEMPERATURE_CALLBACK_PERIOD = 3;

    /**
     * @internal
     */
    const FUNCTION_SET_TEMPERATURE_CALLBACK_THRESHOLD = 4;

    /**
     * @internal
     */
    const FUNCTION_GET_TEMPERATURE_CALLBACK_THRESHOLD = 5;

    /**
     * @internal
     */
    const FUNCTION_SET_DEBOUNCE_PERIOD = 6;

    /**
     * @internal
     */
    const FUNCTION_GET_DEBOUNCE_PERIOD = 7;

    /**
     * @internal
     */
    const FUNCTION_SET_I2C_MODE = 10;

    /**
     * @internal
     */
    const FUNCTION_GET_I2C_MODE = 11;

    /**
     * @internal
     */
    const FUNCTION_GET_IDENTITY = 255;

    const THRESHOLD_OPTION_OFF = 'x';
    const THRESHOLD_OPTION_OUTSIDE = 'o';
    const THRESHOLD_OPTION_INSIDE = 'i';
    const THRESHOLD_OPTION_SMALLER = '<';
    const THRESHOLD_OPTION_GREATER = '>';
    const I2C_MODE_FAST = 0;
    const I2C_MODE_SLOW = 1;

    const DEVICE_IDENTIFIER = 216;

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

        $this->responseExpected[self::FUNCTION_GET_TEMPERATURE] = self::RESPONSE_EXPECTED_ALWAYS_TRUE;
        $this->responseExpected[self::FUNCTION_SET_TEMPERATURE_CALLBACK_PERIOD] = self::RESPONSE_EXPECTED_TRUE;
        $this->responseExpected[self::FUNCTION_GET_TEMPERATURE_CALLBACK_PERIOD] = self::RESPONSE_EXPECTED_ALWAYS_TRUE;
        $this->responseExpected[self::FUNCTION_SET_TEMPERATURE_CALLBACK_THRESHOLD] = self::RESPONSE_EXPECTED_TRUE;
        $this->responseExpected[self::FUNCTION_GET_TEMPERATURE_CALLBACK_THRESHOLD] = self::RESPONSE_EXPECTED_ALWAYS_TRUE;
        $this->responseExpected[self::FUNCTION_SET_DEBOUNCE_PERIOD] = self::RESPONSE_EXPECTED_TRUE;
        $this->responseExpected[self::FUNCTION_GET_DEBOUNCE_PERIOD] = self::RESPONSE_EXPECTED_ALWAYS_TRUE;
        $this->responseExpected[self::CALLBACK_TEMPERATURE] = self::RESPONSE_EXPECTED_ALWAYS_FALSE;
        $this->responseExpected[self::CALLBACK_TEMPERATURE_REACHED] = self::RESPONSE_EXPECTED_ALWAYS_FALSE;
        $this->responseExpected[self::FUNCTION_SET_I2C_MODE] = self::RESPONSE_EXPECTED_FALSE;
        $this->responseExpected[self::FUNCTION_GET_I2C_MODE] = self::RESPONSE_EXPECTED_ALWAYS_TRUE;
        $this->responseExpected[self::FUNCTION_GET_IDENTITY] = self::RESPONSE_EXPECTED_ALWAYS_TRUE;

        $this->callbackWrappers[self::CALLBACK_TEMPERATURE] = 'callbackWrapperTemperature';
        $this->callbackWrappers[self::CALLBACK_TEMPERATURE_REACHED] = 'callbackWrapperTemperatureReached';
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
     * Returns the temperature of the sensor. The value
     * has a range of -2500 to 8500 and is given in °C/100,
     * e.g. a value of 4223 means that a temperature of 42.23 °C is measured.
     * 
     * If you want to get the temperature periodically, it is recommended 
     * to use the callback BrickletTemperature::CALLBACK_TEMPERATURE and set the period with 
     * BrickletTemperature::setTemperatureCallbackPeriod().
     * 
     * 
     * @return int
     */
    public function getTemperature()
    {
        $payload = '';

        $data = $this->sendRequest(self::FUNCTION_GET_TEMPERATURE, $payload);

        $payload = unpack('v1temperature', $data);

        return IPConnection::fixUnpackedInt16($payload['temperature']);
    }

    /**
     * Sets the period in ms with which the BrickletTemperature::CALLBACK_TEMPERATURE callback is triggered
     * periodically. A value of 0 turns the callback off.
     * 
     * BrickletTemperature::CALLBACK_TEMPERATURE is only triggered if the temperature has changed since the
     * last triggering.
     * 
     * The default value is 0.
     * 
     * @param int $period
     * 
     * @return void
     */
    public function setTemperatureCallbackPeriod($period)
    {
        $payload = '';
        $payload .= pack('V', $period);

        $this->sendRequest(self::FUNCTION_SET_TEMPERATURE_CALLBACK_PERIOD, $payload);
    }

    /**
     * Returns the period as set by BrickletTemperature::setTemperatureCallbackPeriod().
     * 
     * 
     * @return int
     */
    public function getTemperatureCallbackPeriod()
    {
        $payload = '';

        $data = $this->sendRequest(self::FUNCTION_GET_TEMPERATURE_CALLBACK_PERIOD, $payload);

        $payload = unpack('V1period', $data);

        return IPConnection::fixUnpackedUInt32($payload['period']);
    }

    /**
     * Sets the thresholds for the BrickletTemperature::CALLBACK_TEMPERATURE_REACHED callback. 
     * 
     * The following options are possible:
     * 
     * <code>
     *  "Option", "Description"
     * 
     *  "'x'",    "Callback is turned off"
     *  "'o'",    "Callback is triggered when the temperature is *outside* the min and max values"
     *  "'i'",    "Callback is triggered when the temperature is *inside* the min and max values"
     *  "'<'",    "Callback is triggered when the temperature is smaller than the min value (max is ignored)"
     *  "'>'",    "Callback is triggered when the temperature is greater than the min value (max is ignored)"
     * </code>
     * 
     * The default value is ('x', 0, 0).
     * 
     * @param string $option
     * @param int $min
     * @param int $max
     * 
     * @return void
     */
    public function setTemperatureCallbackThreshold($option, $min, $max)
    {
        $payload = '';
        $payload .= pack('c', ord($option));
        $payload .= pack('v', $min);
        $payload .= pack('v', $max);

        $this->sendRequest(self::FUNCTION_SET_TEMPERATURE_CALLBACK_THRESHOLD, $payload);
    }

    /**
     * Returns the threshold as set by BrickletTemperature::setTemperatureCallbackThreshold().
     * 
     * 
     * @return array
     */
    public function getTemperatureCallbackThreshold()
    {
        $result = array();

        $payload = '';

        $data = $this->sendRequest(self::FUNCTION_GET_TEMPERATURE_CALLBACK_THRESHOLD, $payload);

        $payload = unpack('c1option/v1min/v1max', $data);

        $result['option'] = chr($payload['option']);
        $result['min'] = IPConnection::fixUnpackedInt16($payload['min']);
        $result['max'] = IPConnection::fixUnpackedInt16($payload['max']);

        return $result;
    }

    /**
     * Sets the period in ms with which the threshold callback
     * 
     * * BrickletTemperature::CALLBACK_TEMPERATURE_REACHED
     * 
     * is triggered, if the threshold
     * 
     * * BrickletTemperature::setTemperatureCallbackThreshold()
     * 
     * keeps being reached.
     * 
     * The default value is 100.
     * 
     * @param int $debounce
     * 
     * @return void
     */
    public function setDebouncePeriod($debounce)
    {
        $payload = '';
        $payload .= pack('V', $debounce);

        $this->sendRequest(self::FUNCTION_SET_DEBOUNCE_PERIOD, $payload);
    }

    /**
     * Returns the debounce period as set by BrickletTemperature::setDebouncePeriod().
     * 
     * 
     * @return int
     */
    public function getDebouncePeriod()
    {
        $payload = '';

        $data = $this->sendRequest(self::FUNCTION_GET_DEBOUNCE_PERIOD, $payload);

        $payload = unpack('V1debounce', $data);

        return IPConnection::fixUnpackedUInt32($payload['debounce']);
    }

    /**
     * Sets the I2C mode. Possible modes are:
     * 
     * * 0: Fast (400kHz, default)
     * * 1: Slow (100kHz)
     * 
     * If you have problems with obvious outliers in the
     * Temperature Bricklet measurements, they may be caused by EMI issues.
     * In this case it may be helpful to lower the I2C speed.
     * 
     * It is however not recommended to lower the I2C speed in applications where
     * a high throughput needs to be achieved.
     * 
     * .. versionadded:: 2.0.1~(Plugin)
     * 
     * @param int $mode
     * 
     * @return void
     */
    public function setI2CMode($mode)
    {
        $payload = '';
        $payload .= pack('C', $mode);

        $this->sendRequest(self::FUNCTION_SET_I2C_MODE, $payload);
    }

    /**
     * Returns the I2C mode as set by BrickletTemperature::setI2CMode().
     * 
     * .. versionadded:: 2.0.1~(Plugin)
     * 
     * 
     * @return int
     */
    public function getI2CMode()
    {
        $payload = '';

        $data = $this->sendRequest(self::FUNCTION_GET_I2C_MODE, $payload);

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

    /**
     * Registers a callback with ID $id to the callable $callback.
     *
     * @param int $id
     * @param callable $callback
     * @param mixed $userData
     *
     * @return void
     */
    public function registerCallback($id, $callback, $userData = NULL)
    {
        $this->registeredCallbacks[$id] = $callback;
        $this->registeredCallbackUserData[$id] = $userData;
    }

    /**
     * @internal
     * @param string $data
     */
    public function callbackWrapperTemperature($data)
    {
        $result = array();
        $payload = unpack('v1temperature', $data);

        array_push($result, IPConnection::fixUnpackedInt16($payload['temperature']));

        call_user_func_array($this->registeredCallbacks[self::CALLBACK_TEMPERATURE], $result);
    }

    /**
     * @internal
     * @param string $data
     */
    public function callbackWrapperTemperatureReached($data)
    {
        $result = array();
        $payload = unpack('v1temperature', $data);

        array_push($result, IPConnection::fixUnpackedInt16($payload['temperature']));

        call_user_func_array($this->registeredCallbacks[self::CALLBACK_TEMPERATURE_REACHED], $result);
    }
}

?>
