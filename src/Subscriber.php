<?php
namespace Kastilyo\RabbitHole;

use Kastilyo\RabbitHole\AMQP\QueueBuilder;

/**
 * This trait is meant to be used by Subscribing implementations. With the
 * exception of processMessage, this provides implementations of the contract.
 */
trait Subscriber
{
    /**
     * [$amqp_exchange_name description]
     * @var string
     */
    protected static $amqp_exchange_name = '';

    /**
     * [$amqp_queue_name description]
     * @var string
     */
    protected static $amqp_queue_name = '';

    /**
     * [$amqp_binding_keys description]
     * @var array
     */
    protected static $amqp_binding_keys = [];

    /**
     * [$amqp_connection description]
     * @var [type]
     */
    private $amqp_connection;

    /**
     * [$amqp_queue description]
     * @var [type]
     */
    private $amqp_queue;

    /**
     * [getExchangeName description]
     * @return [type] [description]
     */
    public static function getExchangeName()
    {
        return static::$amqp_exchange_name;
    }

    /**
     * [getQueueName description]
     * @return [type] [description]
     */
    public static function getQueueName()
    {
        return static::$amqp_queue_name;
    }

    /**
     * [getBindingKeys description]
     * @return [type] [description]
     */
    public static function getBindingKeys()
    {
        return static::$amqp_binding_keys;
    }

    /**
     * [consume description]
     * @return [type] [description]
     */
    public function consume()
    {
        $this->getQueue();
    }

    /**
     * [buildQueue description]
     * @return [type] [description]
     */
    private function buildQueue()
    {
        return (new QueueBuilder($this->amqp_connection))
            ->setName(static::getQueueName())
            ->setExchangeName(static::getExchangeName())
            ->setFlags(AMQP_DURABLE)
            ->setBindingKeys(static::getBindingKeys())
            ->build();
    }

    /**
     * [getQueue description]
     * @return [type] [description]
     */
    private function getQueue()
    {
        return $this->amqp_queue ?: ($this->amqp_queue = $this->buildQueue());
    }
}
