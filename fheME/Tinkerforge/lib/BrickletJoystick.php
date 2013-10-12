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
 * Dual-Axis Joystick with Button
 */
class BrickletJoystick extends Device
{

    /**
     * This callback is triggered periodically with the period that is set by
     * BrickletJoystick::setPositionCallbackPeriod(). The parameter is the position of the
     * Joystick.
     * 
     * BrickletJoystick::CALLBACK_POSITION is only triggered if the position has changed since the
     * last triggering.
     */
    const CALLBACK_POSITION = 15;

    /**
     * This callback is triggered periodically with the period that is set by
     * BrickletJoystick::setAnalogValueCallbackPeriod(). The parameters are the analog values
     * of the Joystick.
     * 
     * BrickletJoystick::CALLBACK_ANALOG_VALUE is only triggered if the values have changed since the
     * last triggering.
     */
    const CALLBACK_ANALOG_VALUE = 16;

    /**
     * This callback is triggered when the threshold as set by
     * BrickletJoystick::setPositionCallbackThreshold() is reached.
     * The parameters are the position of the Joystick.
     * 
     * If the threshold keeps being reached, the callback is triggered periodically
     * with the period as set by BrickletJoystick::setDebouncePeriod().
     */
    const CALLBACK_POSITION_REACHED = 17;

    /**
     * This callback is triggered when the threshold as set by
     * BrickletJoystick::setAnalogValueCallbackThreshold() is reached.
     * The parameters are the analog values of the Joystick.
     * 
     * If the threshold keeps being reached, the callback is triggered periodically
     * with the period as set by BrickletJoystick::setDebouncePeriod().
     */
    const CALLBACK_ANALOG_VALUE_REACHED = 18;

    /**
     * This callback is triggered when the button is pressed.
     */
    const CALLBACK_PRESSED = 19;

    /**
     * This callback is triggered when the button is released.
     */
    const CALLBACK_RELEASED = 20;


    /**
     * @internal
     */
    const FUNCTION_GET_POSITION = 1;

    /**
     * @internal
     */
    const FUNCTION_IS_PRESSED = 2;

    /**
     * @internal
     */
    const FUNCTION_GET_ANALOG_VALUE = 3;

    /**
     * @internal
     */
    const FUNCTION_CALIBRATE = 4;

    /**
     * @internal
     */
    const FUNCTION_SET_POSITION_CALLBACK_PERIOD = 5;

    /**
     * @internal
     */
    const FUNCTION_GET_POSITION_CALLBACK_PERIOD = 6;

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
    const FUNCTION_SET_POSITION_CALLBACK_THRESHOLD = 9;

    /**
     * @internal
     */
    const FUNCTION_GET_POSITION_CALLBACK_THRESHOLD = 10;

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
     * @internal
     */
    const FUNCTION_GET_IDENTITY = 255;

    const THRESHOLD_OPTION_OFF = 'x';
    const THRESHOLD_OPTION_OUTSIDE = 'o';
    const THRESHOLD_OPTION_INSIDE = 'i';
    const THRESHOLD_OPTION_SMALLER = '<';
    const THRESHOLD_OPTION_GREATER = '>';

    const DEVICE_IDENTIFIER = 210;

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

