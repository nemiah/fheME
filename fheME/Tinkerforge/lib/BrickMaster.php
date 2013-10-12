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
 * Device for controlling Stacks and four Bricklets
 */
class BrickMaster extends Device
{

    /**
     * This callback is triggered periodically with the period that is set by
     * BrickMaster::setStackCurrentCallbackPeriod(). The parameter is the current of the
     * sensor.
     * 
     * BrickMaster::CALLBACK_STACK_CURRENT is only triggered if the current has changed since the
     * last triggering.
     * 
     * .. versionadded:: 2.0.5~(Firmware)
     */
    const CALLBACK_STACK_CURRENT = 59;

    /**
     * This callback is triggered periodically with the period that is set by
     * BrickMaster::setStackVoltageCallbackPeriod(). The parameter is the voltage of the
     * sensor.
     * 
     * BrickMaster::CALLBACK_STACK_VOLTAGE is only triggered if the voltage has changed since the
     * last triggering.
     * 
     * .. versionadded:: 2.0.5~(Firmware)
     */
    const CALLBACK_STACK_VOLTAGE = 60;

    /**
     * This callback is triggered periodically with the period that is set by
     * BrickMaster::setUSBVoltageCallbackPeriod(). The parameter is the USB voltage
     * in mV.
     * 
     * BrickMaster::CALLBACK_USB_VOLTAGE is only triggered if the USB voltage has changed since the
     * last triggering.
     * 
     * .. versionadded:: 2.0.5~(Firmware)
     */
    const CALLBACK_USB_VOLTAGE = 61;

    /**
     * This callback is triggered when the threshold as set by
     * BrickMaster::setStackCurrentCallbackThreshold() is reached.
     * The parameter is the stack current in mA.
     * 
     * If the threshold keeps being reached, the callback is triggered periodically
     * with the period as set by BrickMaster::setDebouncePeriod().
     * 
     * .. versionadded:: 2.0.5~(Firmware)
     */
    const CALLBACK_STACK_CURRENT_REACHED = 62;

    /**
     * This callback is triggered when the threshold as set by
     * BrickMaster::setStackVoltageCallbackThreshold() is reached.
     * The parameter is the stack voltage in mV.
     * 
     * If the threshold keeps being reached, the callback is triggered periodically
     * with the period as set by BrickMaster::setDebouncePeriod().
     * 
     * .. versionadded:: 2.0.5~(Firmware)
     */
    const CALLBACK_STACK_VOLTAGE_REACHED = 63;

    /**
     * This callback is triggered when the threshold as set by
     * BrickMaster::setUSBVoltageCallbackThreshold() is reached.
     * The parameter is the voltage of the sensor.
     * 
     * If the threshold keeps being reached, the callback is triggered periodically
     * with the period as set by BrickMaster::setDebouncePeriod().
     * 
     * .. versionadded:: 2.0.5~(Firmware)
     */
    const CALLBACK_USB_VOLTAGE_REACHED = 64;


    /**
     * @internal
     */
    const FUNCTION_GET_STACK_VOLTAGE = 1;

    /**
     * @internal
     */
    const FUNCTION_GET_STACK_CURRENT = 2;

    /**
     * @internal
     */
    const FUNCTION_SET_EXTENSION_TYPE = 3;

    /**
     * @internal
     */
    const FUNCTION_GET_EXTENSION_TYPE = 4;

    /**
     * @internal
     */
    const FUNCTION_IS_CHIBI_PRESENT = 5;

    /**
     * @internal
     */
    const FUNCTION_SET_CHIBI_ADDRESS = 6;

    /**
     * @internal
     */
    const FUNCTION_GET_CHIBI_ADDRESS = 7;

    /**
     * @internal
     */
    const FUNCTION_SET_CHIBI_MASTER_ADDRESS = 8;

    /**
     * @internal
     */
    const FUNCTION_GET_CHIBI_MASTER_ADDRESS = 9;

    /**
     * @internal
     */
    const FUNCTION_SET_CHIBI_SLAVE_ADDRESS = 10;

    /**
     * @internal
     */
    const FUNCTION_GET_CHIBI_SLAVE_ADDRESS = 11;

    /**
     * @internal
     */
    const FUNCTION_GET_CHIBI_SIGNAL_STRENGTH = 12;

    /**
     * @internal
     */
    const FUNCTION_GET_CHIBI_ERROR_LOG = 13;

    /**
     * @internal
     */
    const FUNCTION_SET_CHIBI_FREQUENCY = 14;

    /**
     * @internal
     */
    const FUNCTION_GET_CHIBI_FREQUENCY = 15;

    /**
     * @internal
     */
    const FUNCTION_SET_CHIBI_CHANNEL = 16;

    /**
     * @internal
     */
    const FUNCTION_GET_CHIBI_CHANNEL = 17;

    /**
     * @internal
     */
    const FUNCTION_IS_RS485_PRESENT = 18;

    /**
     * @internal
     */
    const FUNCTION_SET_RS485_ADDRESS = 19;

    /**
     * @internal
     */
    const FUNCTION_GET_RS485_ADDRESS = 20;

    /**
     * @internal
     */
    const FUNCTION_SET_RS485_SLAVE_ADDRESS = 21;

    /**
     * @internal
     */
    const FUNCTION_GET_RS485_SLAVE_ADDRESS = 22;

    /**
     * @internal
     */
    const FUNCTION_GET_RS485_ERROR_LOG = 23;

    /**
     * @internal
     */
    const FUNCTION_SET_RS485_CONFIGURATION = 24;

    /**
     * @internal
     */
    const FUNCTION_GET_RS485_CONFIGURATION = 25;

    /**
     * @internal
     */
    const FUNCTION_IS_WIFI_PRESENT = 26;

    /**
     * @internal
     */
    const FUNCTION_SET_WIFI_CONFIGURATION = 27;

    /**
     * @internal
     */
    const FUNCTION_GET_WIFI_CONFIGURATION = 28;

    /**
     * @internal
     */
    const FUNCTION_SET_WIFI_ENCRYPTION = 29;

    /**
     * @internal
     */
    const FUNCTION_GET_WIFI_ENCRYPTION = 30;

    /**
     * @internal
     */
    const FUNCTION_GET_WIFI_STATUS = 31;

    /**
     * @internal
     */
    const FUNCTION_REFRESH_WIFI_STATUS = 32;

    /**
     * @internal
     */
    const FUNCTION_SET_WIFI_CERTIFICATE = 33;

    /**
     * @internal
     */
    const FUNCTION_GET_WIFI_CERTIFICATE = 34;

    /**
     * @internal
     */
    const FUNCTION_SET_WIFI_POWER_MODE = 35;

    /**
     * @internal
     */
    const FUNCTION_GET_WIFI_POWER_MODE = 36;

    /**
     * @internal
     */
    const FUNCTION_GET_WIFI_BUFFER_INFO = 37;

    /**
     * @internal
     */
    const FUNCTION_SET_WIFI_REGULATORY_DOMAIN = 38;

    /**
     * @internal
     */
    const FUNCTION_GET_WIFI_REGULATORY_DOMAIN = 39;

    /**
     * @internal
     */
    const FUNCTION_GET_USB_VOLTAGE = 40;

    /**
     * @internal
     */
    const FUNCTION_SET_LONG_WIFI_KEY = 41;

    /**
     * @internal
     */
    const FUNCTION_GET_LONG_WIFI_KEY = 42;

    /**
     * @internal
     */
    const FUNCTION_SET_WIFI_HOSTNAME = 43;

    /**
     * @internal
     */
    const FUNCTION_GET_WIFI_HOSTNAME = 44;

    /**
     * @internal
     */
    const FUNCTION_SET_STACK_CURRENT_CALLBACK_PERIOD = 45;

    /**
     * @internal
     */
    const FUNCTION_GET_STACK_CURRENT_CALLBACK_PERIOD = 46;

    /**
     * @internal
     */
    const FUNCTION_SET_STACK_VOLTAGE_CALLBACK_PERIOD = 47;

    /**
     * @internal
     */
    const FUNCTION_GET_STACK_VOLTAGE_CALLBACK_PERIOD = 48;

    /**
     * @internal
     */
    const FUNCTION_SET_USB_VOLTAGE_CALLBACK_PERIOD = 49;

    /**
     * @internal
     */
    const FUNCTION_GET_USB_VOLTAGE_CALLBACK_PERIOD = 50;

    /**
     * @internal
     */
    const FUNCTION_SET_STACK_CURRENT_CALLBACK_THRESHOLD = 51;

    /**
     * @internal
     */
    const FUNCTION_GET_STACK_CURRENT_CALLBACK_THRESHOLD = 52;

    /**
     * @internal
     */
    const FUNCTION_SET_STACK_VOLTAGE_CALLBACK_THRESHOLD = 53;

    /**
     * @internal
     */
    const FUNCTION_GET_STACK_VOLTAGE_CALLBACK_THRESHOLD = 54;

    /**
     * @internal
     */
    const FUNCTION_SET_USB_VOLTAGE_CALLBACK_THRESHOLD = 55;

    /**
     * @internal
     */
    const FUNCTION_GET_USB_VOLTAGE_CALLBACK_THRESHOLD = 56;

    /**
     * @internal
     */
    const FUNCTION_SET_DEBOUNCE_PERIOD = 57;

    /**
     * @internal
     */
    const FUNCTION_GET_DEBOUNCE_PERIOD = 58;

    /**
     * @internal
     */
    const FUNCTION_IS_ETHERNET_PRESENT = 65;

    /**
     * @internal
     */
    const FUNCTION_SET_ETHERNET_CONFIGURATION = 66;

    /**
     * @internal
     */
    const FUNCTION_GET_ETHERNET_CONFIGURATION = 67;

    /**
     * @internal
     */
    const FUNCTION_GET_ETHERNET_STATUS = 68;

    /**
     * @internal
     */
    const FUNCTION_SET_ETHERNET_HOSTNAME = 69;

    /**
     * @internal
     */
    const FUNCTION_SET_ETHERNET_MAC_ADDRESS = 70;

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

