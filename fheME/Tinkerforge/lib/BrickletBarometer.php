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
 * Device for sensing air pressure and altitude changes
 */
class BrickletBarometer extends Device
{

    /**
     * This callback is triggered periodically with the period that is set by
     * BrickletBarometer::setAirPressureCallbackPeriod(). The parameter is the air pressure of the
     * air pressure sensor.
     * 
     * BrickletBarometer::CALLBACK_AIR_PRESSURE is only triggered if the air pressure has changed since the
     * last triggering.
     */
    const CALLBACK_AIR_PRESSURE = 15;

    /**
     * This callback is triggered periodically with the period that is set by
     * BrickletBarometer::setAltitudeCallbackPeriod(). The parameter is the altitude of the
     * air pressure sensor.
     * 
     * BrickletBarometer::CALLBACK_ALTITUDE is only triggered if the altitude has changed since the
     * last triggering.
     */
    const CALLBACK_ALTITUDE = 16;

    /**
     * This callback is triggered when the threshold as set by
     * BrickletBarometer::setAirPressureCallbackThreshold() is reached.
     * The parameter is the air pressure of the air pressure sensor.
     * 
     * If the threshold keeps being reached, the callback is triggered periodically
     * with the period as set by BrickletBarometer::setDebouncePeriod().
     */
    const CALLBACK_AIR_PRESSURE_REACHED = 17;

    /**
     * This callback is triggered when the threshold as set by
     * BrickletBarometer::setAltitudeCallbackThreshold() is reached.
     * The parameter is the altitude of the air pressure sensor.
     * 
     * If the threshold keeps being reached, the callback is triggered periodically
     * with the period as set by BrickletBarometer::setDebouncePeriod().
     */
    const CALLBACK_ALTITUDE_REACHED = 18;


    /**
     * @internal
     */
    const FUNCTION_GET_AIR_PRESSURE = 1;

    /**
     * @internal
     */
    const FUNCTION_GET_ALTITUDE = 2;

    /**
     * @internal
     */
    const FUNCTION_SET_AIR_PRESSURE_CALLBACK_PERIOD = 3;

    /**
     * @internal
     */
    const FUNCTION_GET_AIR_PRESSURE_CALLBACK_PERIOD = 4;

    /**
     * @internal
     */
    const FUNCTION_SET_ALTITUDE_CALLBACK_PERIOD = 5;

    /**
     * @internal
     */
    const FUNCTION_GET_ALTITUDE_CALLBACK_PERIOD = 6;

    /**
     * @internal
     */
    const FUNCTION_SET_AIR_PRESSURE_CALLBACK_THRESHOLD = 7;

    /**
     * @internal
     */
    const FUNCTION_GET_AIR_PRESSURE_CALLBACK_THRESHOLD = 8;

    /**
     * @internal
     */
    const FUNCTION_SET_ALTITUDE_CALLBACK_THRESHOLD = 9;

    /**
     * @internal
     */
    const FUNCTION_GET_ALTITUDE_CALLBACK_THRESHOLD = 10;

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
    const FUNCTION_SET_REFERENCE_AIR_PRESSURE = 13;

    /**
     * @internal
     */
    const FUNCTION_GET_CHIP_TEMPERATURE = 14;

    /**
     * @internal
     */
    const FUNCTION_GET_REFERENCE_AIR_PRESSURE = 19;

    /**
     * @internal
     */
    const FUNCTION_SET_AVERAGING = 20;

    /**
     * @internal
     */
    const FUNCTION_GET_AVERAGING = 21;

    /**
     * @internal
     */
    const FUNCTION_GET_IDENTITY = 255;

    const THRESHOLD_OPTION_OFF = 'x';
    const THRESHOLD_OPTION_OUTSIDE = 'o';
    const THRESHOLD_OPTION_INSIDE = 'i';
    const THRESHOLD_OPTION_SMALLER = '<';
    const THRESHOLD_OPTION_GREATER = '>';

    const DEVICE_IDENTIFIER = 221;

