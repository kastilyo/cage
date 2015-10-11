<?php
namespace Kastilyo\RabbitHole\AMQP;

use AMQPConnection;
use AMQPChannel;
use AMQPExchange;

/**
 * Responsible for declaring exchanges on a RabbitMQ host and returning the
 * AMQPExchange instance.
 */
class ExchangeBuilder
{
    use ResourceBuilder;

    /**
     * Object cache of built exchanges indexed by name
     * @var array
     */
    private $exchanges = [];

    /**
     * Creates a fresh AMQPExchange instance, declaring it as a durable topic
     * exchange with currently set name.
     * @return \AMQPExchange
     */
    private function build()
    {
        $exchange = new AMQPExchange($this->getChannel());
        $exchange->setType(AMQP_EX_TYPE_TOPIC);
        $exchange->setName($this->getName());
        $exchange->setFlags(AMQP_DURABLE);
        $exchange->declareExchange();
        return $exchange;
    }

    /**
     * Lazily returns a fully-prepared AMQPExchange
     * @return \AMQPExchange
     */
    public function get()
    {
        $name = $this->getName();
        if (!isset($this->exchanges[$name])) {
            $this->exchanges[$name] = $this->build();
        }
        $this->reset();
        return $this->exchanges[$name];
    }

    /**
     * Resets the state of the builder
     */
    private function reset()
    {
        $this->name = null;
    }
}