    const EXTENSION_TYPE_CHIBI = 1;
    const EXTENSION_TYPE_RS485 = 2;
    const EXTENSION_TYPE_WIFI = 3;
    const EXTENSION_TYPE_ETHERNET = 4;
    const CHIBI_FREQUENCY_OQPSK_868_MHZ = 0;
    const CHIBI_FREQUENCY_OQPSK_915_MHZ = 1;
    const CHIBI_FREQUENCY_OQPSK_780_MHZ = 2;
    const CHIBI_FREQUENCY_BPSK40_915_MHZ = 3;
    const RS485_PARITY_NONE = 'n';
    const RS485_PARITY_EVEN = 'e';
    const RS485_PARITY_ODD = 'o';
    const WIFI_CONNECTION_DHCP = 0;
    const WIFI_CONNECTION_STATIC_IP = 1;
    const WIFI_CONNECTION_ACCESS_POINT_DHCP = 2;
    const WIFI_CONNECTION_ACCESS_POINT_STATIC_IP = 3;
    const WIFI_CONNECTION_AD_HOC_DHCP = 4;
    const WIFI_CONNECTION_AD_HOC_STATIC_IP = 5;
    const WIFI_ENCRYPTION_WPA_WPA2 = 0;
    const WIFI_ENCRYPTION_WPA_ENTERPRISE = 1;
    const WIFI_ENCRYPTION_WEP = 2;
    const WIFI_ENCRYPTION_NO_ENCRYPTION = 3;
    const WIFI_EAP_OPTION_OUTER_AUTH_EAP_FAST = 0;
    const WIFI_EAP_OPTION_OUTER_AUTH_EAP_TLS = 1;
    const WIFI_EAP_OPTION_OUTER_AUTH_EAP_TTLS = 2;
    const WIFI_EAP_OPTION_OUTER_AUTH_EAP_PEAP = 3;
    const WIFI_EAP_OPTION_INNER_AUTH_EAP_MSCHAP = 0;
    const WIFI_EAP_OPTION_INNER_AUTH_EAP_GTC = 4;
    const WIFI_EAP_OPTION_CERT_TYPE_CA_CERT = 0;
    const WIFI_EAP_OPTION_CERT_TYPE_CLIENT_CERT = 8;
    const WIFI_EAP_OPTION_CERT_TYPE_PRIVATE_KEY = 16;
    const WIFI_STATE_DISASSOCIATED = 0;
    const WIFI_STATE_ASSOCIATED = 1;
    const WIFI_STATE_ASSOCIATING = 2;
    const WIFI_STATE_ERROR = 3;
    const WIFI_STATE_NOT_INITIALIZED_YET = 255;
    const WIFI_POWER_MODE_FULL_SPEED = 0;
    const WIFI_POWER_MODE_LOW_POWER = 1;
    const WIFI_DOMAIN_CHANNEL_1TO11 = 0;
    const WIFI_DOMAIN_CHANNEL_1TO13 = 1;
    const WIFI_DOMAIN_CHANNEL_1TO14 = 2;
    const THRESHOLD_OPTION_OFF = 'x';
    const THRESHOLD_OPTION_OUTSIDE = 'o';
    const THRESHOLD_OPTION_INSIDE = 'i';
    const THRESHOLD_OPTION_SMALLER = '<';
    const THRESHOLD_OPTION_GREATER = '>';
    const ETHERNET_CONNECTION_DHCP = 0;
    const ETHERNET_CONNECTION_STATIC_IP = 1;

    const DEVICE_IDENTIFIER = 13;

