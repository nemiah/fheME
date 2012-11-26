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
 * Device for controlling up to 4 optically coupled digital outputs
 */
class BrickletIndustrialDigitalOut4 extends Device
{

    /**
     * This callback is triggered whenever a monoflop timer reaches 0. The
     * parameters contain the involved pins and the current value of the pins
     * (the value after the monoflop).
     */
    const CALLBACK_MONOFLOP_DONE = 8;


    /**
     * @internal
     */
    const FUNCTION_SET_VALUE = 1;

    /**
     * @internal
     */
    const FUNCTION_GET_VALUE = 2;

    /**
     * @internal
     */
    const FUNCTION_SET_MONOFLOP = 3;

    /**
     * @internal
     */
    const FUNCTION_GET_MONOFLOP = 4;

    /**
     * @internal
     */
    const FUNCTION_SET_GROUP = 5;

    /**
     * @internal
     */
    const FUNCTION_GET_GROUP = 6;

    /**
     * @internal
     */
    const FUNCTION_GET_AVAILABLE_FOR_GROUP = 7;

    /**
     * Creates an object with the unique device ID $uid. This object can
     * then be added to the IP connection.
     *
     * @param string $uid
     */
    public function __construct($uid)
    {
        parent::__construct($uid);

        $this->expectedName = 'Industrial Digital Out 4 Bricklet';

        $this->bindingVersion = array(1, 0, 0);

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
     * Sets the output value with a bitmask. The bitmask
     * is 16 bit long, *true* refers to high and *false* refers to 
     * low.
     * 
     * For example: The value 0b0000000000000011 will turn pins 0-1 
     * high and the other pins low.
     * 
     * If no groups are used (see BrickletIndustrialDigitalOut4::setGroup()), the pins correspond to the
     * markings on the Digital Out 4 Bricklet.
     * 
     * If groups are used, the pins correspond to the element in the group.
     * Element 1 in the group will get pins 0-3, element 2 pins 4-7, element 3
     * pins 8-11 and element 4 pins 12-15.
     * 
     * @param int $value_mask
     * 
     * @return void
     */
    public function setValue($value_mask)
    {
        $payload = '';
        $payload .= pack('v', $value_mask);

        $this->sendRequestNoResponse(self::FUNCTION_SET_VALUE, $payload);
    }

    /**
     * Returns the bitmask as set by BrickletIndustrialDigitalOut4::setValue().
     * 
     * 
     * @return int
     */
    public function getValue()
    {
        $payload = '';

        $data = $this->sendRequestExpectResponse(self::FUNCTION_GET_VALUE, $payload, 2);

        $payload = unpack('v1value_mask', $data);

        return $payload['value_mask'];
    }

    /**
     * Configures a monoflop of the pins specified by the first parameter
     * bitmask.
     * 
     * The second parameter is a bitmask with the desired value of the specified
     * pins (*true* means high and *false* means low).
     * 
     * The third parameter indicates the time (in ms) that the pins should hold
     * the value.
     * 
     * If this function is called with the parameters 
     * ((1 << 0) | (1 << 3), (1 << 0), 1500):
     * Pin 0 will get high and pin 3 will get low. In 1.5s pin 0 will get low and
     * pin 3 will get high again.
     * 
     * A monoflop can be used as a fail-safe mechanism. For example: Lets assume you
     * have a RS485 bus and a Digital Out 4 Bricklet connected to one of the slave
     * stacks. You can now call this function every second, with a time parameter
     * of two seconds and pin 0 high. Pin 0 will be high all the time. If now
     * the RS485 connection is lost, then pin 0 will turn low in at most two seconds.
     * 
     * @param int $pin_mask
     * @param int $value_mask
     * @param int $time
     * 
     * @return void
     */
    public function setMonoflop($pin_mask, $value_mask, $time)
    {
        $payload = '';
        $payload .= pack('v', $pin_mask);
        $payload .= pack('v', $value_mask);
        $payload .= pack('V', $time);

        $this->sendRequestNoResponse(self::FUNCTION_SET_MONOFLOP, $payload);
    }

    /**
     * Returns (for the given pin) the current value and the time as set by
     * BrickletIndustrialDigitalOut4::setMonoflop() as well as the remaining time until the value flips.
     * 
     * If the timer is not running currently, the remaining time will be returned
     * as 0.
     * 
     * @param int $pin
     * 
     * @return array
     */
    public function getMonoflop($pin)
    {
        $result = array();

        $payload = '';
        $payload .= pack('C', $pin);

        $data = $this->sendRequestExpectResponse(self::FUNCTION_GET_MONOFLOP, $payload, 10);

        $payload = unpack('v1value/V1time/V1time_remaining', $data);

        $result['value'] = $payload['value'];
        $result['time'] = IPConnection::fixUnpackedUInt32($payload['time']);
        $result['time_remaining'] = IPConnection::fixUnpackedUInt32($payload['time_remaining']);

        return $result;
    }

    /**
     * Sets a group of Digital Out 4 Bricklets that should work together. You can
     * find Bricklets that can be grouped together with BrickletIndustrialDigitalOut4::getAvailableForGroup().
     * 
     * The group consists of 4 elements. Element 1 in the group will get pins 0-3,
     * element 2 pins 4-7, element 3 pins 8-11 and element 4 pins 12-15.
     * 
     * Each element can either be one of the ports ('a' to 'd') or 'n' if it should
     * not be used.
     * 
     * For example: If you have two Digital Out 4 Bricklets connected to port A and
     * port B respectively, you could call with "['a', 'b', 'n', 'n']".
     * 
     * Now the pins on the Digital Out 4 on port A are assigned to 0-3 and the
     * pins on the Digital Out 4 on port B are assigned to 4-7. It is now possible
     * to call BrickletIndustrialDigitalOut4::setValue() and control two Bricklets at the same time.
     * 
     * @param string[] $group
     * 
     * @return void
     */
    public function setGroup($group)
    {
        $payload = '';
        for ($i = 0; $i < count($group) && $i < 4; $i++) {
            $payload .= pack('c', ord($group[$i]));
        }
        for ($i = count($group); $i < 4; $i++) {
            $payload .= pack('c', 0);
        }

        $this->sendRequestNoResponse(self::FUNCTION_SET_GROUP, $payload);
    }

    /**
     * Returns the group as set by BrickletIndustrialDigitalOut4::setGroup()
     * 
     * 
     * @return array
     */
    public function getGroup()
    {
        $payload = '';

        $data = $this->sendRequestExpectResponse(self::FUNCTION_GET_GROUP, $payload, 4);

        $payload = unpack('c4group', $data);

        return IPConnection::collectUnpackedCharArray($payload, 'group', 4);
    }

    /**
     * Returns a bitmask of ports that are available for grouping. For example the
     * value 0b0101 means: Port *A* and Port *C* are connected to Bricklets that
     * can be grouped together.
     * 
     * 
     * @return int
     */
    public function getAvailableForGroup()
    {
        $payload = '';

        $data = $this->sendRequestExpectResponse(self::FUNCTION_GET_AVAILABLE_FOR_GROUP, $payload, 1);

        $payload = unpack('C1available', $data);

        return $payload['available'];
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
    public function callbackWrapperMonoflopDone($data)
    {
        $result = array();
        $payload = unpack('v1pin_mask/v1value_mask', $data);

        array_push($result, $payload['pin_mask']);
        array_push($result, $payload['value_mask']);

        call_user_func_array($this->registeredCallbacks[self::CALLBACK_MONOFLOP_DONE], $result);
    }
}

?>
