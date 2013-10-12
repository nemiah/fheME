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
 * Device for controlling up to 16 general purpose input/output pins
 */
class BrickletIO16 extends Device
{

    /**
     * This callback is triggered whenever a change of the voltage level is detected
     * on pins where the interrupt was activated with BrickletIO16::setPortInterrupt().
     * 
     * The values are the port, a bitmask that specifies which interrupts occurred
     * and the current value bitmask of the port.
     * 
     * For example:
     * 
     * * ("a", 1, 1) means that on port a an interrupt on pin 0 occurred and
     *   currently pin 0 is high and pins 1-7 are low.
     * * ("b", 128, 254) means that on port b interrupts on pins 0 and 7
     *   occurred and currently pin 0 is low and pins 1-7 are high.
     */
    const CALLBACK_INTERRUPT = 9;

    /**
     * This callback is triggered whenever a monoflop timer reaches 0. The
     * parameters contain the port, the involved pins and the current value of
     * the pins (the value after the monoflop).
     * 
     * .. versionadded:: 1.1.2~(Plugin)
     */
    const CALLBACK_MONOFLOP_DONE = 12;


    /**
     * @internal
     */
    const FUNCTION_SET_PORT = 1;

    /**
     * @internal
     */
    const FUNCTION_GET_PORT = 2;

    /**
     * @internal
     */
    const FUNCTION_SET_PORT_CONFIGURATION = 3;

    /**
     * @internal
     */
    const FUNCTION_GET_PORT_CONFIGURATION = 4;

    /**
     * @internal
     */
    const FUNCTION_SET_DEBOUNCE_PERIOD = 5;

    /**
     * @internal
     */
    const FUNCTION_GET_DEBOUNCE_PERIOD = 6;

    /**
     * @internal
     */
    const FUNCTION_SET_PORT_INTERRUPT = 7;

    /**
     * @internal
     */
    const FUNCTION_GET_PORT_INTERRUPT = 8;

    /**
     * @internal
     */
    const FUNCTION_SET_PORT_MONOFLOP = 10;

    /**
     * @internal
     */
    const FUNCTION_GET_PORT_MONOFLOP = 11;

    /**
     * @internal
     */
    const FUNCTION_SET_SELECTED_VALUES = 13;

    /**
     * @internal
     */
    const FUNCTION_GET_EDGE_COUNT = 14;

    /**
     * @internal
     */
    const FUNCTION_SET_EDGE_COUNT_CONFIG = 15;

    /**
     * @internal
     */
    const FUNCTION_GET_EDGE_COUNT_CONFIG = 16;

    /**
     * @internal
     */
    const FUNCTION_GET_IDENTITY = 255;

    const DIRECTION_IN = 'i';
    const DIRECTION_OUT = 'o';
    const EDGE_TYPE_RISING = 0;
    const EDGE_TYPE_FALLING = 1;
    const EDGE_TYPE_BOTH = 2;

    const DEVICE_IDENTIFIER = 28;

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

        $this->responseExpected[self::FUNCTION_SET_PORT] = self::RESPONSE_EXPECTED_FALSE;
        $this->responseExpected[self::FUNCTION_GET_PORT] = self::RESPONSE_EXPECTED_ALWAYS_TRUE;
        $this->responseExpected[self::FUNCTION_SET_PORT_CONFIGURATION] = self::RESPONSE_EXPECTED_FALSE;
        $this->responseExpected[self::FUNCTION_GET_PORT_CONFIGURATION] = self::RESPONSE_EXPECTED_ALWAYS_TRUE;
        $this->responseExpected[self::FUNCTION_SET_DEBOUNCE_PERIOD] = self::RESPONSE_EXPECTED_TRUE;
        $this->responseExpected[self::FUNCTION_GET_DEBOUNCE_PERIOD] = self::RESPONSE_EXPECTED_ALWAYS_TRUE;
        $this->responseExpected[self::FUNCTION_SET_PORT_INTERRUPT] = self::RESPONSE_EXPECTED_TRUE;
        $this->responseExpected[self::FUNCTION_GET_PORT_INTERRUPT] = self::RESPONSE_EXPECTED_ALWAYS_TRUE;
        $this->responseExpected[self::CALLBACK_INTERRUPT] = self::RESPONSE_EXPECTED_ALWAYS_FALSE;
        $this->responseExpected[self::FUNCTION_SET_PORT_MONOFLOP] = self::RESPONSE_EXPECTED_FALSE;
        $this->responseExpected[self::FUNCTION_GET_PORT_MONOFLOP] = self::RESPONSE_EXPECTED_ALWAYS_TRUE;
        $this->responseExpected[self::CALLBACK_MONOFLOP_DONE] = self::RESPONSE_EXPECTED_ALWAYS_FALSE;
        $this->responseExpected[self::FUNCTION_SET_SELECTED_VALUES] = self::RESPONSE_EXPECTED_FALSE;
        $this->responseExpected[self::FUNCTION_GET_EDGE_COUNT] = self::RESPONSE_EXPECTED_ALWAYS_TRUE;
        $this->responseExpected[self::FUNCTION_SET_EDGE_COUNT_CONFIG] = self::RESPONSE_EXPECTED_FALSE;
        $this->responseExpected[self::FUNCTION_GET_EDGE_COUNT_CONFIG] = self::RESPONSE_EXPECTED_ALWAYS_TRUE;
        $this->responseExpected[self::FUNCTION_GET_IDENTITY] = self::RESPONSE_EXPECTED_ALWAYS_TRUE;

