<?php

namespace JSzczypk\WampSyncClient;

class Client
{

    const MESSAGE_HELLO = 1;
    const MESSAGE_WELCOME = 2;
    const MESSAGE_ABORT = 3;
    const MESSAGE_CHALLENGE = 4;
    const MESSAGE_AUTHENTICATE = 5;
    const MESSAGE_GOODBYE = 6;

    const MESSAGE_ERROR = 8;

    const MESSAGE_PUBLISH = 16;
    const MESSAGE_PUBLISHED = 17;

    const MESSAGE_CALL = 48;
    const MESSAGE_RESULT = 50;

    const PROTOCOL_WAMP_2_JSON = 'wamp.2.json';
    const PROTOCOL_WAMP_2_MSGPACK = 'wamp.2.msgpack';

    const AUTHENTICATION_TICKET = 'ticket';
    const AUTHENTICATION_WAMPCRA = 'wampcra';

    protected $ws;
    protected $realm;
    protected $socketTimeout = 5;

    protected $sessionId;
    protected $routerRoles;
    protected $requestId = 1;

    protected $protocol;

    protected $authId;
    protected $authRole;

    /**
     * @param string|callable(string,string,mixed):string $authSecret
     */
    public function __construct(string $websocketUri, string $realm, string $authId = null, $authSecret = null)
    {

        $this->realm = $realm;

        // TODO later check if we have msgpack PECL extension

        $options = [
            'headers' => [ 'Sec-WebSocket-Protocol' => static::PROTOCOL_WAMP_2_JSON /* .' '.static::PROTOCOL_WAMP_2_MSGPACK */ ],
            'timeout' => $this->socketTimeout
        ];

        $this->ws = new \JSzczypk\WebSocket\Client($websocketUri, $options);

        // TODO for now we support only one protocol so we assume this is the only one, later we should read it from websocket protocol negotiations
        $this->protocol = static::PROTOCOL_WAMP_2_JSON;

        $helloDetails = [
            'roles' => [ 'caller' => (object) [], 'publisher' => (object) [] ]
        ];

        if (!is_null($authId)) {
            //$helloDetails['authmethods'] = [ static::AUTHENTICATION_TICKET ];
            $helloDetails['authmethods'] = [ static::AUTHENTICATION_WAMPCRA ];
            $helloDetails['authid'] = $authId;
        }

        $this->send(static::MESSAGE_HELLO, $this->realm, $helloDetails);

        $msg = $this->receive();
		
        if ($msg[0] == static::MESSAGE_ABORT) {
            if ($msg[2] == 'wamp.error.no_auth_method') {
                throw new Exception($msg[1]['message']);
            }
            throw new Exception("Unexpected ABORT message: {$msg[2]}.");
        }

        if ($msg[0] == static::MESSAGE_CHALLENGE) {
            if (is_callable($authSecret)) {
                $authSecret = call_user_func($authSecret, $msg[1], $msg[2]);
            }
            switch ($msg[1]) {
                case static::AUTHENTICATION_TICKET:
                    $this->send(static::MESSAGE_AUTHENTICATE, $authSecret, (object) []);
                    break;
                case static::AUTHENTICATION_WAMPCRA:
                    $this->send(static::MESSAGE_AUTHENTICATE, base64_encode(hash_hmac('sha256', $msg[2]['challenge'], $authSecret, true)), (object) []);
                    break;
            }
            $msg = $this->receive();
			
			
            if ($msg[0] == static::MESSAGE_ABORT) {
                switch ($msg[2]) {
                    case 'wamp.error.not_authorized':
                        throw new Exception($msg[1]['message']);
                }
                //var_dump($msg);
                throw new Exception("Unexpected ABORT message: {$msg[2]}.");
            }
        }

        if ($msg[0] != static::MESSAGE_WELCOME) {
            throw new Exception("Unexpected message type {$msg[0]}.");
        }

        $this->sessionId = $msg[1];
        $this->routerRoles = $msg[2]['roles'];
        $this->authId = $msg[2]['authid'];
        $this->authRole = $msg[2]['authrole'];
        // TODO save some more info from WELCOM like broker/dealer features
    }

    public function __destruct()
    {
        $this->send(static::MESSAGE_GOODBYE, (object) [], 'wamp.close.close_realm');
        $msg = $this->receive();
    }

    protected function send(int $messageType, ...$params): void
    {
        if ($this->protocol == static::PROTOCOL_WAMP_2_JSON) {
            //$this->ws->send(json_encode([ $messageType, ...$params ])); // PHP 7.4
            $this->ws->send(json_encode(array_merge([ $messageType ], $params)));
        } else {
            throw new Exception("Unsupported protocol $this->protocol");
        }
    }

    protected function receive(): array
    {

        // TODO instead of number of tries have timeout as socket may have different timeouts
        $tries = 10;
        $msg = '';

        while ($tries > 0) {
            $tries--;
            try {
                $msg = $this->ws->receive();
                break;
            } catch (\JSzczypk\WebSocket\ConnectionException $e) {
                if ($tries == 0) {
                    throw $e;
                }
            }
        }

        if ($this->protocol == static::PROTOCOL_WAMP_2_JSON) {
            $result = json_decode($msg, true);
            if (is_null($result)) {
                throw new Exception("Error while unserializing message: $msg");
            }
            return $result;
        } else {
            throw new Exception("Unsupported protocol $this->protocol");
        }
    }

