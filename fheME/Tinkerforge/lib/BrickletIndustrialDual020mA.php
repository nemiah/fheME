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
 * Device for sensing two currents between 0 and 20mA (IEC 60381-1)
 */
class BrickletIndustrialDual020mA extends Device
{

    /**
     * This callback is triggered periodically with the period that is set by
     * BrickletIndustrialDual020mA::setCurrentCallbackPeriod(). The parameter is the current of the
     * sensor.
     * 
     * BrickletIndustrialDual020mA::CALLBACK_CURRENT is only triggered if the current has changed since the
     * last triggering.
     */
    const CALLBACK_CURRENT = 10;

    /**
     * This callback is triggered when the threshold as set by
     * BrickletIndustrialDual020mA::setCurrentCallbackThreshold() is reached.
     * The parameter is the current of the sensor.
     * 
     * If the threshold keeps being reached, the callback is triggered periodically
     * with the period as set by BrickletIndustrialDual020mA::setDebouncePeriod().
     */
    const CALLBACK_CURRENT_REACHED = 11;


    /**
     * @internal
     */
    const FUNCTION_GET_CURRENT = 1;

    /**
     * @internal
     */
    const FUNCTION_SET_CURRENT_CALLBACK_PERIOD = 2;

    /**
     * @internal
     */
    const FUNCTION_GET_CURRENT_CALLBACK_PERIOD = 3;

    /**
     * @internal
     */
    const FUNCTION_SET_CURRENT_CALLBACK_THRESHOLD = 4;

    /**
     * @internal
     */
    const FUNCTION_GET_CURRENT_CALLBACK_THRESHOLD = 5;

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
    const FUNCTION_SET_SAMPLE_RATE = 8;

    /**
     * @internal
     */
    const FUNCTION_GET_SAMPLE_RATE = 9;

    /**
     * @internal
     */
    const FUNCTION_GET_IDENTITY = 255;

    const THRESHOLD_OPTION_OFF = 'x';
    const THRESHOLD_OPTION_OUTSIDE = 'o';
    const THRESHOLD_OPTION_INSIDE = 'i';
    const THRESHOLD_OPTION_SMALLER = '<';
    const THRESHOLD_OPTION_GREATER = '>';
    const SAMPLE_RATE_240_SPS = 0;
    const SAMPLE_RATE_60_SPS = 1;
    const SAMPLE_RATE_15_SPS = 2;
    const SAMPLE_RATE_4_SPS = 3;

    const DEVICE_IDENTIFIER = 228;

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

        $this->responseExpected[self::FUNCTION_GET_CURRENT] = self::RESPONSE_EXPECTED_ALWAYS_TRUE;
        $this->responseExpected[self::FUNCTION_SET_CURRENT_CALLBACK_PERIOD] = self::RESPONSE_EXPECTED_TRUE;
        $this->responseExpected[self::FUNCTION_GET_CURRENT_CALLBACK_PERIOD] = self::RESPONSE_EXPECTED_ALWAYS_TRUE;
        $this->responseExpected[self::FUNCTION_SET_CURRENT_CALLBACK_THRESHOLD] = self::RESPONSE_EXPECTED_TRUE;
        $this->responseExpected[self::FUNCTION_GET_CURRENT_CALLBACK_THRESHOLD] = self::RESPONSE_EXPECTED_ALWAYS_TRUE;
        $this->responseExpected[self::FUNCTION_SET_DEBOUNCE_PERIOD] = self::RESPONSE_EXPECTED_TRUE;
        $this->responseExpected[self::FUNCTION_GET_DEBOUNCE_PERIOD] = self::RESPONSE_EXPECTED_ALWAYS_TRUE;
        $this->responseExpected[self::FUNCTION_SET_SAMPLE_RATE] = self::RESPONSE_EXPECTED_FALSE;
        $this->responseExpected[self::FUNCTION_GET_SAMPLE_RATE] = self::RESPONSE_EXPECTED_ALWAYS_TRUE;
        $this->responseExpected[self::CALLBACK_CURRENT] = self::RESPONSE_EXPECTED_ALWAYS_FALSE;
        $this->responseExpected[self::CALLBACK_CURRENT_REACHED] = self::RESPONSE_EXPECTED_ALWAYS_FALSE;
        $this->responseExpected[self::FUNCTION_GET_IDENTITY] = self::RESPONSE_EXPECTED_ALWAYS_TRUE;

        $this->callbackWrappers[self::CALLBACK_CURRENT] = 'callbackWrapperCurrent';
        $this->callbackWrappers[self::CALLBACK_CURRENT_REACHED] = 'callbackWrapperCurrentReached';
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
     * Returns the current of the specified sensor (0 or 1). The value is in nA
     * and between 0nA and 22505322nA (22.5mA).
     * 
     * It is possible to detect if an IEC 60381-1 compatible sensor is connected
     * and if it works probably.
     * 
     * If the returned current is below 4mA, there is likely no sensor connected
     * or the sensor may be defect. If the returned current is over 20mA, there might
     * be a short circuit or the sensor may be defect.
     * 
     * If you want to get the current periodically, it is recommended to use the
     * callback BrickletIndustrialDual020mA::CALLBACK_CURRENT and set the period with 
     * BrickletIndustrialDual020mA::setCurrentCallbackPeriod().
     * 
     * @param int $sensor
     * 
     * @return int
     */
    public function getCurrent($sensor)
    {
        $payload = '';
        $payload .= pack('C', $sensor);

        $data = $this->sendRequest(self::FUNCTION_GET_CURRENT, $payload);

        $payload = unpack('V1current', $data);

        return IPConnection::fixUnpackedInt32($payload['current']);
    }

