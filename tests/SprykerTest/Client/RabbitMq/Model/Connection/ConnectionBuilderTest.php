<?php

/**
 * MIT License
 * For full license information, please view the LICENSE file that was distributed with this source code.
 */

namespace SprykerTest\Client\RabbitMq\Model\Connection;

use Codeception\Test\Unit;
use Generated\Shared\Transfer\StoreTransfer;
use PhpAmqpLib\Exception\AMQPIOException;
use Spryker\Client\RabbitMq\Model\Connection\ConnectionBuilder\ConnectionBuilder;
use Spryker\Client\RabbitMq\Model\Connection\ConnectionBuilder\ConnectionBuilderInterface;
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
     * @var string
     */
    protected const STORE_NAME = 'DE';

    /**
     * @var string
     */
    protected const LOCALE_CODE = 'en_US';

    /**
     * @var string
     */
    protected const QUEUE_POOL_NAME = 'synchronizationPool';

    /**
     * @var string
     */
    protected const INCORRECT_LOCALE_CODE = 'INCORRECT_LOCALE_CODE';

    /**
     * @var string
     */
    protected const INCORRECT_CONNECTION_NAME = 'INCORRECT_CONNECTION_NAME';

    /**
     * @var \SprykerTest\Client\RabbitMq\RabbitMqClientTester
     */
    protected $tester;

    /**
     * @return void
     */
    public function testCreateConnectionByQueueConnectionTransfer(): void
    {
        // Act
        $this->createConnectionBuilder()->createConnectionByQueueConnectionTransfer($this->tester->createQueueConnectionTransfer());
    }

    /**
     * @return void
     */
    public function testCreateConnectionsByQueueConnectionTransfers(): void
    {
        // Act
        $this->createConnectionBuilder()->createConnectionsByQueueConnectionTransfers([$this->tester->createQueueConnectionTransfer()]);
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
