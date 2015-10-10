<?php
namespace Kastilyo\RabbitHole\Spec;

use AMQPConnection;
use kahlan\plugin\Stub;

class Helper
{
    public static function initializeAMQPStubs()
    {
        Stub::on('AMQPChannel')->method('__construct');
        Stub::on('AMQPExchange')->method('__construct');
        Stub::on('AMQPExchange')->method('declareExchange');
        Stub::on('AMQPQueue')->method('__construct');
        Stub::on('AMQPQueue')->method('declareQueue');
        Stub::on('AMQPQueue')->method('bind');
    }
    public static function getAMQPConnection()
    {
        return Stub::create([
            'extends' => 'AMQPConnection',
            'methods' => ['__construct', 'connect', 'isConnected'],
        ]);
    }

    public static function getAMQPQueue()
    {
        return Stub::create([
            'extends' => 'AMQPQueue',
            'methods' => ['__construct']
        ]);
    }
}
