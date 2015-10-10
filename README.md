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

## Testing

``` bash
$ ./vendor/bin/kahlan
```

## Credits

- [kastilyo][https://github.com/kastilyo]
- [burntbrowniez][https://github.com/burntbrowniez]

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
