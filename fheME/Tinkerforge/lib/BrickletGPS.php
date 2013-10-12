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
 * Device for receiving GPS position
 */
class BrickletGPS extends Device
{

    /**
     * This callback is triggered periodically with the period that is set by
     * BrickletGPS::setCoordinatesCallbackPeriod(). The parameters are the same
     * as for BrickletGPS::getCoordinates().
     * 
     * BrickletGPS::CALLBACK_COORDINATES is only triggered if the coordinates changed since the
     * last triggering and if there is currently a fix as indicated by
     * BrickletGPS::getStatus().
     */
    const CALLBACK_COORDINATES = 17;

    /**
     * This callback is triggered periodically with the period that is set by
     * BrickletGPS::setStatusCallbackPeriod(). The parameters are the same
     * as for BrickletGPS::getStatus().
     * 
     * BrickletGPS::CALLBACK_STATUS is only triggered if the status changed since the
     * last triggering.
     */
    const CALLBACK_STATUS = 18;

    /**
     * This callback is triggered periodically with the period that is set by
     * BrickletGPS::setAltitudeCallbackPeriod(). The parameters are the same
     * as for BrickletGPS::getAltitude().
     * 
     * BrickletGPS::CALLBACK_ALTITUDE is only triggered if the altitude changed since the
     * last triggering and if there is currently a fix as indicated by
     * BrickletGPS::getStatus().
     */
    const CALLBACK_ALTITUDE = 19;

    /**
     * This callback is triggered periodically with the period that is set by
     * BrickletGPS::setMotionCallbackPeriod(). The parameters are the same
     * as for BrickletGPS::getMotion().
     * 
     * BrickletGPS::CALLBACK_MOTION is only triggered if the motion changed since the
     * last triggering and if there is currently a fix as indicated by
     * BrickletGPS::getStatus().
     */
    const CALLBACK_MOTION = 20;

    /**
     * This callback is triggered periodically with the period that is set by
     * BrickletGPS::setDateTimeCallbackPeriod(). The parameters are the same
     * as for BrickletGPS::getDateTime().
     * 
     * BrickletGPS::CALLBACK_DATE_TIME is only triggered if the date or time changed since the
     * last triggering.
     */
    const CALLBACK_DATE_TIME = 21;


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
    const FUNCTION_GET_ALTITUDE = 3;

    /**
     * @internal
     */
    const FUNCTION_GET_MOTION = 4;

    /**
     * @internal
     */
    const FUNCTION_GET_DATE_TIME = 5;

    /**
     * @internal
     */
    const FUNCTION_RESTART = 6;

    /**
     * @internal
     */
    const FUNCTION_SET_COORDINATES_CALLBACK_PERIOD = 7;

    /**
     * @internal
     */
    const FUNCTION_GET_COORDINATES_CALLBACK_PERIOD = 8;

    /**
     * @internal
     */
    const FUNCTION_SET_STATUS_CALLBACK_PERIOD = 9;

    /**
     * @internal
     */
    const FUNCTION_GET_STATUS_CALLBACK_PERIOD = 10;

    /**
     * @internal
     */
    const FUNCTION_SET_ALTITUDE_CALLBACK_PERIOD = 11;

    /**
     * @internal
     */
    const FUNCTION_GET_ALTITUDE_CALLBACK_PERIOD = 12;

    /**
     * @internal
     */
    const FUNCTION_SET_DATE_TIME_CALLBACK_PERIOD = 13;

    /**
     * @internal
     */
    const FUNCTION_GET_DATE_TIME_CALLBACK_PERIOD = 14;

    /**
     * @internal
     */
    const FUNCTION_SET_MOTION_CALLBACK_PERIOD = 15;

    /**
     * @internal
     */
    const FUNCTION_GET_MOTION_CALLBACK_PERIOD = 16;

    /**
     * @internal
     */
    const FUNCTION_GET_IDENTITY = 255;

    const FIX_NO_FIX = 1;
    const FIX_2D_FIX = 2;
    const FIX_3D_FIX = 3;
    const RESTART_TYPE_HOT_START = 0;
    const RESTART_TYPE_WARM_START = 1;
    const RESTART_TYPE_COLD_START = 2;
    const RESTART_TYPE_FACTORY_RESET = 3;

    const DEVICE_IDENTIFIER = 222;

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

