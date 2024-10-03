<?php

/**
 * MIT License
 * For full license information, please view the LICENSE file that was distributed with this source code.
 */

namespace SprykerTest\Client\RabbitMq\Model\Connection;

use Codeception\Test\Unit;
use Generated\Shared\Transfer\QueueConnectionTransfer;
use Generated\Shared\Transfer\StoreTransfer;
use PhpAmqpLib\Exception\AMQPIOException;
use Spryker\Client\RabbitMq\Model\Connection\ConnectionBuilder\ConnectionBuilder;
use Spryker\Client\RabbitMq\Model\Connection\ConnectionBuilder\ConnectionBuilderInterface;
use Spryker\Client\RabbitMq\Model\Connection\ConnectionInterface;
use Spryker\Client\RabbitMq\Model\Helper\QueueEstablishmentHelperInterface;

/**
 * Auto-generated group annotations
 *
 * @group SprykerTest
 * @group Client
 * @group RabbitMq
 * @group Model
 * @group Connection
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
    public function testCreateConnectionByQueueConnectionTransfer(): void
    {
        // Arrange
        $connectionBuilder = $this->createConnectionBuilder();

        // Act
        $connectionBuilder->createConnectionByQueueConnectionTransfer(new QueueConnectionTransfer());

        // Assert
        $this->assertInstanceOf(ConnectionInterface::class, $connectionBuilder);
    }

    /**
     * @return void
     */
    public function testCreateConnectionsByQueueConnectionTransfers(): void
    {
        // Arrange
        $connectionBuilder = $this->createConnectionBuilder();

        // Act
        $connectionTransfers = $connectionBuilder->createConnectionsByQueueConnectionTransfers([
            new QueueConnectionTransfer(),
        ]);

        // Assert
        $this->assertIsArray($connectionTransfers);
        $this->assertCount(1, $connectionTransfers);
        $this->assertInstanceOf(ConnectionInterface::class, $connectionTransfers[0]);
    }

    /**
     * @return \Spryker\Client\RabbitMq\Model\Connection\ConnectionBuilder\ConnectionBuilderInterface
     */
    protected function createConnectionBuilder(): ConnectionBuilderInterface
    {
        // Arrange
        /** @var \PHPUnit\Framework\MockObject\MockObject|\Spryker\Client\RabbitMq\Dependency\Client\RabbitMqToStoreClientInterface $storeClientMock */
        $storeClientMock = $this->tester->createStoreClient();
        /** @var \PHPUnit\Framework\MockObject\MockObject|\Spryker\Client\RabbitMq\RabbitMqConfig $rabbitMqConfigMock */
        $rabbitMqConfigMock = $this->tester->createRabbitMqConfig();
        $this->expectException(AMQPIOException::class); // ignore AMQPIO connection
        /** @var \PHPUnit\Framework\MockObject\MockObject|\Spryker\Client\RabbitMq\Model\Helper\QueueEstablishmentHelperInterface $queueEstablishmentHelperMock */
        $queueEstablishmentHelperMock = $this->createMock(QueueEstablishmentHelperInterface::class);

        // Assert
        $rabbitMqConfigMock->expects($this->tester->isDynamicStoreEnabled() ? $this->exactly(2) : $this->once())
            ->method('getDefaultLocaleCode');
        $storeClientMock
            ->expects($this->tester->isDynamicStoreEnabled() ? $this->never() : $this->once())
            ->method('getCurrentStore')
            ->willReturn((new StoreTransfer())->setAvailableLocaleIsoCodes($this->tester::AVAILABLE_LOCALES));

        return new ConnectionBuilder($rabbitMqConfigMock, $storeClientMock, $queueEstablishmentHelperMock);
    }
}
