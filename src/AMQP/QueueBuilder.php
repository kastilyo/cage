<?php
namespace Kastilyo\RabbitHole\AMQP;

use AMQPConnection;
use AMQPChannel;
use AMQPQueue;
use Kastilyo\RabbitHole\Exceptions\InvalidPropertyException;

/**
 * Responsible for declaring queues on a RabbitMQ and returning the AMQPQueue
 * instance.
 */
class QueueBuilder
{
    use ResourceBuilder;

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
     * Lazily instantiates and declares an AMQPQueue instance based on the
     * builder's currently set name, exchange_name, and binding_keys.
     * @return \AMQPQueue
     */
    public function get()
    {
        $name = $this->getName();
        if (!isset($this->queues[$name])) {
            $queue = new AMQPQueue($this->getChannel());
            $queue->setName($name);
            $queue->setFlags(AMQP_DURABLE);
            $queue->declareQueue();
            foreach ($this->getBindingKeys() as $binding_key) {
                $queue->bind($this->getExchangeName(), $binding_key);
            }
            $this->queues[$name] = $queue;
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

    /**
     * Returns the currently set $binding_keys property
     * @throws \Kastilyo\RabbitHole\Exceptions\InvalidPropertyException For empty or non-array values
     * @return array
     */
    private function getBindingKeys()
    {
        if (empty($this->binding_keys)) {
            throw new InvalidPropertyException("Binding keys haven't been set");
        } elseif (!is_array($this->binding_keys)) {
            throw new InvalidPropertyException("Binding keys must be set as an array of strings");
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
}