        $this->responseExpected[self::FUNCTION_GET_COORDINATES] = self::RESPONSE_EXPECTED_ALWAYS_TRUE;
        $this->responseExpected[self::FUNCTION_GET_STATUS] = self::RESPONSE_EXPECTED_ALWAYS_TRUE;
        $this->responseExpected[self::FUNCTION_GET_ALTITUDE] = self::RESPONSE_EXPECTED_ALWAYS_TRUE;
        $this->responseExpected[self::FUNCTION_GET_MOTION] = self::RESPONSE_EXPECTED_ALWAYS_TRUE;
        $this->responseExpected[self::FUNCTION_GET_DATE_TIME] = self::RESPONSE_EXPECTED_ALWAYS_TRUE;
        $this->responseExpected[self::FUNCTION_RESTART] = self::RESPONSE_EXPECTED_FALSE;
        $this->responseExpected[self::FUNCTION_SET_COORDINATES_CALLBACK_PERIOD] = self::RESPONSE_EXPECTED_TRUE;
        $this->responseExpected[self::FUNCTION_GET_COORDINATES_CALLBACK_PERIOD] = self::RESPONSE_EXPECTED_ALWAYS_TRUE;
        $this->responseExpected[self::FUNCTION_SET_STATUS_CALLBACK_PERIOD] = self::RESPONSE_EXPECTED_TRUE;
        $this->responseExpected[self::FUNCTION_GET_STATUS_CALLBACK_PERIOD] = self::RESPONSE_EXPECTED_ALWAYS_TRUE;
        $this->responseExpected[self::FUNCTION_SET_ALTITUDE_CALLBACK_PERIOD] = self::RESPONSE_EXPECTED_TRUE;
        $this->responseExpected[self::FUNCTION_GET_ALTITUDE_CALLBACK_PERIOD] = self::RESPONSE_EXPECTED_ALWAYS_TRUE;
        $this->responseExpected[self::FUNCTION_SET_DATE_TIME_CALLBACK_PERIOD] = self::RESPONSE_EXPECTED_TRUE;
        $this->responseExpected[self::FUNCTION_GET_DATE_TIME_CALLBACK_PERIOD] = self::RESPONSE_EXPECTED_ALWAYS_TRUE;
        $this->responseExpected[self::FUNCTION_SET_MOTION_CALLBACK_PERIOD] = self::RESPONSE_EXPECTED_TRUE;
        $this->responseExpected[self::FUNCTION_GET_MOTION_CALLBACK_PERIOD] = self::RESPONSE_EXPECTED_ALWAYS_TRUE;
        $this->responseExpected[self::CALLBACK_COORDINATES] = self::RESPONSE_EXPECTED_ALWAYS_FALSE;
        $this->responseExpected[self::CALLBACK_STATUS] = self::RESPONSE_EXPECTED_ALWAYS_FALSE;
        $this->responseExpected[self::CALLBACK_ALTITUDE] = self::RESPONSE_EXPECTED_ALWAYS_FALSE;
        $this->responseExpected[self::CALLBACK_MOTION] = self::RESPONSE_EXPECTED_ALWAYS_FALSE;
        $this->responseExpected[self::CALLBACK_DATE_TIME] = self::RESPONSE_EXPECTED_ALWAYS_FALSE;
        $this->responseExpected[self::FUNCTION_GET_IDENTITY] = self::RESPONSE_EXPECTED_ALWAYS_TRUE;

