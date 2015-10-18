<?php
namespace Kastilyo\RabbitHole\Spec;

use AMQPConnection;
use AMQPEnvelope;
use Kastilyo\RabbitHole\Subscriber\SubscriberTrait;
use Kastilyo\RabbitHole\Subscriber\SubscriberInterface;

/**
 * This is a demo Subscriber that implements batch processing using some of the
 * methods provided by SubscriberTrait
 */
class BatchSubscriber extends Subscriber
{
    /**
     * First, have a batch count greater than 1
     */
    public function getBatchCount()
    {
        return 5;
    }

    public function processMessage(AMQPEnvelope $amqp_envelope)
    {
        // Second, collect messages with pushMessage
        $this->pushMessage($amqp_envelope);

        if ($this->hasReachedBatchCount()) {
            // perform batch operation once limit has been reached
            // some other per-message operations may happen in between but that's been
            // omitted as this code is purely for demonstration
            echo implode('', array_map(
                function ($envelope) {
                    return $envelope->getBody();
                },
                $this->amqp_envelopes
            ));

            // Finally, acknowledge all accumulated messages with acknowledgeMessage
            // That assumes that everything has gone well. Other implementations
            // may need more complicated flows that incorporate nacking
            $this->acknowledgeMessages();
        }
    }
}