        $this->responseExpected[self::FUNCTION_GET_POSITION] = self::RESPONSE_EXPECTED_ALWAYS_TRUE;
        $this->responseExpected[self::FUNCTION_IS_PRESSED] = self::RESPONSE_EXPECTED_ALWAYS_TRUE;
        $this->responseExpected[self::FUNCTION_GET_ANALOG_VALUE] = self::RESPONSE_EXPECTED_ALWAYS_TRUE;
        $this->responseExpected[self::FUNCTION_CALIBRATE] = self::RESPONSE_EXPECTED_FALSE;
        $this->responseExpected[self::FUNCTION_SET_POSITION_CALLBACK_PERIOD] = self::RESPONSE_EXPECTED_TRUE;
        $this->responseExpected[self::FUNCTION_GET_POSITION_CALLBACK_PERIOD] = self::RESPONSE_EXPECTED_ALWAYS_TRUE;
        $this->responseExpected[self::FUNCTION_SET_ANALOG_VALUE_CALLBACK_PERIOD] = self::RESPONSE_EXPECTED_TRUE;
        $this->responseExpected[self::FUNCTION_GET_ANALOG_VALUE_CALLBACK_PERIOD] = self::RESPONSE_EXPECTED_ALWAYS_TRUE;
        $this->responseExpected[self::FUNCTION_SET_POSITION_CALLBACK_THRESHOLD] = self::RESPONSE_EXPECTED_TRUE;
        $this->responseExpected[self::FUNCTION_GET_POSITION_CALLBACK_THRESHOLD] = self::RESPONSE_EXPECTED_ALWAYS_TRUE;
        $this->responseExpected[self::FUNCTION_SET_ANALOG_VALUE_CALLBACK_THRESHOLD] = self::RESPONSE_EXPECTED_TRUE;
        $this->responseExpected[self::FUNCTION_GET_ANALOG_VALUE_CALLBACK_THRESHOLD] = self::RESPONSE_EXPECTED_ALWAYS_TRUE;
        $this->responseExpected[self::FUNCTION_SET_DEBOUNCE_PERIOD] = self::RESPONSE_EXPECTED_TRUE;
        $this->responseExpected[self::FUNCTION_GET_DEBOUNCE_PERIOD] = self::RESPONSE_EXPECTED_ALWAYS_TRUE;
        $this->responseExpected[self::CALLBACK_POSITION] = self::RESPONSE_EXPECTED_ALWAYS_FALSE;
        $this->responseExpected[self::CALLBACK_ANALOG_VALUE] = self::RESPONSE_EXPECTED_ALWAYS_FALSE;
        $this->responseExpected[self::CALLBACK_POSITION_REACHED] = self::RESPONSE_EXPECTED_ALWAYS_FALSE;
        $this->responseExpected[self::CALLBACK_ANALOG_VALUE_REACHED] = self::RESPONSE_EXPECTED_ALWAYS_FALSE;
        $this->responseExpected[self::CALLBACK_PRESSED] = self::RESPONSE_EXPECTED_ALWAYS_FALSE;
        $this->responseExpected[self::CALLBACK_RELEASED] = self::RESPONSE_EXPECTED_ALWAYS_FALSE;
        $this->responseExpected[self::FUNCTION_GET_IDENTITY] = self::RESPONSE_EXPECTED_ALWAYS_TRUE;

        $this->callbackWrappers[self::CALLBACK_POSITION] = 'callbackWrapperPosition';
        $this->callbackWrappers[self::CALLBACK_ANALOG_VALUE] = 'callbackWrapperAnalogValue';
        $this->callbackWrappers[self::CALLBACK_POSITION_REACHED] = 'callbackWrapperPositionReached';
        $this->callbackWrappers[self::CALLBACK_ANALOG_VALUE_REACHED] = 'callbackWrapperAnalogValueReached';
        $this->callbackWrappers[self::CALLBACK_PRESSED] = 'callbackWrapperPressed';
        $this->callbackWrappers[self::CALLBACK_RELEASED] = 'callbackWrapperReleased';
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
     * Returns the position of the Joystick. The value ranges between -100 and
     * 100 for both axis. The middle position of the joystick is x=0, y=0. The
     * returned values are averaged and calibrated (see BrickletJoystick::calibrate()).
     * 
     * If you want to get the position periodically, it is recommended to use the
     * callback BrickletJoystick::CALLBACK_POSITION and set the period with 
     * BrickletJoystick::setPositionCallbackPeriod().
     * 
     * 
     * @return array
     */
    public function getPosition()
    {
        $result = array();

        $payload = '';

        $data = $this->sendRequest(self::FUNCTION_GET_POSITION, $payload);

        $payload = unpack('v1x/v1y', $data);

        $result['x'] = IPConnection::fixUnpackedInt16($payload['x']);
        $result['y'] = IPConnection::fixUnpackedInt16($payload['y']);

        return $result;
    }

    /**
     * Returns *true* if the button is pressed and *false* otherwise.
     * 
     * It is recommended to use the BrickletJoystick::CALLBACK_PRESSED and BrickletJoystick::CALLBACK_RELEASED callbacks
     * to handle the button.
     * 
     * 
     * @return bool
     */
    public function isPressed()
    {
        $payload = '';

        $data = $this->sendRequest(self::FUNCTION_IS_PRESSED, $payload);

        $payload = unpack('C1pressed', $data);

        return (bool)$payload['pressed'];
    }

    /**
     * Returns the values as read by a 12-bit analog-to-digital converter.
     * The values are between 0 and 4095 for both axis.
     * 
     * <note>
     *  The values returned by BrickletJoystick::getPosition() are averaged over several samples
     *  to yield less noise, while BrickletJoystick::getAnalogValue() gives back raw
     *  unfiltered analog values. The only reason to use BrickletJoystick::getAnalogValue() is,
     *  if you need the full resolution of the analog-to-digital converter.
     * </note>
     * 
     * If you want the analog values periodically, it is recommended to use the 
     * callback BrickletJoystick::CALLBACK_ANALOG_VALUE and set the period with 
     * BrickletJoystick::setAnalogValueCallbackPeriod().
     * 
     * 
     * @return array
     */
    public function getAnalogValue()
    {
        $result = array();

        $payload = '';

        $data = $this->sendRequest(self::FUNCTION_GET_ANALOG_VALUE, $payload);

        $payload = unpack('v1x/v1y', $data);

        $result['x'] = $payload['x'];
        $result['y'] = $payload['y'];

        return $result;
    }

