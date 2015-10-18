<?php
namespace Kastilyo\RabbitHole\Subscriber;

use AMQPEnvelope;
use AMQPConnection;

/**
 * To be implemented by subscribers.
 */
interface SubscriberInterface
{
    /**
     * The keys bound to the subscribed queue
     * @return array An array of strings
     */
    public function getBindingKeys();

    /**
     * The name of the subscribed queue
     * @return string
     */
    public function getQueueName();

    /**
     * The name of the exchange associated with the subscribed queue
     * @return string
     */
    public function getExchangeName();


    /**
     * Returns the number of unacknowledged messages to get consume
     * @return int
     */
    public function getBatchCount();

    /**
     * Handle a message from the queue
     * @param  AMQPEnvelope $amqp_envelope
     */
    public function processMessage(AMQPEnvelope $amqp_envelope);
}
