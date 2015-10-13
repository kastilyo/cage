<?php
require_once __DIR__ . '/../vendor/autoload.php';

$injector = new Auryn\Injector;
$injector->share('AMQPConnection');
$injector->delegate('AMQPConnection', function () {
    $connection = new AMQPConnection([
        'host' => 'localhost',
        'port' => 5672,
        'login' => 'guest',
        'password' => 'guest',
    ]);
    if (!$connection->isConnected()) {
        $connection->connect();
    }
    return $connection;
});
