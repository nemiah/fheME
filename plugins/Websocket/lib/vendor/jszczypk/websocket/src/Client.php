<?php

namespace JSzczypk\WebSocket;

class Client
{

    const OPCODE_CONTINUATION = 0x0;
    const OPCODE_TEXT = 0x1;
    const OPCODE_BINARY = 0x2;
    // 0x3-0x7 reserved
    const OPCODE_CLOSE = 0x8;
    const OPCODE_PING = 0x9;
    const OPCODE_PONG = 0xA;
    // 0xB-0xF reserved

    const GUID = '258EAFA5-E914-47DA-95CA-C5AB0DC85B11';

    /**
     * @var resource
     */
    protected $socket;

    /**
     * @var string
     */
    protected $uri;

    /**
     * @var ?int
     */
    protected $connectTimeout;

    /**
     * @var int
     */
    protected $timeout = 5;

    /**
     * @var int
     */
    protected $fragmentSize = 4096;

    protected $headers;

    protected $handshakeResponse = [];

    protected $isConnected = false;
    protected $isClosing = false;
    protected $hugePayload;

    /**
     * @param array{timeout:int} $options
     */
    public function __construct(string $uri, array $options = [])
    {

        $parts = parse_url($uri);
        if (!in_array($parts['scheme'], [ 'ws', 'wss' ])) {
            throw new \InvalidArgumentException("Unrecognized URI scheme: {$parts['scheme']}'");
        }

        $this->uri = $uri;

        if (array_key_exists('timeout', $options)) {
            $this->timeout = $options['timeout'];
        }
        if (array_key_exists('fragmentSize', $options)) {
            $this->fragmentSize = $options['fragmentSize'];
        }
        if (array_key_exists('headers', $options)) {
            $this->headers = $options['headers'];
        }
    }

    public function __destruct()
    {
        if ($this->isConnected) {
            $this->close();
        } elseif ($this->socket) {
            fclose($this->socket);
        }
    }

    protected function connect(): void
    {
        $url_parts = parse_url($this->uri);
        $scheme = $url_parts['scheme'];
        $host = $url_parts['host'];
        $user = isset($url_parts['user']) ? $url_parts['user'] : '';
        $pass = isset($url_parts['pass']) ? $url_parts['pass'] : '';
        $port = isset($url_parts['port']) ? $url_parts['port'] : ($scheme === 'wss' ? 443 : 80);
        $path = isset($url_parts['path']) ? $url_parts['path'] : '/';
        $query = isset($url_parts['query']) ? $url_parts['query'] : '';

        $pathWithQuery = $query ? "{$path}?{$query}" : $path;

        $this->socket = @stream_socket_client(
            ($scheme === 'wss' ? 'ssl' : 'tcp')."://{$host}:{$port}",
            $errno,
            $errstr,
            $this->connectTimeout ?? ini_get("default_socket_timeout")
        );

        if ($this->socket === false) {
            throw new ConnectionException("Could not open socket to \"$host:$port\": $errstr ($errno).");
        }

        stream_set_timeout($this->socket, $this->timeout);

        $key = base64_encode(random_bytes(16));

        $headers = [
            'Host' => "$host:$port",
            'User-Agent' => 'jszczypk/websocket',
            'Connection' => 'Upgrade',
            'Upgrade' => 'WebSocket',
            'Sec-WebSocket-Key' => $key,
            'Sec-WebSocket-Version' => '13',
        ];

        // Handle basic authentication.
        if ($user || $pass) {
            $headers['authorization'] = 'Basic ' . base64_encode($user . ':' . $pass) . "\r\n";
        }

        // Add and override headers
        if (count($this->headers)) {
            $headers = array_merge($headers, array_change_key_case($this->headers));
        }

        $handshake = [ "GET {$pathWithQuery} HTTP/1.1" ];
        foreach ($headers as $k => $v) {
            $handshake[] = "{$k}: {$v}";
        }
        $handshake[] = '';
        $handshake[] = '';

        $handshake = implode("\r\n", $handshake);

        //print_r($handshake);

        $this->write($handshake);

        // Get server response header (terminated with double CR+LF).
        $response = stream_get_line($this->socket, 1024, "\r\n\r\n");

        $this->handshakeResponse = $this->parseHeaders(explode("\r\n", $response));

        //print_r($this->handshakeResponse);

        if ($this->handshakeResponse['response_code'] != '101') {
            throw new ConnectionException("Invalid HTTP response code");
        }

        if (strtolower($this->handshakeResponse['Upgrade'] ?? '') != 'websocket') {
            throw new ConnectionException("Missing are invalid Upgrade header in server response.");
        }

        if (strtolower($this->handshakeResponse['Connection'] ?? '') != 'upgrade') {
            throw new ConnectionException("Missing are invalid Upgrade header in server response.");
        }

        if (empty($this->handshakeResponse['Sec-WebSocket-Accept'])) {
            throw new ConnectionException("Connection to '{$this->uri}' failed: Server sent invalid upgrade response:\n{$response}");
        }

        if ($this->handshakeResponse['Sec-WebSocket-Accept'] !== base64_encode(sha1($key.self::GUID, true))) {
            throw new ConnectionException('Server sent bad upgrade response.');
        }

        $this->isConnected = true;
    }

