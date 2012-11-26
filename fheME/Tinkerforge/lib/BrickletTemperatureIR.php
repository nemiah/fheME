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
 * Device for non-contact temperature sensing
 */
class BrickletTemperatureIR extends Device
{

    /**
     * This callback is triggered periodically with the period that is set by
     * BrickletTemperatureIR::setAmbientTemperatureCallbackPeriod(). The parameter is the ambient
     * temperature of the sensor.
     * 
     * BrickletTemperatureIR::CALLBACK_AMBIENT_TEMPERATURE is only triggered if the ambient temperature
     * has changed since the last triggering.
     */
    const CALLBACK_AMBIENT_TEMPERATURE = 15;

    /**
     * This callback is triggered periodically with the period that is set by
     * BrickletTemperatureIR::setObjectTemperatureCallbackPeriod(). The parameter is the object
     * temperature of the sensor.
     * 
     * BrickletTemperatureIR::CALLBACK_OBJECT_TEMPERATURE is only triggered if the object temperature
     * has changed since the last triggering.
     */
    const CALLBACK_OBJECT_TEMPERATURE = 16;

    /**
     * This callback is triggered when the threshold as set by
     * BrickletTemperatureIR::setAmbientTemperatureCallbackThreshold() is reached.
     * The parameter is the ambient temperature of the sensor.
     * 
     * If the threshold keeps being reached, the callback is triggered periodically
     * with the period as set by BrickletTemperatureIR::setDebouncePeriod().
     */
    const CALLBACK_AMBIENT_TEMPERATURE_REACHED = 17;

    /**
     * This callback is triggered when the threshold as set by
     * BrickletTemperatureIR::setObjectTemperatureCallbackThreshold() is reached.
     * The parameter is the object temperature of the sensor.
     * 
     * If the threshold keeps being reached, the callback is triggered periodically
     * with the period as set by BrickletTemperatureIR::setDebouncePeriod().
     */
    const CALLBACK_OBJECT_TEMPERATURE_REACHED = 18;


    /**
     * @internal
     */
    const FUNCTION_GET_AMBIENT_TEMPERATURE = 1;

    /**
     * @internal
     */
    const FUNCTION_GET_OBJECT_TEMPERATURE = 2;

    /**
     * @internal
     */
    const FUNCTION_SET_EMISSIVITY = 3;

    /**
     * @internal
     */
    const FUNCTION_GET_EMISSIVITY = 4;

    /**
     * @internal
     */
    const FUNCTION_SET_AMBIENT_TEMPERATURE_CALLBACK_PERIOD = 5;

    /**
     * @internal
     */
    const FUNCTION_GET_AMBIENT_TEMPERATURE_CALLBACK_PERIOD = 6;

    /**
     * @internal
     */
    const FUNCTION_SET_OBJECT_TEMPERATURE_CALLBACK_PERIOD = 7;

    /**
     * @internal
     */
    const FUNCTION_GET_OBJECT_TEMPERATURE_CALLBACK_PERIOD = 8;

    /**
     * @internal
     */
    const FUNCTION_SET_AMBIENT_TEMPERATURE_CALLBACK_THRESHOLD = 9;

    /**
     * @internal
     */
    const FUNCTION_GET_AMBIENT_TEMPERATURE_CALLBACK_THRESHOLD = 10;

    /**
     * @internal
     */
    const FUNCTION_SET_OBJECT_TEMPERATURE_CALLBACK_THRESHOLD = 11;

    /**
     * @internal
     */
    const FUNCTION_GET_OBJECT_TEMPERATURE_CALLBACK_THRESHOLD = 12;

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

        $this->expectedName = 'Temperature IR Bricklet';

        $this->bindingVersion = array(1, 0, 0);

