<?php
namespace Kastilyo\RabbitHole;

use Kastilyo\RabbitHole\Subscriber;
use Kastilyo\RabbitHole\Subscribing;
use Kastilyo\RabbitHole\AMQP\QueueBuilder;

class TestSubscriber implements Subscribing
{
    protected static $amqp_exchange_name = 'test_exchange';
    protected static $amqp_queue_name = 'test_queue';
    protected static $amqp_binding_keys = ['test.info'];

    public function __construct(AMQPConnection $amqp_connection)
    {
        $this->amqp_connection = $amqp_connection;
    }

    public function processMessage(AMQPEnvelope $amqp_envelope)
    {
        echo $envelope->getBody(), PHP_EOL;
        $this->getQueue()->ack($envelope->getDeliveryTag());
    }
}
