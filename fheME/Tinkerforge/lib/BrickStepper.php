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
 * Device for controlling stepper motors
 */
class BrickStepper extends Device
{

    /**
     * This callback is triggered when the input voltage drops below the value set by
     * BrickStepper::setMinimumVoltage(). The parameter is the current voltage given
     * in mV.
     */
    const CALLBACK_UNDER_VOLTAGE = 31;

    /**
     * This callback is triggered when a position set by BrickStepper::setSteps() or
     * BrickStepper::setTargetPosition() is reached.
     * 
     * <note>
     *  Since we can't get any feedback from the stepper motor, this only works if the
     *  acceleration (see BrickStepper::setSpeedRamping()) is set smaller or equal to the
     *  maximum acceleration of the motor. Otherwise the motor will lag behind the
     *  control value and the callback will be triggered too early.
     * </note>
     */
    const CALLBACK_POSITION_REACHED = 32;

    /**
     * This callback is triggered periodically with the period that is set by
     * BrickStepper::setAllDataPeriod(). The parameters are: the current velocity,
     * the current position, the remaining steps, the stack voltage, the external
     * voltage and the current consumption of the stepper motor.
     * 
     * .. versionadded:: 1.1.6~(Firmware)
     */
    const CALLBACK_ALL_DATA = 40;

    /**
     * This callback is triggered whenever the Stepper Brick enters a new state. 
     * It returns the new state as well as the previous state.
     * 
     * Possible states are:
     * 
     * * 1 = Stop
     * * 2 = Acceleration
     * * 3 = Run
     * * 4 = Deacceleration
     * * 5 = Direction change to forward
     * * 6 = Direction change to backward
     * 
     * .. versionadded:: 1.1.6~(Firmware)
     */
    const CALLBACK_NEW_STATE = 41;


    /**
     * @internal
     */
    const FUNCTION_SET_MAX_VELOCITY = 1;

    /**
     * @internal
     */
    const FUNCTION_GET_MAX_VELOCITY = 2;

    /**
     * @internal
     */
    const FUNCTION_GET_CURRENT_VELOCITY = 3;

    /**
     * @internal
     */
    const FUNCTION_SET_SPEED_RAMPING = 4;

    /**
     * @internal
     */
    const FUNCTION_GET_SPEED_RAMPING = 5;

    /**
     * @internal
     */
    const FUNCTION_FULL_BRAKE = 6;

    /**
     * @internal
     */
    const FUNCTION_SET_CURRENT_POSITION = 7;

    /**
     * @internal
     */
    const FUNCTION_GET_CURRENT_POSITION = 8;

    /**
     * @internal
     */
    const FUNCTION_SET_TARGET_POSITION = 9;

    /**
     * @internal
     */
    const FUNCTION_GET_TARGET_POSITION = 10;

    /**
     * @internal
     */
    const FUNCTION_SET_STEPS = 11;

    /**
     * @internal
     */
    const FUNCTION_GET_STEPS = 12;

    /**
     * @internal
     */
    const FUNCTION_GET_REMAINING_STEPS = 13;

    /**
     * @internal
     */
    const FUNCTION_SET_STEP_MODE = 14;

    /**
     * @internal
     */
    const FUNCTION_GET_STEP_MODE = 15;

    /**
     * @internal
     */
    const FUNCTION_DRIVE_FORWARD = 16;

    /**
     * @internal
     */
    const FUNCTION_DRIVE_BACKWARD = 17;

    /**
     * @internal
     */
    const FUNCTION_STOP = 18;

    /**
     * @internal
     */
    const FUNCTION_GET_STACK_INPUT_VOLTAGE = 19;

    /**
     * @internal
     */
    const FUNCTION_GET_EXTERNAL_INPUT_VOLTAGE = 20;

    /**
     * @internal
     */
    const FUNCTION_GET_CURRENT_CONSUMPTION = 21;

    /**
     * @internal
     */
    const FUNCTION_SET_MOTOR_CURRENT = 22;

    /**
     * @internal
     */
    const FUNCTION_GET_MOTOR_CURRENT = 23;

    /**
     * @internal
     */
    const FUNCTION_ENABLE = 24;

    /**
     * @internal
     */
    const FUNCTION_DISABLE = 25;

    /**
     * @internal
     */
    const FUNCTION_IS_ENABLED = 26;

    /**
     * @internal
     */
    const FUNCTION_SET_DECAY = 27;

    /**
     * @internal
     */
    const FUNCTION_GET_DECAY = 28;

    /**
     * @internal
     */
    const FUNCTION_SET_MINIMUM_VOLTAGE = 29;

