<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerTest\Zed\RabbitMq\Business;

use Codeception\Test\Unit;
use Spryker\Client\Kernel\Container;
use Spryker\Client\Queue\QueueDependencyProvider as SprykerQueueDependencyProvider;
use Spryker\Zed\Queue\QueueDependencyProvider;

/**
 * Auto-generated group annotations
 *
 * @group SprykerTest
 * @group Zed
 * @group RabbitMq
 * @group Business
 * @group Facade
 * @group RabbitMqFacadeTest
 * Add your own group annotations below this line
 */
class RabbitMqFacadeTest extends Unit
{
    /**
     * @var \SprykerTest\Zed\RabbitMq\RabbitMqBusinessTester
     */
    protected $tester;

    /**
     * @uses \Spryker\Shared\Event\EventConstants::EVENT_QUEUE
     *
     * @var string
     */
    protected const EVENT_QUEUE = 'event';

    /**
     * @uses \Spryker\Shared\Publisher\PublisherConfig::PUBLISH_QUEUE
     *
     * @var string
     */
    protected const PUBLISH_QUEUE = 'publish';

    /**
     * @var array<string>
     */
    protected const QUEUE_NAMES = [
        self::EVENT_QUEUE,
        self::PUBLISH_QUEUE,
    ];

    /**
     * @var int
     */
    protected const MESSAGE_AMOUNT = 1000;

    /**
     * @return void
     */
    protected function _before(): void
    {
        $this->tester->setDependency(SprykerQueueDependencyProvider::QUEUE_ADAPTERS, function (Container $container) {
            return [
                $container->getLocator()->rabbitMq()->client()->createQueueAdapter(),
            ];
        });

        $this->tester->setDependency(QueueDependencyProvider::QUEUE_MESSAGE_PROCESSOR_PLUGINS, $this->tester->getMessageProcessorPlugins(static::QUEUE_NAMES));
    }

    /**
     * @return void
     */
    public function testAreQueuesEmptyReturnsTrueForEmptyQueues(): void
    {
        // Arrange
        $queueClient = $this->tester->getLocator()->queue()->client();

        // Act
        foreach (static::QUEUE_NAMES as $queueName) {
            $queueClient->purgeQueue($queueName);
        }

        sleep(10);

        // Assert
        $this->assertTrue($this->tester->getLocator()->rabbitMq()->facade()->areQueuesEmpty(static::QUEUE_NAMES));
    }

    /**
     * @return void
     */
    public function testAreQueuesEmptyReturnsFalseForNonEmptyQueues(): void
    {
        // Arrange
        $queueClient = $this->tester->getLocator()->queue()->client();

        // Act
        $messagesToSend = [];

        for ($i = 0; $i < static::MESSAGE_AMOUNT; $i++) {
            $messagesToSend[] = $this->tester->buildSendMessageTransfer();
        }

        foreach (static::QUEUE_NAMES as $queueName) {
            $queueClient->sendMessages($queueName, $messagesToSend);
        }

        sleep(10);

        // Assert
        $this->assertFalse($this->tester->getLocator()->rabbitMq()->facade()->areQueuesEmpty(static::QUEUE_NAMES));

        // cleanup
        foreach (static::QUEUE_NAMES as $queueName) {
            $queueClient->purgeQueue($queueName);
        }
    }
}
