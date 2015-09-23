<?php
use kahlan\plugin\Stub;
use kahlan\Arg;
use Kastilyo\RabbitHole\AMQP\QueueBuilder;

describe('QueueBuilder', function () {
    beforeEach(function () {
        $this->connection = Stub::create([
            'extends' => 'AMQPConnection',
            'methods' => ['__construct', 'connect', 'isConnected'],
        ]);

        Stub::on('AMQPChannel')->method('__construct');
        Stub::on('AMQPQueue')->method('__construct');
        Stub::on('AMQPQueue')->method('declareQueue');
        Stub::on('AMQPQueue')->method('bind');

        $this->queue_builder = new QueueBuilder($this->connection);
    });

    describe('->build', function () {
        beforeEach(function () {
            $this->queue_name = 'some_queue_name';
            $this->exchange_name = 'some_exchange_name';
            $this->flags = 'some_flags';
            $this->binding_keys = ['some_binding_key', 'another_binding_key'];

            $this->queue_builder
                ->setName($this->queue_name)
                ->setExchangeName($this->exchange_name)
                ->setFlags($this->flags)
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
                    ->with($this->flags);
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
    });
});
