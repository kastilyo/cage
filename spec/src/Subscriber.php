<?php
namespace Kastilyo\RabbitHole\Spec;

use AMQPConnection;
use AMQPEnvelope;
use Kastilyo\RabbitHole\Subscriber\SubscriberTrait;
use Kastilyo\RabbitHole\Subscriber\SubscriberInterface;

/**
 * This is a test SubscriberInterface implementation that mixes in SubscriberTrait.
 * It represents the intended use of this library.
 */
class Subscriber implements SubscriberInterface
{
    use SubscriberTrait;

    public function __construct(AMQPConnection $amqp_connection)
    {
        $this->setAMQPConnection($amqp_connection);
    }

    public function getExchangeName()
    {
        return 'test_exchange';
    }

    public function getQueueName()
    {
        return 'test_queue';
    }

    public function getBindingKeys()
    {
        return ['test.info'];
    }

    public function processMessage(AMQPEnvelope $amqp_envelope)
    {
        echo $amqp_envelope->getBody(), PHP_EOL;
        $this->acknowledgeMessage($amqp_envelope);
    }
}
