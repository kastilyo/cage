<?php
namespace Kastilyo\RabbitHole\Subscriber;

use AMQPEnvelope;
use AMQPConnection;
use Kastilyo\RabbitHole\AMQP\QueueBuilder;
use Kastilyo\RabbitHole\AMQP\ExchangeBuilder;
use Kastilyo\RabbitHole\Exceptions\ImplementationException;

/**
 * This trait is meant to be mixed into SubscriberInterface implementations.
 */
trait SubscriberTrait
{
    /**
     * The connection for the subscribed queue
     * @var \AMQPConnection
     */
    private $amqp_connection;

    /**
     * The subscribed queue
     * @var \AMQPQueue
     */
    private $amqp_queue;

    /**
     * Prepares AMQPQueues
     * @var \Kastilyo\RabbitHole\AMQP\QueueBuilder
     */
    private $queue_builder;

    /**
     * Prepares AMQPExchanges
     * @var \Kastilyo\RabbitHole\AMQP\ExchangeBuilder
     */
    private $exchange_builder;

    private function getAMQPConnection()
    {
        if ($this->amqp_connection instanceof AMQPConnection) {
            return $this->amqp_connection;
        }

        throw new ImplementationException('An invalid AMQPConnection has been set');
    }

    public function setAMQPConnection(AMQPConnection $amqp_connection)
    {
        $this->amqp_connection = $amqp_connection;
    }

    public static function getBatchCount()
    {
        return 1;
    }

    /**
     * Setter injection for QueueBuilder
     * @param \Kastilyo\RabbitHole\AMQPQueueBuilder $queue_builder
     */
    public function setQueueBuilder(QueueBuilder $queue_builder)
    {
        $this->queue_builder = $queue_builder;
    }

    /**
     * Lazily instantiates a QueueBuilder
     * @return \Kastilyo\RabbitHole\AMQP\QueueBuilder
     */
    private function getQueueBuilder()
    {
        return $this->queue_builder ?:
            ($this->queue_builder = new QueueBuilder($this->getAMQPConnection()));
    }

    /**
     * Setter injection for ExchangeBuilder
     * @param \Kastilyo\RabbitHole\AMQP\ExchangeBuilder $queue_builder
     */
    public function setExchangeBuilder(ExchangeBuilder $exchange_builder)
    {
        $this->exchange_builder = $exchange_builder;
    }

    /**
     * Lazily instantiates an ExchangeBuilder
     * @return \Kastilyo\RabbitHole\AMQP\ExchangeBuilder
     */
    private function getExchangeBuilder()
    {
        return $this->exchange_builder ?:
            ($this->exchange_builder = new ExchangeBuilder($this->getAMQPConnection()));
    }

    /**
     * Lazily instantiates a QueueBuilder
     * @return \Kastilyo\RabbitHole\AMQP\QueueBuilder
     */
    private function buildExchange()
    {
        $this->getExchangeBuilder()
            ->setName($this->getAndValidateExchangeName())
            ->get();
    }

    /**
     * @throws \Kastilyo\RabbitHole\Exceptions\ImplementationException For empty exchange names
     * @return string Name of exchange associated with subscribed queue
     */
    private function getAndValidateExchangeName()
    {
        $exchange_name = static::getExchangeName();
        if (!empty($exchange_name)) {
            return $exchange_name;
        }

        throw new ImplementationException('An empty exchange name has been returned by getExchangeName');
    }

    /**
     * @throws \Kastilyo\RabbitHole\Exceptions\ImplementationException For empty queue names
     * @return string Name of queue to subscribe to
     */
    private function getAndValidateQueueName()
    {
        $queue_name = static::getQueueName();
        if (!empty($queue_name)) {
            return $queue_name;
        }

        throw new ImplementationException('An empty queue name has been returned by getQueueName');
    }

    /**
     * @throws \Kastilyo\RabbitHole\Exceptions\ImplementationException For empty binding keys
     * @return array Routing keys to be bound to the subscribed queue
     */
    private function getAndValidateBindingKeys()
    {
        $binding_keys = static::getBindingKeys();
        if (!empty($binding_keys)) {
            return $binding_keys;
        }

        throw new ImplementationException('Invalid binding keys have been returned by getBindingKeys');
    }

    private function getAndValidateBatchCount()
    {
        $batch_count = static::getBatchCount();
        if (!empty($batch_count) && is_int($batch_count)) {
            return $batch_count;
        }

        throw new ImplementationException('Invalid batch count returned by getBatchCount.');
    }

    /**
     * Prepares a queue via QueueBuilder based on the class's
     * queue name, exchange name and binding keys
     * @return \AMQPQueue
     */
    private function buildQueue()
    {
        return $this->getQueueBuilder()
            ->setName($this->getAndValidateQueueName())
            ->setExchangeName($this->getAndValidateExchangeName())
            ->setBindingKeys($this->getAndValidateBindingKeys())
            ->setBatchCount($this->getAndValidateBatchCount())
            ->get();
    }

    /**
     * Lazily instantiates an AMQPQueue
     * @return \AMQPQueue
     */
    private function getQueue()
    {
        $queue = $this->amqp_queue ?: ($this->amqp_queue = $this->buildQueue());
        return $queue;
    }

    /**
     * Consumes messages from the queue, processing them with the processMessage
     * method
     */
    public function consume()
    {
        $this->buildExchange();
        $this->getQueue()->consume([$this, 'processMessage']);
    }

    /**
     * Acknowledes the given message
     * @return \AMQPEnvelope
     */
    public function acknowledgeMessage(AMQPEnvelope $amqp_envelope)
    {
        $this->getQueue()->ack($amqp_envelope->getDeliveryTag());
    }
}
