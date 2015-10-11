<?php
namespace Kastilyo\RabbitHole\Spec;

use kahlan\plugin\Stub;

/**
 * Helper class for stubbing third-party AMQP classes
 */
class Helper
{
    public static function initializeAMQPStubs()
    {
        Stub::on('AMQPChannel')->method('__construct');
        Stub::on('AMQPChannel')->method('qos');
        Stub::on('AMQPExchange')->method('__construct');
        Stub::on('AMQPExchange')->method('declareExchange');
        Stub::on('AMQPQueue')->method('__construct');
        Stub::on('AMQPQueue')->method('declareQueue');
        Stub::on('AMQPQueue')->method('bind');
    }

    public static function getAMQPChannel()
    {
        return Stub::create([
            'extends' => 'AMQPChannel',
            'methods' => ['__construct'],
        ]);
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

    public static function getAMQPEnvelope()
    {
        return Stub::create([
            'extends' => 'AMQPEnvelope',
            'methods' => ['__construct', 'getDeliveryTag']
        ]);
    }
}
