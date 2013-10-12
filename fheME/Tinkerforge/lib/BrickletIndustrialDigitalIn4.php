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
 * Device for controlling up to 4 optically coupled digital inputs
 */
class BrickletIndustrialDigitalIn4 extends Device
{

    /**
     * This callback is triggered whenever a change of the voltage level is detected
     * on pins where the interrupt was activated with BrickletIndustrialDigitalIn4::setInterrupt().
     * 
     * The values are a bitmask that specifies which interrupts occurred
     * and the current value bitmask.
     * 
     * For example:
     * 
     * * (1, 1) means that an interrupt on pin 0 occurred and
     *   currently pin 0 is high and pins 1-3 are low.
     * * (9, 14) means that interrupts on pins 0 and 3
     *   occurred and currently pin 0 is low and pins 1-3 are high.
     */
    const CALLBACK_INTERRUPT = 9;


    /**
     * @internal
     */
    const FUNCTION_GET_VALUE = 1;

    /**
     * @internal
     */
    const FUNCTION_SET_GROUP = 2;

    /**
     * @internal
     */
    const FUNCTION_GET_GROUP = 3;

    /**
     * @internal
     */
    const FUNCTION_GET_AVAILABLE_FOR_GROUP = 4;

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
    const FUNCTION_SET_INTERRUPT = 7;

    /**
     * @internal
     */
    const FUNCTION_GET_INTERRUPT = 8;

    /**
     * @internal
     */
    const FUNCTION_GET_EDGE_COUNT = 10;

    /**
     * @internal
     */
    const FUNCTION_SET_EDGE_COUNT_CONFIG = 11;

    /**
     * @internal
     */
    const FUNCTION_GET_EDGE_COUNT_CONFIG = 12;

    /**
     * @internal
     */
    const FUNCTION_GET_IDENTITY = 255;

    const EDGE_TYPE_RISING = 0;
    const EDGE_TYPE_FALLING = 1;
    const EDGE_TYPE_BOTH = 2;

    const DEVICE_IDENTIFIER = 223;

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

        $this->responseExpected[self::FUNCTION_GET_VALUE] = self::RESPONSE_EXPECTED_ALWAYS_TRUE;
        $this->responseExpected[self::FUNCTION_SET_GROUP] = self::RESPONSE_EXPECTED_FALSE;
        $this->responseExpected[self::FUNCTION_GET_GROUP] = self::RESPONSE_EXPECTED_ALWAYS_TRUE;
        $this->responseExpected[self::FUNCTION_GET_AVAILABLE_FOR_GROUP] = self::RESPONSE_EXPECTED_ALWAYS_TRUE;
        $this->responseExpected[self::FUNCTION_SET_DEBOUNCE_PERIOD] = self::RESPONSE_EXPECTED_TRUE;
        $this->responseExpected[self::FUNCTION_GET_DEBOUNCE_PERIOD] = self::RESPONSE_EXPECTED_ALWAYS_TRUE;
        $this->responseExpected[self::FUNCTION_SET_INTERRUPT] = self::RESPONSE_EXPECTED_TRUE;
        $this->responseExpected[self::FUNCTION_GET_INTERRUPT] = self::RESPONSE_EXPECTED_ALWAYS_TRUE;
        $this->responseExpected[self::CALLBACK_INTERRUPT] = self::RESPONSE_EXPECTED_ALWAYS_FALSE;
        $this->responseExpected[self::FUNCTION_GET_EDGE_COUNT] = self::RESPONSE_EXPECTED_ALWAYS_TRUE;
        $this->responseExpected[self::FUNCTION_SET_EDGE_COUNT_CONFIG] = self::RESPONSE_EXPECTED_FALSE;
        $this->responseExpected[self::FUNCTION_GET_EDGE_COUNT_CONFIG] = self::RESPONSE_EXPECTED_ALWAYS_TRUE;
        $this->responseExpected[self::FUNCTION_GET_IDENTITY] = self::RESPONSE_EXPECTED_ALWAYS_TRUE;

        $this->callbackWrappers[self::CALLBACK_INTERRUPT] = 'callbackWrapperInterrupt';
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
     * Returns the input value with a bitmask. The bitmask
     * is 16 bit long, *true* refers to high and *false* refers to 
     * low.
     * 
     * For example: The value 0b0000000000000011 means that pins 0-1 
     * are high and the other pins are low.
     * 
     * If no groups are used (see BrickletIndustrialDigitalIn4::setGroup()), the pins correspond to the
     * markings on the Digital In 4 Bricklet.
     * 
     * If groups are used, the pins correspond to the element in the group.
     * Element 1 in the group will get pins 0-3, element 2 pins 4-7, element 3
     * pins 8-11 and element 4 pins 12-15.
     * 
     * 
     * @return int
     */
    public function getValue()
    {
        $payload = '';

        $data = $this->sendRequest(self::FUNCTION_GET_VALUE, $payload);

        $payload = unpack('v1value_mask', $data);

        return $payload['value_mask'];
    }

