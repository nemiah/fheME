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
 * Device for high precision sensing of voltage and current
 */
class BrickletVoltageCurrent extends Device
{

    /**
     * This callback is triggered periodically with the period that is set by
     * BrickletVoltageCurrent::setCurrentCallbackPeriod(). The parameter is the current of the
     * sensor.
     * 
     * BrickletVoltageCurrent::CALLBACK_CURRENT is only triggered if the current has changed since the
     * last triggering.
     */
    const CALLBACK_CURRENT = 22;

    /**
     * This callback is triggered periodically with the period that is set by
     * BrickletVoltageCurrent::setVoltageCallbackPeriod(). The parameter is the voltage of the
     * sensor.
     * 
     * BrickletVoltageCurrent::CALLBACK_VOLTAGE is only triggered if the voltage has changed since the
     * last triggering.
     */
    const CALLBACK_VOLTAGE = 23;

    /**
     * This callback is triggered periodically with the period that is set by
     * BrickletVoltageCurrent::setPowerCallbackPeriod(). The parameter is the power of the
     * sensor.
     * 
     * BrickletVoltageCurrent::CALLBACK_POWER is only triggered if the power has changed since the
     * last triggering.
     */
    const CALLBACK_POWER = 24;

    /**
     * This callback is triggered when the threshold as set by
     * BrickletVoltageCurrent::setCurrentCallbackThreshold() is reached.
     * The parameter is the current of the sensor.
     * 
     * If the threshold keeps being reached, the callback is triggered periodically
     * with the period as set by BrickletVoltageCurrent::setDebouncePeriod().
     */
    const CALLBACK_CURRENT_REACHED = 25;

    /**
     * This callback is triggered when the threshold as set by
     * BrickletVoltageCurrent::setVoltageCallbackThreshold() is reached.
     * The parameter is the voltage of the sensor.
     * 
     * If the threshold keeps being reached, the callback is triggered periodically
     * with the period as set by BrickletVoltageCurrent::setDebouncePeriod().
     */
    const CALLBACK_VOLTAGE_REACHED = 26;

    /**
     * This callback is triggered when the threshold as set by
     * BrickletVoltageCurrent::setPowerCallbackThreshold() is reached.
     * The parameter is the power of the sensor.
     * 
     * If the threshold keeps being reached, the callback is triggered periodically
     * with the period as set by BrickletVoltageCurrent::setDebouncePeriod().
     */
    const CALLBACK_POWER_REACHED = 27;


    /**
     * @internal
     */
    const FUNCTION_GET_CURRENT = 1;

    /**
     * @internal
     */
    const FUNCTION_GET_VOLTAGE = 2;

    /**
     * @internal
     */
    const FUNCTION_GET_POWER = 3;

    /**
     * @internal
     */
    const FUNCTION_SET_CONFIGURATION = 4;

    /**
     * @internal
     */
    const FUNCTION_GET_CONFIGURATION = 5;

    /**
     * @internal
     */
    const FUNCTION_SET_CALIBRATION = 6;

    /**
     * @internal
     */
    const FUNCTION_GET_CALIBRATION = 7;

    /**
     * @internal
     */
    const FUNCTION_SET_CURRENT_CALLBACK_PERIOD = 8;

    /**
     * @internal
     */
    const FUNCTION_GET_CURRENT_CALLBACK_PERIOD = 9;

    /**
     * @internal
     */
    const FUNCTION_SET_VOLTAGE_CALLBACK_PERIOD = 10;

    /**
     * @internal
     */
    const FUNCTION_GET_VOLTAGE_CALLBACK_PERIOD = 11;

    /**
     * @internal
     */
    const FUNCTION_SET_POWER_CALLBACK_PERIOD = 12;

    /**
     * @internal
     */
    const FUNCTION_GET_POWER_CALLBACK_PERIOD = 13;

    /**
     * @internal
     */
    const FUNCTION_SET_CURRENT_CALLBACK_THRESHOLD = 14;

    /**
     * @internal
     */
    const FUNCTION_GET_CURRENT_CALLBACK_THRESHOLD = 15;

    /**
     * @internal
     */
    const FUNCTION_SET_VOLTAGE_CALLBACK_THRESHOLD = 16;

    /**
     * @internal
     */
    const FUNCTION_GET_VOLTAGE_CALLBACK_THRESHOLD = 17;

    /**
     * @internal
     */
    const FUNCTION_SET_POWER_CALLBACK_THRESHOLD = 18;