        $this->callbackWrappers[self::CALLBACK_COORDINATES] = 'callbackWrapperCoordinates';
        $this->callbackWrappers[self::CALLBACK_STATUS] = 'callbackWrapperStatus';
        $this->callbackWrappers[self::CALLBACK_ALTITUDE] = 'callbackWrapperAltitude';
        $this->callbackWrappers[self::CALLBACK_MOTION] = 'callbackWrapperMotion';
        $this->callbackWrappers[self::CALLBACK_DATE_TIME] = 'callbackWrapperDateTime';
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
     * Returns the GPS coordinates. Latitude and longitude are given in the
     * ``DD.dddddd°`` format, the value 57123468 means 57.123468°.
     * The parameter ``ns`` and ``ew`` are the cardinal directions for
     * latitude and longitude. Possible values for ``ns`` and ``ew`` are 'N', 'S', 'E'
     * and 'W' (north, south, east and west).
     * 
     * PDOP, HDOP and VDOP are the dilution of precision (DOP) values. They specify
     * the additional multiplicative effect of GPS satellite geometry on GPS 
     * precision. See 
     * `here <http://en.wikipedia.org/wiki/Dilution_of_precision_(GPS)>`__
     * for more information. The values are give in hundredths.
     * 
     * EPE is the "Estimated Position Error". The EPE is given in cm. This is not the
     * absolute maximum error, it is the error with a specific confidence. See
     * `here <http://www.nps.gov/gis/gps/WhatisEPE.html>`__ for more information.
     * 
     * This data is only valid if there is currently a fix as indicated by
     * BrickletGPS::getStatus().
     * 
     * 
     * @return array
     */
    public function getCoordinates()
    {
        $result = array();

        $payload = '';

        $data = $this->sendRequest(self::FUNCTION_GET_COORDINATES, $payload);

        $payload = unpack('V1latitude/c1ns/V1longitude/c1ew/v1pdop/v1hdop/v1vdop/v1epe', $data);

        $result['latitude'] = IPConnection::fixUnpackedUInt32($payload['latitude']);
        $result['ns'] = chr($payload['ns']);
        $result['longitude'] = IPConnection::fixUnpackedUInt32($payload['longitude']);
        $result['ew'] = chr($payload['ew']);
        $result['pdop'] = $payload['pdop'];
        $result['hdop'] = $payload['hdop'];
        $result['vdop'] = $payload['vdop'];
        $result['epe'] = $payload['epe'];

        return $result;
    }

    /**
     * Returns the current fix status, the number of satellites that are in view and
     * the number of satellites that are currently used.
     * 
     * Possible fix status values can be:
     * 
     * <code>
     *  "Value", "Description"
     * 
     *  "1", "No Fix"
     *  "2", "2D Fix"
     *  "3", "3D Fix"
     * </code>
     * 
     * 
     * @return array
     */
    public function getStatus()
    {
        $result = array();

        $payload = '';

        $data = $this->sendRequest(self::FUNCTION_GET_STATUS, $payload);

        $payload = unpack('C1fix/C1satellites_view/C1satellites_used', $data);

        $result['fix'] = $payload['fix'];
        $result['satellites_view'] = $payload['satellites_view'];
        $result['satellites_used'] = $payload['satellites_used'];

        return $result;
    }

    /**
     * Returns the current altitude and corresponding geoidal separation.
     * 
     * Both values are given in cm.
     * 
     * This data is only valid if there is currently a fix as indicated by
     * BrickletGPS::getStatus().
     * 
     * 
     * @return array
     */
    public function getAltitude()
    {
        $result = array();

        $payload = '';

        $data = $this->sendRequest(self::FUNCTION_GET_ALTITUDE, $payload);

        $payload = unpack('V1altitude/V1geoidal_separation', $data);

        $result['altitude'] = IPConnection::fixUnpackedUInt32($payload['altitude']);
        $result['geoidal_separation'] = IPConnection::fixUnpackedUInt32($payload['geoidal_separation']);

        return $result;
    }

    /**
     * Returns the current course and speed. Course is given in hundredths degree
     * and speed is given in hundredths km/h. A course of 0° means the Bricklet is
     * traveling north bound and 90° means it is traveling east bound.
     * 
     * Please note that this only returns useful values if an actual movement
     * is present.
     * 
     * This data is only valid if there is currently a fix as indicated by
     * BrickletGPS::getStatus().
     * 
     * 
     * @return array
     */
    public function getMotion()
    {
        $result = array();

        $payload = '';

        $data = $this->sendRequest(self::FUNCTION_GET_MOTION, $payload);

        $payload = unpack('V1course/V1speed', $data);

        $result['course'] = IPConnection::fixUnpackedUInt32($payload['course']);
        $result['speed'] = IPConnection::fixUnpackedUInt32($payload['speed']);

        return $result;
    }

    /**
     * Returns the current date and time. The date is
     * given in the format ``ddmmyy`` and the time is given
     * in the format ``hhmmss.sss``. For example, 140713 means
     * 14.05.13 as date and 195923568 means 19:59:23.568 as time.
     * 
     * 
     * @return array
     */
    public function getDateTime()
    {
        $result = array();

        $payload = '';

        $data = $this->sendRequest(self::FUNCTION_GET_DATE_TIME, $payload);

        $payload = unpack('V1date/V1time', $data);

        $result['date'] = IPConnection::fixUnpackedUInt32($payload['date']);
        $result['time'] = IPConnection::fixUnpackedUInt32($payload['time']);

        return $result;
    }

