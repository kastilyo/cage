# rabbit-hole

This is a RabbitMQ library heavily inspired by Hutch and may be considered a misuse and abuse of PHP.

## Usage

``` php
namespace Kastilyo\RabbitHole\Spec;

use AMQPConnection;
use AMQPEnvelope;
use Kastilyo\RabbitHole\SubscriberTrait;
use Kastilyo\RabbitHole\SubscriberInterface;

/**
 * This is a test SubscriberInterface implementation that mixes in SubscriberTrait.
 * It represents the intended  use of this library.
 */
class Subscriber implements SubscriberInterface
{
    use SubscriberTrait;

    public function __construct(AMQPConnection $amqp_connection)
    {
        $this->amqp_connection = $amqp_connection;
    }

    public static function getExchangeName()
    {
        return 'test_exchange';
    }

    public static function getQueueName()
    {
        return 'test_queue';
    }

    public static function getBindingKeys()
    {
        return ['test.info'];
    }

    public function processMessage(AMQPEnvelope $amqp_envelope)
    {
        echo $amqp_envelope->getBody(), PHP_EOL;
        $this->getQueue()->ack($amqp_envelope->getDeliveryTag());
    }
}
```

```php
$subscriber = new Kastilyo\RabbitHole\Spec\Subscriber($amqp_connection);
$subscriber->consume();
```

If you have docker and would like to test this out, you can clone this repo and install all dependencies (including dev ones).

Run `docker run -d -p 15672:15672 -p 5672:5672 rabbitmq:3-management` which will run a RabbitMQ docker container with the management installed with the default credentials, forwarding ports 15672 and 5672, in the background.

Then run `php dev/consumer.php` from the root of the project directory which will wait for messages to come in from the queue.

From the rabbitmq management plugin web interface, there should both be a 'test_exchange' exchange and 'test_queue' queue which has the routing key, 'test.info' bound to it.

From the exchange panel, select 'test_exchange' and publish a message with some identifiable content.

The terminal session running `php dev/consumer.php` should have produced the output of the content set in the published message. Graphs in the management interface should indicate that a message was published, consumed and acknowledged.

## Testing

``` bash
$ ./vendor/bin/kahlan
```

## Credits

- [kastilyo](https://github.com/kastilyo)
- [burntbrowniez](https://github.com/burntbrowniez  )

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