    /**
     * @internal
     */
    const FUNCTION_GET_POWER_CALLBACK_THRESHOLD = 19;

    /**
     * @internal
     */
    const FUNCTION_SET_DEBOUNCE_PERIOD = 20;

    /**
     * @internal
     */
    const FUNCTION_GET_DEBOUNCE_PERIOD = 21;

    /**
     * @internal
     */
    const FUNCTION_GET_IDENTITY = 255;

    const AVERAGING_1 = 0;
    const AVERAGING_4 = 1;
    const AVERAGING_16 = 2;
    const AVERAGING_64 = 3;
    const AVERAGING_128 = 4;
    const AVERAGING_256 = 5;
    const AVERAGING_512 = 6;
    const AVERAGING_1024 = 7;
    const THRESHOLD_OPTION_OFF = 'x';
    const THRESHOLD_OPTION_OUTSIDE = 'o';
    const THRESHOLD_OPTION_INSIDE = 'i';
    const THRESHOLD_OPTION_SMALLER = '<';
    const THRESHOLD_OPTION_GREATER = '>';

    const DEVICE_IDENTIFIER = 227;

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
        $this->responseExpected[self::FUNCTION_GET_VOLTAGE] = self::RESPONSE_EXPECTED_ALWAYS_TRUE;
        $this->responseExpected[self::FUNCTION_GET_POWER] = self::RESPONSE_EXPECTED_ALWAYS_TRUE;
        $this->responseExpected[self::FUNCTION_SET_CONFIGURATION] = self::RESPONSE_EXPECTED_FALSE;
        $this->responseExpected[self::FUNCTION_GET_CONFIGURATION] = self::RESPONSE_EXPECTED_ALWAYS_TRUE;
        $this->responseExpected[self::FUNCTION_SET_CALIBRATION] = self::RESPONSE_EXPECTED_FALSE;
        $this->responseExpected[self::FUNCTION_GET_CALIBRATION] = self::RESPONSE_EXPECTED_ALWAYS_TRUE;
        $this->responseExpected[self::FUNCTION_SET_CURRENT_CALLBACK_PERIOD] = self::RESPONSE_EXPECTED_TRUE;
        $this->responseExpected[self::FUNCTION_GET_CURRENT_CALLBACK_PERIOD] = self::RESPONSE_EXPECTED_ALWAYS_TRUE;
        $this->responseExpected[self::FUNCTION_SET_VOLTAGE_CALLBACK_PERIOD] = self::RESPONSE_EXPECTED_TRUE;
        $this->responseExpected[self::FUNCTION_GET_VOLTAGE_CALLBACK_PERIOD] = self::RESPONSE_EXPECTED_ALWAYS_TRUE;
        $this->responseExpected[self::FUNCTION_SET_POWER_CALLBACK_PERIOD] = self::RESPONSE_EXPECTED_TRUE;
        $this->responseExpected[self::FUNCTION_GET_POWER_CALLBACK_PERIOD] = self::RESPONSE_EXPECTED_ALWAYS_TRUE;
        $this->responseExpected[self::FUNCTION_SET_CURRENT_CALLBACK_THRESHOLD] = self::RESPONSE_EXPECTED_TRUE;
        $this->responseExpected[self::FUNCTION_GET_CURRENT_CALLBACK_THRESHOLD] = self::RESPONSE_EXPECTED_ALWAYS_TRUE;
        $this->responseExpected[self::FUNCTION_SET_VOLTAGE_CALLBACK_THRESHOLD] = self::RESPONSE_EXPECTED_TRUE;
        $this->responseExpected[self::FUNCTION_GET_VOLTAGE_CALLBACK_THRESHOLD] = self::RESPONSE_EXPECTED_ALWAYS_TRUE;
        $this->responseExpected[self::FUNCTION_SET_POWER_CALLBACK_THRESHOLD] = self::RESPONSE_EXPECTED_TRUE;
        $this->responseExpected[self::FUNCTION_GET_POWER_CALLBACK_THRESHOLD] = self::RESPONSE_EXPECTED_ALWAYS_TRUE;
        $this->responseExpected[self::FUNCTION_SET_DEBOUNCE_PERIOD] = self::RESPONSE_EXPECTED_TRUE;
        $this->responseExpected[self::FUNCTION_GET_DEBOUNCE_PERIOD] = self::RESPONSE_EXPECTED_ALWAYS_TRUE;
        $this->responseExpected[self::CALLBACK_CURRENT] = self::RESPONSE_EXPECTED_ALWAYS_FALSE;
        $this->responseExpected[self::CALLBACK_VOLTAGE] = self::RESPONSE_EXPECTED_ALWAYS_FALSE;
        $this->responseExpected[self::CALLBACK_POWER] = self::RESPONSE_EXPECTED_ALWAYS_FALSE;
        $this->responseExpected[self::CALLBACK_CURRENT_REACHED] = self::RESPONSE_EXPECTED_ALWAYS_FALSE;
        $this->responseExpected[self::CALLBACK_VOLTAGE_REACHED] = self::RESPONSE_EXPECTED_ALWAYS_FALSE;
        $this->responseExpected[self::CALLBACK_POWER_REACHED] = self::RESPONSE_EXPECTED_ALWAYS_FALSE;
        $this->responseExpected[self::FUNCTION_GET_IDENTITY] = self::RESPONSE_EXPECTED_ALWAYS_TRUE;

