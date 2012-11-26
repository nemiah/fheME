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
 * Device for controlling DC motors
 */
class BrickDC extends Device
{

    /**
     * This callback is triggered when the input voltage drops below the value set by
     * BrickDC::setMinimumVoltage(). The parameter is the current voltage given
     * in mV.
     */
    const CALLBACK_UNDER_VOLTAGE = 21;

    /**
     * This callback is triggered if either the current consumption
     * is too high (above 5A) or the temperature of the driver chip is too high
     * (above 175°C). These two possibilities are essentially the same, since the
     * temperature will reach this threshold immediately if the motor consumes too
     * much current. In case of a voltage below 3.3V (external or stack) this
     * callback is triggered as well.
     * 
     * If this callback is triggered, the driver chip gets disabled at the same time.
     * That means, BrickDC::enable() has to be called to drive the motor again.
     * 
     * <note>
     *  This callback only works in Drive/Brake mode (see BrickDC::setDriveMode()). In
     *  Drive/Coast mode it is unfortunately impossible to reliably read the
     *  overcurrent/overtemperature signal from the driver chip.
     * </note>
     */
    const CALLBACK_EMERGENCY_SHUTDOWN = 22;

    /**
     * This callback is triggered whenever a set velocity is reached. For example:
     * If a velocity of 0 is present, acceleration is set to 5000 and velocity
     * to 10000, BrickDC::CALLBACK_VELOCITY_REACHED will be triggered after about 2 seconds, when
     * the set velocity is actually reached.
     * 
     * <note>
     *  Since we can't get any feedback from the DC motor, this only works if the
     *  acceleration (see BrickDC::setAcceleration()) is set smaller or equal to the
     *  maximum acceleration of the motor. Otherwise the motor will lag behind the
     *  control value and the callback will be triggered too early.
     * </note>
     */
    const CALLBACK_VELOCITY_REACHED = 23;

    /**
     * This callback is triggered with the period that is set by
     * BrickDC::setCurrentVelocityPeriod(). The parameter is the *current* velocity
     * used by the motor.
     * 
     * BrickDC::CALLBACK_CURRENT_VELOCITY is only triggered after the set period if there is
     * a change in the velocity.
     */
    const CALLBACK_CURRENT_VELOCITY = 24;


    /**
     * @internal
     */
    const FUNCTION_SET_VELOCITY = 1;

    /**
     * @internal
     */
    const FUNCTION_GET_VELOCITY = 2;

    /**
     * @internal
     */
    const FUNCTION_GET_CURRENT_VELOCITY = 3;

    /**
     * @internal
     */
    const FUNCTION_SET_ACCELERATION = 4;

    /**
     * @internal
     */
    const FUNCTION_GET_ACCELERATION = 5;

    /**
     * @internal
     */
    const FUNCTION_SET_PWM_FREQUENCY = 6;

    /**
     * @internal
     */
    const FUNCTION_GET_PWM_FREQUENCY = 7;

    /**
     * @internal
     */
    const FUNCTION_FULL_BRAKE = 8;

    /**
     * @internal
     */
    const FUNCTION_GET_STACK_INPUT_VOLTAGE = 9;

    /**
     * @internal
     */
    const FUNCTION_GET_EXTERNAL_INPUT_VOLTAGE = 10;

    /**
     * @internal
     */
    const FUNCTION_GET_CURRENT_CONSUMPTION = 11;

    /**
     * @internal
     */
    const FUNCTION_ENABLE = 12;

    /**
     * @internal
     */
    const FUNCTION_DISABLE = 13;

    /**
     * @internal
     */
    const FUNCTION_IS_ENABLED = 14;

    /**
     * @internal
     */
    const FUNCTION_SET_MINIMUM_VOLTAGE = 15;

    /**
     * @internal
     */
    const FUNCTION_GET_MINIMUM_VOLTAGE = 16;

    /**
     * @internal
     */
    const FUNCTION_SET_DRIVE_MODE = 17;

    /**
     * @internal
     */
    const FUNCTION_GET_DRIVE_MODE = 18;

    /**
     * @internal
     */
    const FUNCTION_SET_CURRENT_VELOCITY_PERIOD = 19;

    /**
     * @internal
     */
    const FUNCTION_GET_CURRENT_VELOCITY_PERIOD = 20;

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

        $this->expectedName = 'DC Brick';

        $this->bindingVersion = array(1, 0, 1);