    /**
     * @params array<int,mixed> $arguments
     * @params array<string,mixed> $argumentsKw
     * @params array{receive_progress?:bool,timeout?:int,disclose_me?:bool} $options
     * TODO we do not support options.runmode == 'partition' and options.rkey == mixed
     * @return \JSzczypk\WampSyncClient\CallResult
     */
    public function call(string $uri, array $arguments = [], array $argumentsKw = [], array $options = []): CallResult
    {

        if (count($argumentsKw)) {
            $this->send(static::MESSAGE_CALL, $this->requestId++, (object) $options, $uri, $arguments, $argumentsKw);
        } elseif (count($arguments)) {
            $this->send(static::MESSAGE_CALL, $this->requestId++, (object) $options, $uri, $arguments);
        } else {
            $this->send(static::MESSAGE_CALL, $this->requestId++, (object) $options, $uri);
        }

        $msg = $this->receive();

        if ($msg[0] == static::MESSAGE_ERROR) {
            switch ($msg[4]) {
                case 'wamp.error.invalid_uri':
                    throw new InvalidURIException($msg[5][0]);
                case 'wamp.error.no_such_procedure':
                    throw new NoSuchProcedureException($msg[5][0]);
            }
            //var_dump($msg);
            //throw new Exception("Invocation error: {$msg[4]}");
            throw new InvocationException($msg[4], $msg[5] ?? [], $msg[6] ?? [], $msg[3]);
        }

        if ($msg[0] != static::MESSAGE_RESULT) {
            throw new Exception("Unexpected message type {$msg[0]}.");
        }

        // TODO support progressive results

        return new CallResult($msg[3] ?? [], $msg[4] ?? []);
    }

    /**
     * @params array{receive_progress?:bool,timeout?:int,disclose_me?:bool} $options
     * @return array<int,mixed>
     */
    public function callArguments(string $uri, array $arguments = [], array $argumentsKw = [], array $options = []): array
    {
        return $this->call($uri, $arguments, $argumentsKw, $options)->arguments;
    }

    /**
     * @params array{receive_progress?:bool,timeout?:int,disclose_me?:bool} $options
     * @return array<string,mixed>
     */
    public function callArgumentsKw(string $uri, array $arguments = [], array $argumentsKw = [], array $options = []): array
    {
        return $this->call($uri, $arguments, $argumentsKw, $options)->argumentsKw;
    }

    /**
     * @params array{receive_progress?:bool,timeout?:int,disclose_me?:bool} $options
     * @return ?mixed
     */
    public function callValue(string $uri, array $arguments = [], array $argumentsKw = [], array $options = [])
    {
        return array_shift($this->call($uri, $arguments, $argumentsKw, $options)->arguments);
    }

    /**
     * @params array{acknowledge?:bool,exclude?:int[],exclude_authid?:string[],exclude_authrole?:string[],eligible?:int[],eligible_authid?:string[],eligible_authrole?:string[],exclude_me?:bool,disclose_me?:bool} $options
     */
    public function publish(string $uri, array $arguments = [], array $argumentsKw = [], array $options = []): void
    {

        if (count($argumentsKw)) {
            $this->send(static::MESSAGE_PUBLISH, $this->requestId++, (object) $options, $uri, $arguments, $argumentsKw);
        } elseif (count($arguments)) {
            $this->send(static::MESSAGE_PUBLISH, $this->requestId++, (object) $options, $uri, $arguments);
        } else {
            $this->send(static::MESSAGE_PUBLISH, $this->requestId++, (object) $options, $uri);
        }

        if (!empty($options['acknowledge'])) {
            $msg = $this->receive();

            if ($msg[0] == static::MESSAGE_ERROR) {
                //var_dump($msg);
                throw new Exception("Publish error: {$msg[4]}");
            }

            if ($msg[0] != static::MESSAGE_PUBLISHED) {
                throw new Exception("Unexpected message type {$msg[0]}.");
            }
        }
    }

    /**
     * @return array{exact:array,prefix:array,wildcard:array}
     */
    public function listRegistrations(): array
    {
        // TODO check if dealer had feature registration_meta_api
        return $this->callArguments('wamp.registration.list')[0];
    }

    /**
     * @return array{id:int,created:string,uri:string,match:string,invoke:string}
     */
    public function getRegistration(int $id): array
    {
        // TODO check if dealer had feature registration_meta_api
        return $this->callArguments('wamp.registration.get', [ $id ])[0];
    }

    public function hasRegistration(string $uri): bool
    {
        // TODO check if dealer had feature registration_meta_api
        return (bool) $this->callArguments('wamp.registration.lookup', [ $uri ])[0];
    }
}

// vim: tabstop=4 shiftwidth=4 expandtab
