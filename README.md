# rabbit-hole

[![Code Climate](https://codeclimate.com/repos/561a04ca69568021270083bd/badges/14463fc15cd68fefb706/gpa.svg)](https://codeclimate.com/repos/561a04ca69568021270083bd/feed)

[![Test Coverage](https://codeclimate.com/repos/561a04ca69568021270083bd/badges/14463fc15cd68fefb706/coverage.svg)](https://codeclimate.com/repos/561a04ca69568021270083bd/coverage)

This is a RabbitMQ library heavily inspired by [Hutch](https://github.com/gocardless/hutch). So far, I've only come up with basic subscribing mechanisms. I would like to expand on this by providing support for batch processing. I'm planning to add publishing mechanisms and then support for RPC patterns. And finally, I would like to add running mechanisms ie. managing and running multiple subscribers of varying configurations simultaneously which will likely be the most difficult and at which point it would be considered highly experimental and perhaps an abuse and misuse of PHP as it's not typically intended for such things.

RabbitHole provides mechanisms for consuming messages from RabbitMQ exchanges without the low-level boilerplate. The tradeoff for that is that this library operates under several assumptions as to how RabbitMQ is being used:
* Exchanges created by RabbitHole subscribers are durable topic exchanges.
* Similarly, queues created by RabbitHole subscribers will be durable.

Thus, RabbitHole is intended for users who want to minimize data loss.

## Usage

A subscriber class made with RabbitHole must implement the SubscriberInterface and use the SubscriberTrait. It must set a property of `$amqp_connection` to an AMQPConnection instance. Lastly, it must acknowledge messages processed by its `processMessage` implemenation.

Consider the following example Subscriber implementation taken directly from the test source.

``` php
namespace Kastilyo\RabbitHole\Spec;

use AMQPConnection;
use AMQPEnvelope;
use Kastilyo\RabbitHole\Subscriber\SubscriberTrait;
use Kastilyo\RabbitHole\Subscriber\SubscriberInterface;

/**
 * This is a test SubscriberInterface implementation that mixes in SubscriberTrait.
 * It represents the intended use of this library.
 */
class Subscriber implements SubscriberInterface
{
    use SubscriberTrait;

    public function __construct(AMQPConnection $amqp_connection)
    {
        $this->setAMQPConnection($amqp_connection);
    }

    public function getExchangeName()
    {
        return 'test_exchange';
    }

    public function getQueueName()
    {
        return 'test_queue';
    }

    public function getBindingKeys()
    {
        return ['test.info'];
    }

    public function processMessage(AMQPEnvelope $amqp_envelope)
    {
        echo $amqp_envelope->getBody(), PHP_EOL;
        $this->acknowledgeMessage($amqp_envelope);
    }
}
```

This class happens to get an `AMQPConnection` instance through constructor injection sets its `$amqp_connection` property with the `setAMQPConnection` method provided by the `SubscriberTrait`.

This class implements `SubscriberInterface` which includes several static getter methods.

`getExchangeName` should return the name of the durable topic exchange to declare. The name of the exchange this class will declare is 'test_exchange'.

`getQueueName` should return the name of the durable queue to declare. The name this class will read from is 'test_queue'.

`getBindingKeys` should return an array of routing keys which may contain the '*' or '#' wildcards. This class only binds 'test.info' to the queue.

Also part of the `SubscriberInterface` is the `processMessage` instance method which is the callback for messages in the queue. This implementation simply prints the message body with a newline and then acknowledges the message.

To actually consume messages, the `consume` method provided by the `SubscriberTrait` must be called on the `Subscriber` instance. It will declare the exchange and queue, and begin consuming messages according to the above.

```php
$amqp_connection = new AMQPConnection([
    'host' => 'localhost',
    'port' => 5672,
    'login' => 'guest',
    'password' => 'guest',
]);
$subscriber = new Kastilyo\RabbitHole\Spec\Subscriber($amqp_connection);
$subscriber->consume();
```

If you have docker and would like to test this out, you can clone this repo and install all dependencies, including dev ones, with `composer install`.

Run `docker run -d -p 15672:15672 -p 5672:5672 rabbitmq:3-management` which will run a RabbitMQ docker container with the management installed with the default credentials of 'guest'/'guest', forwarding ports 15672 and 5672, in the background. The management web interface should be accessible from localhost:15672 and any services running on the host machine should be able to interface with the container through localhost:5672.

Begin a terminal session and then run `php dev/consumer.php` from the root of the project directory which will wait for messages to come in from the queue.

From the rabbitmq management plugin web interface, there should both be a 'test_exchange' exchange and 'test_queue' queue which has the routing key, 'test.info', bound to it.

From the exchange panel, select 'test_exchange' and publish a message with a routing key of 'test.info' and some identifiable content.

The terminal session running `php dev/consumer.php` should have produced the output of the content set in the published message. Graphs in the management interface should also indicate that a message was published, consumed and acknowledged.

In the future, I'll add a Dockerfile to the dev directory to deploy the repo itself to run in a container.

## Dependencies

PHP >= 5.6.0

This library was built using the [calcinai/php-amqplib-bridge](https://github.com/calcinai/php-amqplib-bridge) package for the possibility of compatibility with both the [videlalvaro/php-amqplib](https://github.com/videlalvaro/php-amqplib) package and the PECL AMQP extension. However, I've never run any of the code with the PECL extension myself and can't confirm that it works but theorertically, it should.

## Style and Naming Conventions

For the coding style, I tried to follow PSR coding style guidelines. When it came to naming conventions, I suffixed any traits with 'Trait' and interfaces with 'Interface'. Any other file in the 'src' directory should be a class. I also used underscored names for variables and properties if only to easily visually distinguish them from methods. There are no functions but if there were, I'd underscore them as well.

## Testing
I used the awesome [kahlan](https://github.com/crysalead/kahlan) testing framework.

``` bash
$ ./vendor/bin/kahlan
```

## Credits

- [markalexandercastillo](https://github.com/markalexandercastillo)
- [burntbrowniez](https://github.com/burntbrowniez)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
