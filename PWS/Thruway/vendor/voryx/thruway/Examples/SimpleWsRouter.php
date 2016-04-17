<?php

require 'bootstrap.php';

use Thruway\Peer\Router;
use Thruway\Transport\RatchetTransportProvider;

$router = new Router();

$transportProvider = new RatchetTransportProvider("0.0.0.0", 4444);

$router->addTransportProvider($transportProvider);

$router->start();
