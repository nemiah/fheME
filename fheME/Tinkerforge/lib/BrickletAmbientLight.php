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
 * Device for sensing Ambient Light
 */
class BrickletAmbientLight extends Device
{

    /**
     * This callback is triggered periodically with the period that is set by
     * BrickletAmbientLight::setIlluminanceCallbackPeriod(). The parameter is the illuminance of the
     * ambient light sensor.
     * 
     * BrickletAmbientLight::CALLBACK_ILLUMINANCE is only triggered if the illuminance has changed since the
     * last triggering.
     */
    const CALLBACK_ILLUMINANCE = 13;

    /**
     * This callback is triggered periodically with the period that is set by
     * BrickletAmbientLight::setAnalogValueCallbackPeriod(). The parameter is the analog value of the
     * ambient light sensor.
     * 
     * BrickletAmbientLight::CALLBACK_ANALOG_VALUE is only triggered if the analog value has changed since the
     * last triggering.
     */
    const CALLBACK_ANALOG_VALUE = 14;

    /**
     * This callback is triggered when the threshold as set by
     * BrickletAmbientLight::setIlluminanceCallbackThreshold() is reached.
     * The parameter is the illuminance of the ambient light sensor.
     * 
     * If the threshold keeps being reached, the callback is triggered periodically
     * with the period as set by BrickletAmbientLight::setDebouncePeriod().
     */
    const CALLBACK_ILLUMINANCE_REACHED = 15;

    /**
     * This callback is triggered when the threshold as set by
     * BrickletAmbientLight::setAnalogValueCallbackThreshold() is reached.
     * The parameter is the analog value of the ambient light sensor.
     * 
     * If the threshold keeps being reached, the callback is triggered periodically
     * with the period as set by BrickletAmbientLight::setDebouncePeriod().
     */
    const CALLBACK_ANALOG_VALUE_REACHED = 16;


    /**
     * @internal
     */
    const FUNCTION_GET_ILLUMINANCE = 1;

    /**
     * @internal
     */
    const FUNCTION_GET_ANALOG_VALUE = 2;

    /**
     * @internal
     */
    const FUNCTION_SET_ILLUMINANCE_CALLBACK_PERIOD = 3;

    /**
     * @internal
     */
    const FUNCTION_GET_ILLUMINANCE_CALLBACK_PERIOD = 4;

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
    const FUNCTION_SET_ILLUMINANCE_CALLBACK_THRESHOLD = 7;

    /**
     * @internal
     */
    const FUNCTION_GET_ILLUMINANCE_CALLBACK_THRESHOLD = 8;

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

        $this->expectedName = 'Ambient Light Bricklet';

        $this->bindingVersion = array(1, 0, 0);

        $this->callbackWrappers[self::CALLBACK_ILLUMINANCE] = 'callbackWrapperIlluminance';
        $this->callbackWrappers[self::CALLBACK_ANALOG_VALUE] = 'callbackWrapperAnalogValue';
        $this->callbackWrappers[self::CALLBACK_ILLUMINANCE_REACHED] = 'callbackWrapperIlluminanceReached';
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
     * Returns the illuminance of the ambient light sensor. The value
     * has a range of 0 to 9000 and is given in Lux/10, i.e. a value
     * of 4500 means that an illuminance of 450 Lux is measured.
     * 
     * If you want to get the illuminance periodically, it is recommended to use the
     * callback BrickletAmbientLight::CALLBACK_ILLUMINANCE and set the period with 
     * BrickletAmbientLight::setIlluminanceCallbackPeriod().
     * 
     * 
     * @return int
     */
    public function getIlluminance()
    {
        $payload = '';

        $data = $this->sendRequestExpectResponse(self::FUNCTION_GET_ILLUMINANCE, $payload, 2);

        $payload = unpack('v1illuminance', $data);

        return $payload['illuminance'];
    }

    /**
     * Returns the value as read by a 12-bit analog-to-digital converter.
     * The value is between 0 and 4095.
     * 
     * <note>
     *  The value returned by BrickletAmbientLight::getIlluminance() is averaged over several samples
     *  to yield less noise, while BrickletAmbientLight::getAnalogValue() gives back raw
     *  unfiltered analog values. The only reason to use BrickletAmbientLight::getAnalogValue() is,
     *  if you need the full resolution of the analog-to-digital converter.
     * </note>
     * 
     *  Also, the analog-to-digital converter covers three different ranges that are
     *  set dynamically depending on the light intensity. It is impossible to
     *  distinguish between these ranges with the analog value.
     * 
     * If you want the analog value periodically, it is recommended to use the 
     * callback BrickletAmbientLight::CALLBACK_ANALOG_VALUE and set the period with 
     * BrickletAmbientLight::setAnalogValueCallbackPeriod().
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
     * Sets the period in ms with which the BrickletAmbientLight::CALLBACK_ILLUMINANCE callback is triggered
     * periodically. A value of 0 turns the callback off.
     * 
     * BrickletAmbientLight::CALLBACK_ILLUMINANCE is only triggered if the illuminance has changed since the
     * last triggering.
     * 
     * The default value is 0.
     * 
     * @param int $period
     * 
     * @return void
     */
    public function setIlluminanceCallbackPeriod($period)
    {
        $payload = '';
        $payload .= pack('V', $period);

        $this->sendRequestNoResponse(self::FUNCTION_SET_ILLUMINANCE_CALLBACK_PERIOD, $payload);
    }