        $this->callbackWrappers[self::CALLBACK_INTERRUPT] = 'callbackWrapperInterrupt';
        $this->callbackWrappers[self::CALLBACK_MONOFLOP_DONE] = 'callbackWrapperMonoflopDone';
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
     * Sets the output value (high or low) for a port ("a" or "b") with a bitmask.
     * The bitmask is 8 bit long, *true* refers to high and *false* refers to low.
     * 
     * For example: The value 0b00001111 will turn the pins 0-3 high and the
     * pins 4-7 low for the specified port.
     * 
     * <note>
     *  This function does nothing for pins that are configured as input.
     *  Pull-up resistors can be switched on with BrickletIO16::setPortConfiguration().
     * </note>
     * 
     * @param string $port
     * @param int $value_mask
     * 
     * @return void
     */
    public function setPort($port, $value_mask)
    {
        $payload = '';
        $payload .= pack('c', ord($port));
        $payload .= pack('C', $value_mask);

        $this->sendRequest(self::FUNCTION_SET_PORT, $payload);
    }

    /**
     * Returns a bitmask of the values that are currently measured on the
     * specified port. This function works if the pin is configured to input
     * as well as if it is configured to output.
     * 
     * @param string $port
     * 
     * @return int
     */
    public function getPort($port)
    {
        $payload = '';
        $payload .= pack('c', ord($port));

        $data = $this->sendRequest(self::FUNCTION_GET_PORT, $payload);

        $payload = unpack('C1value_mask', $data);

        return $payload['value_mask'];
    }

    /**
     * Configures the value and direction of a specified port. Possible directions
     * are "i" and "o" for input and output.
     * 
     * If the direction is configured as output, the value is either high or low
     * (set as *true* or *false*).
     * 
     * If the direction is configured as input, the value is either pull-up or
     * default (set as *true* or *false*).
     * 
     * For example:
     * 
     * * ("a", 255, 'i', true) will set all pins of port a as input pull-up.
     * * ("a", 128, 'i', false) will set pin 7 of port a as input default (floating if nothing is connected).
     * * ("b", 3, 'o', false) will set pins 0 and 1 of port b as output low.
     * * ("b", 4, 'o', true) will set pin 2 of port b as output high.
     * 
     * @param string $port
     * @param int $selection_mask
     * @param string $direction
     * @param bool $value
     * 
     * @return void
     */
    public function setPortConfiguration($port, $selection_mask, $direction, $value)
    {
        $payload = '';
        $payload .= pack('c', ord($port));
        $payload .= pack('C', $selection_mask);
        $payload .= pack('c', ord($direction));
        $payload .= pack('C', intval((bool)$value));

        $this->sendRequest(self::FUNCTION_SET_PORT_CONFIGURATION, $payload);
    }

    /**
     * Returns a direction bitmask and a value bitmask for the specified port.
     * 
     * For example: A return value of 0b00001111 and 0b00110011 for
     * direction and value means that:
     * 
     * * pins 0 and 1 are configured as input pull-up,
     * * pins 2 and 3 are configured as input default,
     * * pins 4 and 5 are configured as output high
     * * and pins 6 and 7 are configured as output low.
     * 
     * @param string $port
     * 
     * @return array
     */
    public function getPortConfiguration($port)
    {
        $result = array();

        $payload = '';
        $payload .= pack('c', ord($port));

        $data = $this->sendRequest(self::FUNCTION_GET_PORT_CONFIGURATION, $payload);

        $payload = unpack('C1direction_mask/C1value_mask', $data);

        $result['direction_mask'] = $payload['direction_mask'];
        $result['value_mask'] = $payload['value_mask'];

        return $result;
    }

