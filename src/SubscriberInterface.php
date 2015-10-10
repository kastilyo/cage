<?php
namespace Kastilyo\RabbitHole;

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
    public static function getBindingKeys();

    /**
     * The name of the subscribed queue
     * @return string
     */
    public static function getQueueName();

    /**
     * The name of the exchange with the subscribed queue
     * @return string
     */
    public static function getExchangeName();

    /**
     * Handle a message from the queue
     * @param  AMQPEnvelope $amqp_envelope
     */
    public function processMessage(AMQPEnvelope $amqp_envelope);
}