    /**
     * Creates an object with the unique device ID $uid. This object can
     * then be added to the IP connection.
     *
     * @param string $uid
     */
    public function __construct($uid, $ipcon)
    {
        parent::__construct($uid, $ipcon);

        $this->apiVersion = array(2, 0, 2);

        $this->responseExpected[self::FUNCTION_GET_STACK_VOLTAGE] = self::RESPONSE_EXPECTED_ALWAYS_TRUE;
        $this->responseExpected[self::FUNCTION_GET_STACK_CURRENT] = self::RESPONSE_EXPECTED_ALWAYS_TRUE;
        $this->responseExpected[self::FUNCTION_SET_EXTENSION_TYPE] = self::RESPONSE_EXPECTED_FALSE;
        $this->responseExpected[self::FUNCTION_GET_EXTENSION_TYPE] = self::RESPONSE_EXPECTED_ALWAYS_TRUE;
        $this->responseExpected[self::FUNCTION_IS_CHIBI_PRESENT] = self::RESPONSE_EXPECTED_ALWAYS_TRUE;
        $this->responseExpected[self::FUNCTION_SET_CHIBI_ADDRESS] = self::RESPONSE_EXPECTED_FALSE;
        $this->responseExpected[self::FUNCTION_GET_CHIBI_ADDRESS] = self::RESPONSE_EXPECTED_ALWAYS_TRUE;
        $this->responseExpected[self::FUNCTION_SET_CHIBI_MASTER_ADDRESS] = self::RESPONSE_EXPECTED_FALSE;
        $this->responseExpected[self::FUNCTION_GET_CHIBI_MASTER_ADDRESS] = self::RESPONSE_EXPECTED_ALWAYS_TRUE;
        $this->responseExpected[self::FUNCTION_SET_CHIBI_SLAVE_ADDRESS] = self::RESPONSE_EXPECTED_FALSE;
        $this->responseExpected[self::FUNCTION_GET_CHIBI_SLAVE_ADDRESS] = self::RESPONSE_EXPECTED_ALWAYS_TRUE;
        $this->responseExpected[self::FUNCTION_GET_CHIBI_SIGNAL_STRENGTH] = self::RESPONSE_EXPECTED_ALWAYS_TRUE;
        $this->responseExpected[self::FUNCTION_GET_CHIBI_ERROR_LOG] = self::RESPONSE_EXPECTED_ALWAYS_TRUE;
        $this->responseExpected[self::FUNCTION_SET_CHIBI_FREQUENCY] = self::RESPONSE_EXPECTED_FALSE;
        $this->responseExpected[self::FUNCTION_GET_CHIBI_FREQUENCY] = self::RESPONSE_EXPECTED_ALWAYS_TRUE;
        $this->responseExpected[self::FUNCTION_SET_CHIBI_CHANNEL] = self::RESPONSE_EXPECTED_FALSE;
        $this->responseExpected[self::FUNCTION_GET_CHIBI_CHANNEL] = self::RESPONSE_EXPECTED_ALWAYS_TRUE;
        $this->responseExpected[self::FUNCTION_IS_RS485_PRESENT] = self::RESPONSE_EXPECTED_ALWAYS_TRUE;
        $this->responseExpected[self::FUNCTION_SET_RS485_ADDRESS] = self::RESPONSE_EXPECTED_FALSE;
        $this->responseExpected[self::FUNCTION_GET_RS485_ADDRESS] = self::RESPONSE_EXPECTED_ALWAYS_TRUE;
        $this->responseExpected[self::FUNCTION_SET_RS485_SLAVE_ADDRESS] = self::RESPONSE_EXPECTED_FALSE;
        $this->responseExpected[self::FUNCTION_GET_RS485_SLAVE_ADDRESS] = self::RESPONSE_EXPECTED_ALWAYS_TRUE;
        $this->responseExpected[self::FUNCTION_GET_RS485_ERROR_LOG] = self::RESPONSE_EXPECTED_ALWAYS_TRUE;
        $this->responseExpected[self::FUNCTION_SET_RS485_CONFIGURATION] = self::RESPONSE_EXPECTED_FALSE;
        $this->responseExpected[self::FUNCTION_GET_RS485_CONFIGURATION] = self::RESPONSE_EXPECTED_ALWAYS_TRUE;
        $this->responseExpected[self::FUNCTION_IS_WIFI_PRESENT] = self::RESPONSE_EXPECTED_ALWAYS_TRUE;
        $this->responseExpected[self::FUNCTION_SET_WIFI_CONFIGURATION] = self::RESPONSE_EXPECTED_FALSE;
        $this->responseExpected[self::FUNCTION_GET_WIFI_CONFIGURATION] = self::RESPONSE_EXPECTED_ALWAYS_TRUE;
        $this->responseExpected[self::FUNCTION_SET_WIFI_ENCRYPTION] = self::RESPONSE_EXPECTED_FALSE;
        $this->responseExpected[self::FUNCTION_GET_WIFI_ENCRYPTION] = self::RESPONSE_EXPECTED_ALWAYS_TRUE;
        $this->responseExpected[self::FUNCTION_GET_WIFI_STATUS] = self::RESPONSE_EXPECTED_ALWAYS_TRUE;
        $this->responseExpected[self::FUNCTION_REFRESH_WIFI_STATUS] = self::RESPONSE_EXPECTED_FALSE;
        $this->responseExpected[self::FUNCTION_SET_WIFI_CERTIFICATE] = self::RESPONSE_EXPECTED_FALSE;
        $this->responseExpected[self::FUNCTION_GET_WIFI_CERTIFICATE] = self::RESPONSE_EXPECTED_ALWAYS_TRUE;
        $this->responseExpected[self::FUNCTION_SET_WIFI_POWER_MODE] = self::RESPONSE_EXPECTED_FALSE;
        $this->responseExpected[self::FUNCTION_GET_WIFI_POWER_MODE] = self::RESPONSE_EXPECTED_ALWAYS_TRUE;
        $this->responseExpected[self::FUNCTION_GET_WIFI_BUFFER_INFO] = self::RESPONSE_EXPECTED_ALWAYS_TRUE;
        $this->responseExpected[self::FUNCTION_SET_WIFI_REGULATORY_DOMAIN] = self::RESPONSE_EXPECTED_FALSE;
        $this->responseExpected[self::FUNCTION_GET_WIFI_REGULATORY_DOMAIN] = self::RESPONSE_EXPECTED_ALWAYS_TRUE;
        $this->responseExpected[self::FUNCTION_GET_USB_VOLTAGE] = self::RESPONSE_EXPECTED_ALWAYS_TRUE;
        $this->responseExpected[self::FUNCTION_SET_LONG_WIFI_KEY] = self::RESPONSE_EXPECTED_FALSE;
        $this->responseExpected[self::FUNCTION_GET_LONG_WIFI_KEY] = self::RESPONSE_EXPECTED_ALWAYS_TRUE;
        $this->responseExpected[self::FUNCTION_SET_WIFI_HOSTNAME] = self::RESPONSE_EXPECTED_FALSE;
        $this->responseExpected[self::FUNCTION_GET_WIFI_HOSTNAME] = self::RESPONSE_EXPECTED_ALWAYS_TRUE;
        $this->responseExpected[self::FUNCTION_SET_STACK_CURRENT_CALLBACK_PERIOD] = self::RESPONSE_EXPECTED_TRUE;
        $this->responseExpected[self::FUNCTION_GET_STACK_CURRENT_CALLBACK_PERIOD] = self::RESPONSE_EXPECTED_ALWAYS_TRUE;
        $this->responseExpected[self::FUNCTION_SET_STACK_VOLTAGE_CALLBACK_PERIOD] = self::RESPONSE_EXPECTED_TRUE;
        $this->responseExpected[self::FUNCTION_GET_STACK_VOLTAGE_CALLBACK_PERIOD] = self::RESPONSE_EXPECTED_ALWAYS_TRUE;
        $this->responseExpected[self::FUNCTION_SET_USB_VOLTAGE_CALLBACK_PERIOD] = self::RESPONSE_EXPECTED_TRUE;
        $this->responseExpected[self::FUNCTION_GET_USB_VOLTAGE_CALLBACK_PERIOD] = self::RESPONSE_EXPECTED_ALWAYS_TRUE;
        $this->responseExpected[self::FUNCTION_SET_STACK_CURRENT_CALLBACK_THRESHOLD] = self::RESPONSE_EXPECTED_TRUE;
        $this->responseExpected[self::FUNCTION_GET_STACK_CURRENT_CALLBACK_THRESHOLD] = self::RESPONSE_EXPECTED_ALWAYS_TRUE;
        $this->responseExpected[self::FUNCTION_SET_STACK_VOLTAGE_CALLBACK_THRESHOLD] = self::RESPONSE_EXPECTED_TRUE;
        $this->responseExpected[self::FUNCTION_GET_STACK_VOLTAGE_CALLBACK_THRESHOLD] = self::RESPONSE_EXPECTED_ALWAYS_TRUE;
        $this->responseExpected[self::FUNCTION_SET_USB_VOLTAGE_CALLBACK_THRESHOLD] = self::RESPONSE_EXPECTED_TRUE;
        $this->responseExpected[self::FUNCTION_GET_USB_VOLTAGE_CALLBACK_THRESHOLD] = self::RESPONSE_EXPECTED_ALWAYS_TRUE;
        $this->responseExpected[self::FUNCTION_SET_DEBOUNCE_PERIOD] = self::RESPONSE_EXPECTED_TRUE;
        $this->responseExpected[self::FUNCTION_GET_DEBOUNCE_PERIOD] = self::RESPONSE_EXPECTED_ALWAYS_TRUE;
        $this->responseExpected[self::CALLBACK_STACK_CURRENT] = self::RESPONSE_EXPECTED_ALWAYS_FALSE;
        $this->responseExpected[self::CALLBACK_STACK_VOLTAGE] = self::RESPONSE_EXPECTED_ALWAYS_FALSE;
        $this->responseExpected[self::CALLBACK_USB_VOLTAGE] = self::RESPONSE_EXPECTED_ALWAYS_FALSE;
        $this->responseExpected[self::CALLBACK_STACK_CURRENT_REACHED] = self::RESPONSE_EXPECTED_ALWAYS_FALSE;
        $this->responseExpected[self::CALLBACK_STACK_VOLTAGE_REACHED] = self::RESPONSE_EXPECTED_ALWAYS_FALSE;
        $this->responseExpected[self::CALLBACK_USB_VOLTAGE_REACHED] = self::RESPONSE_EXPECTED_ALWAYS_FALSE;
        $this->responseExpected[self::FUNCTION_IS_ETHERNET_PRESENT] = self::RESPONSE_EXPECTED_ALWAYS_TRUE;
        $this->responseExpected[self::FUNCTION_SET_ETHERNET_CONFIGURATION] = self::RESPONSE_EXPECTED_FALSE;
        $this->responseExpected[self::FUNCTION_GET_ETHERNET_CONFIGURATION] = self::RESPONSE_EXPECTED_ALWAYS_TRUE;
        $this->responseExpected[self::FUNCTION_GET_ETHERNET_STATUS] = self::RESPONSE_EXPECTED_ALWAYS_TRUE;
        $this->responseExpected[self::FUNCTION_SET_ETHERNET_HOSTNAME] = self::RESPONSE_EXPECTED_FALSE;
        $this->responseExpected[self::FUNCTION_SET_ETHERNET_MAC_ADDRESS] = self::RESPONSE_EXPECTED_FALSE;
        $this->responseExpected[self::FUNCTION_GET_PROTOCOL1_BRICKLET_NAME] = self::RESPONSE_EXPECTED_ALWAYS_TRUE;
        $this->responseExpected[self::FUNCTION_GET_CHIP_TEMPERATURE] = self::RESPONSE_EXPECTED_ALWAYS_TRUE;
        $this->responseExpected[self::FUNCTION_RESET] = self::RESPONSE_EXPECTED_FALSE;
        $this->responseExpected[self::FUNCTION_GET_IDENTITY] = self::RESPONSE_EXPECTED_ALWAYS_TRUE;

        $this->callbackWrappers[self::CALLBACK_STACK_CURRENT] = 'callbackWrapperStackCurrent';
        $this->callbackWrappers[self::CALLBACK_STACK_VOLTAGE] = 'callbackWrapperStackVoltage';
        $this->callbackWrappers[self::CALLBACK_USB_VOLTAGE] = 'callbackWrapperUSBVoltage';
        $this->callbackWrappers[self::CALLBACK_STACK_CURRENT_REACHED] = 'callbackWrapperStackCurrentReached';
        $this->callbackWrappers[self::CALLBACK_STACK_VOLTAGE_REACHED] = 'callbackWrapperStackVoltageReached';
        $this->callbackWrappers[self::CALLBACK_USB_VOLTAGE_REACHED] = 'callbackWrapperUSBVoltageReached';
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
     * Returns the stack voltage in mV. The stack voltage is the
     * voltage that is supplied via the stack, i.e. it is given by a 
     * Step-Down or Step-Up Power Supply.
     * 
     * 
     * @return int
     */
    public function getStackVoltage()
    {
        $payload = '';

        $data = $this->sendRequest(self::FUNCTION_GET_STACK_VOLTAGE, $payload);

        $payload = unpack('v1voltage', $data);

        return $payload['voltage'];
    }

    /**
     * Returns the stack current in mA. The stack current is the
     * current that is drawn via the stack, i.e. it is given by a
     * Step-Down or Step-Up Power Supply.
     * 
     * 
     * @return int
     */
    public function getStackCurrent()
    {
        $payload = '';

        $data = $this->sendRequest(self::FUNCTION_GET_STACK_CURRENT, $payload);

        $payload = unpack('v1current', $data);

        return $payload['current'];
    }

    /**
     * Writes the extension type to the EEPROM of a specified extension. 
     * The extension is either 0 or 1 (0 is the on the bottom, 1 is the on on top, 
     * if only one extension is present use 0).
     * 
     * Possible extension types:
     * 
     * <code>
     *  "Type", "Description"
     * 
     *  "1",    "Chibi"
     *  "2",    "RS485"
     *  "3",    "WIFI"
     *  "4",    "Ethernet"
     * </code>
     * 
     * The extension type is already set when bought and it can be set with the 
     * Brick Viewer, it is unlikely that you need this function.
     * 
     * @param int $extension
     * @param int $exttype
     * 
     * @return void
     */
    public function setExtensionType($extension, $exttype)
    {
        $payload = '';
        $payload .= pack('C', $extension);
        $payload .= pack('V', $exttype);

        $this->sendRequest(self::FUNCTION_SET_EXTENSION_TYPE, $payload);
    }

    /**
     * Returns the type for a given extension as set by BrickMaster::setExtensionType().
     * 
     * @param int $extension
     * 
     * @return int
     */
    public function getExtensionType($extension)
    {
        $payload = '';
        $payload .= pack('C', $extension);

        $data = $this->sendRequest(self::FUNCTION_GET_EXTENSION_TYPE, $payload);

        $payload = unpack('V1exttype', $data);

        return IPConnection::fixUnpackedUInt32($payload['exttype']);
    }

    /**
     * Returns *true* if a Chibi Extension is available to be used by the Master Brick.
     * 
     * .. versionadded:: 1.1.0~(Firmware)
     * 
     * 
     * @return bool
     */
    public function isChibiPresent()
    {
        $payload = '';

        $data = $this->sendRequest(self::FUNCTION_IS_CHIBI_PRESENT, $payload);

        $payload = unpack('C1present', $data);

        return (bool)$payload['present'];
    }

    /**
     * Sets the address (1-255) belonging to the Chibi Extension.
     * 
     * It is possible to set the address with the Brick Viewer and it will be 
     * saved in the EEPROM of the Chibi Extension, it does not
     * have to be set on every startup.
     * 
     * .. versionadded:: 1.1.0~(Firmware)
     * 
     * @param int $address
     * 
     * @return void
     */
    public function setChibiAddress($address)
    {
        $payload = '';
        $payload .= pack('C', $address);

        $this->sendRequest(self::FUNCTION_SET_CHIBI_ADDRESS, $payload);
    }

    /**
     * Returns the address as set by BrickMaster::setChibiAddress().
     * 
     * .. versionadded:: 1.1.0~(Firmware)
     * 
     * 
     * @return int
     */
    public function getChibiAddress()
    {
        $payload = '';

        $data = $this->sendRequest(self::FUNCTION_GET_CHIBI_ADDRESS, $payload);

        $payload = unpack('C1address', $data);

        return $payload['address'];
    }

    /**
     * Sets the address (1-255) of the Chibi Master. This address is used if the
     * Chibi Extension is used as slave (i.e. it does not have a USB connection).
     * 
     * It is possible to set the address with the Brick Viewer and it will be 
     * saved in the EEPROM of the Chibi Extension, it does not
     * have to be set on every startup.
     * 
     * .. versionadded:: 1.1.0~(Firmware)
     * 
     * @param int $address
     * 
     * @return void
     */
    public function setChibiMasterAddress($address)
    {
        $payload = '';
        $payload .= pack('C', $address);

        $this->sendRequest(self::FUNCTION_SET_CHIBI_MASTER_ADDRESS, $payload);
    }

    /**
     * Returns the address as set by BrickMaster::setChibiMasterAddress().
     * 
     * .. versionadded:: 1.1.0~(Firmware)
     * 
     * 
     * @return int
     */
    public function getChibiMasterAddress()
    {
        $payload = '';

        $data = $this->sendRequest(self::FUNCTION_GET_CHIBI_MASTER_ADDRESS, $payload);

        $payload = unpack('C1address', $data);

        return $payload['address'];
    }

    /**
     * Sets up to 254 slave addresses. Valid addresses are in range 1-255. 0 has a
     * special meaning, it is used as list terminator and not allowed as normal slave
     * address. The address numeration (via ``num`` parameter) has to be used
     * ascending from 0. For example: If you use the Chibi Extension in Master mode
     * (i.e. the stack has an USB connection) and you want to talk to three other
     * Chibi stacks with the slave addresses 17, 23, and 42, you should call with
     * ``(0, 17)``, ``(1, 23)``, ``(2, 42)`` and ``(3, 0)``. The last call with
     * ``(3, 0)`` is a list terminator and indicates that the Chibi slave address
     * list contains 3 addresses in this case.
     * 
     * It is possible to set the addresses with the Brick Viewer, that will take care
     * of correct address numeration and list termination.
     * 
     * The slave addresses will be saved in the EEPROM of the Chibi Extension, they
     * don't have to be set on every startup.
     * 
     * .. versionadded:: 1.1.0~(Firmware)
     * 
     * @param int $num
     * @param int $address
     * 
     * @return void
     */
    public function setChibiSlaveAddress($num, $address)
    {
        $payload = '';
        $payload .= pack('C', $num);
        $payload .= pack('C', $address);

        $this->sendRequest(self::FUNCTION_SET_CHIBI_SLAVE_ADDRESS, $payload);
    }

    /**
     * Returns the slave address for a given ``num`` as set by
     * BrickMaster::setChibiSlaveAddress().
     * 
     * .. versionadded:: 1.1.0~(Firmware)
     * 
     * @param int $num
     * 
     * @return int
     */
    public function getChibiSlaveAddress($num)
    {
        $payload = '';
        $payload .= pack('C', $num);

        $data = $this->sendRequest(self::FUNCTION_GET_CHIBI_SLAVE_ADDRESS, $payload);

        $payload = unpack('C1address', $data);

        return $payload['address'];
    }

    /**
     * Returns the signal strength in dBm. The signal strength updates every time a
     * packet is received.
     * 
     * .. versionadded:: 1.1.0~(Firmware)
     * 
     * 
     * @return int
     */
    public function getChibiSignalStrength()
    {
        $payload = '';

        $data = $this->sendRequest(self::FUNCTION_GET_CHIBI_SIGNAL_STRENGTH, $payload);

        $payload = unpack('C1signal_strength', $data);

        return $payload['signal_strength'];
    }

    /**
     * Returns underrun, CRC error, no ACK and overflow error counts of the Chibi
     * communication. If these errors start rising, it is likely that either the
     * distance between two Chibi stacks is becoming too big or there are
     * interferences.
     * 
     * .. versionadded:: 1.1.0~(Firmware)
     * 
     * 
     * @return array
     */
    public function getChibiErrorLog()
    {
        $result = array();

        $payload = '';

        $data = $this->sendRequest(self::FUNCTION_GET_CHIBI_ERROR_LOG, $payload);

        $payload = unpack('v1underrun/v1crc_error/v1no_ack/v1overflow', $data);

        $result['underrun'] = $payload['underrun'];
        $result['crc_error'] = $payload['crc_error'];
        $result['no_ack'] = $payload['no_ack'];
        $result['overflow'] = $payload['overflow'];

        return $result;
    }

    /**
     * Sets the Chibi frequency range for the Chibi Extension. Possible values are:
     * 
     * <code>
     *  "Type", "Description"
     * 
     *  "0",    "OQPSK 868MHz (Europe)"
     *  "1",    "OQPSK 915MHz (US)"
     *  "2",    "OQPSK 780MHz (China)"
     *  "3",    "BPSK40 915MHz"
     * </code>
     * 
     * It is possible to set the frequency with the Brick Viewer and it will be 
     * saved in the EEPROM of the Chibi Extension, it does not
     * have to be set on every startup.
     * 
     * .. versionadded:: 1.1.0~(Firmware)
     * 
     * @param int $frequency
     * 
     * @return void
     */
    public function setChibiFrequency($frequency)
    {
        $payload = '';
        $payload .= pack('C', $frequency);

        $this->sendRequest(self::FUNCTION_SET_CHIBI_FREQUENCY, $payload);
    }

    /**
     * Returns the frequency value as set by BrickMaster::setChibiFrequency().
     * 
     * .. versionadded:: 1.1.0~(Firmware)
     * 
     * 
     * @return int
     */
    public function getChibiFrequency()
    {
        $payload = '';

        $data = $this->sendRequest(self::FUNCTION_GET_CHIBI_FREQUENCY, $payload);

        $payload = unpack('C1frequency', $data);

        return $payload['frequency'];
    }

    /**
     * Sets the channel used by the Chibi Extension. Possible channels are
     * different for different frequencies:
     * 
     * <code>
     *  "Frequency",             "Possible Channels"
     * 
     *  "OQPSK 868MHz (Europe)", "0"
     *  "OQPSK 915MHz (US)",     "1, 2, 3, 4, 5, 6, 7, 8, 9, 10"
     *  "OQPSK 780MHz (China)",  "0, 1, 2, 3"
     *  "BPSK40 915MHz",         "1, 2, 3, 4, 5, 6, 7, 8, 9, 10"
     * </code>
     * 
     * It is possible to set the channel with the Brick Viewer and it will be 
     * saved in the EEPROM of the Chibi Extension, it does not
     * have to be set on every startup.
     * 
     * .. versionadded:: 1.1.0~(Firmware)
     * 
     * @param int $channel
     * 
     * @return void
     */
    public function setChibiChannel($channel)
    {
        $payload = '';
        $payload .= pack('C', $channel);

        $this->sendRequest(self::FUNCTION_SET_CHIBI_CHANNEL, $payload);
    }

    /**
     * Returns the channel as set by BrickMaster::setChibiChannel().
     * 
     * .. versionadded:: 1.1.0~(Firmware)
     * 
     * 
     * @return int
     */
    public function getChibiChannel()
    {
        $payload = '';

        $data = $this->sendRequest(self::FUNCTION_GET_CHIBI_CHANNEL, $payload);

        $payload = unpack('C1channel', $data);

        return $payload['channel'];
    }

    /**
     * Returns *true* if a RS485 Extension is available to be used by the Master Brick.
     * 
     * .. versionadded:: 1.2.0~(Firmware)
     * 
     * 
     * @return bool
     */
    public function isRS485Present()
    {
        $payload = '';

        $data = $this->sendRequest(self::FUNCTION_IS_RS485_PRESENT, $payload);

        $payload = unpack('C1present', $data);

        return (bool)$payload['present'];
    }

    /**
     * Sets the address (0-255) belonging to the RS485 Extension.
     * 
     * Set to 0 if the RS485 Extension should be the RS485 Master (i.e.
     * connected to a PC via USB).
     * 
     * It is possible to set the address with the Brick Viewer and it will be 
     * saved in the EEPROM of the RS485 Extension, it does not
     * have to be set on every startup.
     * 
     * .. versionadded:: 1.2.0~(Firmware)
     * 
     * @param int $address
     * 
     * @return void
     */
    public function setRS485Address($address)
    {
        $payload = '';
        $payload .= pack('C', $address);

        $this->sendRequest(self::FUNCTION_SET_RS485_ADDRESS, $payload);
    }

    /**
     * Returns the address as set by BrickMaster::setRS485Address().
     * 
     * .. versionadded:: 1.2.0~(Firmware)
     * 
     * 
     * @return int
     */
    public function getRS485Address()
    {
        $payload = '';

        $data = $this->sendRequest(self::FUNCTION_GET_RS485_ADDRESS, $payload);

        $payload = unpack('C1address', $data);

        return $payload['address'];
    }

    /**
     * Sets up to 255 slave addresses. Valid addresses are in range 1-255. 0 has a
     * special meaning, it is used as list terminator and not allowed as normal slave
     * address. The address numeration (via ``num`` parameter) has to be used
     * ascending from 0. For example: If you use the RS485 Extension in Master mode
     * (i.e. the stack has an USB connection) and you want to talk to three other
     * RS485 stacks with the addresses 17, 23, and 42, you should call with
     * ``(0, 17)``, ``(1, 23)``, ``(2, 42)`` and ``(3, 0)``. The last call with
     * ``(3, 0)`` is a list terminator and indicates that the RS485 slave address list
     * contains 3 addresses in this case.
     * 
     * It is possible to set the addresses with the Brick Viewer, that will take care
     * of correct address numeration and list termination.
     * 
     * The slave addresses will be saved in the EEPROM of the Chibi Extension, they
     * don't have to be set on every startup.
     * 
     * .. versionadded:: 1.2.0~(Firmware)
     * 
     * @param int $num
     * @param int $address
     * 
     * @return void
     */
    public function setRS485SlaveAddress($num, $address)
    {
        $payload = '';
        $payload .= pack('C', $num);
        $payload .= pack('C', $address);

        $this->sendRequest(self::FUNCTION_SET_RS485_SLAVE_ADDRESS, $payload);
    }

    /**
     * Returns the slave address for a given ``num`` as set by
     * BrickMaster::setRS485SlaveAddress().
     * 
     * .. versionadded:: 1.2.0~(Firmware)
     * 
     * @param int $num
     * 
     * @return int
     */
    public function getRS485SlaveAddress($num)
    {
        $payload = '';
        $payload .= pack('C', $num);

        $data = $this->sendRequest(self::FUNCTION_GET_RS485_SLAVE_ADDRESS, $payload);

        $payload = unpack('C1address', $data);

        return $payload['address'];
    }

    /**
     * Returns CRC error counts of the RS485 communication.
     * If this counter starts rising, it is likely that the distance
     * between the RS485 nodes is too big or there is some kind of
     * interference.
     * 
     * .. versionadded:: 1.2.0~(Firmware)
     * 
     * 
     * @return int
     */
    public function getRS485ErrorLog()
    {
        $payload = '';

        $data = $this->sendRequest(self::FUNCTION_GET_RS485_ERROR_LOG, $payload);

        $payload = unpack('v1crc_error', $data);

        return $payload['crc_error'];
    }

    /**
     * Sets the configuration of the RS485 Extension. Speed is given in baud. The
     * Master Brick will try to match the given baud rate as exactly as possible.
     * The maximum recommended baud rate is 2000000 (2Mbit/s).
     * Possible values for parity are 'n' (none), 'e' (even) and 'o' (odd).
     * Possible values for stop bits are 1 and 2.
     * 
     * If your RS485 is unstable (lost messages etc.), the first thing you should
     * try is to decrease the speed. On very large bus (e.g. 1km), you probably
     * should use a value in the range of 100000 (100kbit/s).
     * 
     * The values are stored in the EEPROM and only applied on startup. That means
     * you have to restart the Master Brick after configuration.
     * 
     * .. versionadded:: 1.2.0~(Firmware)
     * 
     * @param int $speed
     * @param string $parity
     * @param int $stopbits
     * 
     * @return void
     */
    public function setRS485Configuration($speed, $parity, $stopbits)
    {
        $payload = '';
        $payload .= pack('V', $speed);
        $payload .= pack('c', ord($parity));
        $payload .= pack('C', $stopbits);

        $this->sendRequest(self::FUNCTION_SET_RS485_CONFIGURATION, $payload);
    }

    /**
     * Returns the configuration as set by BrickMaster::setRS485Configuration().
     * 
     * .. versionadded:: 1.2.0~(Firmware)
     * 
     * 
     * @return array
     */
    public function getRS485Configuration()
    {
        $result = array();

        $payload = '';

        $data = $this->sendRequest(self::FUNCTION_GET_RS485_CONFIGURATION, $payload);

        $payload = unpack('V1speed/c1parity/C1stopbits', $data);

        $result['speed'] = IPConnection::fixUnpackedUInt32($payload['speed']);
        $result['parity'] = chr($payload['parity']);
        $result['stopbits'] = $payload['stopbits'];

        return $result;
    }

    /**
     * Returns *true* if a WIFI Extension is available to be used by the Master Brick.
     * 
     * .. versionadded:: 1.2.0~(Firmware)
     * 
     * 
     * @return bool
     */
    public function isWifiPresent()
    {
        $payload = '';

        $data = $this->sendRequest(self::FUNCTION_IS_WIFI_PRESENT, $payload);

        $payload = unpack('C1present', $data);

        return (bool)$payload['present'];
    }

    /**
     * Sets the configuration of the WIFI Extension. The ``ssid`` can have a max length
     * of 32 characters. Possible values for ``connection`` are:
     * 
     * <code>
     *  "Value", "Description"
     * 
     *  "0", "DHCP"
     *  "1", "Static IP"
     *  "2", "Access Point: DHCP"
     *  "3", "Access Point: Static IP"
     *  "4", "Ad Hoc: DHCP"
     *  "5", "Ad Hoc: Static IP"
     * </code>
     * 
     * If you set ``connection`` to one of the static IP options then you have to
     * supply ``ip``, ``subnet_mask`` and ``gateway`` as an array of size 4 (first
     * element of the array is the least significant byte of the address). If
     * ``connection`` is set to one of the DHCP options then ``ip``, ``subnet_mask``
     * and ``gateway`` are ignored, you can set them to 0.
     * 
     * The last parameter is the port that your program will connect to. The
     * default port, that is used by brickd, is 4223.
     * 
     * The values are stored in the EEPROM and only applied on startup. That means
     * you have to restart the Master Brick after configuration.
     * 
     * It is recommended to use the Brick Viewer to set the WIFI configuration.
     * 
     * .. versionadded:: 1.3.0~(Firmware)
     * 
     * @param string $ssid
     * @param int $connection
     * @param int[] $ip
     * @param int[] $subnet_mask
     * @param int[] $gateway
     * @param int $port
     * 
     * @return void
     */
    public function setWifiConfiguration($ssid, $connection, $ip, $subnet_mask, $gateway, $port)
    {
        $payload = '';
        for ($i = 0; $i < strlen($ssid) && $i < 32; $i++) {
            $payload .= pack('c', ord($ssid[$i]));
        }
        for ($i = strlen($ssid); $i < 32; $i++) {
            $payload .= pack('c', 0);
        }
        $payload .= pack('C', $connection);
        for ($i = 0; $i < 4; $i++) {
            $payload .= pack('C', $ip[$i]);
        }
        for ($i = 0; $i < 4; $i++) {
            $payload .= pack('C', $subnet_mask[$i]);
        }
        for ($i = 0; $i < 4; $i++) {
            $payload .= pack('C', $gateway[$i]);
        }
        $payload .= pack('v', $port);

        $this->sendRequest(self::FUNCTION_SET_WIFI_CONFIGURATION, $payload);
    }

    /**
     * Returns the configuration as set by BrickMaster::setWifiConfiguration().
     * 
     * .. versionadded:: 1.3.0~(Firmware)
     * 
     * 
     * @return array
     */
    public function getWifiConfiguration()
    {
        $result = array();

        $payload = '';

        $data = $this->sendRequest(self::FUNCTION_GET_WIFI_CONFIGURATION, $payload);

        $payload = unpack('c32ssid/C1connection/C4ip/C4subnet_mask/C4gateway/v1port', $data);

        $result['ssid'] = IPConnection::implodeUnpackedString($payload, 'ssid', 32);
        $result['connection'] = $payload['connection'];
        $result['ip'] = IPConnection::collectUnpackedArray($payload, 'ip', 4);
        $result['subnet_mask'] = IPConnection::collectUnpackedArray($payload, 'subnet_mask', 4);
        $result['gateway'] = IPConnection::collectUnpackedArray($payload, 'gateway', 4);
        $result['port'] = $payload['port'];

        return $result;
    }

    /**
     * Sets the encryption of the WIFI Extension. The first parameter is the
     * type of the encryption. Possible values are:
     * 
     * <code>
     *  "Value", "Description"
     * 
     *  "0", "WPA/WPA2"
     *  "1", "WPA Enterprise (EAP-FAST, EAP-TLS, EAP-TTLS, PEAP)"
     *  "2", "WEP"
     *  "3", "No Encryption"
     * </code>
     * 
     * The ``key`` has a max length of 50 characters and is used if ``encryption``
     * is set to 0 or 2 (WPA/WPA2 or WEP). Otherwise the value is ignored.
     * 
     * For WPA/WPA2 the key has to be at least 8 characters long. If you want to set
     * a key with more than 50 characters, see BrickMaster::setLongWifiKey().
     * 
     * For WEP the key has to be either 10 or 26 hexadecimal digits long. It is
     * possible to set the WEP ``key_index`` (1-4). If you don't know your
     * ``key_index``, it is likely 1.
     * 
     * If you choose WPA Enterprise as encryption, you have to set ``eap_options`` and
     * the length of the certificates (for other encryption types these parameters
     * are ignored). The certificate length are given in byte and the certificates
     * themselves can be set with BrickMaster::setWifiCertificate(). ``eap_options`` consist
     * of the outer authentication (bits 1-2), inner authentication (bit 3) and
     * certificate type (bits 4-5):
     * 
     * <code>
     *  "Option", "Bits", "Description"
     * 
     *  "outer authentication", "1-2", "0=EAP-FAST, 1=EAP-TLS, 2=EAP-TTLS, 3=EAP-PEAP"
     *  "inner authentication", "3", "0=EAP-MSCHAP, 1=EAP-GTC"
     *  "certificate type", "4-5", "0=CA Certificate, 1=Client Certificate, 2=Private Key"
     * </code>
     * 
     * Example for EAP-TTLS + EAP-GTC + Private Key: ``option = 2 | (1 << 2) | (2 << 3)``.
     * 
     * The values are stored in the EEPROM and only applied on startup. That means
     * you have to restart the Master Brick after configuration.
     * 
     * It is recommended to use the Brick Viewer to set the WIFI encryption.
     * 
     * .. versionadded:: 1.3.0~(Firmware)
     * 
     * @param int $encryption
     * @param string $key
     * @param int $key_index
     * @param int $eap_options
     * @param int $ca_certificate_length
     * @param int $client_certificate_length
     * @param int $private_key_length
     * 
     * @return void
     */
    public function setWifiEncryption($encryption, $key, $key_index, $eap_options, $ca_certificate_length, $client_certificate_length, $private_key_length)
    {
        $payload = '';
        $payload .= pack('C', $encryption);
        for ($i = 0; $i < strlen($key) && $i < 50; $i++) {
            $payload .= pack('c', ord($key[$i]));
        }
        for ($i = strlen($key); $i < 50; $i++) {
            $payload .= pack('c', 0);
        }
        $payload .= pack('C', $key_index);
        $payload .= pack('C', $eap_options);
        $payload .= pack('v', $ca_certificate_length);
        $payload .= pack('v', $client_certificate_length);
        $payload .= pack('v', $private_key_length);

        $this->sendRequest(self::FUNCTION_SET_WIFI_ENCRYPTION, $payload);
    }

    /**
     * Returns the encryption as set by BrickMaster::setWifiEncryption().
     * 
     * .. versionadded:: 1.3.0~(Firmware)
     * 
     * 
     * @return array
     */
    public function getWifiEncryption()
    {
        $result = array();

        $payload = '';

        $data = $this->sendRequest(self::FUNCTION_GET_WIFI_ENCRYPTION, $payload);

        $payload = unpack('C1encryption/c50key/C1key_index/C1eap_options/v1ca_certificate_length/v1client_certificate_length/v1private_key_length', $data);

        $result['encryption'] = $payload['encryption'];
        $result['key'] = IPConnection::implodeUnpackedString($payload, 'key', 50);
        $result['key_index'] = $payload['key_index'];
        $result['eap_options'] = $payload['eap_options'];
        $result['ca_certificate_length'] = $payload['ca_certificate_length'];
        $result['client_certificate_length'] = $payload['client_certificate_length'];
        $result['private_key_length'] = $payload['private_key_length'];

        return $result;
    }

    /**
     * Returns the status of the WIFI Extension. The ``state`` is updated automatically,
     * all of the other parameters are updated on startup and every time
     * BrickMaster::refreshWifiStatus() is called.
     * 
     * Possible states are:
     * 
     * <code>
     *  "State", "Description"
     * 
     *  "0", "Disassociated"
     *  "1", "Associated"
     *  "2", "Associating"
     *  "3", "Error"
     *  "255", "Not initialized yet"
     * </code>
     * 
     * .. versionadded:: 1.3.0~(Firmware)
     * 
     * 
     * @return array
     */
    public function getWifiStatus()
    {
        $result = array();

        $payload = '';

        $data = $this->sendRequest(self::FUNCTION_GET_WIFI_STATUS, $payload);

        $payload = unpack('C6mac_address/C6bssid/C1channel/v1rssi/C4ip/C4subnet_mask/C4gateway/V1rx_count/V1tx_count/C1state', $data);

        $result['mac_address'] = IPConnection::collectUnpackedArray($payload, 'mac_address', 6);
        $result['bssid'] = IPConnection::collectUnpackedArray($payload, 'bssid', 6);
        $result['channel'] = $payload['channel'];
        $result['rssi'] = IPConnection::fixUnpackedInt16($payload['rssi']);
        $result['ip'] = IPConnection::collectUnpackedArray($payload, 'ip', 4);
        $result['subnet_mask'] = IPConnection::collectUnpackedArray($payload, 'subnet_mask', 4);
        $result['gateway'] = IPConnection::collectUnpackedArray($payload, 'gateway', 4);
        $result['rx_count'] = IPConnection::fixUnpackedUInt32($payload['rx_count']);
        $result['tx_count'] = IPConnection::fixUnpackedUInt32($payload['tx_count']);
        $result['state'] = $payload['state'];

        return $result;
    }

    /**
     * Refreshes the WIFI status (see BrickMaster::getWifiStatus()). To read the status
     * of the WIFI module, the Master Brick has to change from data mode to
     * command mode and back. This transaction and the readout itself is
     * unfortunately time consuming. This means, that it might take some ms
     * until the stack with attached WIFI Extension reacts again after this
     * function is called.
     * 
     * .. versionadded:: 1.3.0~(Firmware)
     * 
     * 
     * @return void
     */
    public function refreshWifiStatus()
    {
        $payload = '';

        $this->sendRequest(self::FUNCTION_REFRESH_WIFI_STATUS, $payload);
    }

    /**
     * This function is used to set the certificate as well as password and username
     * for WPA Enterprise. To set the username use index 0xFFFF,
     * to set the password use index 0xFFFE. The max length of username and 
     * password is 32.
     * 
     * The certificate is written in chunks of size 32 and the index is used as
     * the index of the chunk. ``data_length`` should nearly always be 32. Only
     * the last chunk can have a length that is not equal to 32.
     * 
     * The starting index of the CA Certificate is 0, of the Client Certificate
     * 10000 and for the Private Key 20000. Maximum sizes are 1312, 1312 and
     * 4320 byte respectively.
     * 
     * The values are stored in the EEPROM and only applied on startup. That means
     * you have to restart the Master Brick after uploading the certificate.
     * 
     * It is recommended to use the Brick Viewer to set the certificate, username
     * and password.
     * 
     * .. versionadded:: 1.3.0~(Firmware)
     * 
     * @param int $index
     * @param int[] $data
     * @param int $data_length
     * 
     * @return void
     */
    public function setWifiCertificate($index, $data, $data_length)
    {
        $payload = '';
        $payload .= pack('v', $index);
        for ($i = 0; $i < 32; $i++) {
            $payload .= pack('C', $data[$i]);
        }
        $payload .= pack('C', $data_length);

        $this->sendRequest(self::FUNCTION_SET_WIFI_CERTIFICATE, $payload);
    }

    /**
     * Returns the certificate for a given index as set by BrickMaster::setWifiCertificate().
     * 
     * .. versionadded:: 1.3.0~(Firmware)
     * 
     * @param int $index
     * 
     * @return array
     */
    public function getWifiCertificate($index)
    {
        $result = array();

        $payload = '';
        $payload .= pack('v', $index);

        $data = $this->sendRequest(self::FUNCTION_GET_WIFI_CERTIFICATE, $payload);

        $payload = unpack('C32data/C1data_length', $data);

        $result['data'] = IPConnection::collectUnpackedArray($payload, 'data', 32);
        $result['data_length'] = $payload['data_length'];

        return $result;
    }

    /**
     * Sets the power mode of the WIFI Extension. Possible modes are:
     * 
     * <code>
     *  "Mode", "Description"
     * 
     *  "0", "Full Speed (high power consumption, high throughput)"
     *  "1", "Low Power (low power consumption, low throughput)"
     * </code>
     * 
     * The default value is 0 (Full Speed).
     * 
     * .. versionadded:: 1.3.0~(Firmware)
     * 
     * @param int $mode
     * 
     * @return void
     */
    public function setWifiPowerMode($mode)
    {
        $payload = '';
        $payload .= pack('C', $mode);

        $this->sendRequest(self::FUNCTION_SET_WIFI_POWER_MODE, $payload);
    }

    /**
     * Returns the power mode as set by BrickMaster::setWifiPowerMode().
     * 
     * .. versionadded:: 1.3.0~(Firmware)
     * 
     * 
     * @return int
     */
    public function getWifiPowerMode()
    {
        $payload = '';

        $data = $this->sendRequest(self::FUNCTION_GET_WIFI_POWER_MODE, $payload);

        $payload = unpack('C1mode', $data);

        return $payload['mode'];
    }

    /**
     * Returns informations about the WIFI receive buffer. The WIFI
     * receive buffer has a max size of 1500 byte and if data is transfered
     * too fast, it might overflow.
     * 
     * The return values are the number of overflows, the low watermark 
     * (i.e. the smallest number of bytes that were free in the buffer) and
     * the bytes that are currently used.
     * 
     * You should always try to keep the buffer empty, otherwise you will
     * have a permanent latency. A good rule of thumb is, that you can transfer
     * 1000 messages per second without problems.
     * 
     * Try to not send more then 50 messages at a time without any kind of
     * break between them.
     * 
     * .. versionadded:: 1.3.2~(Firmware)
     * 
     * 
     * @return array
     */
    public function getWifiBufferInfo()
    {
        $result = array();

        $payload = '';

        $data = $this->sendRequest(self::FUNCTION_GET_WIFI_BUFFER_INFO, $payload);

        $payload = unpack('V1overflow/v1low_watermark/v1used', $data);

        $result['overflow'] = IPConnection::fixUnpackedUInt32($payload['overflow']);
        $result['low_watermark'] = $payload['low_watermark'];
        $result['used'] = $payload['used'];

        return $result;
    }

    /**
     * Sets the regulatory domain of the WIFI Extension. Possible domains are:
     * 
     * <code>
     *  "Domain", "Description"
     * 
     *  "0", "FCC: Channel 1-11 (N/S America, Australia, New Zealand)"
     *  "1", "ETSI: Channel 1-13 (Europe, Middle East, Africa)"
     *  "2", "TELEC: Channel 1-14 (Japan)"
     * </code>
     * 
     * The default value is 1 (ETSI).
     * 
     * .. versionadded:: 1.3.4~(Firmware)
     * 
     * @param int $domain
     * 
     * @return void
     */
    public function setWifiRegulatoryDomain($domain)
    {
        $payload = '';
        $payload .= pack('C', $domain);

        $this->sendRequest(self::FUNCTION_SET_WIFI_REGULATORY_DOMAIN, $payload);
    }

    /**
     * Returns the regulatory domain as set by BrickMaster::setWifiRegulatoryDomain().
     * 
     * .. versionadded:: 1.3.4~(Firmware)
     * 
     * 
     * @return int
     */
    public function getWifiRegulatoryDomain()
    {
        $payload = '';

        $data = $this->sendRequest(self::FUNCTION_GET_WIFI_REGULATORY_DOMAIN, $payload);

        $payload = unpack('C1domain', $data);

        return $payload['domain'];
    }

    /**
     * Returns the USB voltage in mV.
     * 
     * .. versionadded:: 1.3.5~(Firmware)
     * 
     * 
     * @return int
     */
    public function getUSBVoltage()
    {
        $payload = '';

        $data = $this->sendRequest(self::FUNCTION_GET_USB_VOLTAGE, $payload);

        $payload = unpack('v1voltage', $data);

        return $payload['voltage'];
    }

    /**
     * Sets a long WIFI key (up to 63 chars, at least 8 chars) for WPA encryption.
     * This key will be used
     * if the key in BrickMaster::setWifiEncryption() is set to "-". In the old protocol,
     * a payload of size 63 was not possible, so the maximum key length was 50 chars.
     * 
     * With the new protocol this is possible, since we didn't want to break API,
     * this function was added additionally.
     * 
     * .. versionadded:: 2.0.2~(Firmware)
     * 
     * @param string $key
     * 
     * @return void
     */
    public function setLongWifiKey($key)
    {
        $payload = '';
        for ($i = 0; $i < strlen($key) && $i < 64; $i++) {
            $payload .= pack('c', ord($key[$i]));
        }
        for ($i = strlen($key); $i < 64; $i++) {
            $payload .= pack('c', 0);
        }

        $this->sendRequest(self::FUNCTION_SET_LONG_WIFI_KEY, $payload);
    }

    /**
     * Returns the encryption key as set by BrickMaster::setLongWifiKey().
     * 
     * .. versionadded:: 2.0.2~(Firmware)
     * 
     * 
     * @return string
     */
    public function getLongWifiKey()
    {
        $payload = '';

        $data = $this->sendRequest(self::FUNCTION_GET_LONG_WIFI_KEY, $payload);

        $payload = unpack('c64key', $data);

        return IPConnection::implodeUnpackedString($payload, 'key', 64);
    }

    /**
     * Sets the hostname of the WIFI Extension. The hostname will be displayed 
     * by access points as the hostname in the DHCP clients table.
     * 
     * Setting an empty String will restore the default hostname.
     * 
     * .. versionadded:: 2.0.5~(Firmware)
     * 
     * @param string $hostname
     * 
     * @return void
     */
    public function setWifiHostname($hostname)
    {
        $payload = '';
        for ($i = 0; $i < strlen($hostname) && $i < 16; $i++) {
            $payload .= pack('c', ord($hostname[$i]));
        }
        for ($i = strlen($hostname); $i < 16; $i++) {
            $payload .= pack('c', 0);
        }

        $this->sendRequest(self::FUNCTION_SET_WIFI_HOSTNAME, $payload);
    }

    /**
     * Returns the hostname as set by BrickMaster::getWifiHostname().
     * 
     * An empty String means, that the default hostname is used.
     * 
     * .. versionadded:: 2.0.5~(Firmware)
     * 
     * 
     * @return string
     */
    public function getWifiHostname()
    {
        $payload = '';

        $data = $this->sendRequest(self::FUNCTION_GET_WIFI_HOSTNAME, $payload);

        $payload = unpack('c16hostname', $data);

        return IPConnection::implodeUnpackedString($payload, 'hostname', 16);
    }

    /**
     * Sets the period in ms with which the BrickMaster::CALLBACK_STACK_CURRENT callback is triggered
     * periodically. A value of 0 turns the callback off.
     * 
     * BrickMaster::CALLBACK_STACK_CURRENT is only triggered if the current has changed since the
     * last triggering.
     * 
     * The default value is 0.
     * 
     * .. versionadded:: 2.0.5~(Firmware)
     * 
     * @param int $period
     * 
     * @return void
     */
    public function setStackCurrentCallbackPeriod($period)
    {
        $payload = '';
        $payload .= pack('V', $period);

        $this->sendRequest(self::FUNCTION_SET_STACK_CURRENT_CALLBACK_PERIOD, $payload);
    }

    /**
     * Returns the period as set by :func:`SetCurrentCallbackPeriod`.
     * 
     * .. versionadded:: 2.0.5~(Firmware)
     * 
     * 
     * @return int
     */
    public function getStackCurrentCallbackPeriod()
    {
        $payload = '';

        $data = $this->sendRequest(self::FUNCTION_GET_STACK_CURRENT_CALLBACK_PERIOD, $payload);

        $payload = unpack('V1period', $data);

        return IPConnection::fixUnpackedUInt32($payload['period']);
    }

    /**
     * Sets the period in ms with which the BrickMaster::CALLBACK_STACK_VOLTAGE callback is triggered
     * periodically. A value of 0 turns the callback off.
     * 
     * BrickMaster::CALLBACK_STACK_VOLTAGE is only triggered if the voltage has changed since the
     * last triggering.
     * 
     * The default value is 0.
     * 
     * .. versionadded:: 2.0.5~(Firmware)
     * 
     * @param int $period
     * 
     * @return void
     */
    public function setStackVoltageCallbackPeriod($period)
    {
        $payload = '';
        $payload .= pack('V', $period);

        $this->sendRequest(self::FUNCTION_SET_STACK_VOLTAGE_CALLBACK_PERIOD, $payload);
    }

    /**
     * Returns the period as set by BrickMaster::setStackVoltageCallbackPeriod().
     * 
     * .. versionadded:: 2.0.5~(Firmware)
     * 
     * 
     * @return int
     */
    public function getStackVoltageCallbackPeriod()
    {
        $payload = '';

        $data = $this->sendRequest(self::FUNCTION_GET_STACK_VOLTAGE_CALLBACK_PERIOD, $payload);

        $payload = unpack('V1period', $data);

        return IPConnection::fixUnpackedUInt32($payload['period']);
    }

    /**
     * Sets the period in ms with which the BrickMaster::CALLBACK_USB_VOLTAGE callback is triggered
     * periodically. A value of 0 turns the callback off.
     * 
     * BrickMaster::CALLBACK_USB_VOLTAGE is only triggered if the voltage has changed since the
     * last triggering.
     * 
     * The default value is 0.
     * 
     * .. versionadded:: 2.0.5~(Firmware)
     * 
     * @param int $period
     * 
     * @return void
     */
    public function setUSBVoltageCallbackPeriod($period)
    {
        $payload = '';
        $payload .= pack('V', $period);

        $this->sendRequest(self::FUNCTION_SET_USB_VOLTAGE_CALLBACK_PERIOD, $payload);
    }

    /**
     * Returns the period as set by BrickMaster::setUSBVoltageCallbackPeriod().
     * 
     * .. versionadded:: 2.0.5~(Firmware)
     * 
     * 
     * @return int
     */
    public function getUSBVoltageCallbackPeriod()
    {
        $payload = '';

        $data = $this->sendRequest(self::FUNCTION_GET_USB_VOLTAGE_CALLBACK_PERIOD, $payload);

        $payload = unpack('V1period', $data);

        return IPConnection::fixUnpackedUInt32($payload['period']);
    }

    /**
     * Sets the thresholds for the BrickMaster::CALLBACK_STACK_CURRENT_REACHED callback. 
     * 
     * The following options are possible:
     * 
     * <code>
     *  "Option", "Description"
     * 
     *  "'x'",    "Callback is turned off"
     *  "'o'",    "Callback is triggered when the current is *outside* the min and max values"
     *  "'i'",    "Callback is triggered when the current is *inside* the min and max values"
     *  "'<'",    "Callback is triggered when the current is smaller than the min value (max is ignored)"
     *  "'>'",    "Callback is triggered when the current is greater than the min value (max is ignored)"
     * </code>
     * 
     * The default value is ('x', 0, 0).
     * 
     * .. versionadded:: 2.0.5~(Firmware)
     * 
     * @param string $option
     * @param int $min
     * @param int $max
     * 
     * @return void
     */
    public function setStackCurrentCallbackThreshold($option, $min, $max)
    {
        $payload = '';
        $payload .= pack('c', ord($option));
        $payload .= pack('v', $min);
        $payload .= pack('v', $max);

        $this->sendRequest(self::FUNCTION_SET_STACK_CURRENT_CALLBACK_THRESHOLD, $payload);
    }

    /**
     * Returns the threshold as set by BrickMaster::setStackCurrentCallbackThreshold().
     * 
     * .. versionadded:: 2.0.5~(Firmware)
     * 
     * 
     * @return array
     */
    public function getStackCurrentCallbackThreshold()
    {
        $result = array();

        $payload = '';

        $data = $this->sendRequest(self::FUNCTION_GET_STACK_CURRENT_CALLBACK_THRESHOLD, $payload);

        $payload = unpack('c1option/v1min/v1max', $data);

        $result['option'] = chr($payload['option']);
        $result['min'] = $payload['min'];
        $result['max'] = $payload['max'];

        return $result;
    }

    /**
     * Sets the thresholds for the :func:`StackStackVoltageReached` callback. 
     * 
     * The following options are possible:
     * 
     * <code>
     *  "Option", "Description"
     * 
     *  "'x'",    "Callback is turned off"
     *  "'o'",    "Callback is triggered when the voltage is *outside* the min and max values"
     *  "'i'",    "Callback is triggered when the voltage is *inside* the min and max values"
     *  "'<'",    "Callback is triggered when the voltage is smaller than the min value (max is ignored)"
     *  "'>'",    "Callback is triggered when the voltage is greater than the min value (max is ignored)"
     * </code>
     * 
     * The default value is ('x', 0, 0).
     * 
     * .. versionadded:: 2.0.5~(Firmware)
     * 
     * @param string $option
     * @param int $min
     * @param int $max
     * 
     * @return void
     */
    public function setStackVoltageCallbackThreshold($option, $min, $max)
    {
        $payload = '';
        $payload .= pack('c', ord($option));
        $payload .= pack('v', $min);
        $payload .= pack('v', $max);

        $this->sendRequest(self::FUNCTION_SET_STACK_VOLTAGE_CALLBACK_THRESHOLD, $payload);
    }

    /**
     * Returns the threshold as set by BrickMaster::setStackVoltageCallbackThreshold().
     * 
     * .. versionadded:: 2.0.5~(Firmware)
     * 
     * 
     * @return array
     */
    public function getStackVoltageCallbackThreshold()
    {
        $result = array();

        $payload = '';

        $data = $this->sendRequest(self::FUNCTION_GET_STACK_VOLTAGE_CALLBACK_THRESHOLD, $payload);

        $payload = unpack('c1option/v1min/v1max', $data);

        $result['option'] = chr($payload['option']);
        $result['min'] = $payload['min'];
        $result['max'] = $payload['max'];

        return $result;
    }

    /**
     * Sets the thresholds for the BrickMaster::CALLBACK_USB_VOLTAGE_REACHED callback. 
     * 
     * The following options are possible:
     * 
     * <code>
     *  "Option", "Description"
     * 
     *  "'x'",    "Callback is turned off"
     *  "'o'",    "Callback is triggered when the voltage is *outside* the min and max values"
     *  "'i'",    "Callback is triggered when the voltage is *inside* the min and max values"
     *  "'<'",    "Callback is triggered when the voltage is smaller than the min value (max is ignored)"
     *  "'>'",    "Callback is triggered when the voltage is greater than the min value (max is ignored)"
     * </code>
     * 
     * The default value is ('x', 0, 0).
     * 
     * .. versionadded:: 2.0.5~(Firmware)
     * 
     * @param string $option
     * @param int $min
     * @param int $max
     * 
     * @return void
     */
    public function setUSBVoltageCallbackThreshold($option, $min, $max)
    {
        $payload = '';
        $payload .= pack('c', ord($option));
        $payload .= pack('v', $min);
        $payload .= pack('v', $max);

        $this->sendRequest(self::FUNCTION_SET_USB_VOLTAGE_CALLBACK_THRESHOLD, $payload);
    }

    /**
     * Returns the threshold as set by BrickMaster::setUSBVoltageCallbackThreshold().
     * 
     * .. versionadded:: 2.0.5~(Firmware)
     * 
     * 
     * @return array
     */
    public function getUSBVoltageCallbackThreshold()
    {
        $result = array();

        $payload = '';

        $data = $this->sendRequest(self::FUNCTION_GET_USB_VOLTAGE_CALLBACK_THRESHOLD, $payload);

        $payload = unpack('c1option/v1min/v1max', $data);

        $result['option'] = chr($payload['option']);
        $result['min'] = $payload['min'];
        $result['max'] = $payload['max'];

        return $result;
    }

    /**
     * Sets the period in ms with which the threshold callbacks
     * 
     * * BrickMaster::CALLBACK_STACK_CURRENT_REACHED,
     * * BrickMaster::CALLBACK_STACK_VOLTAGE_REACHED,
     * * BrickMaster::CALLBACK_USB_VOLTAGE_REACHED
     * 
     * are triggered, if the thresholds
     * 
     * * BrickMaster::setStackCurrentCallbackThreshold(),
     * * BrickMaster::setStackVoltageCallbackThreshold(),
     * * BrickMaster::setUSBVoltageCallbackThreshold()
     * 
     * keep being reached.
     * 
     * The default value is 100.
     * 
     * .. versionadded:: 2.0.5~(Firmware)
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
     * Returns the debounce period as set by BrickMaster::setDebouncePeriod().
     * 
     * .. versionadded:: 2.0.5~(Firmware)
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
     * Returns *true* if a Ethernet Extension is available to be used by the Master
     * Brick.
     * 
     * .. versionadded:: 2.1.0~(Firmware)
     * 
     * 
     * @return bool
     */
    public function isEthernetPresent()
    {
        $payload = '';

        $data = $this->sendRequest(self::FUNCTION_IS_ETHERNET_PRESENT, $payload);

        $payload = unpack('C1present', $data);

        return (bool)$payload['present'];
    }

    /**
     * Sets the configuration of the Ethernet Extension. Possible values for
     * ``connection`` are:
     * 
     * <code>
     *  "Value", "Description"
     * 
     *  "0", "DHCP"
     *  "1", "Static IP"
     * </code>
     * 
     * If you set ``connection`` to static IP options then you have to supply ``ip``,
     * ``subnet_mask`` and ``gateway`` as an array of size 4 (first element of the
     * array is the least significant byte of the address). If ``connection`` is set
     * to the DHCP options then ``ip``, ``subnet_mask`` and ``gateway`` are ignored,
     * you can set them to 0.
     * 
     * The last parameter is the port that your program will connect to. The
     * default port, that is used by brickd, is 4223.
     * 
     * The values are stored in the EEPROM and only applied on startup. That means
     * you have to restart the Master Brick after configuration.
     * 
     * It is recommended to use the Brick Viewer to set the Ethernet configuration.
     * 
     * .. versionadded:: 2.1.0~(Firmware)
     * 
     * @param int $connection
     * @param int[] $ip
     * @param int[] $subnet_mask
     * @param int[] $gateway
     * @param int $port
     * 
     * @return void
     */
    public function setEthernetConfiguration($connection, $ip, $subnet_mask, $gateway, $port)
    {
        $payload = '';
        $payload .= pack('C', $connection);
        for ($i = 0; $i < 4; $i++) {
            $payload .= pack('C', $ip[$i]);
        }
        for ($i = 0; $i < 4; $i++) {
            $payload .= pack('C', $subnet_mask[$i]);
        }
        for ($i = 0; $i < 4; $i++) {
            $payload .= pack('C', $gateway[$i]);
        }
        $payload .= pack('v', $port);

        $this->sendRequest(self::FUNCTION_SET_ETHERNET_CONFIGURATION, $payload);
    }

    /**
     * Returns the configuration as set by BrickMaster::setEthernetConfiguration().
     * 
     * .. versionadded:: 2.1.0~(Firmware)
     * 
     * 
     * @return array
     */
    public function getEthernetConfiguration()
    {
        $result = array();

        $payload = '';

        $data = $this->sendRequest(self::FUNCTION_GET_ETHERNET_CONFIGURATION, $payload);

        $payload = unpack('C1connection/C4ip/C4subnet_mask/C4gateway/v1port', $data);

        $result['connection'] = $payload['connection'];
        $result['ip'] = IPConnection::collectUnpackedArray($payload, 'ip', 4);
        $result['subnet_mask'] = IPConnection::collectUnpackedArray($payload, 'subnet_mask', 4);
        $result['gateway'] = IPConnection::collectUnpackedArray($payload, 'gateway', 4);
        $result['port'] = $payload['port'];

        return $result;
    }

    /**
     * Returns the status of the Ethernet Extension.
     * 
     * ``mac_address``, ``ip``, ``subnet_mask`` and ``gateway`` are given as an array.
     * The first element of the array is the least significant byte of the address.
     * 
     * ``rx_count`` and ``tx_count`` are the number of bytes that have been
     * received/send since last restart.
     * 
     * ``hostname`` is the currently used hostname.
     * 
     * .. versionadded:: 2.1.0~(Firmware)
     * 
     * 
     * @return array
     */
    public function getEthernetStatus()
    {
        $result = array();

        $payload = '';

        $data = $this->sendRequest(self::FUNCTION_GET_ETHERNET_STATUS, $payload);

        $payload = unpack('C6mac_address/C4ip/C4subnet_mask/C4gateway/V1rx_count/V1tx_count/c32hostname', $data);

        $result['mac_address'] = IPConnection::collectUnpackedArray($payload, 'mac_address', 6);
        $result['ip'] = IPConnection::collectUnpackedArray($payload, 'ip', 4);
        $result['subnet_mask'] = IPConnection::collectUnpackedArray($payload, 'subnet_mask', 4);
        $result['gateway'] = IPConnection::collectUnpackedArray($payload, 'gateway', 4);
        $result['rx_count'] = IPConnection::fixUnpackedUInt32($payload['rx_count']);
        $result['tx_count'] = IPConnection::fixUnpackedUInt32($payload['tx_count']);
        $result['hostname'] = IPConnection::implodeUnpackedString($payload, 'hostname', 32);

        return $result;
    }

    /**
     * Sets the hostname of the Ethernet Extension. The hostname will be displayed 
     * by access points as the hostname in the DHCP clients table.
     * 
     * Setting an empty String will restore the default hostname.
     * 
     * The current hostname can be discovered with BrickMaster::getEthernetStatus().
     * 
     * .. versionadded:: 2.1.0~(Firmware)
     * 
     * @param string $hostname
     * 
     * @return void
     */
    public function setEthernetHostname($hostname)
    {
        $payload = '';
        for ($i = 0; $i < strlen($hostname) && $i < 32; $i++) {
            $payload .= pack('c', ord($hostname[$i]));
        }
        for ($i = strlen($hostname); $i < 32; $i++) {
            $payload .= pack('c', 0);
        }

        $this->sendRequest(self::FUNCTION_SET_ETHERNET_HOSTNAME, $payload);
    }

    /**
     * Sets the MAC address of the Ethernet Extension. The Ethernet Extension should
     * come configured with a valid MAC address, that is also written on a
     * sticker of the extension itself.
     * 
     * The MAC address can be read out again with BrickMaster::getEthernetStatus().
     * 
     * .. versionadded:: 2.1.0~(Firmware)
     * 
     * @param int[] $mac_address
     * 
     * @return void
     */
    public function setEthernetMACAddress($mac_address)
    {
        $payload = '';
        for ($i = 0; $i < 6; $i++) {
            $payload .= pack('C', $mac_address[$i]);
        }

        $this->sendRequest(self::FUNCTION_SET_ETHERNET_MAC_ADDRESS, $payload);
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
     * .. versionadded:: 1.2.1~(Firmware)
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
     * .. versionadded:: 1.2.1~(Firmware)
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
    public function callbackWrapperStackCurrent($data)
    {
        $result = array();
        $payload = unpack('v1current', $data);

        array_push($result, $payload['current']);

        call_user_func_array($this->registeredCallbacks[self::CALLBACK_STACK_CURRENT], $result);
    }

    /**
     * @internal
     * @param string $data
     */
    public function callbackWrapperStackVoltage($data)
    {
        $result = array();
        $payload = unpack('v1voltage', $data);

        array_push($result, $payload['voltage']);

        call_user_func_array($this->registeredCallbacks[self::CALLBACK_STACK_VOLTAGE], $result);
    }

    /**
     * @internal
     * @param string $data
     */
    public function callbackWrapperUSBVoltage($data)
    {
        $result = array();
        $payload = unpack('v1voltage', $data);

        array_push($result, $payload['voltage']);

        call_user_func_array($this->registeredCallbacks[self::CALLBACK_USB_VOLTAGE], $result);
    }

    /**
     * @internal
     * @param string $data
     */
    public function callbackWrapperStackCurrentReached($data)
    {
        $result = array();
        $payload = unpack('v1current', $data);

        array_push($result, $payload['current']);

        call_user_func_array($this->registeredCallbacks[self::CALLBACK_STACK_CURRENT_REACHED], $result);
    }

    /**
     * @internal
     * @param string $data
     */
    public function callbackWrapperStackVoltageReached($data)
    {
        $result = array();
        $payload = unpack('v1voltage', $data);

        array_push($result, $payload['voltage']);

        call_user_func_array($this->registeredCallbacks[self::CALLBACK_STACK_VOLTAGE_REACHED], $result);
    }

    /**
     * @internal
     * @param string $data
     */
    public function callbackWrapperUSBVoltageReached($data)
    {
        $result = array();
        $payload = unpack('v1voltage', $data);

        array_push($result, $payload['voltage']);

        call_user_func_array($this->registeredCallbacks[self::CALLBACK_USB_VOLTAGE_REACHED], $result);
    }
}

?>
