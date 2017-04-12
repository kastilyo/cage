<?php
namespace Kastilyo\RabbitHole\Spec;

use Kahlan\Arg;
use AMQPEnvelope;
use Eloquent\Liberator\Liberator;
use Kastilyo\RabbitHole\Subscriber\SubscriberInterface;
use Kastilyo\RabbitHole\Subscriber\SubscriberTrait;
use Kastilyo\RabbitHole\Exceptions\ImplementationException;
use Kastilyo\RabbitHole\AMQP\QueueBuilder;
use Kastilyo\RabbitHole\AMQP\ExchangeBuilder;

describe('BatchSubscriber', function () {
    beforeEach(function () {
        $this->amqp_connection = Helper::getAMQPConnection();
        $this->batch_subscriber = new BatchSubscriber($this->amqp_connection);
    });

    describe('->processMessage', function () {
        beforeEach(function () {
            $this->envelopes = array_map(
                function ($ignore) {
                    $envelope = Helper::getAMQPEnvelope();
                    allow($envelope)
                        ->toReceive('getDeliveryTag')
                        ->andReturn('some_delivery_tag');
                    return $envelope;
                },
                array_fill(0, $this->batch_subscriber->getBatchCount(), null)
            );

            allow($this->batch_subscriber)->toReceive('acknowledgeMessage');

            // process three ahead of time
            $this->batch_subscriber->processMessage($this->envelopes[0]);
            $this->batch_subscriber->processMessage($this->envelopes[1]);
            $this->batch_subscriber->processMessage($this->envelopes[2]);
        });

        it("doesn't acknowledge messages if the limit hasn't been reached", function () {
            array_map(
                function ($envelope) {
                    expect($this->batch_subscriber)
                        ->not
                        ->toReceive('acknowledgeMessage')
                        ->with($envelope);
                },
                $this->envelopes
            );
            $this->batch_subscriber->processMessage($this->envelopes[3]);
        });

        it('acknowledges messages if the limit has been reached', function () {
            array_map(
                function ($envelope) {
                    expect($this->batch_subscriber)
                        ->toReceive('acknowledgeMessage')
                        ->with($envelope);
                },
                $this->envelopes
            );
            $this->batch_subscriber->processMessage($this->envelopes[3]);
            // should hit limit after this call
            $this->batch_subscriber->processMessage($this->envelopes[4]);
        });

    });
});
