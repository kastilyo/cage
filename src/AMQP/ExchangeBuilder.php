<?php
namespace Kastilyo\RabbitHole\AMQP;

use AMQPConnection;
use AMQPChannel;
use AMQPExchange;

class ExchangeBuilder
{
    use ResourceBuilder;

    private $exchanges = [];

    private function getExchange()
    {
        return new AMQPExchange($this->getChannel());
    }

    public function build()
    {
        $name = $this->getName();
        if (!isset($this->exchanges[$name])) {
            $exchange = $this->getExchange();
            $exchange->setType(AMQP_EX_TYPE_TOPIC);
            $exchange->setName($this->getName());
            $exchange->setFlags(AMQP_DURABLE);
            $exchange->declareExchange();
            $this->exchanges[$name] = $exchange;
        }
        $this->reset();
        return $this->exchanges[$name];
    }

    private function reset()
    {
        $this->name = null;
    }
}
