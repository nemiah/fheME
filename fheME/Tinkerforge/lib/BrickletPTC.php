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
 * Device for reading temperatures from Pt100 or Pt1000 sensors
 */
class BrickletPTC extends Device
{

    /**
     * This callback is triggered periodically with the period that is set by
     * BrickletPTC::setTemperatureCallbackPeriod(). The parameter is the temperature
     * of the connected sensor.
     * 
     * BrickletPTC::CALLBACK_TEMPERATURE is only triggered if the temperature has changed since the
     * last triggering.
     */
    const CALLBACK_TEMPERATURE = 13;

    /**
     * This callback is triggered when the threshold as set by
     * BrickletPTC::setTemperatureCallbackThreshold() is reached.
     * The parameter is the temperature of the connected sensor.
     * 
     * If the threshold keeps being reached, the callback is triggered periodically
     * with the period as set by BrickletPTC::setDebouncePeriod().
     */
    const CALLBACK_TEMPERATURE_REACHED = 14;

    /**
     * This callback is triggered periodically with the period that is set by
     * BrickletPTC::setResistanceCallbackPeriod(). The parameter is the resistance
     * of the connected sensor.
     * 
     * BrickletPTC::CALLBACK_RESISTANCE is only triggered if the resistance has changed since the
     * last triggering.
     */
    const CALLBACK_RESISTANCE = 15;

    /**
     * This callback is triggered when the threshold as set by
     * BrickletPTC::setResistanceCallbackThreshold() is reached.
     * The parameter is the resistance of the connected sensor.
     * 
     * If the threshold keeps being reached, the callback is triggered periodically
     * with the period as set by BrickletPTC::setDebouncePeriod().
     */
    const CALLBACK_RESISTANCE_REACHED = 16;


    /**
     * @internal
     */
    const FUNCTION_GET_TEMPERATURE = 1;

    /**
     * @internal
     */
    const FUNCTION_GET_RESISTANCE = 2;

    /**
     * @internal
     */
    const FUNCTION_SET_TEMPERATURE_CALLBACK_PERIOD = 3;

    /**
     * @internal
     */
    const FUNCTION_GET_TEMPERATURE_CALLBACK_PERIOD = 4;

    /**
     * @internal
     */
    const FUNCTION_SET_RESISTANCE_CALLBACK_PERIOD = 5;

    /**
     * @internal
     */
    const FUNCTION_GET_RESISTANCE_CALLBACK_PERIOD = 6;

    /**
     * @internal
     */
    const FUNCTION_SET_TEMPERATURE_CALLBACK_THRESHOLD = 7;

    /**
     * @internal
     */
    const FUNCTION_GET_TEMPERATURE_CALLBACK_THRESHOLD = 8;

    /**
     * @internal
     */
    const FUNCTION_SET_RESISTANCE_CALLBACK_THRESHOLD = 9;

    /**
     * @internal
     */
    const FUNCTION_GET_RESISTANCE_CALLBACK_THRESHOLD = 10;

    /**
     * @internal
     */
    const FUNCTION_SET_DEBOUNCE_PERIOD = 11;

    /**
     * @internal
     */
    const FUNCTION_GET_DEBOUNCE_PERIOD = 12;

    /**
     * @internal
     */
    const FUNCTION_SET_NOISE_REJECTION_FILTER = 17;

    /**
     * @internal
     */
    const FUNCTION_GET_NOISE_REJECTION_FILTER = 18;

    /**
     * @internal
     */
    const FUNCTION_IS_SENSOR_CONNECTED = 19;

    /**
     * @internal
     */
    const FUNCTION_SET_WIRE_MODE = 20;

    /**
     * @internal
     */
    const FUNCTION_GET_WIRE_MODE = 21;

    /**
     * @internal
     */
    const FUNCTION_GET_IDENTITY = 255;

    const THRESHOLD_OPTION_OFF = 'x';
    const THRESHOLD_OPTION_OUTSIDE = 'o';
    const THRESHOLD_OPTION_INSIDE = 'i';
    const THRESHOLD_OPTION_SMALLER = '<';
    const THRESHOLD_OPTION_GREATER = '>';
    const FILTER_OPTION_50HZ = 0;
    const FILTER_OPTION_60HZ = 1;
    const WIRE_MODE_2 = 2;
    const WIRE_MODE_3 = 3;
    const WIRE_MODE_4 = 4;

