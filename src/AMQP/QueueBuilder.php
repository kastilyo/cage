<?php
namespace Kastilyo\RabbitHole\AMQP;

use AMQPConnection;
use AMQPChannel;
use AMQPQueue;
use Kastilyo\RabbitHole\Exceptions\InvalidPropertyException;

/**
 * Responsible for declaring queues on a RabbitMQ exchange and returning the
 * AMQPQueue instance.
 */
class QueueBuilder
{
    use ResourceBuilderTrait;

    /**
     * Object cache of built queues indexed by name
     * @var array
     */
    private $queues = [];

    /**
     * Name of exchange associated with queue being built
     * @var string
     */
    private $exchange_name;

    /**
     * Array of routing keys to bind to the queue
     * @var array
     */
    private $binding_keys = [];

    /**
     * Number of unacknowledged messages to consume at once
     * @var integer
     */
    private $batch_count = 1;

    /**
     * Creates a fresh AMQPQueue instance, declaring it as durable and with
     * the currently set name. It will also bind the currently set binding keys
     * to it.
     * @return AMQPQueue
     */
    private function build()
    {
        $queue = new AMQPQueue($this->getChannel());
        $queue->getChannel()->qos(($prefetch_size = 0), $this->getBatchCount());
        $queue->setName($this->getName());
        $queue->setFlags(AMQP_DURABLE);
        $queue->declareQueue();
        foreach ($this->getBindingKeys() as $binding_key) {
            $queue->bind($this->getExchangeName(), $binding_key);
        }
        return $queue;
    }

    /**
     * Lazily returns a fully-prepared AMQPQueue
     * @return \AMQPQueue
     */
    public function get()
    {
        $name = $this->getName();
        if (!isset($this->queues[$name])) {
            $this->queues[$name] = $this->build();
        }
        $this->reset();
        return $this->queues[$name];
    }

    /**
     * Resets the state of the builder
     */
    private function reset()
    {
        foreach (['name', 'binding_keys', 'exchange_name'] as $property) {
            $this->$property = is_array($this->$property) ? [] : null;
        }
    }

    private function getBatchCount()
    {
        if (empty($this->batch_count)) {
            throw new InvalidPropertyException("Need to set a batch count of 1 or more");
        }

        return $this->batch_count;
    }

    /**
     * Returns the currently set $binding_keys property
     * @throws \Kastilyo\RabbitHole\Exceptions\InvalidPropertyException For empty or non-array values
     * @return array
     */
    private function getBindingKeys()
    {
        if (empty($this->binding_keys)) {
            throw new InvalidPropertyException("Binding keys haven't been set");
        }

        return $this->binding_keys;
    }

    /**
     * @throws \Kastilyo\RabbitHole\Exceptions\InvalidPropertyException For empty values
     * @return string The currently set $exchange_name property
     */
    private function getExchangeName()
    {
        if (empty($this->exchange_name)) {
            throw new InvalidPropertyException("An exchange name hasn't been set");
        }
        return $this->exchange_name;
    }

    /**
     * @param array $binding_keys Array of routing keys to bind to the queue
     * @return $this
     */
    public function setBindingKeys(array $binding_keys)
    {
        $this->binding_keys = $binding_keys;
        return $this;
    }

    /**
     * @param string $name Name of the exchange on which to declare the queue
     * @return $this
     */
    public function setExchangeName($name)
    {
        $this->exchange_name = $name;
        return $this;
    }

    /**
     * @param int $batch_count Number of unacknowledged messages to consume at once
     */
    public function setBatchCount($batch_count)
    {
        $this->batch_count = (int) $batch_count;
        return $this;
    }
}
