<?php

/**
 * MIT License
 * For full license information, please view the LICENSE file that was distributed with this source code.
 */

namespace SprykerTest\Client\RabbitMq\Model\Connection\ConnectionBuilder;

use Codeception\Test\Unit;
use Generated\Shared\Transfer\QueueConnectionTransfer;
use PhpAmqpLib\Channel\AMQPChannel;
use ReflectionClass;
use Spryker\Client\RabbitMq\Model\Connection\ConnectionBuilder\ConnectionBuilder;
use Spryker\Client\RabbitMq\Model\Connection\ConnectionInterface;

/**
 * Auto-generated group annotations
 *
 * @group SprykerTest
 * @group Client
 * @group RabbitMq
 * @group Model
 * @group Connection
 * @group ConnectionBuilder
 * @group ConnectionBuilderTest
 * Add your own group annotations below this line
 */
class ConnectionBuilderTest extends Unit
{
    /**
     * @var \SprykerTest\Client\RabbitMq\RabbitMqClientTester
     */
    protected $tester;

    /**
     * @return void
     */
    public function testReturnsExistingConnectionIfAlreadyCreatedAndChannelIsOpen(): void
    {
        // Arrange
        $queueConnectionTransfer = $this->createMock(QueueConnectionTransfer::class);
        $queueConnectionTransfer->method('getName')->willReturn('test_connection');

        $channel = $this->createMock(AMQPChannel::class);
        $channel->method('is_open')->willReturn(true);

        $existingConnection = $this->createMock(ConnectionInterface::class);
        $existingConnection->method('getChannel')->willReturn($channel);

        $connectionBuilder = $this->getMockBuilder(ConnectionBuilder::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['createConnection'])
            ->getMock();
        $reflection = new ReflectionClass(ConnectionBuilder::class);
        $property = $reflection->getProperty('createdConnectionsByConnectionName');
        $property->setAccessible(true);
        $property->setValue($connectionBuilder, ['test_connection' => $existingConnection]);

        // Act
        $result = $connectionBuilder->createConnectionByQueueConnectionTransfer($queueConnectionTransfer);

        // Assert
        $this->assertSame($existingConnection, $result);
    }
}
