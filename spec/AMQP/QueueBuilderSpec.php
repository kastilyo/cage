<?php
namespace Kastilyo\RabbitHole\Spec;

use Kahlan\Arg;
use Kastilyo\RabbitHole\Exceptions\InvalidPropertyException;
use Kastilyo\RabbitHole\AMQP\QueueBuilder;

describe('QueueBuilder', function () {
    beforeEach(function () {
        Helper::initializeAMQPStubs();
        $this->connection = Helper::getAMQPConnection();
        $this->channel = Helper::getAMQPChannel();
        allow('AMQPQueue')
            ->toReceive('getChannel')
            ->andReturn($this->channel);
        $this->queue_builder = new QueueBuilder($this->connection);
    });

    describe('->get', function () {
        beforeEach(function () {
            $this->queue_name = 'some_queue_name';
            $this->exchange_name = 'some_exchange_name';
            $this->binding_keys = ['some_binding_key', 'another_binding_key'];

            $this->queue_builder
                ->setName($this->queue_name)
                ->setExchangeName($this->exchange_name)
                ->setBindingKeys($this->binding_keys);
        });

        it("makes the connection if it hasn't been made yet", function () {
            expect($this->connection)
                ->toReceive('connect');
            $this->queue_builder->get();
        });

        it("doesn't make the connection if it's been made already", function () {
            allow($this->connection)
                ->toReceive('isConnected')
                ->andReturn(true);
            expect($this->connection)
                ->not
                ->toReceive('connect');
            $this->queue_builder->get();
        });

        context('Queue declaration', function () {
            it('gets a channel from the connection', function () {
                expect('AMQPChannel')
                    ->toReceive('__construct')
                    ->with($this->connection);
                $this->queue_builder->get();
            });

            it('sets qos prefetch count to 1 by default', function () {
                expect($this->channel)
                    ->toReceive('qos')
                    ->with(0, 1);
                $this->queue_builder->get();
            });

            it('sets the name', function () {
                expect('AMQPQueue')
                    ->toReceive('setName')
                    ->with($this->queue_name);
                $this->queue_builder->get();
            });

            it('sets it as durable', function () {
                expect('AMQPQueue')
                    ->toReceive('setFlags')
                    ->with(AMQP_DURABLE);
                $this->queue_builder->get();
            });

            it('performs the declaration', function () {
                expect('AMQPQueue')
                    ->toReceive('declareQueue');
                $this->queue_builder->get();
            });
        });

        context('Binding', function () {
            it('binds with the exchange name', function () {
                expect('AMQPQueue')
                    ->toReceive('bind')
                    ->with($this->exchange_name, Arg::toBeAny());
                $this->queue_builder->get();
            });

            it('binds with the binding keys', function () {
                foreach ($this->binding_keys as $binding_key) {
                    expect('AMQPQueue')
                        ->toReceive('bind')
                        ->with(Arg::toBeAny(), $binding_key);
                }
                $this->queue_builder->get();
            });
        });

        context('Exceptional behavior', function () {
            beforeEach(function () {
                $this->expectInvalidPropertyException = function () {
                    expect(function () {
                        $this->queue_builder->get();
                    })->toThrow(new InvalidPropertyException);
                };
            });

            it("throws an exception when a name hasn't been set", function () {
                $this->queue_builder->setName(null);
                $this->expectInvalidPropertyException();
            });

            it("throws an exception when binding keys haven't been set", function () {
                $this->queue_builder->setBindingKeys([]);
                $this->expectInvalidPropertyException();
            });

            it("throws an exception when an exchange name hasn't been set", function () {
                $this->queue_builder->setExchangeName(null);
                $this->expectInvalidPropertyException();
            });

            it("throws an exception when a prefetch count of 0 has been set", function () {
                $this->queue_builder->setPrefetchCount(0);
                $this->expectInvalidPropertyException();
            });
        });
    });
});