        $this->callbackWrappers[self::CALLBACK_CURRENT] = 'callbackWrapperCurrent';
        $this->callbackWrappers[self::CALLBACK_VOLTAGE] = 'callbackWrapperVoltage';
        $this->callbackWrappers[self::CALLBACK_POWER] = 'callbackWrapperPower';
        $this->callbackWrappers[self::CALLBACK_CURRENT_REACHED] = 'callbackWrapperCurrentReached';
        $this->callbackWrappers[self::CALLBACK_VOLTAGE_REACHED] = 'callbackWrapperVoltageReached';
        $this->callbackWrappers[self::CALLBACK_POWER_REACHED] = 'callbackWrapperPowerReached';
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
     * Returns the current. The value is in mA
     * and between 0mA and 20000mA.
     * 
     * If you want to get the current periodically, it is recommended to use the
     * callback BrickletVoltageCurrent::CALLBACK_CURRENT and set the period with 
     * BrickletVoltageCurrent::setCurrentCallbackPeriod().
     * 
     * 
     * @return int
     */
    public function getCurrent()
    {
        $payload = '';

        $data = $this->sendRequest(self::FUNCTION_GET_CURRENT, $payload);

        $payload = unpack('V1current', $data);

        return IPConnection::fixUnpackedInt32($payload['current']);
    }

    /**
     * Returns the voltage. The value is in mV
     * and between 0mV and 36000mV.
     * 
     * If you want to get the voltage periodically, it is recommended to use the
     * callback BrickletVoltageCurrent::CALLBACK_VOLTAGE and set the period with 
     * BrickletVoltageCurrent::setVoltageCallbackPeriod().
     * 
     * 
     * @return int
     */
    public function getVoltage()
    {
        $payload = '';

        $data = $this->sendRequest(self::FUNCTION_GET_VOLTAGE, $payload);

        $payload = unpack('V1voltage', $data);

        return IPConnection::fixUnpackedInt32($payload['voltage']);
    }

    /**
     * Returns the power. The value is in mW
     * and between 0mV and 720000mW.
     * 
     * If you want to get the power periodically, it is recommended to use the
     * callback BrickletVoltageCurrent::CALLBACK_POWER and set the period with 
     * BrickletVoltageCurrent::setPowerCallbackPeriod().
     * 
     * 
     * @return int
     */
    public function getPower()
    {
        $payload = '';

        $data = $this->sendRequest(self::FUNCTION_GET_POWER, $payload);

        $payload = unpack('V1power', $data);

        return IPConnection::fixUnpackedInt32($payload['power']);
    }

    /**
     * Sets the configuration of the Voltage/Current Bricklet. It is
     * possible to configure number of averages as well as
     * voltage and current conversion time.
     * 
     * Averaging:
     * 
     * <code>
     *  "Value", "Number of Averages"
     * 
     *  "0",    "1"
     *  "1",    "4"
     *  "2",    "16"
     *  "3",    "64"
     *  "4",    "128"
     *  "5",    "256"
     *  "6",    "512"
     *  ">=7",  "1024"
     * </code>
     * 
     * Voltage/Current conversion:
     * 
     * <code>
     *  "Value", "Conversion time"
     * 
     *  "0",    "140µs"
     *  "1",    "204µs"
     *  "2",    "332µs"
     *  "3",    "588µs"
     *  "4",    "1.1ms"
     *  "5",    "2.116ms"
     *  "6",    "4.156ms"
     *  ">=7",  "8.244ms"
     * </code>
     * 
     * The default values are 3, 4 and 4 (64, 1.1ms, 1.1ms) for averaging, voltage 
     * conversion and current conversion.
     * 
     * @param int $averaging
     * @param int $voltage_conversion_time
     * @param int $current_conversion_time
     * 
     * @return void
     */
    public function setConfiguration($averaging, $voltage_conversion_time, $current_conversion_time)
    {
        $payload = '';
        $payload .= pack('C', $averaging);
        $payload .= pack('C', $voltage_conversion_time);
        $payload .= pack('C', $current_conversion_time);

        $this->sendRequest(self::FUNCTION_SET_CONFIGURATION, $payload);
    }

