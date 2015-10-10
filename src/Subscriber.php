<?php
namespace Kastilyo\RabbitHole;

use AMQPEnvelope;
use Kastilyo\RabbitHole\AMQP\QueueBuilder;
use Kastilyo\RabbitHole\AMQP\ExchangeBuilder;

/**
 * This trait is meant to be used by Subscribing implementations. With the
 * exception of processMessage, this provides implementations of the contract.
 */
trait Subscriber
{
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
     * [$queue_builder description]
     * @var [type]
     */
    private $queue_builder;

    public function setQueueBuilder(QueueBuilder $queue_builder)
    {
        $this->queue_builder = $queue_builder;
    }

    private function getQueueBuilder()
    {
        return $this->queue_builder ?:
            ($this->queue_builder = new QueueBuilder($this->amqp_connection));
    }

    public function setExchangeBuilder(ExchangeBuilder $exchange_builder)
    {
        $this->exchange_builder = $exchange_builder;
    }

    private function getExchangeBuilder()
    {
        return $this->exchange_builder ?:
            ($this->exchange_builder = new ExchangeBuilder($this->amqp_connection));
    }

    private function buildExchange()
    {
        $this->getExchangeBuilder()
            ->setName(static::getExchangeName())
            ->build();
    }

    /**
     * [buildQueue description]
     * @return [type] [description]
     */
    private function buildQueue()
    {
        return $this->getQueueBuilder()
            ->setName(static::getQueueName())
            ->setExchangeName(static::getExchangeName())
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

    public function consume()
    {
        $this->buildExchange();
        $this->getQueue()->consume(function (AMQPEnvelope $envelope) {
            $this->processMessage($envelope);
        });
    }
}