    /**
     * Sets the period in ms with which the BrickletIndustrialDual020mA::CALLBACK_CURRENT callback is triggered
     * periodically for the given sensor. A value of 0 turns the callback off.
     * 
     * BrickletIndustrialDual020mA::CALLBACK_CURRENT is only triggered if the current has changed since the
     * last triggering.
     * 
     * The default value is 0.
     * 
     * @param int $sensor
     * @param int $period
     * 
     * @return void
     */
    public function setCurrentCallbackPeriod($sensor, $period)
    {
        $payload = '';
        $payload .= pack('C', $sensor);
        $payload .= pack('V', $period);

        $this->sendRequest(self::FUNCTION_SET_CURRENT_CALLBACK_PERIOD, $payload);
    }

    /**
     * Returns the period as set by BrickletIndustrialDual020mA::setCurrentCallbackPeriod().
     * 
     * @param int $sensor
     * 
     * @return int
     */
    public function getCurrentCallbackPeriod($sensor)
    {
        $payload = '';
        $payload .= pack('C', $sensor);

        $data = $this->sendRequest(self::FUNCTION_GET_CURRENT_CALLBACK_PERIOD, $payload);

        $payload = unpack('V1period', $data);

        return IPConnection::fixUnpackedUInt32($payload['period']);
    }

    /**
     * Sets the thresholds for the BrickletIndustrialDual020mA::CALLBACK_CURRENT_REACHED callback for the given
     * sensor.
     * 
     * The following options are possible:
     * 
     * <code>
     *  "Option", "Description"
     * 
     *  "'x'",    "Callback is turned off"
     *  "'o'",    "Callback is triggered when the current is *outside* the min and max values"
     *  "'i'",    "Callback is triggered when the current is *inside* the min and max values"
     *  "'<'",    "Callback is triggered when the current is smaller than the min value (max is ignored)"
     *  "'>'",    "Callback is triggered when the current is greater than the min value (max is ignored)"
     * </code>
     * 
     * The default value is ('x', 0, 0).
     * 
     * @param int $sensor
     * @param string $option
     * @param int $min
     * @param int $max
     * 
     * @return void
     */
    public function setCurrentCallbackThreshold($sensor, $option, $min, $max)
    {
        $payload = '';
        $payload .= pack('C', $sensor);
        $payload .= pack('c', ord($option));
        $payload .= pack('V', $min);
        $payload .= pack('V', $max);

        $this->sendRequest(self::FUNCTION_SET_CURRENT_CALLBACK_THRESHOLD, $payload);
    }

    /**
     * Returns the threshold as set by BrickletIndustrialDual020mA::setCurrentCallbackThreshold().
     * 
     * @param int $sensor
     * 
     * @return array
     */
    public function getCurrentCallbackThreshold($sensor)
    {
        $result = array();

        $payload = '';
        $payload .= pack('C', $sensor);

        $data = $this->sendRequest(self::FUNCTION_GET_CURRENT_CALLBACK_THRESHOLD, $payload);

        $payload = unpack('c1option/V1min/V1max', $data);

        $result['option'] = chr($payload['option']);
        $result['min'] = IPConnection::fixUnpackedInt32($payload['min']);
        $result['max'] = IPConnection::fixUnpackedInt32($payload['max']);

        return $result;
    }

    /**
     * Sets the period in ms with which the threshold callback
     * 
     * * BrickletIndustrialDual020mA::CALLBACK_CURRENT_REACHED
     * 
     * is triggered, if the threshold
     * 
     * * BrickletIndustrialDual020mA::setCurrentCallbackThreshold()
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
     * Returns the debounce period as set by BrickletIndustrialDual020mA::setDebouncePeriod().
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
     * Sets the sample rate to either 240, 60, 15 or 4 samples per second.
     * The resolution for the rates is 12, 14, 16 and 18 bit respectively.
     * 
     * <code>
     *  "Value", "Description"
     * 
     *  "0",    "240 samples per second, 12 bit resolution"
     *  "1",    "60 samples per second, 14 bit resolution"
     *  "2",    "15 samples per second, 16 bit resolution"
     *  "3",    "4 samples per second, 18 bit resolution"
     * </code>
     * 
     * The default value is 3: 4 samples per second with 18 bit resolution.
     * 
     * @param int $rate
     * 
     * @return void
     */
    public function setSampleRate($rate)
    {
        $payload = '';
        $payload .= pack('C', $rate);

        $this->sendRequest(self::FUNCTION_SET_SAMPLE_RATE, $payload);
    }

    /**
     * Returns the sample rate as set by BrickletIndustrialDual020mA::setSampleRate().
     * 
     * 
     * @return int
     */
    public function getSampleRate()
    {
        $payload = '';

        $data = $this->sendRequest(self::FUNCTION_GET_SAMPLE_RATE, $payload);

        $payload = unpack('C1rate', $data);

        return $payload['rate'];
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
    public function callbackWrapperCurrent($data)
    {
        $result = array();
        $payload = unpack('C1sensor/V1current', $data);

        array_push($result, $payload['sensor']);
        array_push($result, IPConnection::fixUnpackedInt32($payload['current']));

        call_user_func_array($this->registeredCallbacks[self::CALLBACK_CURRENT], $result);
    }

    /**
     * @internal
     * @param string $data
     */
    public function callbackWrapperCurrentReached($data)
    {
        $result = array();
        $payload = unpack('C1sensor/V1current', $data);

        array_push($result, $payload['sensor']);
        array_push($result, IPConnection::fixUnpackedInt32($payload['current']));

        call_user_func_array($this->registeredCallbacks[self::CALLBACK_CURRENT_REACHED], $result);
    }
}

?>