    /**
     * Calibrates the middle position of the Joystick. If your Joystick Bricklet
     * does not return x=0 and y=0 in the middle position, call this function
     * while the Joystick is standing still in the middle position.
     * 
     * The resulting calibration will be saved on the EEPROM of the Joystick
     * Bricklet, thus you only have to calibrate it once.
     * 
     * 
     * @return void
     */
    public function calibrate()
    {
        $payload = '';

        $this->sendRequest(self::FUNCTION_CALIBRATE, $payload);
    }

    /**
     * Sets the period in ms with which the BrickletJoystick::CALLBACK_POSITION callback is triggered
     * periodically. A value of 0 turns the callback off.
     * 
     * BrickletJoystick::CALLBACK_POSITION is only triggered if the position has changed since the
     * last triggering.
     * 
     * The default value is 0.
     * 
     * @param int $period
     * 
     * @return void
     */
    public function setPositionCallbackPeriod($period)
    {
        $payload = '';
        $payload .= pack('V', $period);

        $this->sendRequest(self::FUNCTION_SET_POSITION_CALLBACK_PERIOD, $payload);
    }

    /**
     * Returns the period as set by BrickletJoystick::setPositionCallbackPeriod().
     * 
     * 
     * @return int
     */
    public function getPositionCallbackPeriod()
    {
        $payload = '';

        $data = $this->sendRequest(self::FUNCTION_GET_POSITION_CALLBACK_PERIOD, $payload);

        $payload = unpack('V1period', $data);

        return IPConnection::fixUnpackedUInt32($payload['period']);
    }

    /**
     * Sets the period in ms with which the BrickletJoystick::CALLBACK_ANALOG_VALUE callback is triggered
     * periodically. A value of 0 turns the callback off.
     * 
     * BrickletJoystick::CALLBACK_ANALOG_VALUE is only triggered if the analog values have changed since the
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

        $this->sendRequest(self::FUNCTION_SET_ANALOG_VALUE_CALLBACK_PERIOD, $payload);
    }

    /**
     * Returns the period as set by BrickletJoystick::setAnalogValueCallbackPeriod().
     * 
     * 
     * @return int
     */
    public function getAnalogValueCallbackPeriod()
    {
        $payload = '';

        $data = $this->sendRequest(self::FUNCTION_GET_ANALOG_VALUE_CALLBACK_PERIOD, $payload);

        $payload = unpack('V1period', $data);

        return IPConnection::fixUnpackedUInt32($payload['period']);
    }

    /**
     * Sets the thresholds for the BrickletJoystick::CALLBACK_POSITION_REACHED callback. 
     * 
     * The following options are possible:
     * 
     * <code>
     *  "Option", "Description"
     * 
     *  "'x'",    "Callback is turned off"
     *  "'o'",    "Callback is triggered when the position is *outside* the min and max values"
     *  "'i'",    "Callback is triggered when the position is *inside* the min and max values"
     *  "'<'",    "Callback is triggered when the position is smaller than the min values (max is ignored)"
     *  "'>'",    "Callback is triggered when the position is greater than the min values (max is ignored)"
     * </code>
     * 
     * The default value is ('x', 0, 0, 0, 0).
     * 
     * @param string $option
     * @param int $min_x
     * @param int $max_x
     * @param int $min_y
     * @param int $max_y
     * 
     * @return void
     */
    public function setPositionCallbackThreshold($option, $min_x, $max_x, $min_y, $max_y)
    {
        $payload = '';
        $payload .= pack('c', ord($option));
        $payload .= pack('v', $min_x);
        $payload .= pack('v', $max_x);
        $payload .= pack('v', $min_y);
        $payload .= pack('v', $max_y);

        $this->sendRequest(self::FUNCTION_SET_POSITION_CALLBACK_THRESHOLD, $payload);
    }

    /**
     * Returns the threshold as set by BrickletJoystick::setPositionCallbackThreshold().
     * 
     * 
     * @return array
     */
    public function getPositionCallbackThreshold()
    {
        $result = array();

        $payload = '';

        $data = $this->sendRequest(self::FUNCTION_GET_POSITION_CALLBACK_THRESHOLD, $payload);

        $payload = unpack('c1option/v1min_x/v1max_x/v1min_y/v1max_y', $data);

        $result['option'] = chr($payload['option']);
        $result['min_x'] = IPConnection::fixUnpackedInt16($payload['min_x']);
        $result['max_x'] = IPConnection::fixUnpackedInt16($payload['max_x']);
        $result['min_y'] = IPConnection::fixUnpackedInt16($payload['min_y']);
        $result['max_y'] = IPConnection::fixUnpackedInt16($payload['max_y']);

        return $result;
    }

