<?php
namespace Kastilyo\RabbitHole\Spec;

use Eloquent\Liberator\Liberator;
use kahlan\plugin\Stub;
use kahlan\Arg;
use Kastilyo\RabbitHole\ImplementationException;
use Kastilyo\RabbitHole\AMQP\QueueBuilder;
use Kastilyo\RabbitHole\AMQP\ExchangeBuilder;

describe('Subscriber', function () {
    beforeEach(function () {
        $this->amqp_connection = Helper::getAMQPConnection();
        $this->subscriber = new Subscriber($this->amqp_connection);
    });

    describe('->consume', function () {
        beforeEach(function () {
            Stub::on(QueueBuilder::class)
                ->method('build')
                ->andReturn(($this->amqp_queue = Helper::getAMQPQueue()));

            Stub::on(ExchangeBuilder::class)
                ->method('build');

            $this->queue_builder_spy = Stub::create([
                'extends' => QueueBuilder::class,
                'methods' => ['__construct']
            ]);

            $this->exchange_builder_spy = Stub::create([
                'extends' => ExchangeBuilder::class,
                'methods' => ['__construct']
            ]);

            Stub::on($this->amqp_queue)
                ->method('consume');

            $this->subscriber->setQueueBuilder($this->queue_builder_spy);
            $this->subscriber->setExchangeBuilder($this->exchange_builder_spy);
        });

        context('Building the exchange', function () {
            it('sets the exchange name and then builds', function () {
                expect($this->exchange_builder_spy)
                    ->toReceive('setName')
                    ->with(Subscriber::getExchangeName());
                expect($this->exchange_builder_spy)
                    ->toReceiveNext('build');
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
                    ->toReceive('build');
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
                    ->toReceiveNext('build');

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
            expect($this->amqp_queue)
                ->toReceive('consume')
                ->with([$this->subscriber, 'processMessage']);
            $this->subscriber->consume();
        });
    });
});
