<?php

namespace Swarrot\Broker\MessageProvider;

use PhpAmqpLib\Message\AMQPMessage;
use Prophecy\Prophet;
use Swarrot\Broker\Message;

class PhpAmqpLibMessageProviderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Prophet
     */
    protected $prophet;

    protected function setUp()
    {
        $this->prophet = new Prophet;
    }

    protected function tearDown()
    {
        $this->prophet->checkPredictions();
    }

    public function test_get_with_messages_in_queue_return_message()
    {
        $channel     = $this->prophet->prophesize('PhpAmqpLib\Channel\AMQPChannel');
        $amqpMessage = new AMQPMessage('foobar');

        $amqpMessage->delivery_info['delivery_tag'] =  '1';

        $channel->basic_get('my_queue')->shouldBeCalled()->willReturn($amqpMessage);

        $provider = new PhpAmqpLibMessageProvider($channel->reveal(), 'my_queue');
        $message  = $provider->get();

        $this->assertInstanceOf('Swarrot\Broker\Message', $message);
    }

    public function test_get_without_messages_in_queue_return_null()
    {
        $channel = $this->prophet->prophesize('PhpAmqpLib\Channel\AMQPChannel');

        $channel->basic_get('my_queue')->shouldBeCalled()->willReturn(null);

        $provider = new PhpAmqpLibMessageProvider($channel->reveal(), 'my_queue');
        $message  = $provider->get();

        $this->assertNull($message);
    }

    public function test_ack()
    {
        $channel = $this->prophet->prophesize('PhpAmqpLib\Channel\AMQPChannel');

        $channel->basic_ack('5')->shouldBeCalled();

        $provider = new PhpAmqpLibMessageProvider($channel->reveal(), 'my_queue');

        $provider->ack(new Message('foobar', array(), 5));
    }

    public function test_nack()
    {
        $channel = $this->prophet->prophesize('PhpAmqpLib\Channel\AMQPChannel');

        $channel->basic_nack('5', false, true)->shouldBeCalled();

        $provider = new PhpAmqpLibMessageProvider($channel->reveal(), 'my_queue');

        $provider->nack(new Message('foobar', array(), 5), true);
    }

    public function test_get_name()
    {
        $channel = $this->prophet->prophesize('PhpAmqpLib\Channel\AMQPChannel');
        $provider = new PhpAmqpLibMessageProvider($channel->reveal(), 'foobar');

        $this->assertEquals('foobar', $provider->getQueueName());
    }
}
