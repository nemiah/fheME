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
 * Device for controlling a LCD with 2 lines a 16 characters
 */
class BrickletLCD16x2 extends Device
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
     * @internal
     */
    const FUNCTION_SET_CUSTOM_CHARACTER = 11;

    /**
     * @internal
     */
    const FUNCTION_GET_CUSTOM_CHARACTER = 12;

    /**
     * @internal
     */
    const FUNCTION_GET_IDENTITY = 255;


    const DEVICE_IDENTIFIER = 211;

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

        $this->responseExpected[self::FUNCTION_WRITE_LINE] = self::RESPONSE_EXPECTED_FALSE;
        $this->responseExpected[self::FUNCTION_CLEAR_DISPLAY] = self::RESPONSE_EXPECTED_FALSE;
        $this->responseExpected[self::FUNCTION_BACKLIGHT_ON] = self::RESPONSE_EXPECTED_FALSE;
        $this->responseExpected[self::FUNCTION_BACKLIGHT_OFF] = self::RESPONSE_EXPECTED_FALSE;
        $this->responseExpected[self::FUNCTION_IS_BACKLIGHT_ON] = self::RESPONSE_EXPECTED_ALWAYS_TRUE;
        $this->responseExpected[self::FUNCTION_SET_CONFIG] = self::RESPONSE_EXPECTED_FALSE;
        $this->responseExpected[self::FUNCTION_GET_CONFIG] = self::RESPONSE_EXPECTED_ALWAYS_TRUE;
        $this->responseExpected[self::FUNCTION_IS_BUTTON_PRESSED] = self::RESPONSE_EXPECTED_ALWAYS_TRUE;
        $this->responseExpected[self::CALLBACK_BUTTON_PRESSED] = self::RESPONSE_EXPECTED_ALWAYS_FALSE;
        $this->responseExpected[self::CALLBACK_BUTTON_RELEASED] = self::RESPONSE_EXPECTED_ALWAYS_FALSE;
        $this->responseExpected[self::FUNCTION_SET_CUSTOM_CHARACTER] = self::RESPONSE_EXPECTED_FALSE;
        $this->responseExpected[self::FUNCTION_GET_CUSTOM_CHARACTER] = self::RESPONSE_EXPECTED_ALWAYS_TRUE;
        $this->responseExpected[self::FUNCTION_GET_IDENTITY] = self::RESPONSE_EXPECTED_ALWAYS_TRUE;

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
     * Writes text to a specific line (0 to 1) with a specific position 
     * (0 to 15). The text can have a maximum of 16 characters.
     * 
     * For example: (0, 5, "Hello") will write *Hello* in the middle of the
     * first line of the display.
     * 
     * The display uses a special charset that includes all ASCII characters except
     * backslash and tilde. The LCD charset also includes several other non-ASCII characters, see
     * the `charset specification <https://github.com/Tinkerforge/lcd-16x2-bricklet/raw/master/datasheets/standard_charset.pdf>`__
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
        for ($i = 0; $i < strlen($text) && $i < 16; $i++) {
            $payload .= pack('c', ord($text[$i]));
        }
        for ($i = strlen($text); $i < 16; $i++) {
            $payload .= pack('c', 0);
        }

        $this->sendRequest(self::FUNCTION_WRITE_LINE, $payload);
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

        $this->sendRequest(self::FUNCTION_CLEAR_DISPLAY, $payload);
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

        $this->sendRequest(self::FUNCTION_BACKLIGHT_ON, $payload);
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

        $this->sendRequest(self::FUNCTION_BACKLIGHT_OFF, $payload);
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

        $data = $this->sendRequest(self::FUNCTION_IS_BACKLIGHT_ON, $payload);

        $payload = unpack('C1backlight', $data);

        return (bool)$payload['backlight'];
    }

    /**
     * Configures if the cursor (shown as "_") should be visible and if it
     * should be blinking (shown as a blinking block). The cursor position
     * is one character behind the the last text written with 
     * BrickletLCD16x2::writeLine().
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

        $this->sendRequest(self::FUNCTION_SET_CONFIG, $payload);
    }

    /**
     * Returns the configuration as set by BrickletLCD16x2::setConfig().
     * 
     * 
     * @return array
     */
    public function getConfig()
    {
        $result = array();

        $payload = '';

        $data = $this->sendRequest(self::FUNCTION_GET_CONFIG, $payload);

        $payload = unpack('C1cursor/C1blinking', $data);

        $result['cursor'] = (bool)$payload['cursor'];
        $result['blinking'] = (bool)$payload['blinking'];

        return $result;
    }

    /**
     * Returns *true* if the button (0 to 2) is pressed. If you want to react
     * on button presses and releases it is recommended to use the
     * BrickletLCD16x2::CALLBACK_BUTTON_PRESSED and BrickletLCD16x2::CALLBACK_BUTTON_RELEASED callbacks.
     * 
     * @param int $button
     * 
     * @return bool
     */
    public function isButtonPressed($button)
    {
        $payload = '';
        $payload .= pack('C', $button);

        $data = $this->sendRequest(self::FUNCTION_IS_BUTTON_PRESSED, $payload);

        $payload = unpack('C1pressed', $data);

        return (bool)$payload['pressed'];
    }

    /**
     * The LCD 16x2 Bricklet can store up to 8 custom characters. The characters
     * consist of 5x8 pixels and can be addressed with the index 0-7. To describe
     * the pixels, the first 5 bits of 8 bytes are used. For example, to make
     * a custom character "H", you should transfer the following:
     * 
     * * ``character[0] = 0b00010001`` (decimal value 17)
     * * ``character[1] = 0b00010001`` (decimal value 17)
     * * ``character[2] = 0b00010001`` (decimal value 17)
     * * ``character[3] = 0b00011111`` (decimal value 31)
     * * ``character[4] = 0b00010001`` (decimal value 17)
     * * ``character[5] = 0b00010001`` (decimal value 17)
     * * ``character[6] = 0b00010001`` (decimal value 17)
     * * ``character[7] = 0b00000000`` (decimal value 0)
     * 
     * The characters can later be written with BrickletLCD16x2::writeLine() by using the
     * characters with the byte representation 8 to 15.
     * 
     * You can play around with the custom characters in Brick Viewer since
     * version 2.0.1.
     * 
     * Custom characters are stored by the LCD in RAM, so they have to be set
     * after each startup.
     * 
     * .. versionadded:: 2.0.1~(Plugin)
     * 
     * @param int $index
     * @param int[] $character
     * 
     * @return void
     */
    public function setCustomCharacter($index, $character)
    {
        $payload = '';
        $payload .= pack('C', $index);
        for ($i = 0; $i < 8; $i++) {
            $payload .= pack('C', $character[$i]);
        }

        $this->sendRequest(self::FUNCTION_SET_CUSTOM_CHARACTER, $payload);
    }

    /**
     * Returns the custom character for a given index, as set with
     * BrickletLCD16x2::setCustomCharacter().
     * 
     * .. versionadded:: 2.0.1~(Plugin)
     * 
     * @param int $index
     * 
     * @return array
     */
    public function getCustomCharacter($index)
    {
        $payload = '';
        $payload .= pack('C', $index);

        $data = $this->sendRequest(self::FUNCTION_GET_CUSTOM_CHARACTER, $payload);

        $payload = unpack('C8character', $data);

        return IPConnection::collectUnpackedArray($payload, 'character', 8);
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