    const DEVICE_IDENTIFIER = 226;

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
        $this->responseExpected[self::FUNCTION_GET_RESISTANCE] = self::RESPONSE_EXPECTED_ALWAYS_TRUE;
        $this->responseExpected[self::FUNCTION_SET_TEMPERATURE_CALLBACK_PERIOD] = self::RESPONSE_EXPECTED_TRUE;
        $this->responseExpected[self::FUNCTION_GET_TEMPERATURE_CALLBACK_PERIOD] = self::RESPONSE_EXPECTED_ALWAYS_TRUE;
        $this->responseExpected[self::FUNCTION_SET_RESISTANCE_CALLBACK_PERIOD] = self::RESPONSE_EXPECTED_TRUE;
        $this->responseExpected[self::FUNCTION_GET_RESISTANCE_CALLBACK_PERIOD] = self::RESPONSE_EXPECTED_ALWAYS_TRUE;
        $this->responseExpected[self::FUNCTION_SET_TEMPERATURE_CALLBACK_THRESHOLD] = self::RESPONSE_EXPECTED_TRUE;
        $this->responseExpected[self::FUNCTION_GET_TEMPERATURE_CALLBACK_THRESHOLD] = self::RESPONSE_EXPECTED_ALWAYS_TRUE;
        $this->responseExpected[self::FUNCTION_SET_RESISTANCE_CALLBACK_THRESHOLD] = self::RESPONSE_EXPECTED_TRUE;
        $this->responseExpected[self::FUNCTION_GET_RESISTANCE_CALLBACK_THRESHOLD] = self::RESPONSE_EXPECTED_ALWAYS_TRUE;
        $this->responseExpected[self::FUNCTION_SET_DEBOUNCE_PERIOD] = self::RESPONSE_EXPECTED_TRUE;
        $this->responseExpected[self::FUNCTION_GET_DEBOUNCE_PERIOD] = self::RESPONSE_EXPECTED_ALWAYS_TRUE;
        $this->responseExpected[self::CALLBACK_TEMPERATURE] = self::RESPONSE_EXPECTED_ALWAYS_FALSE;
        $this->responseExpected[self::CALLBACK_TEMPERATURE_REACHED] = self::RESPONSE_EXPECTED_ALWAYS_FALSE;
        $this->responseExpected[self::CALLBACK_RESISTANCE] = self::RESPONSE_EXPECTED_ALWAYS_FALSE;
        $this->responseExpected[self::CALLBACK_RESISTANCE_REACHED] = self::RESPONSE_EXPECTED_ALWAYS_FALSE;
        $this->responseExpected[self::FUNCTION_SET_NOISE_REJECTION_FILTER] = self::RESPONSE_EXPECTED_FALSE;
        $this->responseExpected[self::FUNCTION_GET_NOISE_REJECTION_FILTER] = self::RESPONSE_EXPECTED_ALWAYS_TRUE;
        $this->responseExpected[self::FUNCTION_IS_SENSOR_CONNECTED] = self::RESPONSE_EXPECTED_ALWAYS_TRUE;
        $this->responseExpected[self::FUNCTION_SET_WIRE_MODE] = self::RESPONSE_EXPECTED_FALSE;
        $this->responseExpected[self::FUNCTION_GET_WIRE_MODE] = self::RESPONSE_EXPECTED_ALWAYS_TRUE;
        $this->responseExpected[self::FUNCTION_GET_IDENTITY] = self::RESPONSE_EXPECTED_ALWAYS_TRUE;

        $this->callbackWrappers[self::CALLBACK_TEMPERATURE] = 'callbackWrapperTemperature';
        $this->callbackWrappers[self::CALLBACK_TEMPERATURE_REACHED] = 'callbackWrapperTemperatureReached';
        $this->callbackWrappers[self::CALLBACK_RESISTANCE] = 'callbackWrapperResistance';
        $this->callbackWrappers[self::CALLBACK_RESISTANCE_REACHED] = 'callbackWrapperResistanceReached';
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
     * Returns the temperature of connected sensor. The value
     * has a range of -246 to 849 °C and is given in °C/100,
     * e.g. a value of 4223 means that a temperature of 42.23 °C is measured.
     * 
     * If you want to get the temperature periodically, it is recommended 
     * to use the callback BrickletPTC::CALLBACK_TEMPERATURE and set the period with 
     * BrickletPTC::setTemperatureCallbackPeriod().
     * 
     * 
     * @return int
     */
    public function getTemperature()
    {
        $payload = '';

        $data = $this->sendRequest(self::FUNCTION_GET_TEMPERATURE, $payload);

        $payload = unpack('V1temperature', $data);

        return IPConnection::fixUnpackedInt32($payload['temperature']);
    }