        $this->callbackWrappers[self::CALLBACK_AMBIENT_TEMPERATURE] = 'callbackWrapperAmbientTemperature';
        $this->callbackWrappers[self::CALLBACK_OBJECT_TEMPERATURE] = 'callbackWrapperObjectTemperature';
        $this->callbackWrappers[self::CALLBACK_AMBIENT_TEMPERATURE_REACHED] = 'callbackWrapperAmbientTemperatureReached';
        $this->callbackWrappers[self::CALLBACK_OBJECT_TEMPERATURE_REACHED] = 'callbackWrapperObjectTemperatureReached';
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
     * Returns the ambient temperature of the sensor. The value
     * has a range of -400 to 1250 and is given in °C/10,
     * e.g. a value of 423 means that an ambient temperature of 42.3 °C is 
     * measured.
     * 
     * If you want to get the ambient temperature periodically, it is recommended 
     * to use the callback BrickletTemperatureIR::CALLBACK_AMBIENT_TEMPERATURE and set the period with 
     * BrickletTemperatureIR::setAmbientTemperatureCallbackPeriod().
     * 
     * 
     * @return int
     */
    public function getAmbientTemperature()
    {
        $payload = '';

        $data = $this->sendRequestExpectResponse(self::FUNCTION_GET_AMBIENT_TEMPERATURE, $payload, 2);

        $payload = unpack('v1temperature', $data);

        return IPConnection::fixUnpackedInt16($payload['temperature']);
    }

    /**
     * Returns the object temperature of the sensor, i.e. the temperature
     * of the surface of the object the sensor is aimed at. The value
     * has a range of -700 to 3800 and is given in °C/10,
     * e.g. a value of 3001 means that a temperature of 300.1 °C is measured
     * on the surface of the object.
     * 
     * The temperature of different materials is dependent on their `emissivity 
     * <http://en.wikipedia.org/wiki/Emissivity>`__. The emissivity of the material
     * can be set with BrickletTemperatureIR::setEmissivity().
     * 
     * If you want to get the object temperature periodically, it is recommended 
     * to use the callback BrickletTemperatureIR::CALLBACK_OBJECT_TEMPERATURE and set the period with 
     * BrickletTemperatureIR::setObjectTemperatureCallbackPeriod().
     * 
     * 
     * @return int
     */
    public function getObjectTemperature()
    {
        $payload = '';

        $data = $this->sendRequestExpectResponse(self::FUNCTION_GET_OBJECT_TEMPERATURE, $payload, 2);

        $payload = unpack('v1temperature', $data);

        return IPConnection::fixUnpackedInt16($payload['temperature']);
    }

    /**
     * Sets the `emissivity <http://en.wikipedia.org/wiki/Emissivity>`__ that is
     * used to calculate the surface temperature as returned by 
     * BrickletTemperatureIR::getObjectTemperature(). 
     * 
     * The emissivity is usually given as a value between 0.0 and 1.0. A list of
     * emissivities of different materials can be found 
     * `here <http://www.infrared-thermography.com/material.htm>`__.
     * 
     * The parameter of BrickletTemperatureIR::setEmissivity() has to be given with a factor of
     * 65535 (16-bit). For example: An emissivity of 0.1 can be set with the
     * value 6553, an emissivity of 0.5 with the value 32767 and so on.
     * 
     * <note>
     *  If you need a precise measurement for the object temperature, it is
     *  absolutely crucial that you also provide a precise emissivity.
     * </note>
     * 
     * The default emissivity is 1.0 (value of 65535) and the minimum emissivity the
     * sensor can handle is 0.1 (value of 6553).
     * 
     * @param int $emissivity
     * 
     * @return void
     */
    public function setEmissivity($emissivity)
    {
        $payload = '';
        $payload .= pack('v', $emissivity);

        $this->sendRequestNoResponse(self::FUNCTION_SET_EMISSIVITY, $payload);
    }

    /**
     * Returns the emissivity as set by BrickletTemperatureIR::setEmissivity().
     * 
     * 
     * @return int
     */
    public function getEmissivity()
    {
        $payload = '';

        $data = $this->sendRequestExpectResponse(self::FUNCTION_GET_EMISSIVITY, $payload, 2);

        $payload = unpack('v1emissivity', $data);

        return $payload['emissivity'];
    }