    /**
     * @internal
     */
    const FUNCTION_GET_MINIMUM_VOLTAGE = 30;

    /**
     * @internal
     */
    const FUNCTION_SET_SYNC_RECT = 33;

    /**
     * @internal
     */
    const FUNCTION_IS_SYNC_RECT = 34;

    /**
     * @internal
     */
    const FUNCTION_SET_TIME_BASE = 35;

    /**
     * @internal
     */
    const FUNCTION_GET_TIME_BASE = 36;

    /**
     * @internal
     */
    const FUNCTION_GET_ALL_DATA = 37;

    /**
     * @internal
     */
    const FUNCTION_SET_ALL_DATA_PERIOD = 38;

    /**
     * @internal
     */
    const FUNCTION_GET_ALL_DATA_PERIOD = 39;

    /**
     * @internal
     */
    const FUNCTION_GET_PROTOCOL1_BRICKLET_NAME = 241;

    /**
     * @internal
     */
    const FUNCTION_GET_CHIP_TEMPERATURE = 242;

    /**
     * @internal
     */
    const FUNCTION_RESET = 243;

    /**
     * @internal
     */
    const FUNCTION_GET_IDENTITY = 255;

    const STEP_MODE_FULL_STEP = 1;
    const STEP_MODE_HALF_STEP = 2;
    const STEP_MODE_QUARTER_STEP = 4;
    const STEP_MODE_EIGHTH_STEP = 8;
    const STATE_STOP = 1;
    const STATE_ACCELERATION = 2;
    const STATE_RUN = 3;
    const STATE_DEACCELERATION = 4;
    const STATE_DIRECTION_CHANGE_TO_FORWARD = 5;
    const STATE_DIRECTION_CHANGE_TO_BACKWARD = 6;

    const DEVICE_IDENTIFIER = 15;

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