        $this->callbackWrappers[self::CALLBACK_UNDER_VOLTAGE] = 'callbackWrapperUnderVoltage';
        $this->callbackWrappers[self::CALLBACK_EMERGENCY_SHUTDOWN] = 'callbackWrapperEmergencyShutdown';
        $this->callbackWrappers[self::CALLBACK_VELOCITY_REACHED] = 'callbackWrapperVelocityReached';
        $this->callbackWrappers[self::CALLBACK_CURRENT_VELOCITY] = 'callbackWrapperCurrentVelocity';
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
     * Sets the velocity of the motor. Whereas -32767 is full speed backward,
     * 0 is stop and 32767 is full speed forward. Depending on the
     * acceleration (see BrickDC::setAcceleration()), the motor is not immediately
     * brought to the velocity but smoothly accelerated.
     * 
     * The velocity describes the duty cycle of the PWM with which the motor is
     * controlled, e.g. a velocity of 3277 sets a PWM with a 10% duty cycle.
     * You can not only control the duty cycle of the PWM but also the frequency,
     * see BrickDC::setPWMFrequency().
     * 
     * The default velocity is 0.
     * 
     * @param int $velocity
     * 
     * @return void
     */
    public function setVelocity($velocity)
    {
        $payload = '';
        $payload .= pack('v', $velocity);

        $this->sendRequestNoResponse(self::FUNCTION_SET_VELOCITY, $payload);
    }

    /**
     * Returns the velocity as set by BrickDC::setVelocity().
     * 
     * 
     * @return int
     */
    public function getVelocity()
    {
        $payload = '';

        $data = $this->sendRequestExpectResponse(self::FUNCTION_GET_VELOCITY, $payload, 2);

        $payload = unpack('v1velocity', $data);

        return IPConnection::fixUnpackedInt16($payload['velocity']);
    }

    /**
     * Returns the *current* velocity of the motor. This value is different
     * from BrickDC::getVelocity() whenever the motor is currently accelerating
     * to a goal set by BrickDC::setVelocity().
     * 
     * 
     * @return int
     */
    public function getCurrentVelocity()
    {
        $payload = '';

        $data = $this->sendRequestExpectResponse(self::FUNCTION_GET_CURRENT_VELOCITY, $payload, 2);

        $payload = unpack('v1velocity', $data);

        return IPConnection::fixUnpackedInt16($payload['velocity']);
    }

    /**
     * Sets the acceleration of the motor. It is given in *velocity/s*. An
     * acceleration of 10000 means, that every second the velocity is increased
     * by 10000 (or about 30% duty cycle).
     * 
     * For example: If the current velocity is 0 and you want to accelerate to a
     * velocity of 16000 (about 50% duty cycle) in 10 seconds, you should set
     * an acceleration of 1600.
     * 
     * If acceleration is set to 0, there is no speed ramping, i.e. a new velocity
     * is immediately given to the motor.
     * 
     * The default acceleration is 10000.
     * 
     * @param int $acceleration
     * 
     * @return void
     */
    public function setAcceleration($acceleration)
    {
        $payload = '';
        $payload .= pack('v', $acceleration);

        $this->sendRequestNoResponse(self::FUNCTION_SET_ACCELERATION, $payload);
    }

    /**
     * Returns the acceleration as set by BrickDC::setAcceleration().
     * 
     * 
     * @return int
     */
    public function getAcceleration()
    {
        $payload = '';

        $data = $this->sendRequestExpectResponse(self::FUNCTION_GET_ACCELERATION, $payload, 2);

        $payload = unpack('v1acceleration', $data);

        return $payload['acceleration'];
    }

    /**
     * Sets the frequency (in Hz) of the PWM with which the motor is driven.
     * The possible range of the frequency is 1-20000Hz. Often a high frequency
     * is less noisy and the motor runs smoother. However, with a low frequency
     * there are less switches and therefore fewer switching losses. Also with
     * most motors lower frequencies enable higher torque.
     * 
     * If you have no idea what all this means, just ignore this function and use
     * the default frequency, it will very likely work fine.
     * 
     * The default frequency is 15 kHz.
     * 
     * @param int $frequency
     * 
     * @return void
     */
    public function setPWMFrequency($frequency)
    {
        $payload = '';
        $payload .= pack('v', $frequency);

        $this->sendRequestNoResponse(self::FUNCTION_SET_PWM_FREQUENCY, $payload);
    }

    /**
     * Returns the PWM frequency (in Hz) as set by BrickDC::setPWMFrequency().
     * 
     * 
     * @return int
     */
    public function getPWMFrequency()
    {
        $payload = '';

        $data = $this->sendRequestExpectResponse(self::FUNCTION_GET_PWM_FREQUENCY, $payload, 2);

        $payload = unpack('v1frequency', $data);

        return $payload['frequency'];
    }