    /**
     * Returns the value as measured by the MAX31865 precision delta-sigma ADC.
     * 
     * The value can be converted with the following formulas:
     * 
     * * Pt100:  resistance = (value * 390) / 32768
     * * Pt1000: resistance = (value * 3900) / 32768
     * 
     * If you want to get the resistance periodically, it is recommended 
     * to use the callback BrickletPTC::CALLBACK_RESISTANCE and set the period with 
     * BrickletPTC::setResistanceCallbackPeriod().
     * 
     * 
     * @return int
     */
    public function getResistance()
    {
        $payload = '';

        $data = $this->sendRequest(self::FUNCTION_GET_RESISTANCE, $payload);

        $payload = unpack('v1resistance', $data);

        return $payload['resistance'];
    }

    /**
     * Sets the period in ms with which the BrickletPTC::CALLBACK_TEMPERATURE callback is triggered
     * periodically. A value of 0 turns the callback off.
     * 
     * BrickletPTC::CALLBACK_TEMPERATURE is only triggered if the temperature has changed since the
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
     * Returns the period as set by BrickletPTC::setTemperatureCallbackPeriod().
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
     * Sets the period in ms with which the BrickletPTC::CALLBACK_RESISTANCE callback is triggered
     * periodically. A value of 0 turns the callback off.
     * 
     * BrickletPTC::CALLBACK_RESISTANCE is only triggered if the resistance has changed since the
     * last triggering.
     * 
     * The default value is 0.
     * 
     * @param int $period
     * 
     * @return void
     */
    public function setResistanceCallbackPeriod($period)
    {
        $payload = '';
        $payload .= pack('V', $period);

        $this->sendRequest(self::FUNCTION_SET_RESISTANCE_CALLBACK_PERIOD, $payload);
    }

    /**
     * Returns the period as set by BrickletPTC::setResistanceCallbackPeriod().
     * 
     * 
     * @return int
     */
    public function getResistanceCallbackPeriod()
    {
        $payload = '';

        $data = $this->sendRequest(self::FUNCTION_GET_RESISTANCE_CALLBACK_PERIOD, $payload);

        $payload = unpack('V1period', $data);

        return IPConnection::fixUnpackedUInt32($payload['period']);
    }

    /**
     * Sets the thresholds for the BrickletPTC::CALLBACK_TEMPERATURE_REACHED callback. 
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
        $payload .= pack('V', $min);
        $payload .= pack('V', $max);

        $this->sendRequest(self::FUNCTION_SET_TEMPERATURE_CALLBACK_THRESHOLD, $payload);
    }

    /**
     * Returns the threshold as set by BrickletPTC::setTemperatureCallbackThreshold().
     * 
     * 
     * @return array
     */
    public function getTemperatureCallbackThreshold()
    {
        $result = array();

        $payload = '';

        $data = $this->sendRequest(self::FUNCTION_GET_TEMPERATURE_CALLBACK_THRESHOLD, $payload);

        $payload = unpack('c1option/V1min/V1max', $data);

        $result['option'] = chr($payload['option']);
        $result['min'] = IPConnection::fixUnpackedInt32($payload['min']);
        $result['max'] = IPConnection::fixUnpackedInt32($payload['max']);

        return $result;
    }

    /**
     * Sets the thresholds for the BrickletPTC::CALLBACK_RESISTANCE_REACHED callback. 
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
    public function setResistanceCallbackThreshold($option, $min, $max)
    {
        $payload = '';
        $payload .= pack('c', ord($option));
        $payload .= pack('v', $min);
        $payload .= pack('v', $max);

        $this->sendRequest(self::FUNCTION_SET_RESISTANCE_CALLBACK_THRESHOLD, $payload);
    }

    /**
     * Returns the threshold as set by BrickletPTC::setResistanceCallbackThreshold().
     * 
     * 
     * @return array
     */
    public function getResistanceCallbackThreshold()
    {
        $result = array();

        $payload = '';

        $data = $this->sendRequest(self::FUNCTION_GET_RESISTANCE_CALLBACK_THRESHOLD, $payload);

        $payload = unpack('c1option/v1min/v1max', $data);

        $result['option'] = chr($payload['option']);
        $result['min'] = $payload['min'];
        $result['max'] = $payload['max'];

        return $result;
    }

