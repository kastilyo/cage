<?php
namespace Kastilyo\RabbitHole\Spec;

use AMQPConnection;
use AMQPEnvelope;
use Kastilyo\RabbitHole\Subscriber;
use Kastilyo\RabbitHole\Subscribing;
use Kastilyo\RabbitHole\AMQP\QueueBuilder;

class SpecSubscriber implements Subscribing
{
    use Subscriber;

    public function __construct(AMQPConnection $amqp_connection)
    {
        $this->amqp_connection = $amqp_connection;
    }

    public static function getExchangeName()
    {
        return 'test_exchange';
    }

    public static function getQueueName()
    {
        return 'test_queue';
    }

    public static function getBindingKeys()
    {
        return ['test.info'];
    }

    public function processMessage(AMQPEnvelope $amqp_envelope)
    {
        echo $amqp_envelope->getBody(), PHP_EOL;
        $this->getQueue()->ack($amqp_envelope->getDeliveryTag());
    }
}
