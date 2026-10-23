<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types = 1);

namespace SprykerTest\Client\RabbitMq\Model\Manager;

use Codeception\Test\Unit;
use Generated\Shared\Transfer\QueueSendMessageTransfer;
use Generated\Shared\Transfer\RabbitMqOptionTransfer;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use Spryker\Client\RabbitMq\Model\Connection\ConnectionManagerInterface;
use Spryker\Client\RabbitMq\Model\Helper\QueueEstablishmentHelper;
use Spryker\Client\RabbitMq\Model\Manager\Manager;
use Spryker\Client\RabbitMq\Model\Publisher\Publisher;
use Spryker\Client\RabbitMq\RabbitMqConfig;

/**
 * Auto-generated group annotations
 *
 * @group SprykerTest
 * @group Client
 * @group RabbitMq
 * @group Model
 * @group Manager
 * @group ManagerSendMessageTest
 * Add your own group annotations below this line
 */
class ManagerSendMessageTest extends Unit
{
    /**
     * @var \PhpAmqpLib\Connection\AMQPStreamConnection
     */
    protected AMQPStreamConnection $streamConnection;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->streamConnection = new AMQPStreamConnection(
            getenv('SPRYKER_BROKER_HOST') ?: 'broker',
            (int)(getenv('SPRYKER_BROKER_PORT') ?: 5672),
            getenv('SPRYKER_BROKER_USERNAME') ?: 'spryker',
            getenv('SPRYKER_BROKER_PASSWORD') ?: 'secret',
            getenv('SPRYKER_BROKER_NAMESPACE') ?: 'eu-docker',
        );
    }

    /**
     * @return void
     */
    protected function tearDown(): void
    {
        $this->streamConnection->close();

        parent::tearDown();
    }

    /**
     * Reproduces the reported bug: a queue created through `Manager::createQueue()` (the ad-hoc,
     * non-bootstrap path) only declares an AMQP queue, never a matching exchange. `Publisher::sendMessage()`
     * always passes its first argument to `basic_publish()` as the exchange name, so publishing with the real
     * queue name targets a non-existent exchange. The broker closes the publishing channel with a
     * `NOT_FOUND - no exchange` protocol error instead of delivering the message; php-amqplib only surfaces
     * that error on the channel's next operation, so the assertion runs on a fresh channel.
     *
     * @return void
     */
    public function testSendMessageDeliversMessageWhenPublishedWithQueueNameToPlainlyDeclaredQueue(): void
    {
        // Arrange
        $queueName = sprintf('spy_test_queue_%s', uniqid());
        $publishChannel = $this->streamConnection->channel();

        $manager = new Manager($publishChannel, new QueueEstablishmentHelper());
        $manager->createQueue($queueName, [
            'rabbitMqConsumerOption' => (new RabbitMqOptionTransfer())->setQueueName($queueName),
        ]);

        $connectionManagerMock = $this->createMock(ConnectionManagerInterface::class);
        $connectionManagerMock->method('getDefaultChannel')->willReturn($publishChannel);
        $publisher = new Publisher($connectionManagerMock, new RabbitMqConfig());

        $queueSendMessageTransfer = (new QueueSendMessageTransfer())->setBody('foo');

        // Act
        $publisher->sendMessage($queueName, $queueSendMessageTransfer);

        // Assert
        $cleanupChannel = $this->streamConnection->channel();
        [, $messageCount] = $cleanupChannel->queue_declare($queueName, true);
        $cleanupChannel->queue_delete($queueName);

        $this->assertSame(1, $messageCount, 'Expected the message to have been delivered to the queue.');
    }
}
