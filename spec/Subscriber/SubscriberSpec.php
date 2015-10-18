<?php
namespace Kastilyo\RabbitHole\Spec;

use kahlan\plugin\Stub;
use kahlan\Arg;
use AMQPEnvelope;
use Eloquent\Liberator\Liberator;
use Kastilyo\RabbitHole\Subscriber\SubscriberInterface;
use Kastilyo\RabbitHole\Subscriber\SubscriberTrait;
use Kastilyo\RabbitHole\Exceptions\ImplementationException;
use Kastilyo\RabbitHole\AMQP\QueueBuilder;
use Kastilyo\RabbitHole\AMQP\ExchangeBuilder;

describe('Subscriber', function () {
    beforeEach(function () {
        $this->amqp_connection = Helper::getAMQPConnection();
        $this->subscriber = new Subscriber($this->amqp_connection);

        Stub::on(QueueBuilder::class)
            ->method('get')
            ->andReturn(($this->amqp_queue_spy = Helper::getAMQPQueue()));

        Stub::on(ExchangeBuilder::class)
            ->method('get');

        $this->queue_builder_spy = Stub::create([
            'extends' => QueueBuilder::class,
            'methods' => ['__construct']
        ]);

        $this->exchange_builder_spy = Stub::create([
            'extends' => ExchangeBuilder::class,
            'methods' => ['__construct']
        ]);

        Stub::on($this->amqp_queue_spy)
            ->method('consume');

        $this->subscriber->setQueueBuilder($this->queue_builder_spy);
        $this->subscriber->setExchangeBuilder($this->exchange_builder_spy);
        $this->subscriber->setAMQPConnection($this->amqp_connection);
    });

    describe('getBatchCount', function () {
        it('has a default implementation of 1', function () {
            expect($this->subscriber->getBatchCount())->toBe(1);
        });
    });

    describe('->consume', function () {
        context('Building the exchange', function () {
            it('sets the exchange name and then builds', function () {
                expect($this->exchange_builder_spy)
                    ->toReceive('setName')
                    ->with($this->subscriber->getExchangeName());
                expect($this->exchange_builder_spy)
                    ->toReceiveNext('get');
                $this->subscriber->consume();
            });
        });

        context('Building the queue', function () {
            it('sets the batch count', function () {
                expect($this->queue_builder_spy)
                    ->toReceive('setPrefetchCount')
                    ->with($this->subscriber->getBatchCount());
                $this->subscriber->consume();
            });

            it('sets the queue name', function () {
                expect($this->queue_builder_spy)
                    ->toReceive('setName')
                    ->with($this->subscriber->getQueueName());
                $this->subscriber->consume();
            });

            it('sets the exchange name', function () {
                expect($this->queue_builder_spy)
                    ->toReceive('setExchangeName')
                    ->with($this->subscriber->getExchangeName());
                $this->subscriber->consume();
            });

            it('sets the binding keys', function () {
                expect($this->queue_builder_spy)
                    ->toReceive('setBindingKeys')
                    ->with($this->subscriber->getBindingKeys());
                $this->subscriber->consume();
            });

            it('builds the queue', function () {
                expect($this->queue_builder_spy)
                    ->toReceive('get');
                $this->subscriber->consume();
            });

            it('calls the above methods in that order', function () {
                expect($this->queue_builder_spy)
                    ->toReceive('setName')
                    ->with($this->subscriber->getQueueName());

                expect($this->queue_builder_spy)
                    ->toReceiveNext('setExchangeName')
                    ->with($this->subscriber->getExchangeName());

                expect($this->queue_builder_spy)
                    ->toReceiveNext('setBindingKeys')
                    ->with($this->subscriber->getBindingKeys());

                expect($this->queue_builder_spy)
                    ->toReceive('setPrefetchCount')
                    ->with($this->subscriber->getBatchCount());

                expect($this->queue_builder_spy)
                    ->toReceiveNext('get');

                $this->subscriber->consume();
            });
        });

        context('Exceptional behavior', function () {
            beforeEach(function () {
                $this->expectImplementationException = function () {
                    expect(function () {
                        $this->subscriber->consume();
                    })->toThrow(new ImplementationException);
                };
            });

            it('throws an exception when the exchange name is missing', function () {
                Stub::on($this->subscriber)
                    ->method('getExchangeName');
                $this->expectImplementationException();
            });

            it('throws an exception when the queue name is missing', function () {
                Stub::on($this->subscriber)
                    ->method('getQueueName');
                $this->expectImplementationException();
            });

            it('throws an exception when the binding keys are missing', function () {
                Stub::on($this->subscriber)
                    ->method('getBindingKeys');
                $this->expectImplementationException();
            });

            it('throws an exception when batch count is an empty value', function () {
                Stub::on($this->subscriber)
                    ->method('getBatchCount');
                $this->expectImplementationException();
            });

            it('throws an exception when batch count is non-integer value', function () {
                Stub::on($this->subscriber)
                    ->method('getBatchCount', function () {
                        return 'asdf';
                    });
                $this->expectImplementationException();
            });
        });

        it('sets processMessage as the callback', function () {
            expect($this->amqp_queue_spy)
                ->toReceive('consume')
                ->with([$this->subscriber, 'processMessage']);
            $this->subscriber->consume();
        });
    });

    describe('->acknowledgeMessage', function () {
        it("calls ack on the queue, passing in the message's delivery tag", function () {
            $expected_delivery_tag = 'some_delivery_tag';
            $message_spy = Helper::getAMQPEnvelope();
            Stub::on($message_spy)
                ->method('getDeliveryTag')
                ->andReturn($expected_delivery_tag);

            Stub::on($this->amqp_queue_spy)
                ->method('ack', function ($delivery_tag, $flags = AMQP_NOPARAM) {});

            expect($this->amqp_queue_spy)
                ->toReceive('ack')
                ->with($expected_delivery_tag, Arg::toBeAny());

            $this->subscriber->acknowledgeMessage($message_spy);
        });
    });
});