    /**
     * Sets the period in ms with which the BrickletTemperatureIR::CALLBACK_AMBIENT_TEMPERATURE callback is triggered
     * periodically. A value of 0 turns the callback off.
     * 
     * BrickletTemperatureIR::CALLBACK_AMBIENT_TEMPERATURE is only triggered if the temperature has changed since the
     * last triggering.
     * 
     * The default value is 0.
     * 
     * @param int $period
     * 
     * @return void
     */
    public function setAmbientTemperatureCallbackPeriod($period)
    {
        $payload = '';
        $payload .= pack('V', $period);

        $this->sendRequestNoResponse(self::FUNCTION_SET_AMBIENT_TEMPERATURE_CALLBACK_PERIOD, $payload);
    }

    /**
     * Returns the period as set by BrickletTemperatureIR::setAmbientTemperatureCallbackPeriod().
     * 
     * 
     * @return int
     */
    public function getAmbientTemperatureCallbackPeriod()
    {
        $payload = '';

        $data = $this->sendRequestExpectResponse(self::FUNCTION_GET_AMBIENT_TEMPERATURE_CALLBACK_PERIOD, $payload, 4);

        $payload = unpack('V1period', $data);

        return IPConnection::fixUnpackedUInt32($payload['period']);
    }

    /**
     * Sets the period in ms with which the BrickletTemperatureIR::CALLBACK_OBJECT_TEMPERATURE callback is triggered
     * periodically. A value of 0 turns the callback off.
     * 
     * BrickletTemperatureIR::CALLBACK_OBJECT_TEMPERATURE is only triggered if the temperature has changed since the
     * last triggering.
     * 
     * The default value is 0.
     * 
     * @param int $period
     * 
     * @return void
     */
    public function setObjectTemperatureCallbackPeriod($period)
    {
        $payload = '';
        $payload .= pack('V', $period);

        $this->sendRequestNoResponse(self::FUNCTION_SET_OBJECT_TEMPERATURE_CALLBACK_PERIOD, $payload);
    }

    /**
     * Returns the period as set by BrickletTemperatureIR::setObjectTemperatureCallbackPeriod().
     * 
     * 
     * @return int
     */
    public function getObjectTemperatureCallbackPeriod()
    {
        $payload = '';

        $data = $this->sendRequestExpectResponse(self::FUNCTION_GET_OBJECT_TEMPERATURE_CALLBACK_PERIOD, $payload, 4);

        $payload = unpack('V1period', $data);

        return IPConnection::fixUnpackedUInt32($payload['period']);
    }

    /**
     * Sets the thresholds for the BrickletTemperatureIR::CALLBACK_AMBIENT_TEMPERATURE_REACHED callback. 
     * 
     * The following options are possible:
     * 
     * <code>
     *  "Option", "Description"
     * 
     *  "'x'",    "Callback is turned off"
     *  "'o'",    "Callback is triggered when the ambient temperature is *outside* the min and max values"
     *  "'i'",    "Callback is triggered when the ambient temperature is *inside* the min and max values"
     *  "'<'",    "Callback is triggered when the ambient temperature is smaller than the min value (max is ignored)"
     *  "'>'",    "Callback is triggered when the ambient temperature is greater than the min value (max is ignored)"
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
    public function setAmbientTemperatureCallbackThreshold($option, $min, $max)
    {
        $payload = '';
        $payload .= pack('c', ord($option));
        $payload .= pack('v', $min);
        $payload .= pack('v', $max);

        $this->sendRequestNoResponse(self::FUNCTION_SET_AMBIENT_TEMPERATURE_CALLBACK_THRESHOLD, $payload);
    }

    /**
     * Returns the threshold as set by BrickletTemperatureIR::setAmbientTemperatureCallbackThreshold().
     * 
     * 
     * @return array
     */
    public function getAmbientTemperatureCallbackThreshold()
    {
        $result = array();

        $payload = '';

        $data = $this->sendRequestExpectResponse(self::FUNCTION_GET_AMBIENT_TEMPERATURE_CALLBACK_THRESHOLD, $payload, 5);

        $payload = unpack('c1option/v1min/v1max', $data);

        $result['option'] = chr($payload['option']);
        $result['min'] = IPConnection::fixUnpackedInt16($payload['min']);
        $result['max'] = IPConnection::fixUnpackedInt16($payload['max']);

        return $result;
    }

