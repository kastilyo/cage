<?php
namespace Kastilyo\RabbitHole\Spec;

use Kahlan\Plugin\Double;

/**
 * Helper class for stubbing third-party AMQP classes
 */
class Helper
{
    public static function initializeAMQPStubs()
    {
        allow('AMQPChannel')->toReceive('__construct');
        allow('AMQPChannel')->toReceive('qos');
        allow('AMQPExchange')->toReceive('__construct');
        allow('AMQPExchange')->toReceive('declareExchange');
        allow('AMQPQueue')->toReceive('__construct');
        allow('AMQPQueue')->toReceive('declareQueue');
        allow('AMQPQueue')->toReceive('bind');
    }

    public static function getAMQPChannel()
    {
        return Double::instance([
            'extends' => 'AMQPChannel',
            'methods' => ['__construct'],
        ]);
    }

    public static function getAMQPConnection()
    {
        return Double::instance([
            'extends' => 'AMQPConnection',
            'methods' => ['__construct', 'connect', 'isConnected'],
        ]);
    }

    public static function getAMQPQueue()
    {
        return Double::instance([
            'extends' => 'AMQPQueue',
            'methods' => ['__construct']
        ]);
    }

    public static function getAMQPEnvelope()
    {
        return Double::instance([
            'extends' => 'AMQPEnvelope',
            'methods' => ['__construct', 'getDeliveryTag']
        ]);
    }
}