    /**
     * Returns the configuration as set by BrickletVoltageCurrent::setConfiguration().
     * 
     * 
     * @return array
     */
    public function getConfiguration()
    {
        $result = array();

        $payload = '';

        $data = $this->sendRequest(self::FUNCTION_GET_CONFIGURATION, $payload);

        $payload = unpack('C1averaging/C1voltage_conversion_time/C1current_conversion_time', $data);

        $result['averaging'] = $payload['averaging'];
        $result['voltage_conversion_time'] = $payload['voltage_conversion_time'];
        $result['current_conversion_time'] = $payload['current_conversion_time'];

        return $result;
    }

    /**
     * Since the shunt resistor that is used to measure the current is not
     * perfectly precise, it needs to be calibrated by a multiplier and
     * divisor if a very precise reading is needed.
     * 
     * For example, if you are expecting a measurement of 1000mA and you
     * are measuring 1023mA, you can calibrate the Voltage/Current Bricklet 
     * by setting the multiplier to 1000 and the divisor to 1023.
     * 
     * @param int $gain_multiplier
     * @param int $gain_divisor
     * 
     * @return void
     */
    public function setCalibration($gain_multiplier, $gain_divisor)
    {
        $payload = '';
        $payload .= pack('v', $gain_multiplier);
        $payload .= pack('v', $gain_divisor);

        $this->sendRequest(self::FUNCTION_SET_CALIBRATION, $payload);
    }

    /**
     * Returns the calibration as set by BrickletVoltageCurrent::setCalibration().
     * 
     * 
     * @return array
     */
    public function getCalibration()
    {
        $result = array();

        $payload = '';

        $data = $this->sendRequest(self::FUNCTION_GET_CALIBRATION, $payload);

        $payload = unpack('v1gain_multiplier/v1gain_divisor', $data);

        $result['gain_multiplier'] = $payload['gain_multiplier'];
        $result['gain_divisor'] = $payload['gain_divisor'];

        return $result;
    }

    /**
     * Sets the period in ms with which the BrickletVoltageCurrent::CALLBACK_CURRENT callback is triggered
     * periodically. A value of 0 turns the callback off.
     * 
     * BrickletVoltageCurrent::CALLBACK_CURRENT is only triggered if the current has changed since the
     * last triggering.
     * 
     * The default value is 0.
     * 
     * @param int $period
     * 
     * @return void
     */
    public function setCurrentCallbackPeriod($period)
    {
        $payload = '';
        $payload .= pack('V', $period);

        $this->sendRequest(self::FUNCTION_SET_CURRENT_CALLBACK_PERIOD, $payload);
    }

    /**
     * Returns the period as set by BrickletVoltageCurrent::setCurrentCallbackPeriod().
     * 
     * 
     * @return int
     */
    public function getCurrentCallbackPeriod()
    {
        $payload = '';

        $data = $this->sendRequest(self::FUNCTION_GET_CURRENT_CALLBACK_PERIOD, $payload);

        $payload = unpack('V1period', $data);

        return IPConnection::fixUnpackedUInt32($payload['period']);
    }

    /**
     * Sets the period in ms with which the BrickletVoltageCurrent::CALLBACK_VOLTAGE callback is triggered
     * periodically. A value of 0 turns the callback off.
     * 
     * BrickletVoltageCurrent::CALLBACK_VOLTAGE is only triggered if the voltage has changed since the
     * last triggering.
     * 
     * The default value is 0.
     * 
     * @param int $period
     * 
     * @return void
     */
    public function setVoltageCallbackPeriod($period)
    {
        $payload = '';
        $payload .= pack('V', $period);

        $this->sendRequest(self::FUNCTION_SET_VOLTAGE_CALLBACK_PERIOD, $payload);
    }

