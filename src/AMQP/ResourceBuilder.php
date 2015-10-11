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
     * Connection from which to get AMQPChannels and to declare and use resources
     * @var \AMQPConnection
     */
    private $connection;

    /**
     * Name of the resource
     * @var string
     */
    private $name;

    /**
     * @param \AMQPConnection $connection
     */
    public function __construct(AMQPConnection $connection)
    {
        $this->connection = $connection;
    }

    /**
     * Returns the name currently set on the builder
     * @throws \Kastilyo\RabbitHole\Exceptions\InvalidPropertyException
     * @return string
     */
    private function getName()
    {
        if (empty($this->name)) {
            throw new InvalidPropertyException('An invalid name has been set');
        }

        return $this->name;
    }

    /**
     * Sets the resource name on the builder
     * @param string $name
     * @return $this
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * Returns an AMQPConnection instance, ensuring that it is connected
     * @return \AMQPConnection
     */
    private function getConnection()
    {
        if (!$this->connection->isConnected()) {
            $this->connection->connect();
        }

        return $this->connection;
    }

    /**
     * @return \AMQPChannel
     */
    private function getChannel()
    {
        return new AMQPChannel($this->getConnection());
    }
}
