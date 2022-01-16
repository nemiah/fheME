<?php

namespace JSzczypk\WampSyncClient;

class InvocationException extends Exception
{

    /** @var string */
    protected $uri;

    /** @var array<int,mixed> */
    protected $arguments;

    /** @var array<string,mixed> */
    protected $argumentsKw;

    public function __construct(string $uri, array $arguments = [], array $argumentsKw = [], array $details = null)
    {

        if (count($arguments) == 1 && is_string($arguments[0])) {
            $errorMessage = $arguments[0];
        } else {
            $errorMessage = $uri;
        }

        parent::__construct($errorMessage);

        $this->uri = $uri;
        $this->arguments = $arguments;
        $this->argumentsKw = $argumentsKw;
    }

    public function getURI(): string
    {
        return $this->uri;
    }

    /**
     * @return array<int,mixed>
     * */
    public function getArguments(): array
    {
        return $this->arguments;
    }

    /**
     * @return array<string,mixed>
     * */
    public function getArgumentsKw(): array
    {
        return $this->argumentsKw;
    }
}

// vim: tabstop=4 shiftwidth=4 expandtab
