<?php
namespace Kastilyo\RabbitHole\Spec;

use Eloquent\Liberator\Liberator;
use kahlan\plugin\Stub;
use kahlan\Arg;
use Kastilyo\RabbitHole\Exceptions\ImplementationException;
use Kastilyo\RabbitHole\AMQP\QueueBuilder;
use Kastilyo\RabbitHole\AMQP\ExchangeBuilder;

describe('SubscriberTrait + SubscriberInterface', function () {
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
    });

    describe('->consume', function () {
        context('Building the exchange', function () {
            it('sets the exchange name and then builds', function () {
                expect($this->exchange_builder_spy)
                    ->toReceive('setName')
                    ->with(Subscriber::getExchangeName());
                expect($this->exchange_builder_spy)
                    ->toReceiveNext('get');
                $this->subscriber->consume();
            });
        });

        context('Building the queue', function () {
            it('sets the queue name', function () {
                expect($this->queue_builder_spy)
                    ->toReceive('setName')
                    ->with(Subscriber::getQueueName());
                $this->subscriber->consume();
            });

            it('sets the exchange name', function () {
                expect($this->queue_builder_spy)
                    ->toReceive('setExchangeName')
                    ->with(Subscriber::getExchangeName());
                $this->subscriber->consume();
            });

            it('sets the binding keys', function () {
                expect($this->queue_builder_spy)
                    ->toReceive('setBindingKeys')
                    ->with(Subscriber::getBindingKeys());
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
                    ->with(Subscriber::getQueueName());

                expect($this->queue_builder_spy)
                    ->toReceiveNext('setExchangeName')
                    ->with(Subscriber::getExchangeName());

                expect($this->queue_builder_spy)
                    ->toReceiveNext('setBindingKeys')
                    ->with(Subscriber::getBindingKeys());

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
                Stub::on(Subscriber::class)
                    ->method('::getExchangeName');
                $this->expectImplementationException();
            });

            it('throws an exception when the queue name is missing', function () {
                Stub::on(Subscriber::class)
                    ->method('::getQueueName');
                $this->expectImplementationException();
            });

            it('throws an exception when the binding keys are missing', function () {
                Stub::on(Subscriber::class)
                    ->method('::getBindingKeys');
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
