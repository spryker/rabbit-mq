<?php

namespace SprykerTest\Client\RabbitMq;

use Codeception\Test\Unit;
use PhpAmqpLib\Channel\AMQPChannel;
use PHPUnit\Framework\MockObject\MockObject;
use Spryker\Client\RabbitMq\Model\Connection\ConnectionManagerInterface;

class RabbitMqClientTest extends Unit
{
    /**
     * @var \SprykerTest\Client\RabbitMq\RabbitMqClientTester $tester
     */
    protected RabbitMqClientTester $tester;

    /**
     * @return void
     *
     * @throws \Exception
     */
    public function testGetQueueMetricsShouldThrowException(): void
    {
        // Arrange
        $connectionManagerMock = $this->getMockBuilder(ConnectionManagerInterface::class)->getMock();

        $connectionManagerMock->method('getChannelsByStoreName')->willReturn([]);

        $this->tester->mockFactoryMethod('getStaticConnectionManager', $connectionManagerMock);

        $rabbitMqClient = $this->tester->createRabbitMqClient();
        $rabbitMqClient->setFactory($this->tester->getFactory());

        // Assert
        $this->expectExceptionMessage('Could not find a connection for DE test_queue');

        // Act
        $rabbitMqClient->getQueueMetrics('test_queue', 'DE');
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
        $rabbitMqClient->setFactory($this->tester->getFactory());

        // Act
        $queueMetricsResult = $rabbitMqClient->getQueueMetrics('test_queue', $storeCode);

        // Assert
        $this->assertArrayHasKey('messageCount', $queueMetricsResult);
        $this->assertArrayHasKey('consumerCount', $queueMetricsResult);

        $this->assertSame($expectedMessageCount, $queueMetricsResult['messageCount']);
        $this->assertSame($expectedConsumerCount, $queueMetricsResult['consumerCount']);
    }

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
}