    /**
     * Sets the thresholds for the BrickletTemperatureIR::CALLBACK_OBJECT_TEMPERATURE_REACHED callback. 
     * 
     * The following options are possible:
     * 
     * <code>
     *  "Option", "Description"
     * 
     *  "'x'",    "Callback is turned off"
     *  "'o'",    "Callback is triggered when the object temperature is *outside* the min and max values"
     *  "'i'",    "Callback is triggered when the object temperature is *inside* the min and max values"
     *  "'<'",    "Callback is triggered when the object temperature is smaller than the min value (max is ignored)"
     *  "'>'",    "Callback is triggered when the object temperature is greater than the min value (max is ignored)"
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
    public function setObjectTemperatureCallbackThreshold($option, $min, $max)
    {
        $payload = '';
        $payload .= pack('c', ord($option));
        $payload .= pack('v', $min);
        $payload .= pack('v', $max);

        $this->sendRequestNoResponse(self::FUNCTION_SET_OBJECT_TEMPERATURE_CALLBACK_THRESHOLD, $payload);
    }

    /**
     * Returns the threshold as set by BrickletTemperatureIR::setObjectTemperatureCallbackThreshold().
     * 
     * 
     * @return array
     */
    public function getObjectTemperatureCallbackThreshold()
    {
        $result = array();

        $payload = '';

        $data = $this->sendRequestExpectResponse(self::FUNCTION_GET_OBJECT_TEMPERATURE_CALLBACK_THRESHOLD, $payload, 5);

        $payload = unpack('c1option/v1min/v1max', $data);

        $result['option'] = chr($payload['option']);
        $result['min'] = IPConnection::fixUnpackedInt16($payload['min']);
        $result['max'] = IPConnection::fixUnpackedInt16($payload['max']);

        return $result;
    }

    /**
     * Sets the period in ms with which the threshold callbacks
     * 
     *  BrickletTemperatureIR::CALLBACK_AMBIENT_TEMPERATURE_REACHED, BrickletTemperatureIR::CALLBACK_OBJECT_TEMPERATURE_REACHED
     * 
     * are triggered, if the thresholds
     * 
     *  BrickletTemperatureIR::setAmbientTemperatureCallbackThreshold(), BrickletTemperatureIR::setObjectTemperatureCallbackThreshold()
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
     * Returns the debounce period as set by BrickletTemperatureIR::setDebouncePeriod().
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
    public function callbackWrapperAmbientTemperature($data)
    {
        $result = array();
        $payload = unpack('v1temperature', $data);

        array_push($result, IPConnection::fixUnpackedInt16($payload['temperature']));

        call_user_func_array($this->registeredCallbacks[self::CALLBACK_AMBIENT_TEMPERATURE], $result);
    }

    /**
     * @internal
     * @param string $data
     */
    public function callbackWrapperObjectTemperature($data)
    {
        $result = array();
        $payload = unpack('v1temperature', $data);

        array_push($result, IPConnection::fixUnpackedInt16($payload['temperature']));

        call_user_func_array($this->registeredCallbacks[self::CALLBACK_OBJECT_TEMPERATURE], $result);
    }

    /**
     * @internal
     * @param string $data
     */
    public function callbackWrapperAmbientTemperatureReached($data)
    {
        $result = array();
        $payload = unpack('v1temperature', $data);

        array_push($result, IPConnection::fixUnpackedInt16($payload['temperature']));

        call_user_func_array($this->registeredCallbacks[self::CALLBACK_AMBIENT_TEMPERATURE_REACHED], $result);
    }

    /**
     * @internal
     * @param string $data
     */
    public function callbackWrapperObjectTemperatureReached($data)
    {
        $result = array();
        $payload = unpack('v1temperature', $data);

        array_push($result, IPConnection::fixUnpackedInt16($payload['temperature']));

        call_user_func_array($this->registeredCallbacks[self::CALLBACK_OBJECT_TEMPERATURE_REACHED], $result);
    }
}

?>