    /**
     * Sets the period in ms with which the threshold callback
     * 
     * * BrickletPTC::CALLBACK_TEMPERATURE_REACHED,
     * * BrickletPTC::CALLBACK_RESISTANCE_REACHED
     * 
     * is triggered, if the threshold
     * 
     * * BrickletPTC::setTemperatureCallbackThreshold(),
     * * BrickletPTC::setResistanceCallbackThreshold()
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
     * Returns the debounce period as set by BrickletPTC::setDebouncePeriod().
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
     * Sets the noise rejection filter to either 50Hz (0) or 60Hz (1).
     * Noise from 50Hz or 60Hz power sources (including
     * harmonics of the AC power's fundamental frequency) is
     * attenuated by 82dB.
     * 
     * Default value is 0 = 50Hz.
     * 
     * @param int $filter
     * 
     * @return void
     */
    public function setNoiseRejectionFilter($filter)
    {
        $payload = '';
        $payload .= pack('C', $filter);

        $this->sendRequest(self::FUNCTION_SET_NOISE_REJECTION_FILTER, $payload);
    }

    /**
     * Returns the noise rejection filter option as set by 
     * BrickletPTC::setNoiseRejectionFilter()
     * 
     * 
     * @return int
     */
    public function getNoiseRejectionFilter()
    {
        $payload = '';

        $data = $this->sendRequest(self::FUNCTION_GET_NOISE_REJECTION_FILTER, $payload);

        $payload = unpack('C1filter', $data);

        return $payload['filter'];
    }

    /**
     * Returns *true* if the sensor is connected correctly. 
     * 
     * If this function
     * returns *false*, there is either no Pt100 or Pt1000 sensor connected,
     * the sensor is connected incorrectly or the sensor itself is faulty.
     * 
     * 
     * @return bool
     */
    public function isSensorConnected()
    {
        $payload = '';

        $data = $this->sendRequest(self::FUNCTION_IS_SENSOR_CONNECTED, $payload);

        $payload = unpack('C1connected', $data);

        return (bool)$payload['connected'];
    }

    /**
     * Sets the wire mode of the sensor. Possible values are 2, 3 and 4 which
     * correspond to 2-, 3- and 4-wire sensors. The value has to match the jumper
     * configuration on the Bricklet.
     * 
     * The default value is 2 = 2-wire.
     * 
     * @param int $mode
     * 
     * @return void
     */
    public function setWireMode($mode)
    {
        $payload = '';
        $payload .= pack('C', $mode);

        $this->sendRequest(self::FUNCTION_SET_WIRE_MODE, $payload);
    }

    /**
     * Returns the wire mode as set by BrickletPTC::setWireMode()
     * 
     * 
     * @return int
     */
    public function getWireMode()
    {
        $payload = '';

        $data = $this->sendRequest(self::FUNCTION_GET_WIRE_MODE, $payload);

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
        $payload = unpack('V1temperature', $data);

        array_push($result, IPConnection::fixUnpackedInt32($payload['temperature']));

        call_user_func_array($this->registeredCallbacks[self::CALLBACK_TEMPERATURE], $result);
    }

    /**
     * @internal
     * @param string $data
     */
    public function callbackWrapperTemperatureReached($data)
    {
        $result = array();
        $payload = unpack('V1temperature', $data);

        array_push($result, IPConnection::fixUnpackedInt32($payload['temperature']));

        call_user_func_array($this->registeredCallbacks[self::CALLBACK_TEMPERATURE_REACHED], $result);
    }

    /**
     * @internal
     * @param string $data
     */
    public function callbackWrapperResistance($data)
    {
        $result = array();
        $payload = unpack('v1resistance', $data);

        array_push($result, $payload['resistance']);

        call_user_func_array($this->registeredCallbacks[self::CALLBACK_RESISTANCE], $result);
    }

    /**
     * @internal
     * @param string $data
     */
    public function callbackWrapperResistanceReached($data)
    {
        $result = array();
        $payload = unpack('v1resistance', $data);

        array_push($result, $payload['resistance']);

        call_user_func_array($this->registeredCallbacks[self::CALLBACK_RESISTANCE_REACHED], $result);
    }
}

?>