    /**
     * Sets a group of Digital In 4 Bricklets that should work together. You can
     * find Bricklets that can be grouped together with BrickletIndustrialDigitalIn4::getAvailableForGroup().
     * 
     * The group consists of 4 elements. Element 1 in the group will get pins 0-3,
     * element 2 pins 4-7, element 3 pins 8-11 and element 4 pins 12-15.
     * 
     * Each element can either be one of the ports ('a' to 'd') or 'n' if it should
     * not be used.
     * 
     * For example: If you have two Digital In 4 Bricklets connected to port A and
     * port B respectively, you could call with "['a', 'b', 'n', 'n']".
     * 
     * Now the pins on the Digital In 4 on port A are assigned to 0-3 and the
     * pins on the Digital In 4 on port B are assigned to 4-7. It is now possible
     * to call BrickletIndustrialDigitalIn4::getValue() and read out two Bricklets at the same time.
     * 
     * Changing the group configuration resets the alle edge counter configurations
     * and values.
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

        $this->sendRequest(self::FUNCTION_SET_GROUP, $payload);
    }

    /**
     * Returns the group as set by BrickletIndustrialDigitalIn4::setGroup()
     * 
     * 
     * @return array
     */
    public function getGroup()
    {
        $payload = '';

        $data = $this->sendRequest(self::FUNCTION_GET_GROUP, $payload);

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

        $data = $this->sendRequest(self::FUNCTION_GET_AVAILABLE_FOR_GROUP, $payload);

        $payload = unpack('C1available', $data);

        return $payload['available'];
    }

    /**
     * Sets the debounce period of the BrickletIndustrialDigitalIn4::CALLBACK_INTERRUPT callback in ms.
     * 
     * For example: If you set this value to 100, you will get the interrupt
     * maximal every 100ms. This is necessary if something that bounces is
     * connected to the Digital In 4 Bricklet, such as a button.
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
     * Returns the debounce period as set by BrickletIndustrialDigitalIn4::setDebouncePeriod().
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
     * For example: An interrupt bitmask of 9 (0b0000000000001001) will 
     * enable the interrupt for pins 0 and 3.
     * 
     * The interrupts use the grouping as set by BrickletIndustrialDigitalIn4::setGroup().
     * 
     * The interrupt is delivered with the callback BrickletIndustrialDigitalIn4::CALLBACK_INTERRUPT.
     * 
     * @param int $interrupt_mask
     * 
     * @return void
     */
    public function setInterrupt($interrupt_mask)
    {
        $payload = '';
        $payload .= pack('v', $interrupt_mask);

        $this->sendRequest(self::FUNCTION_SET_INTERRUPT, $payload);
    }

    /**
     * Returns the interrupt bitmask as set by BrickletIndustrialDigitalIn4::setInterrupt().
     * 
     * 
     * @return int
     */
    public function getInterrupt()
    {
        $payload = '';

        $data = $this->sendRequest(self::FUNCTION_GET_INTERRUPT, $payload);

        $payload = unpack('v1interrupt_mask', $data);

        return $payload['interrupt_mask'];
    }

    /**
     * Returns the current value of the edge counter for the selected pin. You can
     * configure the edges that are counted with BrickletIndustrialDigitalIn4::setEdgeCountConfig().
     * 
     * If you set the reset counter to *true*, the count is set back to 0
     * directly after it is read.
     * 
     * .. versionadded:: 2.0.1~(Plugin)
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
     * Configures the edge counter for the selected pins.
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
     * .. versionadded:: 2.0.1~(Plugin)
     * 
     * @param int $selection_mask
     * @param int $edge_type
     * @param int $debounce
     * 
     * @return void
     */
    public function setEdgeCountConfig($selection_mask, $edge_type, $debounce)
    {
        $payload = '';
        $payload .= pack('v', $selection_mask);
        $payload .= pack('C', $edge_type);
        $payload .= pack('C', $debounce);

        $this->sendRequest(self::FUNCTION_SET_EDGE_COUNT_CONFIG, $payload);
    }

    /**
     * Returns the edge type and debounce time for the selected pin as set by
     * BrickletIndustrialDigitalIn4::setEdgeCountConfig().
     * 
     * .. versionadded:: 2.0.1~(Plugin)
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
        $payload = unpack('v1interrupt_mask/v1value_mask', $data);

        array_push($result, $payload['interrupt_mask']);
        array_push($result, $payload['value_mask']);

        call_user_func_array($this->registeredCallbacks[self::CALLBACK_INTERRUPT], $result);
    }
}

?>
