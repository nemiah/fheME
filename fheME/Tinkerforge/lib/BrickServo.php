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
 * Device for controlling up to seven servos
 */
class BrickServo extends Device
{

    /**
     * This callback is triggered when the input voltage drops below the value set by
     * BrickServo::setMinimumVoltage(). The parameter is the current voltage given
     * in mV.
     */
    const CALLBACK_UNDER_VOLTAGE = 26;

    /**
     * This callback is triggered when a position set by BrickServo::setPosition()
     * is reached. The parameters are the servo and the position that is reached.
     * 
     * <note>
     *  Since we can't get any feedback from the servo, this only works if the
     *  velocity (see BrickServo::setVelocity()) is set smaller or equal to the
     *  maximum velocity of the servo. Otherwise the servo will lag behind the
     *  control value and the callback will be triggered too early.
     * </note>
     */
    const CALLBACK_POSITION_REACHED = 27;

    /**
     * This callback is triggered when a velocity set by BrickServo::setVelocity()
     * is reached. The parameters are the servo and the velocity that is reached.
     * 
     * <note>
     *  Since we can't get any feedback from the servo, this only works if the
     *  acceleration (see BrickServo::setAcceleration()) is set smaller or equal to the
     *  maximum acceleration of the servo. Otherwise the servo will lag behind the
     *  control value and the callback will be triggered too early.
     * </note>
     */
    const CALLBACK_VELOCITY_REACHED = 28;


    /**
     * @internal
     */
    const FUNCTION_ENABLE = 1;

    /**
     * @internal
     */
    const FUNCTION_DISABLE = 2;

    /**
     * @internal
     */
    const FUNCTION_IS_ENABLED = 3;

    /**
     * @internal
     */
    const FUNCTION_SET_POSITION = 4;

    /**
     * @internal
     */
    const FUNCTION_GET_POSITION = 5;

    /**
     * @internal
     */
    const FUNCTION_GET_CURRENT_POSITION = 6;

    /**
     * @internal
     */
    const FUNCTION_SET_VELOCITY = 7;

    /**
     * @internal
     */
    const FUNCTION_GET_VELOCITY = 8;

    /**
     * @internal
     */
    const FUNCTION_GET_CURRENT_VELOCITY = 9;

    /**
     * @internal
     */
    const FUNCTION_SET_ACCELERATION = 10;

    /**
     * @internal
     */
    const FUNCTION_GET_ACCELERATION = 11;

    /**
     * @internal
     */
    const FUNCTION_SET_OUTPUT_VOLTAGE = 12;

    /**
     * @internal
     */
    const FUNCTION_GET_OUTPUT_VOLTAGE = 13;

    /**
     * @internal
     */
    const FUNCTION_SET_PULSE_WIDTH = 14;

    /**
     * @internal
     */
    const FUNCTION_GET_PULSE_WIDTH = 15;

    /**
     * @internal
     */
    const FUNCTION_SET_DEGREE = 16;

    /**
     * @internal
     */
    const FUNCTION_GET_DEGREE = 17;

    /**
     * @internal
     */
    const FUNCTION_SET_PERIOD = 18;

    /**
     * @internal
     */
    const FUNCTION_GET_PERIOD = 19;

    /**
     * @internal
     */
    const FUNCTION_GET_SERVO_CURRENT = 20;

    /**
     * @internal
     */
    const FUNCTION_GET_OVERALL_CURRENT = 21;

    /**
     * @internal
     */
    const FUNCTION_GET_STACK_INPUT_VOLTAGE = 22;

    /**
     * @internal
     */
    const FUNCTION_GET_EXTERNAL_INPUT_VOLTAGE = 23;

    /**
     * @internal
     */
    const FUNCTION_SET_MINIMUM_VOLTAGE = 24;

    /**
     * @internal
     */
    const FUNCTION_GET_MINIMUM_VOLTAGE = 25;

    /**
     * @internal
     */
    const FUNCTION_RESET = 243;

