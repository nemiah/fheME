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
 * Device for controlling Stacks and four Bricklets
 */
class BrickMaster extends Device
{


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

        $this->expectedName = 'Master Brick';

        $this->bindingVersion = array(1, 3, 2);

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

        $data = $this->sendRequestExpectResponse(self::FUNCTION_GET_STACK_VOLTAGE, $payload, 2);

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

        $data = $this->sendRequestExpectResponse(self::FUNCTION_GET_STACK_CURRENT, $payload, 2);

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
     * </code>
     * 
     * The extension type is already set when bought and it can be set with the 
     * Brick Viewer, it is unlikely that you need this function.
     * 
     * The value will be saved in the EEPROM of the Chibi Extension, it does not
     * have to be set on every startup.
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

        $this->sendRequestNoResponse(self::FUNCTION_SET_EXTENSION_TYPE, $payload);
    }

    /**
     * Returns the extension type for a given extension as set by 
     * BrickMaster::setExtensionType().
     * 
     * @param int $extension
     * 
     * @return int
     */
    public function getExtensionType($extension)
    {
        $payload = '';
        $payload .= pack('C', $extension);

        $data = $this->sendRequestExpectResponse(self::FUNCTION_GET_EXTENSION_TYPE, $payload, 4);

        $payload = unpack('V1exttype', $data);

        return IPConnection::fixUnpackedUInt32($payload['exttype']);
    }

    /**
     * Returns *true* if a Chibi Extension is available to be used by the Master.
     * 
     * .. versionadded:: 1.1.0
     * 
     * 
     * @return bool
     */
    public function isChibiPresent()
    {
        $payload = '';

        $data = $this->sendRequestExpectResponse(self::FUNCTION_IS_CHIBI_PRESENT, $payload, 1);

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
     * .. versionadded:: 1.1.0
     * 
     * @param int $address
     * 
     * @return void
     */
    public function setChibiAddress($address)
    {
        $payload = '';
        $payload .= pack('C', $address);

        $this->sendRequestNoResponse(self::FUNCTION_SET_CHIBI_ADDRESS, $payload);
    }

    /**
     * Returns the address as set by BrickMaster::setChibiAddress().
     * 
     * .. versionadded:: 1.1.0
     * 
     * 
     * @return int
     */
    public function getChibiAddress()
    {
        $payload = '';

        $data = $this->sendRequestExpectResponse(self::FUNCTION_GET_CHIBI_ADDRESS, $payload, 1);

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
     * .. versionadded:: 1.1.0
     * 
     * @param int $address
     * 
     * @return void
     */
    public function setChibiMasterAddress($address)
    {
        $payload = '';
        $payload .= pack('C', $address);

        $this->sendRequestNoResponse(self::FUNCTION_SET_CHIBI_MASTER_ADDRESS, $payload);
    }

    /**
     * Returns the address as set by BrickMaster::setChibiMasterAddress().
     * 
     * .. versionadded:: 1.1.0
     * 
     * 
     * @return int
     */
    public function getChibiMasterAddress()
    {
        $payload = '';

        $data = $this->sendRequestExpectResponse(self::FUNCTION_GET_CHIBI_MASTER_ADDRESS, $payload, 1);

        $payload = unpack('C1address', $data);

        return $payload['address'];
    }

    /**
     * Sets up to 254 slave addresses. Valid addresses are in range 1-255.
     * The address numeration (via num parameter) has to be used
     * ascending from 0. For example: If you use the Chibi Extension in Master mode
     * (i.e. the stack has an USB connection) and you want to talk to three other
     * Chibi stacks with the slave addresses 17, 23, and 42, you should call with "(0, 17),
     * (1, 23) and (2, 42)".
     * 
     * It is possible to set the addresses with the Brick Viewer and it will be 
     * saved in the EEPROM of the Chibi Extension, they don't
     * have to be set on every startup.
     * 
     * .. versionadded:: 1.1.0
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

        $this->sendRequestNoResponse(self::FUNCTION_SET_CHIBI_SLAVE_ADDRESS, $payload);
    }

    /**
     * Returns the slave address for a given num as set by 
     * BrickMaster::setChibiSlaveAddress().
     * 
     * .. versionadded:: 1.1.0
     * 
     * @param int $num
     * 
     * @return int
     */
    public function getChibiSlaveAddress($num)
    {
        $payload = '';
        $payload .= pack('C', $num);

        $data = $this->sendRequestExpectResponse(self::FUNCTION_GET_CHIBI_SLAVE_ADDRESS, $payload, 1);

        $payload = unpack('C1address', $data);

        return $payload['address'];
    }

    /**
     * Returns the signal strength in dBm. The signal strength updates every time a
     * packet is received.
     * 
     * .. versionadded:: 1.1.0
     * 
     * 
     * @return int
     */
    public function getChibiSignalStrength()
    {
        $payload = '';

        $data = $this->sendRequestExpectResponse(self::FUNCTION_GET_CHIBI_SIGNAL_STRENGTH, $payload, 1);

        $payload = unpack('C1signal_strength', $data);

        return $payload['signal_strength'];
    }

    /**
     * Returns underrun, CRC error, no ACK and overflow error counts of the Chibi
     * communication. If these errors start rising, it is likely that either the
     * distance between two Chibi stacks is becoming too big or there are
     * interferences.
     * 
     * .. versionadded:: 1.1.0
     * 
     * 
     * @return array
     */
    public function getChibiErrorLog()
    {
        $result = array();

        $payload = '';

        $data = $this->sendRequestExpectResponse(self::FUNCTION_GET_CHIBI_ERROR_LOG, $payload, 8);

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
     *  "0",    "OQPSK 868Mhz (Europe)"
     *  "1",    "OQPSK 915Mhz (US)"
     *  "2",    "OQPSK 780Mhz (China)"
     *  "3",    "BPSK40 915Mhz"
     * </code>
     * 
     * It is possible to set the frequency with the Brick Viewer and it will be 
     * saved in the EEPROM of the Chibi Extension, it does not
     * have to be set on every startup.
     * 
     * .. versionadded:: 1.1.0
     * 
     * @param int $frequency
     * 
     * @return void
     */
    public function setChibiFrequency($frequency)
    {
        $payload = '';
        $payload .= pack('C', $frequency);

        $this->sendRequestNoResponse(self::FUNCTION_SET_CHIBI_FREQUENCY, $payload);
    }

    /**
     * Returns the frequency value as set by BrickMaster::setChibiFrequency().
     * 
     * .. versionadded:: 1.1.0
     * 
     * 
     * @return int
     */
    public function getChibiFrequency()
    {
        $payload = '';

        $data = $this->sendRequestExpectResponse(self::FUNCTION_GET_CHIBI_FREQUENCY, $payload, 1);

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
     *  "OQPSK 868Mhz (Europe)", "0"
     *  "OQPSK 915Mhz (US)",     "1, 2, 3, 4, 5, 6, 7, 8, 9, 10"
     *  "OQPSK 780Mhz (China)",  "0, 1, 2, 3"
     *  "BPSK40 915Mhz",         "1, 2, 3, 4, 5, 6, 7, 8, 9, 10"
     * </code>
     * 
     * It is possible to set the channel with the Brick Viewer and it will be 
     * saved in the EEPROM of the Chibi Extension, it does not
     * have to be set on every startup.
     * 
     * .. versionadded:: 1.1.0
     * 
     * @param int $channel
     * 
     * @return void
     */
    public function setChibiChannel($channel)
    {
        $payload = '';
        $payload .= pack('C', $channel);

        $this->sendRequestNoResponse(self::FUNCTION_SET_CHIBI_CHANNEL, $payload);
    }

    /**
     * Returns the channel as set by BrickMaster::setChibiChannel().
     * 
     * .. versionadded:: 1.1.0
     * 
     * 
     * @return int
     */
    public function getChibiChannel()
    {
        $payload = '';

        $data = $this->sendRequestExpectResponse(self::FUNCTION_GET_CHIBI_CHANNEL, $payload, 1);

        $payload = unpack('C1channel', $data);

        return $payload['channel'];
    }

    /**
     * Returns *true* if a RS485 Extension is available to be used by the Master.
     * 
     * .. versionadded:: 1.2.0
     * 
     * 
     * @return bool
     */
    public function isRS485Present()
    {
        $payload = '';

        $data = $this->sendRequestExpectResponse(self::FUNCTION_IS_RS485_PRESENT, $payload, 1);

        $payload = unpack('C1present', $data);

        return (bool)$payload['present'];
    }

    /**
     * Sets the address (1-255) belonging to the RS485 Extension.
     * 
     * Set to 0 if the RS485 Extension should be the RS485 Master (i.e.
     * connected to a PC via USB).
     * 
     * It is possible to set the address with the Brick Viewer and it will be 
     * saved in the EEPROM of the RS485 Extension, it does not
     * have to be set on every startup.
     * 
     * .. versionadded:: 1.2.0
     * 
     * @param int $address
     * 
     * @return void
     */
    public function setRS485Address($address)
    {
        $payload = '';
        $payload .= pack('C', $address);

        $this->sendRequestNoResponse(self::FUNCTION_SET_RS485_ADDRESS, $payload);
    }

    /**
     * Returns the address as set by BrickMaster::setRS485Address().
     * 
     * .. versionadded:: 1.2.0
     * 
     * 
     * @return int
     */
    public function getRS485Address()
    {
        $payload = '';

        $data = $this->sendRequestExpectResponse(self::FUNCTION_GET_RS485_ADDRESS, $payload, 1);

        $payload = unpack('C1address', $data);

        return $payload['address'];
    }

    /**
     * Sets up to 255 slave addresses. Valid addresses are in range 1-255.
     * The address numeration (via num parameter) has to be used
     * ascending from 0. For example: If you use the RS485 Extension in Master mode
     * (i.e. the stack has an USB connection) and you want to talk to three other
     * RS485 stacks with the IDs 17, 23, and 42, you should call with "(0, 17),
     * (1, 23) and (2, 42)".
     * 
     * It is possible to set the addresses with the Brick Viewer and it will be 
     * saved in the EEPROM of the RS485 Extension, they don't
     * have to be set on every startup.
     * 
     * .. versionadded:: 1.2.0
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

        $this->sendRequestNoResponse(self::FUNCTION_SET_RS485_SLAVE_ADDRESS, $payload);
    }

    /**
     * Returns the slave address for a given num as set by 
     * BrickMaster::setRS485SlaveAddress().
     * 
     * .. versionadded:: 1.2.0
     * 
     * @param int $num
     * 
     * @return int
     */
    public function getRS485SlaveAddress($num)
    {
        $payload = '';
        $payload .= pack('C', $num);

        $data = $this->sendRequestExpectResponse(self::FUNCTION_GET_RS485_SLAVE_ADDRESS, $payload, 1);

        $payload = unpack('C1address', $data);

        return $payload['address'];
    }

    /**
     * Returns CRC error counts of the RS485 communication.
     * If this counter starts rising, it is likely that the distance
     * between the RS485 nodes is too big or there is some kind of
     * interference.
     * 
     * .. versionadded:: 1.2.0
     * 
     * 
     * @return int
     */
    public function getRS485ErrorLog()
    {
        $payload = '';

        $data = $this->sendRequestExpectResponse(self::FUNCTION_GET_RS485_ERROR_LOG, $payload, 2);

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
     * If your RS485 is unstable (lost messages etc), the first thing you should
     * try is to decrease the speed. On very large bus (e.g. 1km), you probably
     * should use a value in the range of 100khz.
     * 
     * The values are stored in the EEPROM and only applied on startup. That means
     * you have to restart the Master Brick after configuration.
     * 
     * .. versionadded:: 1.2.0
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

        $this->sendRequestNoResponse(self::FUNCTION_SET_RS485_CONFIGURATION, $payload);
    }

    /**
     * Returns the configuration as set by BrickMaster::setRS485Configuration().
     * 
     * .. versionadded:: 1.2.0
     * 
     * 
     * @return array
     */
    public function getRS485Configuration()
    {
        $result = array();

        $payload = '';

        $data = $this->sendRequestExpectResponse(self::FUNCTION_GET_RS485_CONFIGURATION, $payload, 6);

        $payload = unpack('V1speed/c1parity/C1stopbits', $data);

        $result['speed'] = IPConnection::fixUnpackedUInt32($payload['speed']);
        $result['parity'] = chr($payload['parity']);
        $result['stopbits'] = $payload['stopbits'];

        return $result;
    }

    /**
     * Returns *true* if a WIFI Extension is available to be used by the Master.
     * 
     * .. versionadded:: 1.2.0
     * 
     * 
     * @return bool
     */
    public function isWifiPresent()
    {
        $payload = '';

        $data = $this->sendRequestExpectResponse(self::FUNCTION_IS_WIFI_PRESENT, $payload, 1);

        $payload = unpack('C1present', $data);

        return (bool)$payload['present'];
    }

    /**
     * Sets the configuration of the WIFI Extension. The ssid can have a max length
     * of 32 characters, the connection is either 0 for DHCP or 1 for static IP.
     * 
     * If you set connection to 1, you have to supply ip, subnet mask and gateway
     * as an array of size 4 (first element of the array is the least significant
     * byte of the address). If connection is set to 0 ip, subnet mask and gateway
     * are ignored, you can set them to 0.
     * 
     * The last parameter is the port that your program will connect to. The
     * default port, that is used by brickd, is 4223.
     * 
     * The values are stored in the EEPROM and only applied on startup. That means
     * you have to restart the Master Brick after configuration.
     * 
     * It is recommended to use the Brick Viewer to set the WIFI configuration.
     * 
     * .. versionadded:: 1.3.0
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

        $this->sendRequestNoResponse(self::FUNCTION_SET_WIFI_CONFIGURATION, $payload);
    }

    /**
     * Returns the configuration as set by BrickMaster::setWifiConfiguration().
     * 
     * .. versionadded:: 1.3.0
     * 
     * 
     * @return array
     */
    public function getWifiConfiguration()
    {
        $result = array();

        $payload = '';

        $data = $this->sendRequestExpectResponse(self::FUNCTION_GET_WIFI_CONFIGURATION, $payload, 47);

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
     *  "3", "Open Network"
     * </code>
     * 
     * The key has a max length of 50 characters and is used if encryption
     * is set to 0 or 2 (WPA or WEP). Otherwise the value is ignored.
     * For WEP it is possible to set the key index (1-4). If you don't know your
     * key index, it is likely 1.
     * 
     * If you choose WPA Enterprise as encryption, you have to set eap options and
     * the length of the certificates (for other encryption types these paramters
     * are ignored). The certificate length are given in byte and the certificates
     * themself can be set with  BrickMaster::setWifiCertificate(). Eap options consist of 
     * the outer authentication (bits 1-2), inner authentication (bit 3) and 
     * certificate type (bits 4-5):
     * 
     * <code>
     *  "Option", "Bits", "Description"
     * 
     *  "outer auth", "1-2", "0=EAP-FAST, 1=EAP-TLS, 2=EAP-TTLS, 3=EAP-PEAP"
     *  "inner auth", "3", "0=EAP-MSCHAP, 1=EAP-GTC"
     *  "cert type", "4-5", "0=CA Certificate, 1=Client Certificate, 2=Private Key"
     * </code>
     * 
     * Example for EAP-TTLS + EAP-GTC + Private Key: option = 2 | (1 << 2) | (2 << 3).
     * 
     * The values are stored in the EEPROM and only applied on startup. That means
     * you have to restart the Master Brick after configuration.
     * 
     * It is recommended to use the Brick Viewer to set the WIFI encryption.
     * 
     * .. versionadded:: 1.3.0
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

        $this->sendRequestNoResponse(self::FUNCTION_SET_WIFI_ENCRYPTION, $payload);
    }

    /**
     * Returns the encryption as set by BrickMaster::setWifiEncryption().
     * 
     * .. versionadded:: 1.3.0
     * 
     * 
     * @return array
     */
    public function getWifiEncryption()
    {
        $result = array();

        $payload = '';

        $data = $this->sendRequestExpectResponse(self::FUNCTION_GET_WIFI_ENCRYPTION, $payload, 59);

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
     * Returns the status of the WIFI Extension. The state is updated automatically,
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
     * .. versionadded:: 1.3.0
     * 
     * 
     * @return array
     */
    public function getWifiStatus()
    {
        $result = array();

        $payload = '';

        $data = $this->sendRequestExpectResponse(self::FUNCTION_GET_WIFI_STATUS, $payload, 36);

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
     * until the stack with attached WIFI Extensions reacts again after this
     * function is called.
     * 
     * .. versionadded:: 1.3.0
     * 
     * 
     * @return void
     */
    public function refreshWifiStatus()
    {
        $payload = '';

        $this->sendRequestNoResponse(self::FUNCTION_REFRESH_WIFI_STATUS, $payload);
    }

    /**
     * This function is used to set the certificate as well as password and username
     * for WPA Enterprise. To set the username use index 0xFFFF,
     * to set the password use index 0xFFFE. The max length of username and 
     * password is 32.
     * 
     * The certificate is written in chunks of size 32 and the index is used as
     * the index of the chunk. The data length should nearly always be 32. Only
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
     * .. versionadded:: 1.3.0
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

        $this->sendRequestNoResponse(self::FUNCTION_SET_WIFI_CERTIFICATE, $payload);
    }

    /**
     * Returns the certificate for a given index as set by BrickMaster::setWifiCertificate().
     * 
     * .. versionadded:: 1.3.0
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

        $data = $this->sendRequestExpectResponse(self::FUNCTION_GET_WIFI_CERTIFICATE, $payload, 33);

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
     * .. versionadded:: 1.3.0
     * 
     * @param int $mode
     * 
     * @return void
     */
    public function setWifiPowerMode($mode)
    {
        $payload = '';
        $payload .= pack('C', $mode);

        $this->sendRequestNoResponse(self::FUNCTION_SET_WIFI_POWER_MODE, $payload);
    }

    /**
     * Returns the power mode as set by BrickMaster::setWifiPowerMode().
     * 
     * .. versionadded:: 1.3.0
     * 
     * 
     * @return int
     */
    public function getWifiPowerMode()
    {
        $payload = '';

        $data = $this->sendRequestExpectResponse(self::FUNCTION_GET_WIFI_POWER_MODE, $payload, 1);

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
     * .. versionadded:: 1.3.2
     * 
     * 
     * @return array
     */
    public function getWifiBufferInfo()
    {
        $result = array();

        $payload = '';

        $data = $this->sendRequestExpectResponse(self::FUNCTION_GET_WIFI_BUFFER_INFO, $payload, 8);

        $payload = unpack('V1overflow/v1low_watermark/v1used', $data);

        $result['overflow'] = IPConnection::fixUnpackedUInt32($payload['overflow']);
        $result['low_watermark'] = $payload['low_watermark'];
        $result['used'] = $payload['used'];

        return $result;
    }

    /**
     * Sets the regulatory domain of the WIFI Extension. Possible modes are:
     * 
     * <code>
     *  "Mode", "Description"
     * 
     *  "0", "FCC: Channel 1-11 (N/S America, Australia, New Zealand)"
     *  "1", "ETSI: Channel 1-13 (Europe, Middle East, Africa)"
     *  "2", "TELEC: Channel 1-14 (Japan)"
     * </code>
     * 
     * The default value is 1 (ETSI).
     * 
     * .. versionadded:: 1.3.4
     * 
     * @param int $domain
     * 
     * @return void
     */
    public function setWifiRegulatoryDomain($domain)
    {
        $payload = '';
        $payload .= pack('C', $domain);

        $this->sendRequestNoResponse(self::FUNCTION_SET_WIFI_REGULATORY_DOMAIN, $payload);
    }

    /**
     * Returns the regulatory domain as set by BrickMaster::setWifiRegulatoryDomain().
     * 
     * .. versionadded:: 1.3.4
     * 
     * 
     * @return int
     */
    public function getWifiRegulatoryDomain()
    {
        $payload = '';

        $data = $this->sendRequestExpectResponse(self::FUNCTION_GET_WIFI_REGULATORY_DOMAIN, $payload, 1);

        $payload = unpack('C1mode', $data);

        return $payload['mode'];
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
}

?>