    /**
     * Returns the period as set by BrickletAmbientLight::setIlluminanceCallbackPeriod().
     * 
     * 
     * @return int
     */
    public function getIlluminanceCallbackPeriod()
    {
        $payload = '';

        $data = $this->sendRequestExpectResponse(self::FUNCTION_GET_ILLUMINANCE_CALLBACK_PERIOD, $payload, 4);

        $payload = unpack('V1period', $data);

        return IPConnection::fixUnpackedUInt32($payload['period']);
    }

    /**
     * Sets the period in ms with which the BrickletAmbientLight::CALLBACK_ANALOG_VALUE callback is triggered
     * periodically. A value of 0 turns the callback off.
     * 
     * BrickletAmbientLight::CALLBACK_ANALOG_VALUE is only triggered if the analog value has changed since the
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
     * Returns the period as set by BrickletAmbientLight::setAnalogValueCallbackPeriod().
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
     * Sets the thresholds for the BrickletAmbientLight::CALLBACK_ILLUMINANCE_REACHED callback. 
     * 
     * The following options are possible:
     * 
     * <code>
     *  "Option", "Description"
     * 
     *  "'x'",    "Callback is turned off"
     *  "'o'",    "Callback is triggered when the illuminance is *outside* the min and max values"
     *  "'i'",    "Callback is triggered when the illuminance is *inside* the min and max values"
     *  "'<'",    "Callback is triggered when the illuminance is smaller than the min value (max is ignored)"
     *  "'>'",    "Callback is triggered when the illuminance is greater than the min value (max is ignored)"
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
    public function setIlluminanceCallbackThreshold($option, $min, $max)
    {
        $payload = '';
        $payload .= pack('c', ord($option));
        $payload .= pack('v', $min);
        $payload .= pack('v', $max);

        $this->sendRequestNoResponse(self::FUNCTION_SET_ILLUMINANCE_CALLBACK_THRESHOLD, $payload);
    }

    /**
     * Returns the threshold as set by BrickletAmbientLight::setIlluminanceCallbackThreshold().
     * 
     * 
     * @return array
     */
    public function getIlluminanceCallbackThreshold()
    {
        $result = array();

        $payload = '';

        $data = $this->sendRequestExpectResponse(self::FUNCTION_GET_ILLUMINANCE_CALLBACK_THRESHOLD, $payload, 5);

        $payload = unpack('c1option/v1min/v1max', $data);

        $result['option'] = chr($payload['option']);
        $result['min'] = IPConnection::fixUnpackedInt16($payload['min']);
        $result['max'] = IPConnection::fixUnpackedInt16($payload['max']);

        return $result;
    }

    /**
     * Sets the thresholds for the BrickletAmbientLight::CALLBACK_ANALOG_VALUE_REACHED callback. 
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
     * Returns the threshold as set by BrickletAmbientLight::setAnalogValueCallbackThreshold().
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
     *  BrickletAmbientLight::CALLBACK_ILLUMINANCE_REACHED, BrickletAmbientLight::CALLBACK_ANALOG_VALUE_REACHED
     * 
     * are triggered, if the thresholds
     * 
     *  BrickletAmbientLight::setIlluminanceCallbackThreshold(), BrickletAmbientLight::setAnalogValueCallbackThreshold()
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
     * Returns the debounce period as set by BrickletAmbientLight::setDebouncePeriod().
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
    public function callbackWrapperIlluminance($data)
    {
        $result = array();
        $payload = unpack('v1illuminance', $data);

        array_push($result, $payload['illuminance']);

        call_user_func_array($this->registeredCallbacks[self::CALLBACK_ILLUMINANCE], $result);
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
    public function callbackWrapperIlluminanceReached($data)
    {
        $result = array();
        $payload = unpack('v1illuminance', $data);

        array_push($result, $payload['illuminance']);

        call_user_func_array($this->registeredCallbacks[self::CALLBACK_ILLUMINANCE_REACHED], $result);
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