    /**
     * Returns the period as set by BrickletVoltageCurrent::setVoltageCallbackPeriod().
     * 
     * 
     * @return int
     */
    public function getVoltageCallbackPeriod()
    {
        $payload = '';

        $data = $this->sendRequest(self::FUNCTION_GET_VOLTAGE_CALLBACK_PERIOD, $payload);

        $payload = unpack('V1period', $data);

        return IPConnection::fixUnpackedUInt32($payload['period']);
    }

    /**
     * Sets the period in ms with which the BrickletVoltageCurrent::CALLBACK_POWER callback is triggered
     * periodically. A value of 0 turns the callback off.
     * 
     * BrickletVoltageCurrent::CALLBACK_POWER is only triggered if the power has changed since the
     * last triggering.
     * 
     * The default value is 0.
     * 
     * @param int $period
     * 
     * @return void
     */
    public function setPowerCallbackPeriod($period)
    {
        $payload = '';
        $payload .= pack('V', $period);

        $this->sendRequest(self::FUNCTION_SET_POWER_CALLBACK_PERIOD, $payload);
    }

    /**
     * Returns the period as set by BrickletVoltageCurrent::getPowerCallbackPeriod().
     * 
     * 
     * @return int
     */
    public function getPowerCallbackPeriod()
    {
        $payload = '';

        $data = $this->sendRequest(self::FUNCTION_GET_POWER_CALLBACK_PERIOD, $payload);

        $payload = unpack('V1period', $data);

        return IPConnection::fixUnpackedUInt32($payload['period']);
    }

    /**
     * Sets the thresholds for the BrickletVoltageCurrent::CALLBACK_CURRENT_REACHED callback. 
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
     * @param string $option
     * @param int $min
     * @param int $max
     * 
     * @return void
     */
    public function setCurrentCallbackThreshold($option, $min, $max)
    {
        $payload = '';
        $payload .= pack('c', ord($option));
        $payload .= pack('V', $min);
        $payload .= pack('V', $max);

        $this->sendRequest(self::FUNCTION_SET_CURRENT_CALLBACK_THRESHOLD, $payload);
    }

    /**
     * Returns the threshold as set by BrickletVoltageCurrent::setCurrentCallbackThreshold().
     * 
     * 
     * @return array
     */
    public function getCurrentCallbackThreshold()
    {
        $result = array();

        $payload = '';

        $data = $this->sendRequest(self::FUNCTION_GET_CURRENT_CALLBACK_THRESHOLD, $payload);

        $payload = unpack('c1option/V1min/V1max', $data);

        $result['option'] = chr($payload['option']);
        $result['min'] = IPConnection::fixUnpackedInt32($payload['min']);
        $result['max'] = IPConnection::fixUnpackedInt32($payload['max']);

        return $result;
    }

    /**
     * Sets the thresholds for the BrickletVoltageCurrent::CALLBACK_VOLTAGE_REACHED callback. 
     * 
     * The following options are possible:
     * 
     * <code>
     *  "Option", "Description"
     * 
     *  "'x'",    "Callback is turned off"
     *  "'o'",    "Callback is triggered when the voltage is *outside* the min and max values"
     *  "'i'",    "Callback is triggered when the voltage is *inside* the min and max values"
     *  "'<'",    "Callback is triggered when the voltage is smaller than the min value (max is ignored)"
     *  "'>'",    "Callback is triggered when the voltage is greater than the min value (max is ignored)"
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
    public function setVoltageCallbackThreshold($option, $min, $max)
    {
        $payload = '';
        $payload .= pack('c', ord($option));
        $payload .= pack('V', $min);
        $payload .= pack('V', $max);

        $this->sendRequest(self::FUNCTION_SET_VOLTAGE_CALLBACK_THRESHOLD, $payload);
    }

    /**
     * Returns the threshold as set by BrickletVoltageCurrent::setVoltageCallbackThreshold().
     * 
     * 
     * @return array
     */
    public function getVoltageCallbackThreshold()
    {
        $result = array();

        $payload = '';

        $data = $this->sendRequest(self::FUNCTION_GET_VOLTAGE_CALLBACK_THRESHOLD, $payload);

        $payload = unpack('c1option/V1min/V1max', $data);

        $result['option'] = chr($payload['option']);
        $result['min'] = IPConnection::fixUnpackedInt32($payload['min']);
        $result['max'] = IPConnection::fixUnpackedInt32($payload['max']);

        return $result;
    }

