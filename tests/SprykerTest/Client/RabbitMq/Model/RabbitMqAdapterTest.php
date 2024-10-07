<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerTest\Client\RabbitMq\Model;

use Codeception\Test\Unit;
use Generated\Shared\Transfer\QueueMetricsRequestTransfer;
use PhpAmqpLib\Channel\AMQPChannel;
use PHPUnit\Framework\MockObject\MockObject;
use ReflectionClass;
use Spryker\Client\RabbitMq\Model\Connection\ConnectionManagerInterface;
use Spryker\Client\RabbitMq\Model\Consumer\ConsumerInterface;
use Spryker\Client\RabbitMq\Model\Manager\ManagerInterface;
use Spryker\Client\RabbitMq\Model\Publisher\PublisherInterface;
use Spryker\Client\RabbitMq\Model\Queue\QueueMetricReader;
use Spryker\Client\RabbitMq\Model\RabbitMqAdapter;
use Spryker\Client\RabbitMq\RabbitMqFactory;
use SprykerTest\Client\RabbitMq\RabbitMqClientTester;

/**
 * Auto-generated group annotations
 *
 * @group SprykerTest
 * @group Client
 * @group RabbitMq
 * @group Model
 * @group RabbitMqAdapterTest
 * Add your own group annotations below this line
 */
class RabbitMqAdapterTest extends Unit
{
    /**
     * @var \SprykerTest\Client\RabbitMq\RabbitMqClientTester $tester
     */
    protected RabbitMqClientTester $tester;

    /**
     * @return void
     */
    public function testGetQueueMetricsShouldThrowException(): void
    {
        // Arrange
        $connectionManagerMock = $this->getMockBuilder(ConnectionManagerInterface::class)->getMock();

        $connectionManagerMock->method('getChannelsByStoreName')->willReturn([]);

        $rabbitMqAdapter = new RabbitMqAdapter(
            $this->getMockBuilder(ManagerInterface::class)->getMock(),
            $this->getMockBuilder(PublisherInterface::class)->getMock(),
            $this->getMockBuilder(ConsumerInterface::class)->getMock(),
            (new QueueMetricReader($connectionManagerMock)),
        );

        // Assert
        $this->expectExceptionMessage('Could not find a connection for DE test_queue');

        // Act
        $rabbitMqAdapter->getQueueMetrics(
            (new QueueMetricsRequestTransfer())
                ->setQueueName('test_queue')
                ->setStoreName('DE'),
        );
    }

    /**
     * @dataProvider getQueueMetricsDataProvider
     *
     * @param string|null $storeName
     * @param int $expectedMessageCount
     * @param int $expectedConsumerCount
     *
     * @return void
     */
    public function testGetQueueMetricsShouldReturnMetrics(
        ?string $storeName,
        int $expectedMessageCount,
        int $expectedConsumerCount,
    ): void {
        // Arrange
        $this->tester->mockFactoryMethod('getStaticConnectionManager', $this->getConnectionManagerMock());

        $rabbitMqAdapter = new RabbitMqAdapter(
            $this->getMockBuilder(ManagerInterface::class)->getMock(),
            $this->getMockBuilder(PublisherInterface::class)->getMock(),
            $this->getMockBuilder(ConsumerInterface::class)->getMock(),
            (new QueueMetricReader($this->getConnectionManagerMock())),
        );

        // Act
        $queueMetricsResult = $rabbitMqAdapter->getQueueMetrics((new QueueMetricsRequestTransfer())
            ->setQueueName('test_queue')
            ->setStoreName($storeName));

        // Assert
        $this->assertSame($expectedMessageCount, $queueMetricsResult->getMessageCount());
        $this->assertSame($expectedConsumerCount, $queueMetricsResult->getConsumerCount());
    }

    /**
     * @return array<string, mixed>
     */
    protected function getQueueMetricsDataProvider(): array
    {
        return [
            'check with no store set' => [
                'storeName' => null,
                'expectedMessageCount' => 1,
                'expectedConsumerCount' => 3,
            ],
            'check with store name set' => [
                'storeName' => 'DE',
                'expectedMessageCount' => 2,
                'expectedConsumerCount' => 1,
            ],
        ];
    }

    /**
     * @return \Spryker\Client\RabbitMq\Model\Connection\ConnectionManagerInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    public function getConnectionManagerMock(): ConnectionManagerInterface|MockObject
    {
        $connectionManagerMock = $this->getMockBuilder(ConnectionManagerInterface::class)->getMock();

        $AMQPChannelMock = $this->createMock(AMQPChannel::class);
        $AMQPChannelMock->method('queue_declare')->willReturn([null, 2, 1]);

        $connectionManagerMock->method('getChannelsByStoreName')->willReturn([$AMQPChannelMock]);

        $AMQPChannelMock = $this->createMock(AMQPChannel::class);
        $AMQPChannelMock->method('queue_declare')->willReturn([null, 1, 3]);

        $connectionManagerMock->method('getDefaultChannel')->willReturn($AMQPChannelMock);

        return $connectionManagerMock;
    }

    /**
     * @return \Spryker\Client\RabbitMq\RabbitMqFactory
     */
    protected function getFactory(): RabbitMqFactory
    {
        $rabbitMqFactory = $this->tester->getFactory();

        $rabbitMqFactoryReflection = new ReflectionClass($rabbitMqFactory);
        $queueMetricReaderProperty = $rabbitMqFactoryReflection->getProperty('queueMetricReader');
        $queueMetricReaderProperty->setAccessible(true);
        $queueMetricReaderProperty->setValue($rabbitMqFactory, null);

        return $rabbitMqFactory;
    }
}
