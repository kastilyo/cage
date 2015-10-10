<?php
require_once __DIR__ . '/../vendor/autoload.php';

$injector = new Auryn\Injector;

$injector->define('AMQPConnection', [
    ':credentials' => [
        'host' => 'localhost',
        'port' => 5672,
        'login' => 'guest',
        'password' => 'guest',
    ],
]);

$injector->define('Kastilyo\RabbitHole\Spec\Subscriber', [
    ':amqp_connection' => $injector->make('AMQPConnection'),
]);