    /**
     * Sets the debounce period of the BrickletIO16::CALLBACK_INTERRUPT callback in ms.
     * 
     * For example: If you set this value to 100, you will get the interrupt
     * maximal every 100ms. This is necessary if something that bounces is
     * connected to the IO-16 Bricklet, such as a button.
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
     * Returns the debounce period as set by BrickletIO16::setDebouncePeriod().
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
     * Sets the pins on which an interrupt is activated with a bitmask.
     * Interrupts are triggered on changes of the voltage level of the pin,
     * i.e. changes from high to low and low to high.
     * 
     * For example: ('a', 129) will enable the interrupt for pins 0 and 7 of
     * port a.
     * 
     * The interrupt is delivered with the callback BrickletIO16::CALLBACK_INTERRUPT.
     * 
     * @param string $port
     * @param int $interrupt_mask
     * 
     * @return void
     */
    public function setPortInterrupt($port, $interrupt_mask)
    {
        $payload = '';
        $payload .= pack('c', ord($port));
        $payload .= pack('C', $interrupt_mask);

        $this->sendRequest(self::FUNCTION_SET_PORT_INTERRUPT, $payload);
    }

    /**
     * Returns the interrupt bitmask for the specified port as set by
     * BrickletIO16::setPortInterrupt().
     * 
     * @param string $port
     * 
     * @return int
     */
    public function getPortInterrupt($port)
    {
        $payload = '';
        $payload .= pack('c', ord($port));

        $data = $this->sendRequest(self::FUNCTION_GET_PORT_INTERRUPT, $payload);

        $payload = unpack('C1interrupt_mask', $data);

        return $payload['interrupt_mask'];
    }

    /**
     * Configures a monoflop of the pins specified by the second parameter as 8 bit
     * long bitmask. The specified pins must be configured for output. Non-output
     * pins will be ignored.
     * 
     * The third parameter is a bitmask with the desired value of the specified
     * output pins (*true* means high and *false* means low).
     * 
     * The forth parameter indicates the time (in ms) that the pins should hold
     * the value.
     * 
     * If this function is called with the parameters ('a', (1 << 0) | (1 << 3), (1 << 0), 1500):
     * Pin 0 will get high and pin 3 will get low on port 'a'. In 1.5s pin 0 will get
     * low and pin 3 will get high again.
     * 
     * A monoflop can be used as a fail-safe mechanism. For example: Lets assume you
     * have a RS485 bus and an IO-16 Bricklet connected to one of the slave
     * stacks. You can now call this function every second, with a time parameter
     * of two seconds and pin 0 set to high. Pin 0 will be high all the time. If now
     * the RS485 connection is lost, then pin 0 will get low in at most two seconds.
     * 
     * .. versionadded:: 1.1.2~(Plugin)
     * 
     * @param string $port
     * @param int $selection_mask
     * @param int $value_mask
     * @param int $time
     * 
     * @return void
     */
    public function setPortMonoflop($port, $selection_mask, $value_mask, $time)
    {
        $payload = '';
        $payload .= pack('c', ord($port));
        $payload .= pack('C', $selection_mask);
        $payload .= pack('C', $value_mask);
        $payload .= pack('V', $time);

        $this->sendRequest(self::FUNCTION_SET_PORT_MONOFLOP, $payload);
    }

    /**
     * Returns (for the given pin) the current value and the time as set by
     * BrickletIO16::setPortMonoflop() as well as the remaining time until the value flips.
     * 
     * If the timer is not running currently, the remaining time will be returned
     * as 0.
     * 
     * .. versionadded:: 1.1.2~(Plugin)
     * 
     * @param string $port
     * @param int $pin
     * 
     * @return array
     */
    public function getPortMonoflop($port, $pin)
    {
        $result = array();

        $payload = '';
        $payload .= pack('c', ord($port));
        $payload .= pack('C', $pin);

        $data = $this->sendRequest(self::FUNCTION_GET_PORT_MONOFLOP, $payload);

        $payload = unpack('C1value/V1time/V1time_remaining', $data);

        $result['value'] = $payload['value'];
        $result['time'] = IPConnection::fixUnpackedUInt32($payload['time']);
        $result['time_remaining'] = IPConnection::fixUnpackedUInt32($payload['time_remaining']);

        return $result;
    }