        $this->responseExpected[self::FUNCTION_SET_MAX_VELOCITY] = self::RESPONSE_EXPECTED_FALSE;
        $this->responseExpected[self::FUNCTION_GET_MAX_VELOCITY] = self::RESPONSE_EXPECTED_ALWAYS_TRUE;
        $this->responseExpected[self::FUNCTION_GET_CURRENT_VELOCITY] = self::RESPONSE_EXPECTED_ALWAYS_TRUE;
        $this->responseExpected[self::FUNCTION_SET_SPEED_RAMPING] = self::RESPONSE_EXPECTED_FALSE;
        $this->responseExpected[self::FUNCTION_GET_SPEED_RAMPING] = self::RESPONSE_EXPECTED_ALWAYS_TRUE;
        $this->responseExpected[self::FUNCTION_FULL_BRAKE] = self::RESPONSE_EXPECTED_FALSE;
        $this->responseExpected[self::FUNCTION_SET_CURRENT_POSITION] = self::RESPONSE_EXPECTED_FALSE;
        $this->responseExpected[self::FUNCTION_GET_CURRENT_POSITION] = self::RESPONSE_EXPECTED_ALWAYS_TRUE;
        $this->responseExpected[self::FUNCTION_SET_TARGET_POSITION] = self::RESPONSE_EXPECTED_FALSE;
        $this->responseExpected[self::FUNCTION_GET_TARGET_POSITION] = self::RESPONSE_EXPECTED_ALWAYS_TRUE;
        $this->responseExpected[self::FUNCTION_SET_STEPS] = self::RESPONSE_EXPECTED_FALSE;
        $this->responseExpected[self::FUNCTION_GET_STEPS] = self::RESPONSE_EXPECTED_ALWAYS_TRUE;
        $this->responseExpected[self::FUNCTION_GET_REMAINING_STEPS] = self::RESPONSE_EXPECTED_ALWAYS_TRUE;
        $this->responseExpected[self::FUNCTION_SET_STEP_MODE] = self::RESPONSE_EXPECTED_FALSE;
        $this->responseExpected[self::FUNCTION_GET_STEP_MODE] = self::RESPONSE_EXPECTED_ALWAYS_TRUE;
        $this->responseExpected[self::FUNCTION_DRIVE_FORWARD] = self::RESPONSE_EXPECTED_FALSE;
        $this->responseExpected[self::FUNCTION_DRIVE_BACKWARD] = self::RESPONSE_EXPECTED_FALSE;
        $this->responseExpected[self::FUNCTION_STOP] = self::RESPONSE_EXPECTED_FALSE;
        $this->responseExpected[self::FUNCTION_GET_STACK_INPUT_VOLTAGE] = self::RESPONSE_EXPECTED_ALWAYS_TRUE;
        $this->responseExpected[self::FUNCTION_GET_EXTERNAL_INPUT_VOLTAGE] = self::RESPONSE_EXPECTED_ALWAYS_TRUE;
        $this->responseExpected[self::FUNCTION_GET_CURRENT_CONSUMPTION] = self::RESPONSE_EXPECTED_ALWAYS_TRUE;
        $this->responseExpected[self::FUNCTION_SET_MOTOR_CURRENT] = self::RESPONSE_EXPECTED_FALSE;
        $this->responseExpected[self::FUNCTION_GET_MOTOR_CURRENT] = self::RESPONSE_EXPECTED_ALWAYS_TRUE;
        $this->responseExpected[self::FUNCTION_ENABLE] = self::RESPONSE_EXPECTED_FALSE;
        $this->responseExpected[self::FUNCTION_DISABLE] = self::RESPONSE_EXPECTED_FALSE;
        $this->responseExpected[self::FUNCTION_IS_ENABLED] = self::RESPONSE_EXPECTED_ALWAYS_TRUE;
        $this->responseExpected[self::FUNCTION_SET_DECAY] = self::RESPONSE_EXPECTED_FALSE;
        $this->responseExpected[self::FUNCTION_GET_DECAY] = self::RESPONSE_EXPECTED_ALWAYS_TRUE;
        $this->responseExpected[self::FUNCTION_SET_MINIMUM_VOLTAGE] = self::RESPONSE_EXPECTED_TRUE;
        $this->responseExpected[self::FUNCTION_GET_MINIMUM_VOLTAGE] = self::RESPONSE_EXPECTED_ALWAYS_TRUE;
        $this->responseExpected[self::CALLBACK_UNDER_VOLTAGE] = self::RESPONSE_EXPECTED_ALWAYS_FALSE;
        $this->responseExpected[self::CALLBACK_POSITION_REACHED] = self::RESPONSE_EXPECTED_ALWAYS_FALSE;
        $this->responseExpected[self::FUNCTION_SET_SYNC_RECT] = self::RESPONSE_EXPECTED_FALSE;
        $this->responseExpected[self::FUNCTION_IS_SYNC_RECT] = self::RESPONSE_EXPECTED_ALWAYS_TRUE;
        $this->responseExpected[self::FUNCTION_SET_TIME_BASE] = self::RESPONSE_EXPECTED_FALSE;
        $this->responseExpected[self::FUNCTION_GET_TIME_BASE] = self::RESPONSE_EXPECTED_ALWAYS_TRUE;
        $this->responseExpected[self::FUNCTION_GET_ALL_DATA] = self::RESPONSE_EXPECTED_ALWAYS_TRUE;
        $this->responseExpected[self::FUNCTION_SET_ALL_DATA_PERIOD] = self::RESPONSE_EXPECTED_TRUE;
        $this->responseExpected[self::FUNCTION_GET_ALL_DATA_PERIOD] = self::RESPONSE_EXPECTED_ALWAYS_TRUE;
        $this->responseExpected[self::CALLBACK_ALL_DATA] = self::RESPONSE_EXPECTED_ALWAYS_FALSE;
        $this->responseExpected[self::CALLBACK_NEW_STATE] = self::RESPONSE_EXPECTED_ALWAYS_FALSE;
        $this->responseExpected[self::FUNCTION_GET_PROTOCOL1_BRICKLET_NAME] = self::RESPONSE_EXPECTED_ALWAYS_TRUE;
        $this->responseExpected[self::FUNCTION_GET_CHIP_TEMPERATURE] = self::RESPONSE_EXPECTED_ALWAYS_TRUE;
        $this->responseExpected[self::FUNCTION_RESET] = self::RESPONSE_EXPECTED_FALSE;
        $this->responseExpected[self::FUNCTION_GET_IDENTITY] = self::RESPONSE_EXPECTED_ALWAYS_TRUE;

        $this->callbackWrappers[self::CALLBACK_UNDER_VOLTAGE] = 'callbackWrapperUnderVoltage';
        $this->callbackWrappers[self::CALLBACK_POSITION_REACHED] = 'callbackWrapperPositionReached';
        $this->callbackWrappers[self::CALLBACK_ALL_DATA] = 'callbackWrapperAllData';
        $this->callbackWrappers[self::CALLBACK_NEW_STATE] = 'callbackWrapperNewState';
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
     * Sets the maximum velocity of the stepper motor in steps per second.
     * This function does *not* start the motor, it merely sets the maximum
     * velocity the stepper motor is accelerated to. To get the motor running use
     * either BrickStepper::setTargetPosition(), BrickStepper::setSteps(), BrickStepper::driveForward() or
     * BrickStepper::driveBackward().
     * 
     * @param int $velocity
     * 
     * @return void
     */
    public function setMaxVelocity($velocity)
    {
        $payload = '';
        $payload .= pack('v', $velocity);

        $this->sendRequest(self::FUNCTION_SET_MAX_VELOCITY, $payload);
    }

