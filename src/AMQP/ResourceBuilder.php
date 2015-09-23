<?php
namespace Kastilyo\RabbitHole\AMQP;

use AMQPConnection;
use AMQPChannel;
use AMQPQueue;

/**
 * Contains behavior common to declaring queues and exchanges
 */
trait ResourceBuilder
{
    /**
     * [$connection description]
     * @var [type]
     */
    private $connection;

    /**
     * [$name description]
     * @var [type]
     */
    private $name;

    /**
     * [$flags description]
     * @var [type]
     */
    private $flags;

    /**
     * [__construct description]
     * @param AMQPConnection $connection [description]
     */
    public function __construct(AMQPConnection $connection)
    {
        $this->connection = $connection;
    }

    /**
     * [getFlags description]
     * @return [type] [description]
     */
    private function getFlags()
    {
        return $this->flags;
    }

    /**
     * [getName description]
     * @return [type] [description]
     */
    private function getName()
    {
        return $this->name;
    }

    /**
     * [setName description]
     * @param [type] $name [description]
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * [setFlags description]
     * @param [type] $flags [description]
     */
    public function setFlags($flags)
    {
        $this->flags = $flags;
        return $this;
    }

    /**
     * [getConnection description]
     * @return [type] [description]
     */
    private function getConnection()
    {
        if (!$this->connection->isConnected()) {
            $this->connection->connect();
        }

        return $this->connection;
    }

    /**
     * [getChannel description]
     * @return [type] [description]
     */
    private function getChannel()
    {
        return new AMQPChannel($this->getConnection());
    }
}
