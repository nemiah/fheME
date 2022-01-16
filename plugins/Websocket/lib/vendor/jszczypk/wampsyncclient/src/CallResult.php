<?php

namespace JSzczypk\WampSyncClient;

class CallResult
{

    /** @var array<int,mixed> */
    public $arguments;

    /** @var array<string,mixed> */
    public $argumentsKw;

    /**
     * @param array<int,mixed> $arguments
     * @param array<string,mixed> $argumentsKw
     */
    public function __construct(array $arguments, array $argumentsKw)
    {
        $this->arguments = $arguments;
        $this->argumentsKw = $argumentsKw;
    }
}

// vim: tabstop=4 shiftwidth=4 expandtab
