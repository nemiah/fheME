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
 * Device for controlling two relays
 */
class BrickletDualRelay extends Device
{

    /**
     * This callback is triggered whenever a monoflop timer reaches 0. The 
     * parameter contain the relay (1 or 2) and the current state of the relay 
     * (the state after the monoflop).
     * 
     * .. versionadded:: 1.1.1~(Plugin)
     */
    const CALLBACK_MONOFLOP_DONE = 5;


    /**
     * @internal
     */
    const FUNCTION_SET_STATE = 1;

    /**
     * @internal
     */
    const FUNCTION_GET_STATE = 2;

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
    const FUNCTION_SET_SELECTED_STATE = 6;

    /**
     * @internal
     */
    const FUNCTION_GET_IDENTITY = 255;


    const DEVICE_IDENTIFIER = 26;

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

        $this->responseExpected[self::FUNCTION_SET_STATE] = self::RESPONSE_EXPECTED_FALSE;
        $this->responseExpected[self::FUNCTION_GET_STATE] = self::RESPONSE_EXPECTED_ALWAYS_TRUE;
        $this->responseExpected[self::FUNCTION_SET_MONOFLOP] = self::RESPONSE_EXPECTED_FALSE;
        $this->responseExpected[self::FUNCTION_GET_MONOFLOP] = self::RESPONSE_EXPECTED_ALWAYS_TRUE;
        $this->responseExpected[self::CALLBACK_MONOFLOP_DONE] = self::RESPONSE_EXPECTED_ALWAYS_FALSE;
        $this->responseExpected[self::FUNCTION_SET_SELECTED_STATE] = self::RESPONSE_EXPECTED_FALSE;
        $this->responseExpected[self::FUNCTION_GET_IDENTITY] = self::RESPONSE_EXPECTED_ALWAYS_TRUE;

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
     * Sets the state of the relays, *true* means on and *false* means off. 
     * For example: (true, false) turns relay 1 on and relay 2 off.
     * 
     * If you just want to set one of the relays and don't know the current state
     * of the other relay, you can get the state with BrickletDualRelay::getState() or you
     * can use BrickletDualRelay::setSelectedState().
     * 
     * Running monoflop timers will be overwritten if this function is called.
     * 
     * The default value is (*false*, *false*).
     * 
     * @param bool $relay1
     * @param bool $relay2
     * 
     * @return void
     */
    public function setState($relay1, $relay2)
    {
        $payload = '';
        $payload .= pack('C', intval((bool)$relay1));
        $payload .= pack('C', intval((bool)$relay2));

        $this->sendRequest(self::FUNCTION_SET_STATE, $payload);
    }

    /**
     * Returns the state of the relays, *true* means on and *false* means off.
     * 
     * 
     * @return array
     */
    public function getState()
    {
        $result = array();

        $payload = '';

        $data = $this->sendRequest(self::FUNCTION_GET_STATE, $payload);

        $payload = unpack('C1relay1/C1relay2', $data);

        $result['relay1'] = (bool)$payload['relay1'];
        $result['relay2'] = (bool)$payload['relay2'];

        return $result;
    }

    /**
     * The first parameter can be 1 or 2 (relay 1 or relay 2). The second parameter 
     * is the desired state of the relay (*true* means on and *false* means off).
     * The third parameter indicates the time (in ms) that the relay should hold 
     * the state.
     * 
     * If this function is called with the parameters (1, true, 1500):
     * Relay 1 will turn on and in 1.5s it will turn off again.
     * 
     * A monoflop can be used as a failsafe mechanism. For example: Lets assume you 
     * have a RS485 bus and a Dual Relay Bricklet connected to one of the slave 
     * stacks. You can now call this function every second, with a time parameter
     * of two seconds. The relay will be on all the time. If now the RS485 
     * connection is lost, the relay will turn off in at most two seconds.
     * 
     * .. versionadded:: 1.1.1~(Plugin)
     * 
     * @param int $relay
     * @param bool $state
     * @param int $time
     * 
     * @return void
     */
    public function setMonoflop($relay, $state, $time)
    {
        $payload = '';
        $payload .= pack('C', $relay);
        $payload .= pack('C', intval((bool)$state));
        $payload .= pack('V', $time);

        $this->sendRequest(self::FUNCTION_SET_MONOFLOP, $payload);
    }

    /**
     * Returns (for the given relay) the current state and the time as set by 
     * BrickletDualRelay::setMonoflop() as well as the remaining time until the state flips.
     * 
     * If the timer is not running currently, the remaining time will be returned
     * as 0.
     * 
     * .. versionadded:: 1.1.1~(Plugin)
     * 
     * @param int $relay
     * 
     * @return array
     */
    public function getMonoflop($relay)
    {
        $result = array();

        $payload = '';
        $payload .= pack('C', $relay);

        $data = $this->sendRequest(self::FUNCTION_GET_MONOFLOP, $payload);

        $payload = unpack('C1state/V1time/V1time_remaining', $data);

        $result['state'] = (bool)$payload['state'];
        $result['time'] = IPConnection::fixUnpackedUInt32($payload['time']);
        $result['time_remaining'] = IPConnection::fixUnpackedUInt32($payload['time_remaining']);

        return $result;
    }

    /**
     * Sets the state of the selected relay (1 or 2), *true* means on and *false* means off. 
     * 
     * The other relay remains untouched.
     * 
     * .. versionadded:: 2.0.0~(Plugin)
     * 
     * @param int $relay
     * @param bool $state
     * 
     * @return void
     */
    public function setSelectedState($relay, $state)
    {
        $payload = '';
        $payload .= pack('C', $relay);
        $payload .= pack('C', intval((bool)$state));

        $this->sendRequest(self::FUNCTION_SET_SELECTED_STATE, $payload);
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
    public function callbackWrapperMonoflopDone($data)
    {
        $result = array();
        $payload = unpack('C1relay/C1state', $data);

        array_push($result, $payload['relay']);
        array_push($result, (bool)$payload['state']);

        call_user_func_array($this->registeredCallbacks[self::CALLBACK_MONOFLOP_DONE], $result);
    }
}

?>