    /**
     * Creates an object with the unique device ID $uid. This object can
     * then be added to the IP connection.
     *
     * @param string $uid
     */
    public function __construct($uid, $ipcon)
    {
        parent::__construct($uid, $ipcon);

        $this->apiVersion = array(2, 0, 1);

        $this->responseExpected[self::FUNCTION_GET_AIR_PRESSURE] = self::RESPONSE_EXPECTED_ALWAYS_TRUE;
        $this->responseExpected[self::FUNCTION_GET_ALTITUDE] = self::RESPONSE_EXPECTED_ALWAYS_TRUE;
        $this->responseExpected[self::FUNCTION_SET_AIR_PRESSURE_CALLBACK_PERIOD] = self::RESPONSE_EXPECTED_TRUE;
        $this->responseExpected[self::FUNCTION_GET_AIR_PRESSURE_CALLBACK_PERIOD] = self::RESPONSE_EXPECTED_ALWAYS_TRUE;
        $this->responseExpected[self::FUNCTION_SET_ALTITUDE_CALLBACK_PERIOD] = self::RESPONSE_EXPECTED_TRUE;
        $this->responseExpected[self::FUNCTION_GET_ALTITUDE_CALLBACK_PERIOD] = self::RESPONSE_EXPECTED_ALWAYS_TRUE;
        $this->responseExpected[self::FUNCTION_SET_AIR_PRESSURE_CALLBACK_THRESHOLD] = self::RESPONSE_EXPECTED_TRUE;
        $this->responseExpected[self::FUNCTION_GET_AIR_PRESSURE_CALLBACK_THRESHOLD] = self::RESPONSE_EXPECTED_ALWAYS_TRUE;
        $this->responseExpected[self::FUNCTION_SET_ALTITUDE_CALLBACK_THRESHOLD] = self::RESPONSE_EXPECTED_TRUE;
        $this->responseExpected[self::FUNCTION_GET_ALTITUDE_CALLBACK_THRESHOLD] = self::RESPONSE_EXPECTED_ALWAYS_TRUE;
        $this->responseExpected[self::FUNCTION_SET_DEBOUNCE_PERIOD] = self::RESPONSE_EXPECTED_TRUE;
        $this->responseExpected[self::FUNCTION_GET_DEBOUNCE_PERIOD] = self::RESPONSE_EXPECTED_ALWAYS_TRUE;
        $this->responseExpected[self::FUNCTION_SET_REFERENCE_AIR_PRESSURE] = self::RESPONSE_EXPECTED_FALSE;
        $this->responseExpected[self::FUNCTION_GET_CHIP_TEMPERATURE] = self::RESPONSE_EXPECTED_ALWAYS_TRUE;
        $this->responseExpected[self::CALLBACK_AIR_PRESSURE] = self::RESPONSE_EXPECTED_ALWAYS_FALSE;
        $this->responseExpected[self::CALLBACK_ALTITUDE] = self::RESPONSE_EXPECTED_ALWAYS_FALSE;
        $this->responseExpected[self::CALLBACK_AIR_PRESSURE_REACHED] = self::RESPONSE_EXPECTED_ALWAYS_FALSE;
        $this->responseExpected[self::CALLBACK_ALTITUDE_REACHED] = self::RESPONSE_EXPECTED_ALWAYS_FALSE;
        $this->responseExpected[self::FUNCTION_GET_REFERENCE_AIR_PRESSURE] = self::RESPONSE_EXPECTED_ALWAYS_TRUE;
        $this->responseExpected[self::FUNCTION_SET_AVERAGING] = self::RESPONSE_EXPECTED_FALSE;
        $this->responseExpected[self::FUNCTION_GET_AVERAGING] = self::RESPONSE_EXPECTED_ALWAYS_TRUE;
        $this->responseExpected[self::FUNCTION_GET_IDENTITY] = self::RESPONSE_EXPECTED_ALWAYS_TRUE;

        $this->callbackWrappers[self::CALLBACK_AIR_PRESSURE] = 'callbackWrapperAirPressure';
        $this->callbackWrappers[self::CALLBACK_ALTITUDE] = 'callbackWrapperAltitude';
        $this->callbackWrappers[self::CALLBACK_AIR_PRESSURE_REACHED] = 'callbackWrapperAirPressureReached';
        $this->callbackWrappers[self::CALLBACK_ALTITUDE_REACHED] = 'callbackWrapperAltitudeReached';
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
     * Returns the air pressure of the air pressure sensor. The value
     * has a range of 10000 to 1200000 and is given in mbar/1000, i.e. a value
     * of 1001092 means that an air pressure of 1001.092 mbar is measured.
     * 
     * If you want to get the air pressure periodically, it is recommended to use the
     * callback BrickletBarometer::CALLBACK_AIR_PRESSURE and set the period with
     * BrickletBarometer::setAirPressureCallbackPeriod().
     * 
     * 
     * @return int
     */
    public function getAirPressure()
    {
        $payload = '';

        $data = $this->sendRequest(self::FUNCTION_GET_AIR_PRESSURE, $payload);

        $payload = unpack('V1air_pressure', $data);

        return IPConnection::fixUnpackedInt32($payload['air_pressure']);
    }

    /**
     * Returns the relative altitude of the air pressure sensor. The value is given in
     * cm and is calculated based on the difference between the current air pressure
     * and the reference air pressure that can be set with BrickletBarometer::setReferenceAirPressure().
     * 
     * If you want to get the altitude periodically, it is recommended to use the
     * callback BrickletBarometer::CALLBACK_ALTITUDE and set the period with
     * BrickletBarometer::setAltitudeCallbackPeriod().
     * 
     * 
     * @return int
     */
    public function getAltitude()
    {
        $payload = '';

        $data = $this->sendRequest(self::FUNCTION_GET_ALTITUDE, $payload);

        $payload = unpack('V1altitude', $data);

        return IPConnection::fixUnpackedInt32($payload['altitude']);
    }

    /**
     * Sets the period in ms with which the BrickletBarometer::CALLBACK_AIR_PRESSURE callback is triggered
     * periodically. A value of 0 turns the callback off.
     * 
     * BrickletBarometer::CALLBACK_AIR_PRESSURE is only triggered if the air pressure has changed since the
     * last triggering.
     * 
     * The default value is 0.
     * 
     * @param int $period
     * 
     * @return void
     */
    public function setAirPressureCallbackPeriod($period)
    {
        $payload = '';
        $payload .= pack('V', $period);

        $this->sendRequest(self::FUNCTION_SET_AIR_PRESSURE_CALLBACK_PERIOD, $payload);
    }

    /**
     * Returns the period as set by BrickletBarometer::setAirPressureCallbackPeriod().
     * 
     * 
     * @return int
     */
    public function getAirPressureCallbackPeriod()
    {
        $payload = '';

        $data = $this->sendRequest(self::FUNCTION_GET_AIR_PRESSURE_CALLBACK_PERIOD, $payload);

        $payload = unpack('V1period', $data);

        return IPConnection::fixUnpackedUInt32($payload['period']);
    }

    /**
     * Sets the period in ms with which the BrickletBarometer::CALLBACK_ALTITUDE callback is triggered
     * periodically. A value of 0 turns the callback off.
     * 
     * BrickletBarometer::CALLBACK_ALTITUDE is only triggered if the altitude has changed since the
     * last triggering.
     * 
     * The default value is 0.
     * 
     * @param int $period
     * 
     * @return void
     */
    public function setAltitudeCallbackPeriod($period)
    {
        $payload = '';
        $payload .= pack('V', $period);

        $this->sendRequest(self::FUNCTION_SET_ALTITUDE_CALLBACK_PERIOD, $payload);
    }

    /**
     * Returns the period as set by BrickletBarometer::setAltitudeCallbackPeriod().
     * 
     * 
     * @return int
     */
    public function getAltitudeCallbackPeriod()
    {
        $payload = '';

        $data = $this->sendRequest(self::FUNCTION_GET_ALTITUDE_CALLBACK_PERIOD, $payload);

        $payload = unpack('V1period', $data);

        return IPConnection::fixUnpackedUInt32($payload['period']);
    }

    /**
     * Sets the thresholds for the BrickletBarometer::CALLBACK_AIR_PRESSURE_REACHED callback.
     * 
     * The following options are possible:
     * 
     * <code>
     *  "Option", "Description"
     * 
     *  "'x'",    "Callback is turned off"
     *  "'o'",    "Callback is triggered when the air pressure is *outside* the min and max values"
     *  "'i'",    "Callback is triggered when the air pressure is *inside* the min and max values"
     *  "'<'",    "Callback is triggered when the air pressure is smaller than the min value (max is ignored)"
     *  "'>'",    "Callback is triggered when the air pressure is greater than the min value (max is ignored)"
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
    public function setAirPressureCallbackThreshold($option, $min, $max)
    {
        $payload = '';
        $payload .= pack('c', ord($option));
        $payload .= pack('V', $min);
        $payload .= pack('V', $max);

        $this->sendRequest(self::FUNCTION_SET_AIR_PRESSURE_CALLBACK_THRESHOLD, $payload);
    }

    /**
     * Returns the threshold as set by BrickletBarometer::setAirPressureCallbackThreshold().
     * 
     * 
     * @return array
     */
    public function getAirPressureCallbackThreshold()
    {
        $result = array();

        $payload = '';

        $data = $this->sendRequest(self::FUNCTION_GET_AIR_PRESSURE_CALLBACK_THRESHOLD, $payload);

        $payload = unpack('c1option/V1min/V1max', $data);

        $result['option'] = chr($payload['option']);
        $result['min'] = IPConnection::fixUnpackedInt32($payload['min']);
        $result['max'] = IPConnection::fixUnpackedInt32($payload['max']);

        return $result;
    }

    /**
     * Sets the thresholds for the BrickletBarometer::CALLBACK_ALTITUDE_REACHED callback.
     * 
     * The following options are possible:
     * 
     * <code>
     *  "Option", "Description"
     * 
     *  "'x'",    "Callback is turned off"
     *  "'o'",    "Callback is triggered when the altitude is *outside* the min and max values"
     *  "'i'",    "Callback is triggered when the altitude is *inside* the min and max values"
     *  "'<'",    "Callback is triggered when the altitude is smaller than the min value (max is ignored)"
     *  "'>'",    "Callback is triggered when the altitude is greater than the min value (max is ignored)"
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
    public function setAltitudeCallbackThreshold($option, $min, $max)
    {
        $payload = '';
        $payload .= pack('c', ord($option));
        $payload .= pack('V', $min);
        $payload .= pack('V', $max);

        $this->sendRequest(self::FUNCTION_SET_ALTITUDE_CALLBACK_THRESHOLD, $payload);
    }

    /**
     * Returns the threshold as set by BrickletBarometer::setAltitudeCallbackThreshold().
     * 
     * 
     * @return array
     */
    public function getAltitudeCallbackThreshold()
    {
        $result = array();

        $payload = '';

        $data = $this->sendRequest(self::FUNCTION_GET_ALTITUDE_CALLBACK_THRESHOLD, $payload);

        $payload = unpack('c1option/V1min/V1max', $data);

        $result['option'] = chr($payload['option']);
        $result['min'] = IPConnection::fixUnpackedInt32($payload['min']);
        $result['max'] = IPConnection::fixUnpackedInt32($payload['max']);

        return $result;
    }

    /**
     * Sets the period in ms with which the threshold callbacks
     * 
     * * BrickletBarometer::CALLBACK_AIR_PRESSURE_REACHED,
     * * BrickletBarometer::CALLBACK_ALTITUDE_REACHED
     * 
     * are triggered, if the thresholds
     * 
     * * BrickletBarometer::setAirPressureCallbackThreshold(),
     * * BrickletBarometer::setAltitudeCallbackThreshold()
     * 
     * keep being reached.
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
     * Returns the debounce period as set by BrickletBarometer::setDebouncePeriod().
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
     * Sets the reference air pressure in mbar/1000 for the altitude calculation.
     * Setting the reference to the current air pressure results in a calculated
     * altitude of 0cm. Passing 0 is a shortcut for passing the current air pressure as
     * reference.
     * 
     * Well known reference values are the Q codes
     * `QNH <http://en.wikipedia.org/wiki/QNH>`__ and
     * `QFE <http://en.wikipedia.org/wiki/Mean_sea_level_pressure#Mean_sea_level_pressure>`__
     * used in aviation.
     * 
     * The default value is 1013.25mbar.
     * 
     * .. versionadded:: 1.1.0~(Plugin)
     * 
     * @param int $air_pressure
     * 
     * @return void
     */
    public function setReferenceAirPressure($air_pressure)
    {
        $payload = '';
        $payload .= pack('V', $air_pressure);

        $this->sendRequest(self::FUNCTION_SET_REFERENCE_AIR_PRESSURE, $payload);
    }

    /**
     * Returns the temperature of the air pressure sensor. The value
     * has a range of -4000 to 8500 and is given in °C/100, i.e. a value
     * of 2007 means that a temperature of 20.07 °C is measured.
     * 
     * This temperature is used internally for temperature compensation of the air
     * pressure measurement. It is not as accurate as the temperature measured by the
     * :ref:`temperature_bricklet` or the :ref:`temperature_ir_bricklet`.
     * 
     * 
     * @return int
     */
    public function getChipTemperature()
    {
        $payload = '';

        $data = $this->sendRequest(self::FUNCTION_GET_CHIP_TEMPERATURE, $payload);

        $payload = unpack('v1temperature', $data);

        return IPConnection::fixUnpackedInt16($payload['temperature']);
    }

    /**
     * Returns the reference air pressure as set by BrickletBarometer::setReferenceAirPressure().
     * 
     * .. versionadded:: 1.1.0~(Plugin)
     * 
     * 
     * @return int
     */
    public function getReferenceAirPressure()
    {
        $payload = '';

        $data = $this->sendRequest(self::FUNCTION_GET_REFERENCE_AIR_PRESSURE, $payload);

        $payload = unpack('V1air_pressure', $data);

        return IPConnection::fixUnpackedInt32($payload['air_pressure']);
    }

    /**
     * Sets the different averaging parameters. It is possible to set
     * the length of a normal averaging for the temperature and pressure,
     * as well as an additional length of a 
     * `moving average <http://en.wikipedia.org/wiki/Moving_average>`__ 
     * for the pressure. The moving average is calculated from the normal 
     * averages.  There is no moving average for the temperature.
     * 
     * The maximum length for the pressure average is 10, for the
     * temperature average is 255 and for the moving average is 25.
     * 
     * Setting the all three parameters to 0 will turn the averaging
     * completely off. If the averaging is off, there is lots of noise
     * on the data, but the data is without delay. Thus we recommend
     * to turn the averaging off if the Barometer Bricklet data is
     * to be used for sensor fusion with other sensors.
     * 
     * The default values are 10 for the normal averages and 25 for the
     * moving average.
     * 
     * .. versionadded:: 2.0.1~(Plugin)
     * 
     * @param int $moving_average_pressure
     * @param int $average_pressure
     * @param int $average_temperature
     * 
     * @return void
     */
    public function setAveraging($moving_average_pressure, $average_pressure, $average_temperature)
    {
        $payload = '';
        $payload .= pack('C', $moving_average_pressure);
        $payload .= pack('C', $average_pressure);
        $payload .= pack('C', $average_temperature);

        $this->sendRequest(self::FUNCTION_SET_AVERAGING, $payload);
    }

    /**
     * Returns the averaging configuration as set by BrickletBarometer::setAveraging().
     * 
     * .. versionadded:: 2.0.1~(Plugin)
     * 
     * 
     * @return array
     */
    public function getAveraging()
    {
        $result = array();

        $payload = '';

        $data = $this->sendRequest(self::FUNCTION_GET_AVERAGING, $payload);

        $payload = unpack('C1moving_average_pressure/C1average_pressure/C1average_temperature', $data);

        $result['moving_average_pressure'] = $payload['moving_average_pressure'];
        $result['average_pressure'] = $payload['average_pressure'];
        $result['average_temperature'] = $payload['average_temperature'];

        return $result;
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
    public function callbackWrapperAirPressure($data)
    {
        $result = array();
        $payload = unpack('V1air_pressure', $data);

        array_push($result, IPConnection::fixUnpackedInt32($payload['air_pressure']));

        call_user_func_array($this->registeredCallbacks[self::CALLBACK_AIR_PRESSURE], $result);
    }

    /**
     * @internal
     * @param string $data
     */
    public function callbackWrapperAltitude($data)
    {
        $result = array();
        $payload = unpack('V1altitude', $data);

        array_push($result, IPConnection::fixUnpackedInt32($payload['altitude']));

        call_user_func_array($this->registeredCallbacks[self::CALLBACK_ALTITUDE], $result);
    }

    /**
     * @internal
     * @param string $data
     */
    public function callbackWrapperAirPressureReached($data)
    {
        $result = array();
        $payload = unpack('V1air_pressure', $data);

        array_push($result, IPConnection::fixUnpackedInt32($payload['air_pressure']));

        call_user_func_array($this->registeredCallbacks[self::CALLBACK_AIR_PRESSURE_REACHED], $result);
    }

    /**
     * @internal
     * @param string $data
     */
    public function callbackWrapperAltitudeReached($data)
    {
        $result = array();
        $payload = unpack('V1altitude', $data);

        array_push($result, IPConnection::fixUnpackedInt32($payload['altitude']));

        call_user_func_array($this->registeredCallbacks[self::CALLBACK_ALTITUDE_REACHED], $result);
    }
}

?>
