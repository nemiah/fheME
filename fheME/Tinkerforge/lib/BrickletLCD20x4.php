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
 * Device for controlling a LCD with 4 lines a 20 characters
 */
class BrickletLCD20x4 extends Device
{

    /**
     * This callback is triggered when a button is pressed. The parameter is
     * the number of the button (0 to 2).
     */
    const CALLBACK_BUTTON_PRESSED = 9;

    /**
     * This callback is triggered when a button is released. The parameter is
     * the number of the button (0 to 2).
     */
    const CALLBACK_BUTTON_RELEASED = 10;


    /**
     * @internal
     */
    const FUNCTION_WRITE_LINE = 1;

    /**
     * @internal
     */
    const FUNCTION_CLEAR_DISPLAY = 2;

    /**
     * @internal
     */
    const FUNCTION_BACKLIGHT_ON = 3;

    /**
     * @internal
     */
    const FUNCTION_BACKLIGHT_OFF = 4;

    /**
     * @internal
     */
    const FUNCTION_IS_BACKLIGHT_ON = 5;

    /**
     * @internal
     */
    const FUNCTION_SET_CONFIG = 6;

    /**
     * @internal
     */
    const FUNCTION_GET_CONFIG = 7;

    /**
     * @internal
     */
    const FUNCTION_IS_BUTTON_PRESSED = 8;

    /**
     * Creates an object with the unique device ID $uid. This object can
     * then be added to the IP connection.
     *
     * @param string $uid
     */
    public function __construct($uid)
    {
        parent::__construct($uid);

        $this->expectedName = 'LCD 20x4 Bricklet';

        $this->bindingVersion = array(1, 0, 0);

        $this->callbackWrappers[self::CALLBACK_BUTTON_PRESSED] = 'callbackWrapperButtonPressed';
        $this->callbackWrappers[self::CALLBACK_BUTTON_RELEASED] = 'callbackWrapperButtonReleased';
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
     * Writes text to a specific line (0 to 3) with a specific position 
     * (0 to 19). The text can have a maximum of 20 characters.
     * 
     * For example: (0, 7, "Hello") will write *Hello* in the middle of the
     * first line of the display.
     * 
     * The display uses a special charset that includes all ASCII characters except
     * backslash and tilde. The LCD charset also includes several other non-ASCII characters, see
     * the `charset specification <https://github.com/Tinkerforge/lcd-20x4-bricklet/raw/master/datasheets/standard_charset.pdf>`__
     * for details. The Unicode example above shows how to specify non-ASCII characters
     * and how to translate from Unicode to the LCD charset.
     * 
     * @param int $line
     * @param int $position
     * @param string $text
     * 
     * @return void
     */
    public function writeLine($line, $position, $text)
    {
        $payload = '';
        $payload .= pack('C', $line);
        $payload .= pack('C', $position);
        for ($i = 0; $i < strlen($text) && $i < 20; $i++) {
            $payload .= pack('c', ord($text[$i]));
        }
        for ($i = strlen($text); $i < 20; $i++) {
            $payload .= pack('c', 0);
        }

        $this->sendRequestNoResponse(self::FUNCTION_WRITE_LINE, $payload);
    }

    /**
     * Deletes all characters from the display.
     * 
     * 
     * @return void
     */
    public function clearDisplay()
    {
        $payload = '';

        $this->sendRequestNoResponse(self::FUNCTION_CLEAR_DISPLAY, $payload);
    }

    /**
     * Turns the backlight on.
     * 
     * 
     * @return void
     */
    public function backlightOn()
    {
        $payload = '';

        $this->sendRequestNoResponse(self::FUNCTION_BACKLIGHT_ON, $payload);
    }

    /**
     * Turns the backlight off.
     * 
     * 
     * @return void
     */
    public function backlightOff()
    {
        $payload = '';

        $this->sendRequestNoResponse(self::FUNCTION_BACKLIGHT_OFF, $payload);
    }

    /**
     * Returns *true* if the backlight is on and *false* otherwise.
     * 
     * 
     * @return bool
     */
    public function isBacklightOn()
    {
        $payload = '';

        $data = $this->sendRequestExpectResponse(self::FUNCTION_IS_BACKLIGHT_ON, $payload, 1);

        $payload = unpack('C1backlight', $data);

        return (bool)$payload['backlight'];
    }

    /**
     * Configures if the cursor (shown as "_") should be visible and if it
     * should be blinking (shown as a blinking block). The cursor position
     * is one character behind the the last text written with 
     * BrickletLCD20x4::writeLine().
     * 
     * The default is (false, false).
     * 
     * @param bool $cursor
     * @param bool $blinking
     * 
     * @return void
     */
    public function setConfig($cursor, $blinking)
    {
        $payload = '';
        $payload .= pack('C', intval((bool)$cursor));
        $payload .= pack('C', intval((bool)$blinking));

        $this->sendRequestNoResponse(self::FUNCTION_SET_CONFIG, $payload);
    }

    /**
     * Returns the configuration as set by BrickletLCD20x4::setConfig().
     * 
     * 
     * @return array
     */
    public function getConfig()
    {
        $result = array();

        $payload = '';

        $data = $this->sendRequestExpectResponse(self::FUNCTION_GET_CONFIG, $payload, 2);

        $payload = unpack('C1cursor/C1blinking', $data);

        $result['cursor'] = (bool)$payload['cursor'];
        $result['blinking'] = (bool)$payload['blinking'];

        return $result;
    }

    /**
     * Returns *true* if the button (0 to 2) is pressed. If you want to react
     * on button presses and releases it is recommended to use the
     * BrickletLCD20x4::CALLBACK_BUTTON_PRESSED and BrickletLCD20x4::CALLBACK_BUTTON_RELEASED callbacks.
     * 
     * @param int $button
     * 
     * @return bool
     */
    public function isButtonPressed($button)
    {
        $payload = '';
        $payload .= pack('C', $button);

        $data = $this->sendRequestExpectResponse(self::FUNCTION_IS_BUTTON_PRESSED, $payload, 1);

        $payload = unpack('C1pressed', $data);

        return (bool)$payload['pressed'];
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
    public function callbackWrapperButtonPressed($data)
    {
        $result = array();
        $payload = unpack('C1button', $data);

        array_push($result, $payload['button']);

        call_user_func_array($this->registeredCallbacks[self::CALLBACK_BUTTON_PRESSED], $result);
    }

    /**
     * @internal
     * @param string $data
     */
    public function callbackWrapperButtonReleased($data)
    {
        $result = array();
        $payload = unpack('C1button', $data);

        array_push($result, $payload['button']);

        call_user_func_array($this->registeredCallbacks[self::CALLBACK_BUTTON_RELEASED], $result);
    }
}

?>
