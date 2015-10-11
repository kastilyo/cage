<?php
namespace Kastilyo\RabbitHole\AMQP;

use AMQPConnection;
use AMQPChannel;
use AMQPExchange;

class ExchangeBuilder
{
    use ResourceBuilder;

    private $exchanges = [];

    private function build()
    {
        $exchange = new AMQPExchange($this->getChannel());
        $exchange->setType(AMQP_EX_TYPE_TOPIC);
        $exchange->setName($this->getName());
        $exchange->setFlags(AMQP_DURABLE);
        $exchange->declareExchange();
        return $exchange;
    }

    public function get()
    {
        $name = $this->getName();
        if (!isset($this->exchanges[$name])) {
            $this->exchanges[$name] = $this->build();
        }
        $this->reset();
        return $this->exchanges[$name];
    }

    private function reset()
    {
        $this->name = null;
    }
}
