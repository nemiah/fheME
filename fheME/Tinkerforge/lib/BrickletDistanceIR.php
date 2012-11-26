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
 * Device for sensing distance via infrared
 */
class BrickletDistanceIR extends Device
{

    /**
     * This callback is triggered periodically with the period that is set by
     * BrickletDistanceIR::setDistanceCallbackPeriod(). The parameter is the distance of the
     * sensor.
     * 
     * BrickletDistanceIR::CALLBACK_DISTANCE is only triggered if the distance has changed since the
     * last triggering.
     */
    const CALLBACK_DISTANCE = 15;

    /**
     * This callback is triggered periodically with the period that is set by
     * BrickletDistanceIR::setAnalogValueCallbackPeriod(). The parameter is the analog value of the
     * sensor.
     * 
     * BrickletDistanceIR::CALLBACK_ANALOG_VALUE is only triggered if the analog value has changed since the
     * last triggering.
     */
    const CALLBACK_ANALOG_VALUE = 16;

    /**
     * This callback is triggered when the threshold as set by
     * BrickletDistanceIR::setDistanceCallbackThreshold() is reached.
     * The parameter is the distance of the sensor.
     * 
     * If the threshold keeps being reached, the callback is triggered periodically
     * with the period as set by BrickletDistanceIR::setDebouncePeriod().
     */
    const CALLBACK_DISTANCE_REACHED = 17;

    /**
     * This callback is triggered when the threshold as set by
     * BrickletDistanceIR::setAnalogValueCallbackThreshold() is reached.
     * The parameter is the analog value of the sensor.
     * 
     * If the threshold keeps being reached, the callback is triggered periodically
     * with the period as set by BrickletDistanceIR::setDebouncePeriod().
     */
    const CALLBACK_ANALOG_VALUE_REACHED = 18;


    /**
     * @internal
     */
    const FUNCTION_GET_DISTANCE = 1;

    /**
     * @internal
     */
    const FUNCTION_GET_ANALOG_VALUE = 2;

    /**
     * @internal
     */
    const FUNCTION_SET_SAMPLING_POINT = 3;

    /**
     * @internal
     */
    const FUNCTION_GET_SAMPLING_POINT = 4;

    /**
     * @internal
     */
    const FUNCTION_SET_DISTANCE_CALLBACK_PERIOD = 5;

    /**
     * @internal
     */
    const FUNCTION_GET_DISTANCE_CALLBACK_PERIOD = 6;

    /**
     * @internal
     */
    const FUNCTION_SET_ANALOG_VALUE_CALLBACK_PERIOD = 7;

    /**
     * @internal
     */
    const FUNCTION_GET_ANALOG_VALUE_CALLBACK_PERIOD = 8;

    /**
     * @internal
     */
    const FUNCTION_SET_DISTANCE_CALLBACK_THRESHOLD = 9;

    /**
     * @internal
     */
    const FUNCTION_GET_DISTANCE_CALLBACK_THRESHOLD = 10;

    /**
     * @internal
     */
    const FUNCTION_SET_ANALOG_VALUE_CALLBACK_THRESHOLD = 11;

    /**
     * @internal
     */
    const FUNCTION_GET_ANALOG_VALUE_CALLBACK_THRESHOLD = 12;

    /**
     * @internal
     */
    const FUNCTION_SET_DEBOUNCE_PERIOD = 13;

    /**
     * @internal
     */
    const FUNCTION_GET_DEBOUNCE_PERIOD = 14;

