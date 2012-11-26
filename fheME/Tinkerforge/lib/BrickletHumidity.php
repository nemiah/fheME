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
 * Device for sensing Humidity
 */
class BrickletHumidity extends Device
{

    /**
     * This callback is triggered periodically with the period that is set by
     * BrickletHumidity::setHumidityCallbackPeriod(). The parameter is the humidity of the
     * sensor.
     * 
     * BrickletHumidity::CALLBACK_HUMIDITY is only triggered if the humidity has changed since the
     * last triggering.
     */
    const CALLBACK_HUMIDITY = 13;

    /**
     * This callback is triggered periodically with the period that is set by
     * BrickletHumidity::setAnalogValueCallbackPeriod(). The parameter is the analog value of the
     * sensor.
     * 
     * BrickletHumidity::CALLBACK_ANALOG_VALUE is only triggered if the humidity has changed since the
     * last triggering.
     */
    const CALLBACK_ANALOG_VALUE = 14;

    /**
     * This callback is triggered when the threshold as set by
     * BrickletHumidity::setHumidityCallbackThreshold() is reached.
     * The parameter is the humidity of the sensor.
     * 
     * If the threshold keeps being reached, the callback is triggered periodically
     * with the period as set by BrickletHumidity::setDebouncePeriod().
     */
    const CALLBACK_HUMIDITY_REACHED = 15;

    /**
     * This callback is triggered when the threshold as set by
     * BrickletHumidity::setAnalogValueCallbackThreshold() is reached.
     * The parameter is the analog value of the sensor.
     * 
     * If the threshold keeps being reached, the callback is triggered periodically
     * with the period as set by BrickletHumidity::setDebouncePeriod().
     */
    const CALLBACK_ANALOG_VALUE_REACHED = 16;


    /**
     * @internal
     */
    const FUNCTION_GET_HUMIDITY = 1;

    /**
     * @internal
     */
    const FUNCTION_GET_ANALOG_VALUE = 2;

    /**
     * @internal
     */
    const FUNCTION_SET_HUMIDITY_CALLBACK_PERIOD = 3;

    /**
     * @internal
     */
    const FUNCTION_GET_HUMIDITY_CALLBACK_PERIOD = 4;

    /**
     * @internal
     */
    const FUNCTION_SET_ANALOG_VALUE_CALLBACK_PERIOD = 5;

    /**
     * @internal
     */
    const FUNCTION_GET_ANALOG_VALUE_CALLBACK_PERIOD = 6;

    /**
     * @internal
     */
    const FUNCTION_SET_HUMIDITY_CALLBACK_THRESHOLD = 7;

    /**
     * @internal
     */
    const FUNCTION_GET_HUMIDITY_CALLBACK_THRESHOLD = 8;

    /**
     * @internal
     */
    const FUNCTION_SET_ANALOG_VALUE_CALLBACK_THRESHOLD = 9;

    /**
     * @internal
     */
    const FUNCTION_GET_ANALOG_VALUE_CALLBACK_THRESHOLD = 10;

    /**
     * @internal
     */
    const FUNCTION_SET_DEBOUNCE_PERIOD = 11;

    /**
     * @internal
     */
    const FUNCTION_GET_DEBOUNCE_PERIOD = 12;