    /**
     * @internal
     */
    const FUNCTION_GET_CHIP_TEMPERATURE = 242;

    /**
     * Creates an object with the unique device ID $uid. This object can
     * then be added to the IP connection.
     *
     * @param string $uid
     */
    public function __construct($uid)
    {
        parent::__construct($uid);

        $this->expectedName = 'Servo Brick';

        $this->bindingVersion = array(1, 0, 1);

        $this->callbackWrappers[self::CALLBACK_UNDER_VOLTAGE] = 'callbackWrapperUnderVoltage';
        $this->callbackWrappers[self::CALLBACK_POSITION_REACHED] = 'callbackWrapperPositionReached';
        $this->callbackWrappers[self::CALLBACK_VELOCITY_REACHED] = 'callbackWrapperVelocityReached';
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
     * Enables a servo (0 to 6). If a servo is enabled, the configured position,
     * velocity, acceleration, etc. are applied immediately.
     * 
     * @param int $servo_num
     * 
     * @return void
     */
    public function enable($servo_num)
    {
        $payload = '';
        $payload .= pack('C', $servo_num);

        $this->sendRequestNoResponse(self::FUNCTION_ENABLE, $payload);
    }

    /**
     * Disables a servo (0 to 6). Disabled servos are not driven at all, i.e. a
     * disabled servo will not hold its position if a load is applied.
     * 
     * @param int $servo_num
     * 
     * @return void
     */
    public function disable($servo_num)
    {
        $payload = '';
        $payload .= pack('C', $servo_num);

        $this->sendRequestNoResponse(self::FUNCTION_DISABLE, $payload);
    }

    /**
     * Returns *true* if the specified servo is enabled, *false* otherwise.
     * 
     * @param int $servo_num
     * 
     * @return bool
     */
    public function isEnabled($servo_num)
    {
        $payload = '';
        $payload .= pack('C', $servo_num);

        $data = $this->sendRequestExpectResponse(self::FUNCTION_IS_ENABLED, $payload, 1);

        $payload = unpack('C1enabled', $data);

        return (bool)$payload['enabled'];
    }

    /**
     * Sets the position in °/100 for the specified servo. 
     * 
     * The default range of the position is -9000 to 9000, but it can be specified
     * according to your servo with BrickServo::setDegree().
     * 
     * If you want to control a linear servo or RC brushless motor controller or
     * similar with the Servo Brick, you can also define lengths or speeds with
     * BrickServo::setDegree().
     * 
     * @param int $servo_num
     * @param int $position
     * 
     * @return void
     */
    public function setPosition($servo_num, $position)
    {
        $payload = '';
        $payload .= pack('C', $servo_num);
        $payload .= pack('v', $position);

        $this->sendRequestNoResponse(self::FUNCTION_SET_POSITION, $payload);
    }

    /**
     * Returns the position of the specified servo as set by BrickServo::setPosition().
     * 
     * @param int $servo_num
     * 
     * @return int
     */
    public function getPosition($servo_num)
    {
        $payload = '';
        $payload .= pack('C', $servo_num);

        $data = $this->sendRequestExpectResponse(self::FUNCTION_GET_POSITION, $payload, 2);

        $payload = unpack('v1position', $data);

        return IPConnection::fixUnpackedInt16($payload['position']);
    }

    /**
     * Returns the *current* position of the specified servo. This may not be the
     * value of BrickServo::setPosition() if the servo is currently approaching a
     * position goal.
     * 
     * @param int $servo_num
     * 
     * @return int
     */
    public function getCurrentPosition($servo_num)
    {
        $payload = '';
        $payload .= pack('C', $servo_num);

        $data = $this->sendRequestExpectResponse(self::FUNCTION_GET_CURRENT_POSITION, $payload, 2);

        $payload = unpack('v1position', $data);

        return IPConnection::fixUnpackedInt16($payload['position']);
    }

    /**
     * Sets the maximum velocity of the specified servo in °/100s. The velocity
     * is accelerated according to the value set by BrickServo::setAcceleration().
     * 
     * The minimum velocity is 0 (no movement) and the maximum velocity is 65535.
     * With a value of 65535 the position will be set immediately (no velocity).
     * 
     * The default value is 65535.
     * 
     * @param int $servo_num
     * @param int $velocity
     * 
     * @return void
     */
    public function setVelocity($servo_num, $velocity)
    {
        $payload = '';
        $payload .= pack('C', $servo_num);
        $payload .= pack('v', $velocity);

        $this->sendRequestNoResponse(self::FUNCTION_SET_VELOCITY, $payload);
    }

    /**
     * Returns the velocity of the specified servo as set by BrickServo::setVelocity().
     * 
     * @param int $servo_num
     * 
     * @return int
     */
    public function getVelocity($servo_num)
    {
        $payload = '';
        $payload .= pack('C', $servo_num);

        $data = $this->sendRequestExpectResponse(self::FUNCTION_GET_VELOCITY, $payload, 2);

        $payload = unpack('v1velocity', $data);

        return $payload['velocity'];
    }

    /**
     * Returns the *current* velocity of the specified servo. This may not be the
     * value of BrickServo::setVelocity() if the servo is currently approaching a
     * velocity goal.
     * 
     * @param int $servo_num
     * 
     * @return int
     */
    public function getCurrentVelocity($servo_num)
    {
        $payload = '';
        $payload .= pack('C', $servo_num);

        $data = $this->sendRequestExpectResponse(self::FUNCTION_GET_CURRENT_VELOCITY, $payload, 2);

        $payload = unpack('v1velocity', $data);

        return $payload['velocity'];
    }

    /**
     * Sets the acceleration of the specified servo in °/100s².
     * 
     * The minimum acceleration is 1 and the maximum acceleration is 65535.
     * With a value of 65535 the velocity will be set immediately (no acceleration).
     * 
     * The default value is 65535.
     * 
     * @param int $servo_num
     * @param int $acceleration
     * 
     * @return void
     */
    public function setAcceleration($servo_num, $acceleration)
    {
        $payload = '';
        $payload .= pack('C', $servo_num);
        $payload .= pack('v', $acceleration);

        $this->sendRequestNoResponse(self::FUNCTION_SET_ACCELERATION, $payload);
    }

    /**
     * Returns the acceleration for the specified servo as set by 
     * BrickServo::setAcceleration().
     * 
     * @param int $servo_num
     * 
     * @return int
     */
    public function getAcceleration($servo_num)
    {
        $payload = '';
        $payload .= pack('C', $servo_num);

        $data = $this->sendRequestExpectResponse(self::FUNCTION_GET_ACCELERATION, $payload, 2);

        $payload = unpack('v1acceleration', $data);

        return $payload['acceleration'];
    }

    /**
     * Sets the output voltages with which the servos are driven in mV.
     * The minimum output voltage is 5000mV and the maximum output voltage is 
     * 9000mV.
     * 
     * <note>
     *  We recommend that you set this value to the maximum voltage that is
     *  specified for your servo, most servos achieve their maximum force only
     *  with high voltages.
     * </note>
     * 
     * The default value is 5000.
     * 
     * @param int $voltage
     * 
     * @return void
     */
    public function setOutputVoltage($voltage)
    {
        $payload = '';
        $payload .= pack('v', $voltage);

        $this->sendRequestNoResponse(self::FUNCTION_SET_OUTPUT_VOLTAGE, $payload);
    }

    /**
     * Returns the output voltage as specified by BrickServo::setOutputVoltage().
     * 
     * 
     * @return int
     */
    public function getOutputVoltage()
    {
        $payload = '';

        $data = $this->sendRequestExpectResponse(self::FUNCTION_GET_OUTPUT_VOLTAGE, $payload, 2);

        $payload = unpack('v1voltage', $data);

        return $payload['voltage'];
    }

    /**
     * Sets the minimum and maximum pulse width of the specified servo in µs.
     * 
     * Usually, servos are controlled with a 
     * `PWM <http://en.wikipedia.org/wiki/Pulse-width_modulation>`__, whereby the
     * length of the pulse controls the position of the servo. Every servo has
     * different minimum and maximum pulse widths, these can be specified with
     * this function.
     * 
     * If you have a datasheet for your servo that specifies the minimum and
     * maximum pulse width, you should set the values accordingly. If your servo
     * comes without any datasheet you have to find the values via trial and error.
     * 
     * Both values have a range from 1 to 65535 (unsigned 16-bit integer). The
     * minimum must be smaller than the maximum.
     * 
     * The default values are 1000µs (1ms) and 2000µs (2ms) for minimum and 
     * maximum pulse width.
     * 
     * @param int $servo_num
     * @param int $min
     * @param int $max
     * 
     * @return void
     */
    public function setPulseWidth($servo_num, $min, $max)
    {
        $payload = '';
        $payload .= pack('C', $servo_num);
        $payload .= pack('v', $min);
        $payload .= pack('v', $max);

        $this->sendRequestNoResponse(self::FUNCTION_SET_PULSE_WIDTH, $payload);
    }

    /**
     * Returns the minimum and maximum pulse width for the specified servo as set by
     * BrickServo::setPulseWidth().
     * 
     * @param int $servo_num
     * 
     * @return array
     */
    public function getPulseWidth($servo_num)
    {
        $result = array();

        $payload = '';
        $payload .= pack('C', $servo_num);

        $data = $this->sendRequestExpectResponse(self::FUNCTION_GET_PULSE_WIDTH, $payload, 4);

        $payload = unpack('v1min/v1max', $data);

        $result['min'] = $payload['min'];
        $result['max'] = $payload['max'];

        return $result;
    }

    /**
     * Sets the minimum and maximum degree for the specified servo (by default
     * given as °/100).
     * 
     * This only specifies the abstract values between which the minimum and maximum
     * pulse width is scaled. For example: If you specify a pulse width of 1000µs
     * to 2000µs and a degree range of -90° to 90°, a call of BrickServo::setPosition()
     * with 0 will result in a pulse width of 1500µs 
     * (-90° = 1000µs, 90° = 2000µs, etc.).
     * 
     * Possible usage:
     * 
     * * The datasheet of your servo specifies a range of 200° with the middle position
     *   at 110°. In this case you can set the minimum to -9000 and the maximum to 11000.
     * * You measure a range of 220° on your servo and you don't have or need a middle
     *   position. In this case you can set the minimum to 0 and the maximum to 22000.
     * * You have a linear servo with a drive length of 20cm, In this case you could
     *   set the minimum to 0 and the maximum to 20000. Now you can set the Position
     *   with BrickServo::setPosition() with a resolution of cm/100. Also the velocity will
     *   have a resolution of cm/100s and the acceleration will have a resolution of
     *   cm/100s².
     * * You don't care about units and just want the highest possible resolution. In
     *   this case you should set the minimum to -32767 and the maximum to 32767.
     * * You have a brushless motor with a maximum speed of 10000 rpm and want to
     *   control it with a RC brushless motor controller. In this case you can set the
     *   minimum to 0 and the maximum to 10000. BrickServo::setPosition() now controls the rpm.
     * 
     * Both values have a possible range from -32767 to 32767 
     * (signed 16-bit integer). The minimum must be smaller than the maximum.
     * 
     * The default values are -9000 and 9000 for the minimum and maximum degree.
     * 
     * @param int $servo_num
     * @param int $min
     * @param int $max
     * 
     * @return void
     */
    public function setDegree($servo_num, $min, $max)
    {
        $payload = '';
        $payload .= pack('C', $servo_num);
        $payload .= pack('v', $min);
        $payload .= pack('v', $max);

        $this->sendRequestNoResponse(self::FUNCTION_SET_DEGREE, $payload);
    }

    /**
     * Returns the minimum and maximum degree for the specified servo as set by
     * BrickServo::setDegree().
     * 
     * @param int $servo_num
     * 
     * @return array
     */
    public function getDegree($servo_num)
    {
        $result = array();

        $payload = '';
        $payload .= pack('C', $servo_num);

        $data = $this->sendRequestExpectResponse(self::FUNCTION_GET_DEGREE, $payload, 4);

        $payload = unpack('v1min/v1max', $data);

        $result['min'] = IPConnection::fixUnpackedInt16($payload['min']);
        $result['max'] = IPConnection::fixUnpackedInt16($payload['max']);

        return $result;
    }

    /**
     * Sets the period of the specified servo in µs.
     * 
     * Usually, servos are controlled with a 
     * `PWM <http://en.wikipedia.org/wiki/Pulse-width_modulation>`__. Different
     * servos expect PWMs with different periods. Most servos run well with a 
     * period of about 20ms.
     * 
     * If your servo comes with a datasheet that specifies a period, you should
     * set it accordingly. If you don't have a datasheet and you have no idea
     * what the correct period is, the default value (19.5ms) will most likely
     * work fine. 
     * 
     * The minimum possible period is 2000µs and the maximum is 65535µs.
     * 
     * The default value is 19.5ms (19500µs).
     * 
     * @param int $servo_num
     * @param int $period
     * 
     * @return void
     */
    public function setPeriod($servo_num, $period)
    {
        $payload = '';
        $payload .= pack('C', $servo_num);
        $payload .= pack('v', $period);

        $this->sendRequestNoResponse(self::FUNCTION_SET_PERIOD, $payload);
    }

    /**
     * Returns the period for the specified servo as set by BrickServo::setPeriod().
     * 
     * @param int $servo_num
     * 
     * @return int
     */
    public function getPeriod($servo_num)
    {
        $payload = '';
        $payload .= pack('C', $servo_num);

        $data = $this->sendRequestExpectResponse(self::FUNCTION_GET_PERIOD, $payload, 2);

        $payload = unpack('v1period', $data);

        return $payload['period'];
    }

    /**
     * Returns the current consumption of the specified servo in mA.
     * 
     * @param int $servo_num
     * 
     * @return int
     */
    public function getServoCurrent($servo_num)
    {
        $payload = '';
        $payload .= pack('C', $servo_num);

        $data = $this->sendRequestExpectResponse(self::FUNCTION_GET_SERVO_CURRENT, $payload, 2);

        $payload = unpack('v1current', $data);

        return $payload['current'];
    }

    /**
     * Returns the current consumption of all servos together in mA.
     * 
     * 
     * @return int
     */
    public function getOverallCurrent()
    {
        $payload = '';

        $data = $this->sendRequestExpectResponse(self::FUNCTION_GET_OVERALL_CURRENT, $payload, 2);

        $payload = unpack('v1current', $data);

        return $payload['current'];
    }

    /**
     * Returns the stack input voltage in mV. The stack input voltage is the
     * voltage that is supplied via the stack, i.e. it is given by a 
     * Step-Down or Step-Up Power Supply.
     * 
     * 
     * @return int
     */
    public function getStackInputVoltage()
    {
        $payload = '';

        $data = $this->sendRequestExpectResponse(self::FUNCTION_GET_STACK_INPUT_VOLTAGE, $payload, 2);

        $payload = unpack('v1voltage', $data);

        return $payload['voltage'];
    }

    /**
     * Returns the external input voltage in mV. The external input voltage is
     * given via the black power input connector on the Servo Brick. 
     *  
     * If there is an external input voltage and a stack input voltage, the motors
     * will be driven by the external input voltage. If there is only a stack 
     * voltage present, the motors will be driven by this voltage.
     * 
     * <warning>
     *  This means, if you have a high stack voltage and a low external voltage,
     *  the motors will be driven with the low external voltage. If you then remove
     *  the external connection, it will immediately be driven by the high
     *  stack voltage
     * </warning>
     * 
     * 
     * @return int
     */
    public function getExternalInputVoltage()
    {
        $payload = '';

        $data = $this->sendRequestExpectResponse(self::FUNCTION_GET_EXTERNAL_INPUT_VOLTAGE, $payload, 2);

        $payload = unpack('v1voltage', $data);

        return $payload['voltage'];
    }

    /**
     * Sets the minimum voltage in mV, below which the BrickServo::CALLBACK_UNDER_VOLTAGE callback
     * is triggered. The minimum possible value that works with the Servo Brick is 5V.
     * You can use this function to detect the discharge of a battery that is used
     * to drive the stepper motor. If you have a fixed power supply, you likely do 
     * not need this functionality.
     * 
     * The default value is 5V (5000mV).
     * 
     * @param int $voltage
     * 
     * @return void
     */
    public function setMinimumVoltage($voltage)
    {
        $payload = '';
        $payload .= pack('v', $voltage);

        $this->sendRequestNoResponse(self::FUNCTION_SET_MINIMUM_VOLTAGE, $payload);
    }

    /**
     * Returns the minimum voltage as set by BrickServo::setMinimumVoltage()
     * 
     * 
     * @return int
     */
    public function getMinimumVoltage()
    {
        $payload = '';

        $data = $this->sendRequestExpectResponse(self::FUNCTION_GET_MINIMUM_VOLTAGE, $payload, 2);

        $payload = unpack('v1voltage', $data);

        return $payload['voltage'];
    }

    /**
     * Calling this function will reset the Brick. Calling this function
     * on a Brick inside of a stack will reset the whole stack.
     * 
     * After a reset you have to create new device objects,
     * calling functions on the existing ones will result in
     * undefined behavior!
     * 
     * 
     * @return void
     */
    public function reset()
    {
        $payload = '';

        $this->sendRequestNoResponse(self::FUNCTION_RESET, $payload);
    }

    /**
     * Returns the temperature in °C/10 as measured inside the microcontroller. The
     * value returned is not the ambient temperature!
     * 
     * The temperature is only proportional to the real temperature and it has an
     * accuracy of +-15%. Practically it is only useful as an indicator for
     * temperature changes.
     * 
     * 
     * @return int
     */
    public function getChipTemperature()
    {
        $payload = '';

        $data = $this->sendRequestExpectResponse(self::FUNCTION_GET_CHIP_TEMPERATURE, $payload, 2);

        $payload = unpack('v1temperature', $data);

        return IPConnection::fixUnpackedInt16($payload['temperature']);
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
    public function callbackWrapperUnderVoltage($data)
    {
        $result = array();
        $payload = unpack('v1voltage', $data);

        array_push($result, $payload['voltage']);

        call_user_func_array($this->registeredCallbacks[self::CALLBACK_UNDER_VOLTAGE], $result);
    }

    /**
     * @internal
     * @param string $data
     */
    public function callbackWrapperPositionReached($data)
    {
        $result = array();
        $payload = unpack('C1servo_num/v1position', $data);

        array_push($result, $payload['servo_num']);
        array_push($result, IPConnection::fixUnpackedInt16($payload['position']));

        call_user_func_array($this->registeredCallbacks[self::CALLBACK_POSITION_REACHED], $result);
    }

    /**
     * @internal
     * @param string $data
     */
    public function callbackWrapperVelocityReached($data)
    {
        $result = array();
        $payload = unpack('C1servo_num/v1velocity', $data);

        array_push($result, $payload['servo_num']);
        array_push($result, IPConnection::fixUnpackedInt16($payload['velocity']));

        call_user_func_array($this->registeredCallbacks[self::CALLBACK_VELOCITY_REACHED], $result);
    }
}

?>