    /**
     * Sets the thresholds for the BrickletVoltageCurrent::CALLBACK_POWER_REACHED callback. 
     * 
     * The following options are possible:
     * 
     * <code>
     *  "Option", "Description"
     * 
     *  "'x'",    "Callback is turned off"
     *  "'o'",    "Callback is triggered when the power is *outside* the min and max values"
     *  "'i'",    "Callback is triggered when the power is *inside* the min and max values"
     *  "'<'",    "Callback is triggered when the power is smaller than the min value (max is ignored)"
     *  "'>'",    "Callback is triggered when the power is greater than the min value (max is ignored)"
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
    public function setPowerCallbackThreshold($option, $min, $max)
    {
        $payload = '';
        $payload .= pack('c', ord($option));
        $payload .= pack('V', $min);
        $payload .= pack('V', $max);

        $this->sendRequest(self::FUNCTION_SET_POWER_CALLBACK_THRESHOLD, $payload);
    }

    /**
     * Returns the threshold as set by BrickletVoltageCurrent::setPowerCallbackThreshold().
     * 
     * 
     * @return array
     */
    public function getPowerCallbackThreshold()
    {
        $result = array();

        $payload = '';

        $data = $this->sendRequest(self::FUNCTION_GET_POWER_CALLBACK_THRESHOLD, $payload);

        $payload = unpack('c1option/V1min/V1max', $data);

        $result['option'] = chr($payload['option']);
        $result['min'] = IPConnection::fixUnpackedInt32($payload['min']);
        $result['max'] = IPConnection::fixUnpackedInt32($payload['max']);

        return $result;
    }

    /**
     * Sets the period in ms with which the threshold callbacks
     * 
     * * BrickletVoltageCurrent::CALLBACK_CURRENT_REACHED,
     * * BrickletVoltageCurrent::CALLBACK_VOLTAGE_REACHED,
     * * BrickletVoltageCurrent::CALLBACK_POWER_REACHED
     * 
     * are triggered, if the thresholds
     * 
     * * BrickletVoltageCurrent::setCurrentCallbackThreshold(),
     * * BrickletVoltageCurrent::setVoltageCallbackThreshold(),
     * * BrickletVoltageCurrent::setPowerCallbackThreshold()
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
     * Returns the debounce period as set by BrickletVoltageCurrent::setDebouncePeriod().
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
        $payload = unpack('V1current', $data);

        array_push($result, IPConnection::fixUnpackedInt32($payload['current']));

        call_user_func_array($this->registeredCallbacks[self::CALLBACK_CURRENT], $result);
    }

    /**
     * @internal
     * @param string $data
     */
    public function callbackWrapperVoltage($data)
    {
        $result = array();
        $payload = unpack('V1voltage', $data);

        array_push($result, IPConnection::fixUnpackedInt32($payload['voltage']));

        call_user_func_array($this->registeredCallbacks[self::CALLBACK_VOLTAGE], $result);
    }

    /**
     * @internal
     * @param string $data
     */
    public function callbackWrapperPower($data)
    {
        $result = array();
        $payload = unpack('V1power', $data);

        array_push($result, IPConnection::fixUnpackedInt32($payload['power']));

        call_user_func_array($this->registeredCallbacks[self::CALLBACK_POWER], $result);
    }

    /**
     * @internal
     * @param string $data
     */
    public function callbackWrapperCurrentReached($data)
    {
        $result = array();
        $payload = unpack('V1current', $data);

        array_push($result, IPConnection::fixUnpackedInt32($payload['current']));

        call_user_func_array($this->registeredCallbacks[self::CALLBACK_CURRENT_REACHED], $result);
    }

    /**
     * @internal
     * @param string $data
     */
    public function callbackWrapperVoltageReached($data)
    {
        $result = array();
        $payload = unpack('V1voltage', $data);

        array_push($result, IPConnection::fixUnpackedInt32($payload['voltage']));

        call_user_func_array($this->registeredCallbacks[self::CALLBACK_VOLTAGE_REACHED], $result);
    }

    /**
     * @internal
     * @param string $data
     */
    public function callbackWrapperPowerReached($data)
    {
        $result = array();
        $payload = unpack('V1power', $data);

        array_push($result, IPConnection::fixUnpackedInt32($payload['power']));

        call_user_func_array($this->registeredCallbacks[self::CALLBACK_POWER_REACHED], $result);
    }
}

?>