    protected function parseHeaders(array $headers): array
    {
        $result = [];
        foreach ($headers as $k => $v) {
            $t = explode(':', $v, 2);
            if (isset($t[1])) {
                $result[ trim($t[0]) ] = trim($t[1]);
            } else {
                $result[] = $v;
                if (preg_match("#HTTP/[0-9\.]+\s+([0-9]+)#", $v, $out)) {
                    $result['response_code'] = intval($out[1]);
                }
            }
        }
        return $result;
    }

    public function setTimeout(int $timeout): void
    {
        $this->timeout = $timeout;
        if ($this->socket && get_resource_type($this->socket) === 'stream') {
            stream_set_timeout($this->socket, $timeout);
        }
    }

    public function setFragmentSize(int $fragmentSize): void
    {
        $this->fragmentSize = $fragmentSize;
    }

    public function getFragmentSize(): int
    {
        return $this->fragmentSize;
    }

    public function getHandshakeParam(string $key): ?string
    {
        return $this->handshakeResponse[$key] ?? null;
    }

    public function send(string $payload, int $opcode = self::OPCODE_TEXT): void
    {

        if ($opcode > 0x0F) {
            throw new Exception("Opcode out of range");
        }

        if (!$this->isConnected) {
            $this->connect();
        }

        // control frames are always sent without fragmentation
        // it's very unlikely to have fragmentSize set to such small value, but anyway
        $fragments = ($opcode >= 0x8) ? [ $payload ] : str_split($payload, $this->fragmentSize);

        for ($i = 0; $i < count($fragments); $i++) {
            $first = ($i == (count($fragments)-1)) ? 0x80 : 0;
            $first |= ($i == 0) ? $opcode : self::OPCODE_CONTINUATION;

            $second = 0x80; // always mask
            
            $length = strlen($fragments[$i]);

            if ($length <= 125) {
                $second |= $length;
                $msg = pack('CC', $first, $second);
            } elseif ($length <= 65535) {
                $second |= 126;
                $msg = pack('CCn', $first, $second, $length);
            } else {
                $second |= 127;
                $msg = pack('CCJ', $first, $second, $length);
            }
            
            $mask = random_bytes(4);
            $msg .= $mask;
            $msg .= $this->mask($fragments[$i], $mask);

            $this->write($msg);
        }
    }

    public function receive(): string
    {
        if (!$this->isConnected) {
            $this->connect();
        }

        $this->hugePayload = '';

        $response = null;
        while (is_null($response)) {
            $response = $this->receiveFragment();
        }

        // TODO PhanTypeMismatchReturnNullable
        return (string) $response;
    }