    /**
     * Creates an object with the unique device ID $uid. This object can
     * then be added to the IP connection.
     *
     * @param string $uid
     */
    public function __construct($uid)
    {
        parent::__construct($uid);

        $this->expectedName = 'Humidity Bricklet';

        $this->bindingVersion = array(1, 0, 0);

        $this->callbackWrappers[self::CALLBACK_HUMIDITY] = 'callbackWrapperHumidity';
        $this->callbackWrappers[self::CALLBACK_ANALOG_VALUE] = 'callbackWrapperAnalogValue';
        $this->callbackWrappers[self::CALLBACK_HUMIDITY_REACHED] = 'callbackWrapperHumidityReached';
        $this->callbackWrappers[self::CALLBACK_ANALOG_VALUE_REACHED] = 'callbackWrapperAnalogValueReached';
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
     * Returns the humidity of the sensor. The value
     * has a range of 0 to 1000 and is given in %RH/10 (Relative Humidity), 
     * i.e. a value of 421 means that a humidity of 42.1 %RH is measured.
     * 
     * If you want to get the humidity periodically, it is recommended to use the
     * callback BrickletHumidity::CALLBACK_HUMIDITY and set the period with 
     * BrickletHumidity::setHumidityCallbackPeriod().
     * 
     * 
     * @return int
     */
    public function getHumidity()
    {
        $payload = '';

        $data = $this->sendRequestExpectResponse(self::FUNCTION_GET_HUMIDITY, $payload, 2);

        $payload = unpack('v1humidity', $data);

        return $payload['humidity'];
    }

    /**
     * Returns the value as read by a 12-bit analog-to-digital converter.
     * The value is between 0 and 4095.
     * 
     * <note>
     *  The value returned by BrickletHumidity::getHumidity() is averaged over several samples
     *  to yield less noise, while BrickletHumidity::getAnalogValue() gives back raw
     *  unfiltered analog values. The returned humidity value is calibrated for
     *  room temperatures, if you use the sensor in extreme cold or extreme
     *  warm environments, you might want to calculate the humidity from
     *  the analog value yourself. See the `HIH 5030 datasheet
     *  <https://github.com/Tinkerforge/humidity-bricklet/raw/master/datasheets/hih-5030.pdf>`__.
     * </note>
     * 
     * If you want the analog value periodically, it is recommended to use the 
     * callback BrickletHumidity::CALLBACK_ANALOG_VALUE and set the period with 
     * BrickletHumidity::setAnalogValueCallbackPeriod().
     * 
     * 
     * @return int
     */
    public function getAnalogValue()
    {
        $payload = '';

        $data = $this->sendRequestExpectResponse(self::FUNCTION_GET_ANALOG_VALUE, $payload, 2);

        $payload = unpack('v1value', $data);

        return $payload['value'];
    }

    /**
     * Sets the period in ms with which the BrickletHumidity::CALLBACK_HUMIDITY callback is triggered
     * periodically. A value of 0 turns the callback off.
     * 
     * BrickletHumidity::CALLBACK_HUMIDITY is only triggered if the humidity has changed since the
     * last triggering.
     * 
     * The default value is 0.
     * 
     * @param int $period
     * 
     * @return void
     */
    public function setHumidityCallbackPeriod($period)
    {
        $payload = '';
        $payload .= pack('V', $period);

        $this->sendRequestNoResponse(self::FUNCTION_SET_HUMIDITY_CALLBACK_PERIOD, $payload);
    }

    /**
     * Returns the period as set by BrickletHumidity::setHumidityCallbackPeriod().
     * 
     * 
     * @return int
     */
    public function getHumidityCallbackPeriod()
    {
        $payload = '';

        $data = $this->sendRequestExpectResponse(self::FUNCTION_GET_HUMIDITY_CALLBACK_PERIOD, $payload, 4);

        $payload = unpack('V1period', $data);

        return IPConnection::fixUnpackedUInt32($payload['period']);
    }

    /**
     * Sets the period in ms with which the BrickletHumidity::CALLBACK_ANALOG_VALUE callback is triggered
     * periodically. A value of 0 turns the callback off.
     * 
     * BrickletHumidity::CALLBACK_ANALOG_VALUE is only triggered if the analog value has changed since the
     * last triggering.
     * 
     * The default value is 0.
     * 
     * @param int $period
     * 
     * @return void
     */
    public function setAnalogValueCallbackPeriod($period)
    {
        $payload = '';
        $payload .= pack('V', $period);

        $this->sendRequestNoResponse(self::FUNCTION_SET_ANALOG_VALUE_CALLBACK_PERIOD, $payload);
    }

    /**
     * Returns the period as set by BrickletHumidity::setAnalogValueCallbackPeriod().
     * 
     * 
     * @return int
     */
    public function getAnalogValueCallbackPeriod()
    {
        $payload = '';

        $data = $this->sendRequestExpectResponse(self::FUNCTION_GET_ANALOG_VALUE_CALLBACK_PERIOD, $payload, 4);

        $payload = unpack('V1period', $data);

        return IPConnection::fixUnpackedUInt32($payload['period']);
    }

    /**
     * Sets the thresholds for the BrickletHumidity::CALLBACK_HUMIDITY_REACHED callback. 
     * 
     * The following options are possible:
     * 
     * <code>
     *  "Option", "Description"
     * 
     *  "'x'",    "Callback is turned off"
     *  "'o'",    "Callback is triggered when the humidity is *outside* the min and max values"
     *  "'i'",    "Callback is triggered when the humidity is *inside* the min and max values"
     *  "'<'",    "Callback is triggered when the humidity is smaller than the min value (max is ignored)"
     *  "'>'",    "Callback is triggered when the humidity is greater than the min value (max is ignored)"
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
    public function setHumidityCallbackThreshold($option, $min, $max)
    {
        $payload = '';
        $payload .= pack('c', ord($option));
        $payload .= pack('v', $min);
        $payload .= pack('v', $max);

        $this->sendRequestNoResponse(self::FUNCTION_SET_HUMIDITY_CALLBACK_THRESHOLD, $payload);
    }

    /**
     * Returns the threshold as set by BrickletHumidity::setHumidityCallbackThreshold().
     * 
     * 
     * @return array
     */
    public function getHumidityCallbackThreshold()
    {
        $result = array();

        $payload = '';

        $data = $this->sendRequestExpectResponse(self::FUNCTION_GET_HUMIDITY_CALLBACK_THRESHOLD, $payload, 5);

        $payload = unpack('c1option/v1min/v1max', $data);

        $result['option'] = chr($payload['option']);
        $result['min'] = IPConnection::fixUnpackedInt16($payload['min']);
        $result['max'] = IPConnection::fixUnpackedInt16($payload['max']);

        return $result;
    }

    /**
     * Sets the thresholds for the BrickletHumidity::CALLBACK_ANALOG_VALUE_REACHED callback. 
     * 
     * The following options are possible:
     * 
     * <code>
     *  "Option", "Description"
     * 
     *  "'x'",    "Callback is turned off"
     *  "'o'",    "Callback is triggered when the analog value is *outside* the min and max values"
     *  "'i'",    "Callback is triggered when the analog value is *inside* the min and max values"
     *  "'<'",    "Callback is triggered when the analog value is smaller than the min value (max is ignored)"
     *  "'>'",    "Callback is triggered when the analog value is greater than the min value (max is ignored)"
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
    public function setAnalogValueCallbackThreshold($option, $min, $max)
    {
        $payload = '';
        $payload .= pack('c', ord($option));
        $payload .= pack('v', $min);
        $payload .= pack('v', $max);

        $this->sendRequestNoResponse(self::FUNCTION_SET_ANALOG_VALUE_CALLBACK_THRESHOLD, $payload);
    }

    /**
     * Returns the threshold as set by BrickletHumidity::setAnalogValueCallbackThreshold().
     * 
     * 
     * @return array
     */
    public function getAnalogValueCallbackThreshold()
    {
        $result = array();

        $payload = '';

        $data = $this->sendRequestExpectResponse(self::FUNCTION_GET_ANALOG_VALUE_CALLBACK_THRESHOLD, $payload, 5);

        $payload = unpack('c1option/v1min/v1max', $data);

        $result['option'] = chr($payload['option']);
        $result['min'] = $payload['min'];
        $result['max'] = $payload['max'];

        return $result;
    }

    /**
     * Sets the period in ms with which the threshold callbacks
     * 
     *  BrickletHumidity::CALLBACK_HUMIDITY_REACHED, BrickletHumidity::CALLBACK_ANALOG_VALUE_REACHED
     * 
     * are triggered, if the thresholds
     * 
     *  BrickletHumidity::setHumidityCallbackThreshold(), BrickletHumidity::setAnalogValueCallbackThreshold()
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

        $this->sendRequestNoResponse(self::FUNCTION_SET_DEBOUNCE_PERIOD, $payload);
    }

    /**
     * Returns the debounce period as set by BrickletHumidity::setDebouncePeriod().
     * 
     * 
     * @return int
     */
    public function getDebouncePeriod()
    {
        $payload = '';

        $data = $this->sendRequestExpectResponse(self::FUNCTION_GET_DEBOUNCE_PERIOD, $payload, 4);

        $payload = unpack('V1debounce', $data);

        return IPConnection::fixUnpackedUInt32($payload['debounce']);
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
    public function callbackWrapperHumidity($data)
    {
        $result = array();
        $payload = unpack('v1humidity', $data);

        array_push($result, $payload['humidity']);

        call_user_func_array($this->registeredCallbacks[self::CALLBACK_HUMIDITY], $result);
    }

    /**
     * @internal
     * @param string $data
     */
    public function callbackWrapperAnalogValue($data)
    {
        $result = array();
        $payload = unpack('v1value', $data);

        array_push($result, $payload['value']);

        call_user_func_array($this->registeredCallbacks[self::CALLBACK_ANALOG_VALUE], $result);
    }

    /**
     * @internal
     * @param string $data
     */
    public function callbackWrapperHumidityReached($data)
    {
        $result = array();
        $payload = unpack('v1humidity', $data);

        array_push($result, $payload['humidity']);

        call_user_func_array($this->registeredCallbacks[self::CALLBACK_HUMIDITY_REACHED], $result);
    }

    /**
     * @internal
     * @param string $data
     */
    public function callbackWrapperAnalogValueReached($data)
    {
        $result = array();
        $payload = unpack('v1value', $data);

        array_push($result, $payload['value']);

        call_user_func_array($this->registeredCallbacks[self::CALLBACK_ANALOG_VALUE_REACHED], $result);
    }
}

?>