    /**
     * Sets the thresholds for the BrickletJoystick::CALLBACK_ANALOG_VALUE_REACHED callback. 
     * 
     * The following options are possible:
     * 
     * <code>
     *  "Option", "Description"
     * 
     *  "'x'",    "Callback is turned off"
     *  "'o'",    "Callback is triggered when the analog values are *outside* the min and max values"
     *  "'i'",    "Callback is triggered when the analog values are *inside* the min and max values"
     *  "'<'",    "Callback is triggered when the analog values are smaller than the min values (max is ignored)"
     *  "'>'",    "Callback is triggered when the analog values are greater than the min values (max is ignored)"
     * </code>
     * 
     * The default value is ('x', 0, 0, 0, 0).
     * 
     * @param string $option
     * @param int $min_x
     * @param int $max_x
     * @param int $min_y
     * @param int $max_y
     * 
     * @return void
     */
    public function setAnalogValueCallbackThreshold($option, $min_x, $max_x, $min_y, $max_y)
    {
        $payload = '';
        $payload .= pack('c', ord($option));
        $payload .= pack('v', $min_x);
        $payload .= pack('v', $max_x);
        $payload .= pack('v', $min_y);
        $payload .= pack('v', $max_y);

        $this->sendRequest(self::FUNCTION_SET_ANALOG_VALUE_CALLBACK_THRESHOLD, $payload);
    }

    /**
     * Returns the threshold as set by BrickletJoystick::setAnalogValueCallbackThreshold().
     * 
     * 
     * @return array
     */
    public function getAnalogValueCallbackThreshold()
    {
        $result = array();

        $payload = '';

        $data = $this->sendRequest(self::FUNCTION_GET_ANALOG_VALUE_CALLBACK_THRESHOLD, $payload);

        $payload = unpack('c1option/v1min_x/v1max_x/v1min_y/v1max_y', $data);

        $result['option'] = chr($payload['option']);
        $result['min_x'] = $payload['min_x'];
        $result['max_x'] = $payload['max_x'];
        $result['min_y'] = $payload['min_y'];
        $result['max_y'] = $payload['max_y'];

        return $result;
    }

    /**
     * Sets the period in ms with which the threshold callbacks
     * 
     * * BrickletJoystick::CALLBACK_POSITION_REACHED,
     * * BrickletJoystick::CALLBACK_ANALOG_VALUE_REACHED
     * 
     * are triggered, if the thresholds
     * 
     * * BrickletJoystick::setPositionCallbackThreshold(),
     * * BrickletJoystick::setAnalogValueCallbackThreshold()
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
     * Returns the debounce period as set by BrickletJoystick::setDebouncePeriod().
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
    public function callbackWrapperPosition($data)
    {
        $result = array();
        $payload = unpack('v1x/v1y', $data);

        array_push($result, IPConnection::fixUnpackedInt16($payload['x']));
        array_push($result, IPConnection::fixUnpackedInt16($payload['y']));

        call_user_func_array($this->registeredCallbacks[self::CALLBACK_POSITION], $result);
    }

    /**
     * @internal
     * @param string $data
     */
    public function callbackWrapperAnalogValue($data)
    {
        $result = array();
        $payload = unpack('v1x/v1y', $data);

        array_push($result, $payload['x']);
        array_push($result, $payload['y']);

        call_user_func_array($this->registeredCallbacks[self::CALLBACK_ANALOG_VALUE], $result);
    }

    /**
     * @internal
     * @param string $data
     */
    public function callbackWrapperPositionReached($data)
    {
        $result = array();
        $payload = unpack('v1x/v1y', $data);

        array_push($result, IPConnection::fixUnpackedInt16($payload['x']));
        array_push($result, IPConnection::fixUnpackedInt16($payload['y']));

        call_user_func_array($this->registeredCallbacks[self::CALLBACK_POSITION_REACHED], $result);
    }

    /**
     * @internal
     * @param string $data
     */
    public function callbackWrapperAnalogValueReached($data)
    {
        $result = array();
        $payload = unpack('v1x/v1y', $data);

        array_push($result, $payload['x']);
        array_push($result, $payload['y']);

        call_user_func_array($this->registeredCallbacks[self::CALLBACK_ANALOG_VALUE_REACHED], $result);
    }

    /**
     * @internal
     * @param string $data
     */
    public function callbackWrapperPressed($data)
    {
        $result = array();




        call_user_func_array($this->registeredCallbacks[self::CALLBACK_PRESSED], $result);
    }

    /**
     * @internal
     * @param string $data
     */
    public function callbackWrapperReleased($data)
    {
        $result = array();




        call_user_func_array($this->registeredCallbacks[self::CALLBACK_RELEASED], $result);
    }
}

?>