    /**
     * Restarts the GPS Bricklet, the following restart types are available:
     * 
     * <code>
     *  "Value", "Description"
     * 
     *  "0", "Hot start (use all available data in the NV store)"
     *  "1", "Warm start (don't use ephemeris at restart)"
     *  "2", "Cold start (don't use time, position, almanacs and ephemeris at restart)"
     *  "3", "Factory reset (clear all system/user configurations at restart)"
     * </code>
     * 
     * @param int $restart_type
     * 
     * @return void
     */
    public function restart($restart_type)
    {
        $payload = '';
        $payload .= pack('C', $restart_type);

        $this->sendRequest(self::FUNCTION_RESTART, $payload);
    }

    /**
     * Sets the period in ms with which the BrickletGPS::CALLBACK_COORDINATES callback is triggered
     * periodically. A value of 0 turns the callback off.
     * 
     * BrickletGPS::CALLBACK_COORDINATES is only triggered if the coordinates changed since the
     * last triggering.
     * 
     * The default value is 0.
     * 
     * @param int $period
     * 
     * @return void
     */
    public function setCoordinatesCallbackPeriod($period)
    {
        $payload = '';
        $payload .= pack('V', $period);

        $this->sendRequest(self::FUNCTION_SET_COORDINATES_CALLBACK_PERIOD, $payload);
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

        $data = $this->sendRequest(self::FUNCTION_GET_COORDINATES_CALLBACK_PERIOD, $payload);

        $payload = unpack('V1period', $data);

        return IPConnection::fixUnpackedUInt32($payload['period']);
    }

    /**
     * Sets the period in ms with which the BrickletGPS::CALLBACK_STATUS callback is triggered
     * periodically. A value of 0 turns the callback off.
     * 
     * BrickletGPS::CALLBACK_STATUS is only triggered if the status changed since the
     * last triggering.
     * 
     * The default value is 0.
     * 
     * @param int $period
     * 
     * @return void
     */
    public function setStatusCallbackPeriod($period)
    {
        $payload = '';
        $payload .= pack('V', $period);

        $this->sendRequest(self::FUNCTION_SET_STATUS_CALLBACK_PERIOD, $payload);
    }

    /**
     * Returns the period as set by BrickletGPS::setStatusCallbackPeriod().
     * 
     * 
     * @return int
     */
    public function getStatusCallbackPeriod()
    {
        $payload = '';

        $data = $this->sendRequest(self::FUNCTION_GET_STATUS_CALLBACK_PERIOD, $payload);

        $payload = unpack('V1period', $data);

        return IPConnection::fixUnpackedUInt32($payload['period']);
    }

    /**
     * Sets the period in ms with which the BrickletGPS::CALLBACK_ALTITUDE callback is triggered
     * periodically. A value of 0 turns the callback off.
     * 
     * BrickletGPS::CALLBACK_ALTITUDE is only triggered if the altitude changed since the
     * last triggering.
     * 
     * The default value is 0.
     * 
     * @param int $period
     * 
     * @return void
     */
    public function setAltitudeCallbackPeriod($period)
    {
        $payload = '';
        $payload .= pack('V', $period);

        $this->sendRequest(self::FUNCTION_SET_ALTITUDE_CALLBACK_PERIOD, $payload);
    }

    /**
     * Returns the period as set by BrickletGPS::setAltitudeCallbackPeriod().
     * 
     * 
     * @return int
     */
    public function getAltitudeCallbackPeriod()
    {
        $payload = '';

        $data = $this->sendRequest(self::FUNCTION_GET_ALTITUDE_CALLBACK_PERIOD, $payload);

        $payload = unpack('V1period', $data);

        return IPConnection::fixUnpackedUInt32($payload['period']);
    }

    /**
     * Sets the period in ms with which the BrickletGPS::CALLBACK_DATE_TIME callback is triggered
     * periodically. A value of 0 turns the callback off.
     * 
     * BrickletGPS::CALLBACK_DATE_TIME is only triggered if the date or time changed since the
     * last triggering.
     * 
     * The default value is 0.
     * 
     * @param int $period
     * 
     * @return void
     */
    public function setDateTimeCallbackPeriod($period)
    {
        $payload = '';
        $payload .= pack('V', $period);

        $this->sendRequest(self::FUNCTION_SET_DATE_TIME_CALLBACK_PERIOD, $payload);
    }

