<?php

/* ***********************************************************
 * This file was automatically generated on 2012-09-28.      *
 *                                                           *
 * If you have a bugfix for this file and want to commit it, *
 * please fix the bug in the generator. You can find a link  *
 * to the generator git on tinkerforge.com                   *
 *************************************************************/

namespace Tinkerforge;

require_once(__DIR__ . '/IPConnection.php');

/**
 * Device for receiving GPS position
 */
class BrickletGPS extends Device
{

    /**
     * 
     */
    const CALLBACK_COORDINATES = 8;

    /**
     * 
     */
    const CALLBACK_STATUS = 9;


    /**
     * @internal
     */
    const FUNCTION_GET_COORDINATES = 1;

    /**
     * @internal
     */
    const FUNCTION_GET_STATUS = 2;

    /**
     * @internal
     */
    const FUNCTION_RESTART = 3;

    /**
     * @internal
     */
    const FUNCTION_SET_COORDINATES_CALLBACK_PERIOD = 4;

    /**
     * @internal
     */
    const FUNCTION_GET_COORDINATES_CALLBACK_PERIOD = 5;

    /**
     * @internal
     */
    const FUNCTION_SET_STATUS_CALLBACK_PERIOD = 6;

    /**
     * @internal
     */
    const FUNCTION_GET_STATUS_CALLBACK_PERIOD = 7;

    /**
     * Creates an object with the unique device ID $uid. This object can
     * then be added to the IP connection.
     *
     * @param string $uid
     */
    public function __construct($uid)
    {
        parent::__construct($uid);

        $this->expectedName = 'GPS Bricklet';

        $this->bindingVersion = array(1, 0, 0);

        $this->callbackWrappers[self::CALLBACK_COORDINATES] = 'callbackWrapperCoordinates';
        $this->callbackWrappers[self::CALLBACK_STATUS] = 'callbackWrapperStatus';
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
     * 
     * 
     * 
     * @return array
     */
    public function getCoordinates()
    {
        $result = array();

        $payload = '';

        $data = $this->sendRequestExpectResponse(self::FUNCTION_GET_COORDINATES, $payload, 16);

        $payload = unpack('c1ns/v2latitude/c1ew/v2longitude/v1pdop/v1hdop/v1vdop', $data);

        $result['ns'] = chr($payload['ns']);
        $result['latitude'] = IPConnection::collectUnpackedArray($payload, 'latitude', 2);
        $result['ew'] = chr($payload['ew']);
        $result['longitude'] = IPConnection::collectUnpackedArray($payload, 'longitude', 2);
        $result['pdop'] = $payload['pdop'];
        $result['hdop'] = $payload['hdop'];
        $result['vdop'] = $payload['vdop'];

        return $result;
    }

    /**
     * 
     * 
     * 
     * @return array
     */
    public function getStatus()
    {
        $result = array();

        $payload = '';

        $data = $this->sendRequestExpectResponse(self::FUNCTION_GET_STATUS, $payload, 19);

        $payload = unpack('C1fix/C1satellites_view/C1satellites_used/v1speed/v1course/V1date/V1time/v1altitude/v1altitude_accuracy', $data);

        $result['fix'] = $payload['fix'];
        $result['satellites_view'] = $payload['satellites_view'];
        $result['satellites_used'] = $payload['satellites_used'];
        $result['speed'] = $payload['speed'];
        $result['course'] = $payload['course'];
        $result['date'] = IPConnection::fixUnpackedUInt32($payload['date']);
        $result['time'] = IPConnection::fixUnpackedUInt32($payload['time']);
        $result['altitude'] = IPConnection::fixUnpackedInt16($payload['altitude']);
        $result['altitude_accuracy'] = IPConnection::fixUnpackedInt16($payload['altitude_accuracy']);

        return $result;
    }

    /**
     * 
     * 
     * @param int $restart_type
     * 
     * @return void
     */
    public function restart($restart_type)
    {
        $payload = '';
        $payload .= pack('C', $restart_type);

        $this->sendRequestNoResponse(self::FUNCTION_RESTART, $payload);
    }

    /**
     * 
     * 
     * @param int $period
     * 
     * @return void
     */
    public function setCoordinatesCallbackPeriod($period)
    {
        $payload = '';
        $payload .= pack('V', $period);

        $this->sendRequestNoResponse(self::FUNCTION_SET_COORDINATES_CALLBACK_PERIOD, $payload);
    }

    /**
     * Returns the period as set by BrickletGPS::setCoordinatesCallbackPeriod().
     * 
     * 
     * @return int
     */
    public function getCoordinatesCallbackPeriod()
    {
        $payload = '';

        $data = $this->sendRequestExpectResponse(self::FUNCTION_GET_COORDINATES_CALLBACK_PERIOD, $payload, 4);

        $payload = unpack('V1period', $data);

        return IPConnection::fixUnpackedUInt32($payload['period']);
    }

    /**
     * 
     * 
     * @param int $period
     * 
     * @return void
     */
    public function setStatusCallbackPeriod($period)
    {
        $payload = '';
        $payload .= pack('V', $period);

        $this->sendRequestNoResponse(self::FUNCTION_SET_STATUS_CALLBACK_PERIOD, $payload);
    }

    /**
     * Returns the period as set by BrickletGPS::getStatusCallbackPeriod().
     * 
     * 
     * @return int
     */
    public function getStatusCallbackPeriod()
    {
        $payload = '';

        $data = $this->sendRequestExpectResponse(self::FUNCTION_GET_STATUS_CALLBACK_PERIOD, $payload, 4);

        $payload = unpack('V1period', $data);

        return IPConnection::fixUnpackedUInt32($payload['period']);
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
    public function callbackWrapperCoordinates($data)
    {
        $result = array();
        $payload = unpack('c1ns/v2latitude/c1ew/v2longitude/v1pdop/v1hdop/v1vdop', $data);

        array_push($result, chr($payload['ns']));
        array_push($result, IPConnection::collectUnpackedArray($payload, 'latitude', 2));
        array_push($result, chr($payload['ew']));
        array_push($result, IPConnection::collectUnpackedArray($payload, 'longitude', 2));
        array_push($result, $payload['pdop']);
        array_push($result, $payload['hdop']);
        array_push($result, $payload['vdop']);

        call_user_func_array($this->registeredCallbacks[self::CALLBACK_COORDINATES], $result);
    }

    /**
     * @internal
     * @param string $data
     */
    public function callbackWrapperStatus($data)
    {
        $result = array();
        $payload = unpack('C1fix/C1satellites_view/C1satellites_used/v1speed/v1course/V1date/V1time/v1altitude/v1altitude_accuracy', $data);

        array_push($result, $payload['fix']);
        array_push($result, $payload['satellites_view']);
        array_push($result, $payload['satellites_used']);
        array_push($result, $payload['speed']);
        array_push($result, $payload['course']);
        array_push($result, IPConnection::fixUnpackedUInt32($payload['date']));
        array_push($result, IPConnection::fixUnpackedUInt32($payload['time']));
        array_push($result, IPConnection::fixUnpackedInt16($payload['altitude']));
        array_push($result, IPConnection::fixUnpackedInt16($payload['altitude_accuracy']));

        call_user_func_array($this->registeredCallbacks[self::CALLBACK_STATUS], $result);
    }
}

?>
