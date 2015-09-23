# rabbit-hole

This is a RabbitMQ library heavily inspired by Hutch and may be considered a misuse and abuse of PHP.

## Usage

``` php
use Kastilyo\RabbitHole\Subscriber;
use Kastilyo\RabbitHole\Subscring;

class SomeSubscriber implements Subscribing
{
    use Subscriber;

    protected static $amqp_exchange_name = 'some_exchange';
    protected static $amqp_queue_name = 'some_queue';
    protected static $amqp_binding_keys = ['some.binding.key', 'another.binding.key'];

    public function __construct(AMQPConnection $amqp_connection)
    {
        $this->amqp_connection = $amqp_connection;
    }

    public function processMessage(AMQPEnvelope $envelope)
    {
        echo $envelope->getBody(), PHP_EOL;
        static::$amqp_queue->ack($envelope->getDeliveryTag());
    }
}
```

```php
$subscriber = new Kastilyo\RabbitHole\SomeSubscriber($amqp_connection);
$subscriber->consume();
```

## Testing

``` bash
$ ./vendor/bin/kahlan
```

## Credits

- [kastilyo][https://github.com/kastilyo]
- [burntbrowniez][https://github.com/burntbrowniez]

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