    /**
     * Returns the period as set by BrickletGPS::setDateTimeCallbackPeriod().
     * 
     * 
     * @return int
     */
    public function getDateTimeCallbackPeriod()
    {
        $payload = '';

        $data = $this->sendRequest(self::FUNCTION_GET_DATE_TIME_CALLBACK_PERIOD, $payload);

        $payload = unpack('V1period', $data);

        return IPConnection::fixUnpackedUInt32($payload['period']);
    }

    /**
     * Sets the period in ms with which the BrickletGPS::CALLBACK_MOTION callback is triggered
     * periodically. A value of 0 turns the callback off.
     * 
     * BrickletGPS::CALLBACK_MOTION is only triggered if the motion changed since the
     * last triggering.
     * 
     * The default value is 0.
     * 
     * @param int $period
     * 
     * @return void
     */
    public function setMotionCallbackPeriod($period)
    {
        $payload = '';
        $payload .= pack('V', $period);

        $this->sendRequest(self::FUNCTION_SET_MOTION_CALLBACK_PERIOD, $payload);
    }

    /**
     * Returns the period as set by BrickletGPS::setMotionCallbackPeriod().
     * 
     * 
     * @return int
     */
    public function getMotionCallbackPeriod()
    {
        $payload = '';

        $data = $this->sendRequest(self::FUNCTION_GET_MOTION_CALLBACK_PERIOD, $payload);

        $payload = unpack('V1period', $data);

        return IPConnection::fixUnpackedUInt32($payload['period']);
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
    public function callbackWrapperCoordinates($data)
    {
        $result = array();
        $payload = unpack('V1latitude/c1ns/V1longitude/c1ew/v1pdop/v1hdop/v1vdop/v1epe', $data);

        array_push($result, IPConnection::fixUnpackedUInt32($payload['latitude']));
        array_push($result, chr($payload['ns']));
        array_push($result, IPConnection::fixUnpackedUInt32($payload['longitude']));
        array_push($result, chr($payload['ew']));
        array_push($result, $payload['pdop']);
        array_push($result, $payload['hdop']);
        array_push($result, $payload['vdop']);
        array_push($result, $payload['epe']);

        call_user_func_array($this->registeredCallbacks[self::CALLBACK_COORDINATES], $result);
    }

    /**
     * @internal
     * @param string $data
     */
    public function callbackWrapperStatus($data)
    {
        $result = array();
        $payload = unpack('C1fix/C1satellites_view/C1satellites_used', $data);

        array_push($result, $payload['fix']);
        array_push($result, $payload['satellites_view']);
        array_push($result, $payload['satellites_used']);

        call_user_func_array($this->registeredCallbacks[self::CALLBACK_STATUS], $result);
    }

    /**
     * @internal
     * @param string $data
     */
    public function callbackWrapperAltitude($data)
    {
        $result = array();
        $payload = unpack('V1altitude/V1geoidal_separation', $data);

        array_push($result, IPConnection::fixUnpackedUInt32($payload['altitude']));
        array_push($result, IPConnection::fixUnpackedUInt32($payload['geoidal_separation']));

        call_user_func_array($this->registeredCallbacks[self::CALLBACK_ALTITUDE], $result);
    }

    /**
     * @internal
     * @param string $data
     */
    public function callbackWrapperMotion($data)
    {
        $result = array();
        $payload = unpack('V1course/V1speed', $data);

        array_push($result, IPConnection::fixUnpackedUInt32($payload['course']));
        array_push($result, IPConnection::fixUnpackedUInt32($payload['speed']));

        call_user_func_array($this->registeredCallbacks[self::CALLBACK_MOTION], $result);
    }

    /**
     * @internal
     * @param string $data
     */
    public function callbackWrapperDateTime($data)
    {
        $result = array();
        $payload = unpack('V1date/V1time', $data);

        array_push($result, IPConnection::fixUnpackedUInt32($payload['date']));
        array_push($result, IPConnection::fixUnpackedUInt32($payload['time']));

        call_user_func_array($this->registeredCallbacks[self::CALLBACK_DATE_TIME], $result);
    }
}

?>