    protected function receiveFragment(): ?string
    {

        $first = unpack('C', $this->read(1))[1];
        $second = unpack('C', $this->read(1))[1];

        $final = ($first & 0x80) == 0x80;
        $opcode = $first & 0x0F;

        $hasMask = ($second & 0x80) == 0x80;
        $length = $second & 127;

        if ($length == 126) {
            $length = unpack('n', $this->read(2))[1];
        } elseif ($length == 127) {
            $length = unpack('J', $this->read(8))[1];
        }

        if ($hasMask) {
            $mask = $this->read(4);
        }

        if ($length > 0) {
            $payload = $this->read($length);
            if ($hasMask) {
                $payload = $this->mask($payload, $mask);
            }
        } else {
            $payload = '';
        }

        if ($opcode === self::OPCODE_CLOSE) {
            // Get the close status.
            if ($length >= 2) {
                $status = unpack('n', substr($payload, 0, 2))[1];
                if (!$this->isClosing) {
                    $this->send(substr($payload, 0, 2)."Close acknowledged: {$status}", self::OPCODE_CLOSE);
                }
                $payload = substr($payload, 2);
            }

            if ($this->isClosing) {
                $this->isClosing = false; // A close response, all done.
            }

            // And close the socket.
            fclose($this->socket);
            $this->isConnected = false;
        }

        if ($opcode === self::OPCODE_PING) {
            $this->send($payload, self::OPCODE_PONG);
            return null;
        }

        // if this is not the last fragment, then we need to save the payload
        if (!$final) {
            $this->hugePayload .= $payload;
            return null;
        } elseif ($this->hugePayload) {
            // this is the last fragment, and we are processing a huge_payload
            // sp we need to retreive the whole payload
            $payload = $this->hugePayload .= $payload;
            $this->hugePayload = null;
        }

        return $payload;
    }

    /**
     * Tell the socket to close.
     *
     * @param integer $status  http://tools.ietf.org/html/rfc6455#section-7.4
     * @param string $message A closing message, max 125 bytes.
     */
    public function close(int $status = 1000, string $message = ''): void
    {
        $this->send(pack('n', $status).substr($message, 0, 125), self::OPCODE_CLOSE);
        $this->isClosing = true;
        $this->receive();
    }

    protected function write(string $data): void
    {
        $written = fwrite($this->socket, $data);

        if ($written === false) {
            throw new ConnectionException("Error when sending data.");
        }

        if ($written < strlen($data)) {
            throw new ConnectionException("Could only write $written out of " . strlen($data) . " bytes.");
        }
    }

    protected function read(int $length): string
    {
        $data = '';
        while (strlen($data) < $length) {
            $buffer = fread($this->socket, $length - strlen($data));
            if ($buffer === false) {
                $metadata = stream_get_meta_data($this->socket);
                throw new ConnectionException('Broken frame, read ' . strlen($data) . " of stated {$length} bytes. Stream state: ".json_encode($metadata));
            }
            if ($buffer === '') {
                $metadata = stream_get_meta_data($this->socket);
                throw new ConnectionException('Empty read; connection dead? Stream state: '.json_encode($metadata));
            }
            $data .= $buffer;
        }
        return $data;
    }

    protected function mask(string $payload, string $mask): string
    {
        for ($i = 0; $i < strlen($payload); $i++) {
            $payload[$i] = $payload[$i] ^ $mask[$i % 4];
        }
        return $payload;
    }

    // https://stackoverflow.com/questions/1057572/how-can-i-get-a-hex-dump-of-a-string-in-php
    protected function hexDump($data, $newline = "\n")
    {
        static $from = '';
        static $to = '';

        static $width = 16; # number of bytes per line

        static $pad = '.'; # padding for non-visible characters

        if ($from==='') {
            for ($i=0; $i<=0xFF; $i++) {
                $from .= chr($i);
                $to .= ($i >= 0x20 && $i <= 0x7E) ? chr($i) : $pad;
            }
        }

        $hex = str_split(bin2hex($data), $width*2);
        $chars = str_split(strtr($data, $from, $to), $width);

        $offset = 0;
        foreach ($hex as $i => $line) {
            echo sprintf('%6X', $offset).' : '.implode(' ', str_split($line, 2)) . ' [' . $chars[$i] . ']' . $newline;
            $offset += $width;
        }
    }
}

// vim: tabstop=4 shiftwidth=4 expandtab