    /**
     * Executes an active full brake.
     * 
     * <warning>
     *  This function is for emergency purposes,
     *  where an immediate brake is necessary. Depending on the current velocity and
     *  the strength of the motor, a full brake can be quite violent.
     * </warning>
     * 
     * Call BrickDC::setVelocity() with 0 if you just want to stop the motor.
     * 
     * 
     * @return void
     */
    public function fullBrake()
    {
        $payload = '';

        $this->sendRequestNoResponse(self::FUNCTION_FULL_BRAKE, $payload);
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
     * given via the black power input connector on the DC Brick.
     * 
     * If there is an external input voltage and a stack input voltage, the motor
     * will be driven by the external input voltage. If there is only a stack
     * voltage present, the motor will be driven by this voltage.
     * 
     * <warning>
     *  This means, if you have a high stack voltage and a low external voltage,
     *  the motor will be driven with the low external voltage. If you then remove
     *  the external connection, it will immediately be driven by the high
     *  stack voltage.
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
     * Returns the current consumption of the motor in mA.
     * 
     * 
     * @return int
     */
    public function getCurrentConsumption()
    {
        $payload = '';

        $data = $this->sendRequestExpectResponse(self::FUNCTION_GET_CURRENT_CONSUMPTION, $payload, 2);

        $payload = unpack('v1voltage', $data);

        return $payload['voltage'];
    }

    /**
     * Enables the driver chip. The driver parameters can be configured (velocity,
     * acceleration, etc) before it is enabled.
     * 
     * 
     * @return void
     */
    public function enable()
    {
        $payload = '';

        $this->sendRequestNoResponse(self::FUNCTION_ENABLE, $payload);
    }

    /**
     * Disables the driver chip. The configurations are kept (velocity,
     * acceleration, etc) but the motor is not driven until it is enabled again.
     * 
     * 
     * @return void
     */
    public function disable()
    {
        $payload = '';

        $this->sendRequestNoResponse(self::FUNCTION_DISABLE, $payload);
    }

    /**
     * Returns *true* if the driver chip is enabled, *false* otherwise.
     * 
     * 
     * @return bool
     */
    public function isEnabled()
    {
        $payload = '';

        $data = $this->sendRequestExpectResponse(self::FUNCTION_IS_ENABLED, $payload, 1);

        $payload = unpack('C1enabled', $data);

        return (bool)$payload['enabled'];
    }

    /**
     * Sets the minimum voltage in mV, below which the BrickDC::CALLBACK_UNDER_VOLTAGE callback
     * is triggered. The minimum possible value that works with the DC Brick is 5V.
     * You can use this function to detect the discharge of a battery that is used
     * to drive the motor. If you have a fixed power supply, you likely do not need
     * this functionality.
     * 
     * The default value is 5V.
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
     * Returns the minimum voltage as set by BrickDC::setMinimumVoltage()
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
     * Sets the drive mode. Possible modes are:
     * 
     * * 0 = Drive/Brake
     * * 1 = Drive/Coast
     * 
     * These modes are different kinds of motor controls.
     * 
     * In Drive/Brake mode, the motor is always either driving or braking. There
     * is no freewheeling. Advantages are: A more linear correlation between
     * PWM and velocity, more exact accelerations and the possibility to drive
     * with slower velocities.
     * 
     * In Drive/Coast mode, the motor is always either driving or freewheeling.
     * Advantages are: Less current consumption and less demands on the motor and
     * driver chip.
     * 
     * The default value is 0 = Drive/Brake.
     * 
     * @param int $mode
     * 
     * @return void
     */
    public function setDriveMode($mode)
    {
        $payload = '';
        $payload .= pack('C', $mode);

        $this->sendRequestNoResponse(self::FUNCTION_SET_DRIVE_MODE, $payload);
    }

    /**
     * Returns the drive mode, as set by BrickDC::setDriveMode().
     * 
     * 
     * @return int
     */
    public function getDriveMode()
    {
        $payload = '';

        $data = $this->sendRequestExpectResponse(self::FUNCTION_GET_DRIVE_MODE, $payload, 1);

        $payload = unpack('C1mode', $data);

        return $payload['mode'];
    }

    /**
     * Sets a period in ms with which the BrickDC::CALLBACK_CURRENT_VELOCITY callback is triggered.
     * A period of 0 turns the callback off.
     * 
     * The default value is 0.
     * 
     * @param int $period
     * 
     * @return void
     */
    public function setCurrentVelocityPeriod($period)
    {
        $payload = '';
        $payload .= pack('v', $period);

        $this->sendRequestNoResponse(self::FUNCTION_SET_CURRENT_VELOCITY_PERIOD, $payload);
    }

    /**
     * Returns the period as set by BrickDC::setCurrentVelocityPeriod().
     * 
     * 
     * @return int
     */
    public function getCurrentVelocityPeriod()
    {
        $payload = '';

        $data = $this->sendRequestExpectResponse(self::FUNCTION_GET_CURRENT_VELOCITY_PERIOD, $payload, 2);

        $payload = unpack('v1period', $data);

        return $payload['period'];
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
    public function callbackWrapperEmergencyShutdown($data)
    {
        $result = array();




        call_user_func_array($this->registeredCallbacks[self::CALLBACK_EMERGENCY_SHUTDOWN], $result);
    }

    /**
     * @internal
     * @param string $data
     */
    public function callbackWrapperVelocityReached($data)
    {
        $result = array();
        $payload = unpack('v1velocity', $data);

        array_push($result, IPConnection::fixUnpackedInt16($payload['velocity']));

        call_user_func_array($this->registeredCallbacks[self::CALLBACK_VELOCITY_REACHED], $result);
    }

    /**
     * @internal
     * @param string $data
     */
    public function callbackWrapperCurrentVelocity($data)
    {
        $result = array();
        $payload = unpack('v1velocity', $data);

        array_push($result, IPConnection::fixUnpackedInt16($payload['velocity']));

        call_user_func_array($this->registeredCallbacks[self::CALLBACK_CURRENT_VELOCITY], $result);
    }
}

?>
