<?php
use kahlan\plugin\Stub;
use kahlan\Arg;
use Kastilyo\RabbitHole\AMQP\QueueBuilder;
use Kastilyo\RabbitHole\AMQP\ExchangeBuilder;
use Kastilyo\RabbitHole\Subscribing;
use Kastilyo\RabbitHole\Spec\SpecSubscriber;
use Kastilyo\RabbitHole\Spec\Helper;

/**
 * This attempts to test the Subscriber trait in isolation as much as possible,
 * setting properties that should be set by concretions of Subscribing via
 * reflection
 */
describe('Subscriber', function () {
    beforeEach(function () {
        $this->amqp_connection = Helper::getAMQPConnection();
        $this->subscriber = new SpecSubscriber($this->amqp_connection);
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
                    ->with(SpecSubscriber::getExchangeName());
                expect($this->exchange_builder_spy)
                    ->toReceiveNext('build');
                $this->subscriber->consume();
            });

        });

        context('Building the queue', function () {
            it('sets the queue name', function () {
                expect($this->queue_builder_spy)
                    ->toReceive('setName')
                    ->with(SpecSubscriber::getQueueName());
                $this->subscriber->consume();
            });

            it('sets the exchange name', function () {
                expect($this->queue_builder_spy)
                    ->toReceive('setExchangeName')
                    ->with(SpecSubscriber::getExchangeName());
                $this->subscriber->consume();
            });

            it('sets the binding keys', function () {
                expect($this->queue_builder_spy)
                    ->toReceive('setBindingKeys')
                    ->with(SpecSubscriber::getBindingKeys());
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
                    ->with(SpecSubscriber::getQueueName());

                expect($this->queue_builder_spy)
                    ->toReceiveNext('setExchangeName')
                    ->with(SpecSubscriber::getExchangeName());

                expect($this->queue_builder_spy)
                    ->toReceiveNext('setBindingKeys')
                    ->with(SpecSubscriber::getBindingKeys());

                expect($this->queue_builder_spy)
                    ->toReceiveNext('build');

                $this->subscriber->consume();
            });
        });
    });
});