    /**
     * Returns the velocity as set by BrickStepper::setMaxVelocity().
     * 
     * 
     * @return int
     */
    public function getMaxVelocity()
    {
        $payload = '';

        $data = $this->sendRequest(self::FUNCTION_GET_MAX_VELOCITY, $payload);

        $payload = unpack('v1velocity', $data);

        return $payload['velocity'];
    }

    /**
     * Returns the *current* velocity of the stepper motor in steps per second.
     * 
     * 
     * @return int
     */
    public function getCurrentVelocity()
    {
        $payload = '';

        $data = $this->sendRequest(self::FUNCTION_GET_CURRENT_VELOCITY, $payload);

        $payload = unpack('v1velocity', $data);

        return $payload['velocity'];
    }

    /**
     * Sets the acceleration and deacceleration of the stepper motor. The values
     * are given in *steps/s²*. An acceleration of 1000 means, that
     * every second the velocity is increased by 1000 *steps/s*.
     * 
     * For example: If the current velocity is 0 and you want to accelerate to a
     * velocity of 8000 *steps/s* in 10 seconds, you should set an acceleration
     * of 800 *steps/s²*.
     * 
     * An acceleration/deacceleration of 0 means instantaneous
     * acceleration/deacceleration (not recommended)
     * 
     * The default value is 1000 for both
     * 
     * @param int $acceleration
     * @param int $deacceleration
     * 
     * @return void
     */
    public function setSpeedRamping($acceleration, $deacceleration)
    {
        $payload = '';
        $payload .= pack('v', $acceleration);
        $payload .= pack('v', $deacceleration);

        $this->sendRequest(self::FUNCTION_SET_SPEED_RAMPING, $payload);
    }

