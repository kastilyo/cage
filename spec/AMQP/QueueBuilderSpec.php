<?php
namespace Kastilyo\RabbitHole\Spec;

use kahlan\plugin\Stub;
use kahlan\Arg;
use Kastilyo\RabbitHole\Exceptions\InvalidPropertyException;
use Kastilyo\RabbitHole\AMQP\QueueBuilder;

describe('QueueBuilder', function () {
    beforeEach(function () {
        Helper::initializeAMQPStubs();
        $this->connection = Helper::getAMQPConnection();
        $this->queue_builder = new QueueBuilder($this->connection);
    });

    describe('->build', function () {
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
            $this->queue_builder->build();
        });

        it("doesn't make the connection if it's been made already", function () {
            Stub::on($this->connection)
                ->method('isConnected')
                ->andReturn(true);
            expect($this->connection)
                ->not
                ->toReceive('connect');
            $this->queue_builder->build();
        });

        context('Queue declaration', function () {
            it('gets a channel from the connection', function () {
                expect('AMQPChannel')
                    ->toReceive('__construct')
                    ->with($this->connection);
                $this->queue_builder->build();
            });

            it('sets the name', function () {
                expect('AMQPQueue')
                    ->toReceive('setName')
                    ->with($this->queue_name);
                $this->queue_builder->build();
            });

            it('sets it as durable', function () {
                expect('AMQPQueue')
                    ->toReceive('setFlags')
                    ->with(AMQP_DURABLE);
                $this->queue_builder->build();
            });

            it('performs the declaration', function () {
                expect('AMQPQueue')
                    ->toReceive('declareQueue');
                $this->queue_builder->build();
            });
        });

        context('Binding', function () {
            it('binds with the exchange name', function () {
                expect('AMQPQueue')
                    ->toReceive('bind')
                    ->with($this->exchange_name, Arg::notToBeNull());
                $this->queue_builder->build();
            });

            it('binds with the binding keys', function () {
                foreach ($this->binding_keys as $binding_key) {
                    expect('AMQPQueue')
                        ->toReceive('bind')
                        ->with(Arg::notToBeNull(), $binding_key);
                }
                $this->queue_builder->build();
            });
        });

        context('Exceptional behavior', function () {
            beforeEach(function () {
                $this->expectInvalidPropertyException = function () {
                    expect(function () {
                        $this->queue_builder->build();
                    })->toThrow(new InvalidPropertyException);
                };
            });

            it("throws an exception when a name hasn't been set", function () {
                $this->queue_builder->setName(null);
                $this->expectInvalidPropertyException();
            });

            it("throws an exception when binding keys haven't been set", function () {
                $this->queue_builder->setBindingKeys(null);
                $this->expectInvalidPropertyException();
            });

            it("throws an exception when non-array binding keys have been set", function () {
                $this->queue_builder->setBindingKeys('some_binding_key,another_binding_key');
                $this->expectInvalidPropertyException();
            });

            it("throws an exception when an exchange name hasn't been set", function () {
                $this->queue_builder->setExchangeName(null);
                $this->expectInvalidPropertyException();
            });
        });
    });
});