    /**
     * Creates an object with the unique device ID $uid. This object can
     * then be added to the IP connection.
     *
     * @param string $uid
     */
    public function __construct($uid)
    {
        parent::__construct($uid);

        $this->expectedName = 'Distance IR Bricklet';

        $this->bindingVersion = array(1, 0, 0);

        $this->callbackWrappers[self::CALLBACK_DISTANCE] = 'callbackWrapperDistance';
        $this->callbackWrappers[self::CALLBACK_ANALOG_VALUE] = 'callbackWrapperAnalogValue';
        $this->callbackWrappers[self::CALLBACK_DISTANCE_REACHED] = 'callbackWrapperDistanceReached';
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
     * Returns the distance measured by the sensor. The value is in mm and possible
     * distance ranges are 40 to 300, 100 to 800 and 200 to 1500, depending on the
     * selected IR sensor.
     * 
     * If you want to get the distance periodically, it is recommended to use the
     * callback BrickletDistanceIR::CALLBACK_DISTANCE and set the period with 
     * BrickletDistanceIR::setDistanceCallbackPeriod().
     * 
     * 
     * @return int
     */
    public function getDistance()
    {
        $payload = '';

        $data = $this->sendRequestExpectResponse(self::FUNCTION_GET_DISTANCE, $payload, 2);

        $payload = unpack('v1distance', $data);

        return $payload['distance'];
    }

    /**
     * Returns the value as read by a 12-bit analog-to-digital converter.
     * The value is between 0 and 4095.
     * 
     * <note>
     *  The value returned by BrickletDistanceIR::getDistance() is averaged over several samples
     *  to yield less noise, while BrickletDistanceIR::getAnalogValue() gives back raw
     *  unfiltered analog values. The only reason to use BrickletDistanceIR::getAnalogValue() is,
     *  if you need the full resolution of the analog-to-digital converter.
     * </note>
     * 
     * If you want the analog value periodically, it is recommended to use the 
     * callback BrickletDistanceIR::CALLBACK_ANALOG_VALUE and set the period with 
     * BrickletDistanceIR::setAnalogValueCallbackPeriod().
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
     * Sets a sampling point value to a specific position of the lookup table.
     * The lookup table comprises 128 equidistant analog values with
     * corresponding distances.
     * 
     * If you measure a distance of 50cm at the analog value 2048, you
     * should call this function with (64, 5000). The utilized analog-to-digital
     * converter has a resolution of 12 bit. With 128 sampling points on the
     * whole range, this means that every sampling point has a size of 32
     * analog values. Thus the analog value 2048 has the corresponding sampling
     * point 64 = 2048/32.
     * 
     * Sampling points are saved on the EEPROM of the Distance IR Bricklet and
     * loaded again on startup.
     * 
     * <note>
     *  An easy way to calibrate the sampling points of the Distance IR Bricklet is
     *  implemented in the Brick Viewer. If you want to calibrate your Bricklet it is
     *  highly recommended to use this implementation.
     * </note>
     * 
     * @param int $position
     * @param int $distance
     * 
     * @return void
     */
    public function setSamplingPoint($position, $distance)
    {
        $payload = '';
        $payload .= pack('C', $position);
        $payload .= pack('v', $distance);

        $this->sendRequestNoResponse(self::FUNCTION_SET_SAMPLING_POINT, $payload);
    }

    /**
     * Returns the distance to a sampling point position as set by
     * BrickletDistanceIR::setSamplingPoint().
     * 
     * @param int $position
     * 
     * @return int
     */
    public function getSamplingPoint($position)
    {
        $payload = '';
        $payload .= pack('C', $position);

        $data = $this->sendRequestExpectResponse(self::FUNCTION_GET_SAMPLING_POINT, $payload, 2);

        $payload = unpack('v1distance', $data);

        return $payload['distance'];
    }

    /**
     * Sets the period in ms with which the BrickletDistanceIR::CALLBACK_DISTANCE callback is triggered
     * periodically. A value of 0 turns the callback off.
     * 
     * BrickletDistanceIR::CALLBACK_DISTANCE is only triggered if the distance has changed since the
     * last triggering.
     * 
     * The default value is 0.
     * 
     * @param int $period
     * 
     * @return void
     */
    public function setDistanceCallbackPeriod($period)
    {
        $payload = '';
        $payload .= pack('V', $period);

        $this->sendRequestNoResponse(self::FUNCTION_SET_DISTANCE_CALLBACK_PERIOD, $payload);
    }

    /**
     * Returns the period as set by BrickletDistanceIR::setDistanceCallbackPeriod().
     * 
     * 
     * @return int
     */
    public function getDistanceCallbackPeriod()
    {
        $payload = '';

        $data = $this->sendRequestExpectResponse(self::FUNCTION_GET_DISTANCE_CALLBACK_PERIOD, $payload, 4);

        $payload = unpack('V1period', $data);

        return IPConnection::fixUnpackedUInt32($payload['period']);
    }

    /**
     * Sets the period in ms with which the BrickletDistanceIR::CALLBACK_ANALOG_VALUE callback is triggered
     * periodically. A value of 0 turns the callback off.
     * 
     * BrickletDistanceIR::CALLBACK_ANALOG_VALUE is only triggered if the analog value has changed since the
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
     * Returns the period as set by BrickletDistanceIR::setAnalogValueCallbackPeriod().
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
     * Sets the thresholds for the BrickletDistanceIR::CALLBACK_DISTANCE_REACHED callback. 
     * 
     * The following options are possible:
     * 
     * <code>
     *  "Option", "Description"
     * 
     *  "'x'",    "Callback is turned off"
     *  "'o'",    "Callback is triggered when the distance is *outside* the min and max values"
     *  "'i'",    "Callback is triggered when the distance is *inside* the min and max values"
     *  "'<'",    "Callback is triggered when the distance is smaller than the min value (max is ignored)"
     *  "'>'",    "Callback is triggered when the distance is greater than the min value (max is ignored)"
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
    public function setDistanceCallbackThreshold($option, $min, $max)
    {
        $payload = '';
        $payload .= pack('c', ord($option));
        $payload .= pack('v', $min);
        $payload .= pack('v', $max);

        $this->sendRequestNoResponse(self::FUNCTION_SET_DISTANCE_CALLBACK_THRESHOLD, $payload);
    }

    /**
     * Returns the threshold as set by BrickletDistanceIR::setDistanceCallbackThreshold().
     * 
     * 
     * @return array
     */
    public function getDistanceCallbackThreshold()
    {
        $result = array();

        $payload = '';

        $data = $this->sendRequestExpectResponse(self::FUNCTION_GET_DISTANCE_CALLBACK_THRESHOLD, $payload, 5);

        $payload = unpack('c1option/v1min/v1max', $data);

        $result['option'] = chr($payload['option']);
        $result['min'] = IPConnection::fixUnpackedInt16($payload['min']);
        $result['max'] = IPConnection::fixUnpackedInt16($payload['max']);

        return $result;
    }

    /**
     * Sets the thresholds for the BrickletDistanceIR::CALLBACK_ANALOG_VALUE_REACHED callback. 
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
     * Returns the threshold as set by BrickletDistanceIR::setAnalogValueCallbackThreshold().
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
     *  BrickletDistanceIR::CALLBACK_DISTANCE_REACHED, BrickletDistanceIR::CALLBACK_ANALOG_VALUE_REACHED
     * 
     * are triggered, if the thresholds
     * 
     *  BrickletDistanceIR::setDistanceCallbackThreshold(), BrickletDistanceIR::setAnalogValueCallbackThreshold()
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
     * Returns the debounce period as set by BrickletDistanceIR::setDebouncePeriod().
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
    public function callbackWrapperDistance($data)
    {
        $result = array();
        $payload = unpack('v1distance', $data);

        array_push($result, $payload['distance']);

        call_user_func_array($this->registeredCallbacks[self::CALLBACK_DISTANCE], $result);
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
    public function callbackWrapperDistanceReached($data)
    {
        $result = array();
        $payload = unpack('v1distance', $data);

        array_push($result, $payload['distance']);

        call_user_func_array($this->registeredCallbacks[self::CALLBACK_DISTANCE_REACHED], $result);
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