    /**
     * Returns the acceleration and deacceleration as set by 
     * BrickStepper::setSpeedRamping().
     * 
     * 
     * @return array
     */
    public function getSpeedRamping()
    {
        $result = array();

        $payload = '';

        $data = $this->sendRequest(self::FUNCTION_GET_SPEED_RAMPING, $payload);

        $payload = unpack('v1acceleration/v1deacceleration', $data);

        $result['acceleration'] = $payload['acceleration'];
        $result['deacceleration'] = $payload['deacceleration'];

        return $result;
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
     * Call BrickStepper::stop() if you just want to stop the motor.
     * 
     * 
     * @return void
     */
    public function fullBrake()
    {
        $payload = '';

        $this->sendRequest(self::FUNCTION_FULL_BRAKE, $payload);
    }

    /**
     * Sets the current steps of the internal step counter. This can be used to
     * set the current position to 0 when some kind of starting position
     * is reached (e.g. when a CNC machine reaches a corner).
     * 
     * @param int $position
     * 
     * @return void
     */
    public function setCurrentPosition($position)
    {
        $payload = '';
        $payload .= pack('V', $position);

        $this->sendRequest(self::FUNCTION_SET_CURRENT_POSITION, $payload);
    }

    /**
     * Returns the current position of the stepper motor in steps. On startup
     * the position is 0. The steps are counted with all possible driving
     * functions (BrickStepper::setTargetPosition(), BrickStepper::setSteps(), BrickStepper::driveForward() or
     * BrickStepper::driveBackward()). It also is possible to reset the steps to 0 or
     * set them to any other desired value with BrickStepper::setCurrentPosition().
     * 
     * 
     * @return int
     */
    public function getCurrentPosition()
    {
        $payload = '';

        $data = $this->sendRequest(self::FUNCTION_GET_CURRENT_POSITION, $payload);

        $payload = unpack('V1position', $data);

        return IPConnection::fixUnpackedInt32($payload['position']);
    }

    /**
     * Sets the target position of the stepper motor in steps. For example,
     * if the current position of the motor is 500 and BrickStepper::setTargetPosition() is
     * called with 1000, the stepper motor will drive 500 steps forward. It will
     * use the velocity, acceleration and deacceleration as set by
     * BrickStepper::setMaxVelocity() and BrickStepper::setSpeedRamping().
     * 
     * A call of BrickStepper::setTargetPosition() with the parameter *x* is equivalent to
     * a call of BrickStepper::setSteps() with the parameter 
     * (*x* - BrickStepper::getCurrentPosition()).
     * 
     * @param int $position
     * 
     * @return void
     */
    public function setTargetPosition($position)
    {
        $payload = '';
        $payload .= pack('V', $position);

        $this->sendRequest(self::FUNCTION_SET_TARGET_POSITION, $payload);
    }

    /**
     * Returns the last target position as set by BrickStepper::setTargetPosition().
     * 
     * 
     * @return int
     */
    public function getTargetPosition()
    {
        $payload = '';

        $data = $this->sendRequest(self::FUNCTION_GET_TARGET_POSITION, $payload);

        $payload = unpack('V1position', $data);

        return IPConnection::fixUnpackedInt32($payload['position']);
    }

    /**
     * Sets the number of steps the stepper motor should run. Positive values
     * will drive the motor forward and negative values backward. 
     * The velocity, acceleration and deacceleration as set by
     * BrickStepper::setMaxVelocity() and BrickStepper::setSpeedRamping() will be used.
     * 
     * @param int $steps
     * 
     * @return void
     */
    public function setSteps($steps)
    {
        $payload = '';
        $payload .= pack('V', $steps);

        $this->sendRequest(self::FUNCTION_SET_STEPS, $payload);
    }

    /**
     * Returns the last steps as set by BrickStepper::setSteps().
     * 
     * 
     * @return int
     */
    public function getSteps()
    {
        $payload = '';

        $data = $this->sendRequest(self::FUNCTION_GET_STEPS, $payload);

        $payload = unpack('V1steps', $data);

        return IPConnection::fixUnpackedInt32($payload['steps']);
    }

    /**
     * Returns the remaining steps of the last call of BrickStepper::setSteps().
     * For example, if BrickStepper::setSteps() is called with 2000 and 
     * BrickStepper::getRemainingSteps() is called after the motor has run for 500 steps,
     * it will return 1500.
     * 
     * 
     * @return int
     */
    public function getRemainingSteps()
    {
        $payload = '';

        $data = $this->sendRequest(self::FUNCTION_GET_REMAINING_STEPS, $payload);

        $payload = unpack('V1steps', $data);

        return IPConnection::fixUnpackedInt32($payload['steps']);
    }

    /**
     * Sets the step mode of the stepper motor. Possible values are:
     * 
     * * Full Step = 1
     * * Half Step = 2
     * * Quarter Step = 4
     * * Eighth Step = 8
     * 
     * A higher value will increase the resolution and
     * decrease the torque of the stepper motor.
     * 
     * The default value is 8 (Eighth Step).
     * 
     * @param int $mode
     * 
     * @return void
     */
    public function setStepMode($mode)
    {
        $payload = '';
        $payload .= pack('C', $mode);

        $this->sendRequest(self::FUNCTION_SET_STEP_MODE, $payload);
    }

    /**
     * Returns the step mode as set by BrickStepper::setStepMode().
     * 
     * 
     * @return int
     */
    public function getStepMode()
    {
        $payload = '';

        $data = $this->sendRequest(self::FUNCTION_GET_STEP_MODE, $payload);

        $payload = unpack('C1mode', $data);

        return $payload['mode'];
    }

    /**
     * Drives the stepper motor forward until BrickStepper::driveBackward() or
     * BrickStepper::stop() is called. The velocity, acceleration and deacceleration as 
     * set by BrickStepper::setMaxVelocity() and BrickStepper::setSpeedRamping() will be used.
     * 
     * 
     * @return void
     */
    public function driveForward()
    {
        $payload = '';

        $this->sendRequest(self::FUNCTION_DRIVE_FORWARD, $payload);
    }

    /**
     * Drives the stepper motor backward until BrickStepper::driveForward() or
     * BrickStepper::stop() is triggered. The velocity, acceleration and deacceleration as
     * set by BrickStepper::setMaxVelocity() and BrickStepper::setSpeedRamping() will be used.
     * 
     * 
     * @return void
     */
    public function driveBackward()
    {
        $payload = '';

        $this->sendRequest(self::FUNCTION_DRIVE_BACKWARD, $payload);
    }

    /**
     * Stops the stepper motor with the deacceleration as set by 
     * BrickStepper::setSpeedRamping().
     * 
     * 
     * @return void
     */
    public function stop()
    {
        $payload = '';

        $this->sendRequest(self::FUNCTION_STOP, $payload);
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

        $data = $this->sendRequest(self::FUNCTION_GET_STACK_INPUT_VOLTAGE, $payload);

        $payload = unpack('v1voltage', $data);

        return $payload['voltage'];
    }

    /**
     * Returns the external input voltage in mV. The external input voltage is
     * given via the black power input connector on the Stepper Brick. 
     *  
     * If there is an external input voltage and a stack input voltage, the motor
     * will be driven by the external input voltage. If there is only a stack 
     * voltage present, the motor will be driven by this voltage.
     * 
     * <warning>
     *  This means, if you have a high stack voltage and a low external voltage,
     *  the motor will be driven with the low external voltage. If you then remove
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

        $data = $this->sendRequest(self::FUNCTION_GET_EXTERNAL_INPUT_VOLTAGE, $payload);

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

        $data = $this->sendRequest(self::FUNCTION_GET_CURRENT_CONSUMPTION, $payload);

        $payload = unpack('v1current', $data);

        return $payload['current'];
    }

    /**
     * Sets the current in mA with which the motor will be driven.
     * The minimum value is 100mA, the maximum value 2291mA and the 
     * default value is 800mA.
     * 
     * <warning>
     *  Do not set this value above the specifications of your stepper motor.
     *  Otherwise it may damage your motor.
     * </warning>
     * 
     * @param int $current
     * 
     * @return void
     */
    public function setMotorCurrent($current)
    {
        $payload = '';
        $payload .= pack('v', $current);

        $this->sendRequest(self::FUNCTION_SET_MOTOR_CURRENT, $payload);
    }

    /**
     * Returns the current as set by BrickStepper::setMotorCurrent().
     * 
     * 
     * @return int
     */
    public function getMotorCurrent()
    {
        $payload = '';

        $data = $this->sendRequest(self::FUNCTION_GET_MOTOR_CURRENT, $payload);

        $payload = unpack('v1current', $data);

        return $payload['current'];
    }

    /**
     * Enables the driver chip. The driver parameters can be configured (maximum velocity,
     * acceleration, etc) before it is enabled.
     * 
     * 
     * @return void
     */
    public function enable()
    {
        $payload = '';

        $this->sendRequest(self::FUNCTION_ENABLE, $payload);
    }

    /**
     * Disables the driver chip. The configurations are kept (maximum velocity,
     * acceleration, etc) but the motor is not driven until it is enabled again.
     * 
     * 
     * @return void
     */
    public function disable()
    {
        $payload = '';

        $this->sendRequest(self::FUNCTION_DISABLE, $payload);
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

        $data = $this->sendRequest(self::FUNCTION_IS_ENABLED, $payload);

        $payload = unpack('C1enabled', $data);

        return (bool)$payload['enabled'];
    }

    /**
     * Sets the decay mode of the stepper motor. The possible value range is
     * between 0 and 65535. A value of 0 sets the fast decay mode, a value of
     * 65535 sets the slow decay mode and a value in between sets the mixed
     * decay mode.
     * 
     * Changing the decay mode is only possible if synchronous rectification
     * is enabled (see BrickStepper::setSyncRect()).
     * 
     * For a good explanation of the different decay modes see 
     * `this <http://ebldc.com/?p=86/>`__ blog post by Avayan.
     * 
     * A good decay mode is unfortunately different for every motor. The best
     * way to work out a good decay mode for your stepper motor, if you can't
     * measure the current with an oscilloscope, is to listen to the sound of
     * the motor. If the value is too low, you often hear a high pitched 
     * sound and if it is too high you can often hear a humming sound.
     * 
     * Generally, fast decay mode (small value) will be noisier but also
     * allow higher motor speeds.
     * 
     * The default value is 10000.
     * 
     * <note>
     *  There is unfortunately no formula to calculate a perfect decay
     *  mode for a given stepper motor. If you have problems with loud noises
     *  or the maximum motor speed is too slow, you should try to tinker with
     *  the decay value
     * </note>
     * 
     * @param int $decay
     * 
     * @return void
     */
    public function setDecay($decay)
    {
        $payload = '';
        $payload .= pack('v', $decay);

        $this->sendRequest(self::FUNCTION_SET_DECAY, $payload);
    }

    /**
     * Returns the decay mode as set by BrickStepper::setDecay().
     * 
     * 
     * @return int
     */
    public function getDecay()
    {
        $payload = '';

        $data = $this->sendRequest(self::FUNCTION_GET_DECAY, $payload);

        $payload = unpack('v1decay', $data);

        return $payload['decay'];
    }

    /**
     * Sets the minimum voltage in mV, below which the BrickStepper::CALLBACK_UNDER_VOLTAGE callback
     * is triggered. The minimum possible value that works with the Stepper Brick is 8V.
     * You can use this function to detect the discharge of a battery that is used
     * to drive the stepper motor. If you have a fixed power supply, you likely do 
     * not need this functionality.
     * 
     * The default value is 8V.
     * 
     * @param int $voltage
     * 
     * @return void
     */
    public function setMinimumVoltage($voltage)
    {
        $payload = '';
        $payload .= pack('v', $voltage);

        $this->sendRequest(self::FUNCTION_SET_MINIMUM_VOLTAGE, $payload);
    }

    /**
     * Returns the minimum voltage as set by BrickStepper::setMinimumVoltage().
     * 
     * 
     * @return int
     */
    public function getMinimumVoltage()
    {
        $payload = '';

        $data = $this->sendRequest(self::FUNCTION_GET_MINIMUM_VOLTAGE, $payload);

        $payload = unpack('v1voltage', $data);

        return $payload['voltage'];
    }

    /**
     * Turns synchronous rectification on or off (*true* or *false*).
     * 
     * With synchronous rectification on, the decay can be changed
     * (see BrickStepper::setDecay()). Without synchronous rectification fast
     * decay is used.
     * 
     * For an explanation of synchronous rectification see 
     * `here <http://en.wikipedia.org/wiki/Active_rectification>`__.
     * 
     * <warning>
     *  If you want to use high speeds (> 10000 steps/s) for a large 
     *  stepper motor with a large inductivity we strongly
     *  suggest that you disable synchronous rectification. Otherwise the
     *  Brick may not be able to cope with the load and overheat.
     * </warning>
     * 
     * The default value is *false*.
     * 
     * .. versionadded:: 1.1.4~(Firmware)
     * 
     * @param bool $sync_rect
     * 
     * @return void
     */
    public function setSyncRect($sync_rect)
    {
        $payload = '';
        $payload .= pack('C', intval((bool)$sync_rect));

        $this->sendRequest(self::FUNCTION_SET_SYNC_RECT, $payload);
    }

    /**
     * Returns *true* if synchronous rectification is enabled, *false* otherwise.
     * 
     * .. versionadded:: 1.1.4~(Firmware)
     * 
     * 
     * @return bool
     */
    public function isSyncRect()
    {
        $payload = '';

        $data = $this->sendRequest(self::FUNCTION_IS_SYNC_RECT, $payload);

        $payload = unpack('C1sync_rect', $data);

        return (bool)$payload['sync_rect'];
    }

    /**
     * Sets the time base of the velocity and the acceleration of the stepper brick
     * (in seconds).
     * 
     * For example, if you want to make one step every 1.5 seconds, you can set 
     * the time base to 15 and the velocity to 10. Now the velocity is 
     * 10steps/15s = 1steps/1.5s.
     * 
     * The default value is 1.
     * 
     * .. versionadded:: 1.1.6~(Firmware)
     * 
     * @param int $time_base
     * 
     * @return void
     */
    public function setTimeBase($time_base)
    {
        $payload = '';
        $payload .= pack('V', $time_base);

        $this->sendRequest(self::FUNCTION_SET_TIME_BASE, $payload);
    }

    /**
     * Returns the time base as set by BrickStepper::setTimeBase().
     * 
     * .. versionadded:: 1.1.6~(Firmware)
     * 
     * 
     * @return int
     */
    public function getTimeBase()
    {
        $payload = '';

        $data = $this->sendRequest(self::FUNCTION_GET_TIME_BASE, $payload);

        $payload = unpack('V1time_base', $data);

        return IPConnection::fixUnpackedUInt32($payload['time_base']);
    }

    /**
     * Returns the following parameters: The current velocity,
     * the current position, the remaining steps, the stack voltage, the external
     * voltage and the current consumption of the stepper motor.
     * 
     * There is also a callback for this function, see BrickStepper::CALLBACK_ALL_DATA.
     * 
     * .. versionadded:: 1.1.6~(Firmware)
     * 
     * 
     * @return array
     */
    public function getAllData()
    {
        $result = array();

        $payload = '';

        $data = $this->sendRequest(self::FUNCTION_GET_ALL_DATA, $payload);

        $payload = unpack('v1current_velocity/V1current_position/V1remaining_steps/v1stack_voltage/v1external_voltage/v1current_consumption', $data);

        $result['current_velocity'] = $payload['current_velocity'];
        $result['current_position'] = IPConnection::fixUnpackedInt32($payload['current_position']);
        $result['remaining_steps'] = IPConnection::fixUnpackedInt32($payload['remaining_steps']);
        $result['stack_voltage'] = $payload['stack_voltage'];
        $result['external_voltage'] = $payload['external_voltage'];
        $result['current_consumption'] = $payload['current_consumption'];

        return $result;
    }

    /**
     * Sets the period in ms with which the BrickStepper::CALLBACK_ALL_DATA callback is triggered
     * periodically. A value of 0 turns the callback off.
     * 
     * .. versionadded:: 1.1.6~(Firmware)
     * 
     * @param int $period
     * 
     * @return void
     */
    public function setAllDataPeriod($period)
    {
        $payload = '';
        $payload .= pack('V', $period);

        $this->sendRequest(self::FUNCTION_SET_ALL_DATA_PERIOD, $payload);
    }

    /**
     * Returns the period as set by BrickStepper::setAllDataPeriod().
     * 
     * .. versionadded:: 1.1.6~(Firmware)
     * 
     * 
     * @return int
     */
    public function getAllDataPeriod()
    {
        $payload = '';

        $data = $this->sendRequest(self::FUNCTION_GET_ALL_DATA_PERIOD, $payload);

        $payload = unpack('V1period', $data);

        return IPConnection::fixUnpackedUInt32($payload['period']);
    }

    /**
     * Returns the firmware and protocol version and the name of the Bricklet for a
     * given port.
     * 
     * This functions sole purpose is to allow automatic flashing of v1.x.y Bricklet
     * plugins.
     * 
     * .. versionadded:: 2.0.0~(Firmware)
     * 
     * @param string $port
     * 
     * @return array
     */
    public function getProtocol1BrickletName($port)
    {
        $result = array();

        $payload = '';
        $payload .= pack('c', ord($port));

        $data = $this->sendRequest(self::FUNCTION_GET_PROTOCOL1_BRICKLET_NAME, $payload);

        $payload = unpack('C1protocol_version/C3firmware_version/c40name', $data);

        $result['protocol_version'] = $payload['protocol_version'];
        $result['firmware_version'] = IPConnection::collectUnpackedArray($payload, 'firmware_version', 3);
        $result['name'] = IPConnection::implodeUnpackedString($payload, 'name', 40);

        return $result;
    }

    /**
     * Returns the temperature in °C/10 as measured inside the microcontroller. The
     * value returned is not the ambient temperature!
     * 
     * The temperature is only proportional to the real temperature and it has an
     * accuracy of +-15%. Practically it is only useful as an indicator for
     * temperature changes.
     * 
     * .. versionadded:: 1.1.4~(Firmware)
     * 
     * 
     * @return int
     */
    public function getChipTemperature()
    {
        $payload = '';

        $data = $this->sendRequest(self::FUNCTION_GET_CHIP_TEMPERATURE, $payload);

        $payload = unpack('v1temperature', $data);

        return IPConnection::fixUnpackedInt16($payload['temperature']);
    }

    /**
     * Calling this function will reset the Brick. Calling this function
     * on a Brick inside of a stack will reset the whole stack.
     * 
     * After a reset you have to create new device objects,
     * calling functions on the existing ones will result in
     * undefined behavior!
     * 
     * .. versionadded:: 1.1.4~(Firmware)
     * 
     * 
     * @return void
     */
    public function reset()
    {
        $payload = '';

        $this->sendRequest(self::FUNCTION_RESET, $payload);
    }

    /**
     * Returns the UID, the UID where the Brick is connected to, 
     * the position, the hardware and firmware version as well as the
     * device identifier.
     * 
     * The position can be '0'-'8' (stack position).
     * 
     * The device identifiers can be found :ref:`here <device_identifier>`.
     * 
     * .. versionadded:: 2.0.0~(Firmware)
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
        $payload = unpack('V1position', $data);

        array_push($result, IPConnection::fixUnpackedInt32($payload['position']));

        call_user_func_array($this->registeredCallbacks[self::CALLBACK_POSITION_REACHED], $result);
    }

    /**
     * @internal
     * @param string $data
     */
    public function callbackWrapperAllData($data)
    {
        $result = array();
        $payload = unpack('v1current_velocity/V1current_position/V1remaining_steps/v1stack_voltage/v1external_voltage/v1current_consumption', $data);

        array_push($result, $payload['current_velocity']);
        array_push($result, IPConnection::fixUnpackedInt32($payload['current_position']));
        array_push($result, IPConnection::fixUnpackedInt32($payload['remaining_steps']));
        array_push($result, $payload['stack_voltage']);
        array_push($result, $payload['external_voltage']);
        array_push($result, $payload['current_consumption']);

        call_user_func_array($this->registeredCallbacks[self::CALLBACK_ALL_DATA], $result);
    }

    /**
     * @internal
     * @param string $data
     */
    public function callbackWrapperNewState($data)
    {
        $result = array();
        $payload = unpack('C1state_new/C1state_previous', $data);

        array_push($result, $payload['state_new']);
        array_push($result, $payload['state_previous']);

        call_user_func_array($this->registeredCallbacks[self::CALLBACK_NEW_STATE], $result);
    }
}

?>
