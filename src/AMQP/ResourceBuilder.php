<?php
namespace Kastilyo\RabbitHole\AMQP;

use AMQPConnection;
use AMQPChannel;
use AMQPQueue;
use Kastilyo\RabbitHole\Exceptions\InvalidPropertyException;

/**
 * Contains behavior common to declaring queues and exchanges
 */
trait ResourceBuilder
{
    /**
     * [$connection description]
     * @var \AMQPConnection
     */
    private $connection;

    /**
     * [$name description]
     * @var string
     */
    private $name;

    /**
     * @param AMQPConnection $connection Connection from which to get channels
     *                                   and declare and use resources with.
     */
    public function __construct(AMQPConnection $connection)
    {
        $this->connection = $connection;
    }

    /**
     * [getName description]
     * @return [type] [description]
     */
    private function getName()
    {
        if (empty($this->name)) {
            throw new InvalidPropertyException('An invalid name has been set');
        }

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
