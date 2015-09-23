<?php
use kahlan\plugin\Stub;
use kahlan\Arg;
use Eloquent\Liberator\Liberator;
use Kastilyo\RabbitHole\AMQP\QueueBuilder;
use Kastilyo\RabbitHole\Subscribing;
use Kastilyo\RabbitHole\Subscriber;

/**
 * This attempts to test the Subscriber trait in isolation as much as possible,
 * setting properties that should be set by concretions of Subscribing via
 * reflection
 */
describe('Subscriber', function () {
    beforeEach(function () {
        $this->subscriber = Stub::create([
            'uses' => Subscriber::class,
        ]);

        // Using Liberator to mess with visibility
        $this->liberator = Liberator::liberate($this->subscriber);

        $this->liberator->amqp_connection = $this->amqp_connection = Stub::create([
            'extends' => 'AMQPConnection',
            'methods' => ['__construct'],
        ]);

        $this->liberator->amqp_exchange_name = $this->exchange_name = 'some_exchange_name';
        $this->liberator->amqp_queue_name = $this->queue_name = 'some_queue_name';
        $this->liberator->amqp_binding_keys = $this->binding_keys = ['some_binding_key', 'another_binding_key'];

        $this->getKlass = function () {
            return get_class($this->subscriber);
        };
    });

    describe('::getExchangeName', function () {
        it('returns static::$amqp_exchange_name', function () {
            $klass = $this->getKlass();
            expect($klass::getExchangeName())->toBe($this->exchange_name);
        });
    });

    describe('::getQueueName', function () {
        it('returns static::$amqp_queue_name', function () {
            $klass = $this->getKlass();
            expect($klass::getQueueName())->toBe($this->queue_name);
        });
    });

    describe('::getBindingKeys', function () {
        it('returns static::$amqp_binding_keys', function () {
            $klass = $this->getKlass();
            expect($klass::getBindingKeys())->toBe($this->binding_keys);
        });
    });

    describe('->consume', function () {
        beforeEach(function () {
            Stub::on(QueueBuilder::class)
                ->method('build')
                ->andReturn(($this->amqp_queue = Stub::create([
                    'extends' => 'AMQPQueue',
                    'methods' => ['__construct']])
                ));
        });

        context('Building the queue', function () {
            it('instantiates the queue builder, passing in the connection', function () {
                expect(QueueBuilder::class)
                    ->toReceive('__construct')
                    ->with($this->amqp_connection);
                $this->subscriber->consume();
            });

            it('sets the queue name', function () {
                expect(QueueBuilder::class)
                    ->toReceive('setName')
                    ->with($this->queue_name);
                $this->subscriber->consume();
            });

            it('sets the exchange name', function () {
                expect(QueueBuilder::class)
                    ->toReceive('setExchangeName')
                    ->with($this->exchange_name);
                $this->subscriber->consume();
            });

            it('sets the binding keys', function () {
                expect(QueueBuilder::class)
                    ->toReceive('setBindingKeys')
                    ->with($this->binding_keys);
                $this->subscriber->consume();
            });

            it('sets it as durable', function () {
                expect(QueueBuilder::class)
                    ->toReceive('setFlags')
                    ->with(AMQP_DURABLE);
                $this->subscriber->consume();
            });

            it('builds the queue', function () {
                expect(QueueBuilder::class)
                    ->toReceive('build');
                $this->subscriber->consume();
            });
        });
    });
});