    /**
     * Sets the output value (high or low) for a port ("a" or "b" with a bitmask, 
     * according to the selection mask. The bitmask is 8 bit long, *true* refers 
     * to high and *false* refers to low.
     * 
     * For example: The values 0b11000000, 0b10000000 will turn pin 7 high and
     * pin 6 low, pins 0-6 will remain untouched.
     * 
     * <note>
     *  This function does nothing for pins that are configured as input.
     *  Pull-up resistors can be switched on with :func:`SetConfiguration`.
     * </note>
     * 
     * .. versionadded:: 2.0.0~(Plugin)
     * 
     * @param string $port
     * @param int $selection_mask
     * @param int $value_mask
     * 
     * @return void
     */
    public function setSelectedValues($port, $selection_mask, $value_mask)
    {
        $payload = '';
        $payload .= pack('c', ord($port));
        $payload .= pack('C', $selection_mask);
        $payload .= pack('C', $value_mask);

        $this->sendRequest(self::FUNCTION_SET_SELECTED_VALUES, $payload);
    }

    /**
     * Returns the current value of the edge counter for the selected pin on port A.
     * You can configure the edges that are counted with BrickletIO16::setEdgeCountConfig().
     * 
     * If you set the reset counter to *true*, the count is set back to 0
     * directly after it is read.
     * 
     * .. versionadded:: 2.0.3~(Plugin)
     * 
     * @param int $pin
     * @param bool $reset_counter
     * 
     * @return int
     */
    public function getEdgeCount($pin, $reset_counter)
    {
        $payload = '';
        $payload .= pack('C', $pin);
        $payload .= pack('C', intval((bool)$reset_counter));

        $data = $this->sendRequest(self::FUNCTION_GET_EDGE_COUNT, $payload);

        $payload = unpack('V1count', $data);

        return IPConnection::fixUnpackedUInt32($payload['count']);
    }

    /**
     * Configures the edge counter for the selected pin of port A. Pins 0 and 1
     * are available for edge counting.
     * 
     * The edge type parameter configures if rising edges, falling edges or
     * both are counted if the pin is configured for input. Possible edge types are:
     * 
     * * 0 = rising (default)
     * * 1 = falling
     * * 2 = both
     * 
     * The debounce time is given in ms.
     * 
     * If you don't know what any of this means, just leave it at default. The
     * default configuration is very likely OK for you.
     * 
     * Default values: 0 (edge type) and 100ms (debounce time)
     * 
     * .. versionadded:: 2.0.3~(Plugin)
     * 
     * @param int $pin
     * @param int $edge_type
     * @param int $debounce
     * 
     * @return void
     */
    public function setEdgeCountConfig($pin, $edge_type, $debounce)
    {
        $payload = '';
        $payload .= pack('C', $pin);
        $payload .= pack('C', $edge_type);
        $payload .= pack('C', $debounce);

        $this->sendRequest(self::FUNCTION_SET_EDGE_COUNT_CONFIG, $payload);
    }

    /**
     * Returns the edge type and debounce time for the selected pin of port A as set by
     * BrickletIO16::setEdgeCountConfig().
     * 
     * .. versionadded:: 2.0.3~(Plugin)
     * 
     * @param int $pin
     * 
     * @return array
     */
    public function getEdgeCountConfig($pin)
    {
        $result = array();

        $payload = '';
        $payload .= pack('C', $pin);

        $data = $this->sendRequest(self::FUNCTION_GET_EDGE_COUNT_CONFIG, $payload);

        $payload = unpack('C1edge_type/C1debounce', $data);

        $result['edge_type'] = $payload['edge_type'];
        $result['debounce'] = $payload['debounce'];

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
    public function callbackWrapperInterrupt($data)
    {
        $result = array();
        $payload = unpack('c1port/C1interrupt_mask/C1value_mask', $data);

        array_push($result, chr($payload['port']));
        array_push($result, $payload['interrupt_mask']);
        array_push($result, $payload['value_mask']);

        call_user_func_array($this->registeredCallbacks[self::CALLBACK_INTERRUPT], $result);
    }

    /**
     * @internal
     * @param string $data
     */
    public function callbackWrapperMonoflopDone($data)
    {
        $result = array();
        $payload = unpack('c1port/C1selection_mask/C1value_mask', $data);

        array_push($result, chr($payload['port']));
        array_push($result, $payload['selection_mask']);
        array_push($result, $payload['value_mask']);

        call_user_func_array($this->registeredCallbacks[self::CALLBACK_MONOFLOP_DONE], $result);
    }
}

?>
