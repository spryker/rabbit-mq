<?php

/**
 * MIT License
 * For full license information, please view the LICENSE file that was distributed with this source code.
 */

namespace SprykerTest\Client\RabbitMq;

use Codeception\Test\Unit;
use Generated\Shared\Transfer\QueueMetricsRequestTransfer;
use PhpAmqpLib\Channel\AMQPChannel;
use PHPUnit\Framework\MockObject\MockObject;
use ReflectionClass;
use Spryker\Client\RabbitMq\Model\Connection\ConnectionManagerInterface;
use Spryker\Client\RabbitMq\RabbitMqFactory;

/**
 * Auto-generated group annotations
 *
 * @group SprykerTest
 * @group Client
 * @group RabbitMq
 * @group RabbitMqClientTest
 * Add your own group annotations below this line
 */
class RabbitMqClientTest extends Unit
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

        $this->tester->mockFactoryMethod('getStaticConnectionManager', $connectionManagerMock);

        $rabbitMqClient = $this->tester->createRabbitMqClient();

        $rabbitMqClient->setFactory($this->getFactory());

        // Assert
        $this->expectExceptionMessage('Could not find a connection for DE test_queue');

        // Act
        $rabbitMqClient->getQueueMetrics(
            (new QueueMetricsRequestTransfer())
                ->setQueueName('test_queue')
                ->setStoreCode('DE'),
        );
    }

    /**
     * @dataProvider getQueueMetricsDataProvider
     *
     * @param string|null $storeCode
     * @param int $expectedMessageCount
     * @param int $expectedConsumerCount
     *
     * @return void
     */
    public function testGetQueueMetricsShouldReturnMetrics(
        ?string $storeCode,
        int $expectedMessageCount,
        int $expectedConsumerCount
    ): void {
        // Arrange
        $this->tester->mockFactoryMethod('getStaticConnectionManager', $this->getConnectionManagerMock());

        $rabbitMqClient = $this->tester->createRabbitMqClient();
        $rabbitMqClient->setFactory($this->getFactory());

        // Act
        $queueMetricsResult = $rabbitMqClient->getQueueMetrics((new QueueMetricsRequestTransfer())
            ->setQueueName('test_queue')
            ->setStoreCode($storeCode));

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
                'storeCode' => null,
                'expectedMessageCount' => 1,
                'expectedConsumerCount' => 3,
            ],
            'check with store code set' => [
                'storeCode' => 'DE',
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
