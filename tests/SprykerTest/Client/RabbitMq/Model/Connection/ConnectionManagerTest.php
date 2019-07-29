<?php

/**
 * MIT License
 * For full license information, please view the LICENSE file that was distributed with this source code.
 */

namespace SprykerTest\Client\RabbitMq\Model\Connection;

use Codeception\Test\Unit;
use Generated\Shared\Transfer\QueueConnectionTransfer;
use Generated\Shared\Transfer\StoreTransfer;
use PhpAmqpLib\Channel\AMQPChannel;
use Spryker\Client\RabbitMq\Dependency\Client\RabbitMqToStoreClientBridge;
use Spryker\Client\RabbitMq\Model\Connection\Connection;
use Spryker\Client\RabbitMq\Model\Connection\ConnectionFactory;
use Spryker\Client\RabbitMq\Model\Connection\ConnectionManager\ConnectionConfigFilter\ConnectionConfigFilter;
use Spryker\Client\RabbitMq\Model\Connection\ConnectionManager\ConnectionConfigMapper\ConnectionConfigMapper;
use Spryker\Client\RabbitMq\Model\Connection\ConnectionManager\ConnectionCreator\ConnectionCreator;
use Spryker\Client\RabbitMq\Model\Connection\ConnectionManager\ConnectionManager;
use Spryker\Client\RabbitMq\Model\Connection\ConnectionManager\ConnectionManagerInterface;
use Spryker\Client\RabbitMq\Model\Exception\ConnectionConfigIsNotDefinedException;
use Spryker\Client\RabbitMq\Model\Exception\DefaultConnectionNotFoundException;

/**
 * Auto-generated group annotations
 * @group SprykerTest
 * @group Client
 * @group RabbitMq
 * @group Model
 * @group Connection
 * @group ConnectionManagerTest
 * Add your own group annotations below this line
 */
class ConnectionManagerTest extends Unit
{
    protected const STORE_NAME = 'DE';
    protected const LOCALE_CODE = 'en_US';
    protected const QUEUE_POOL_NAME = 'synchronizationPool';
    protected const DEFAULT_POOL_CONNECTION_NAME = self::STORE_NAME . '-name';

    protected const INCORRECT_LOCALE_CODE = 'INCORRECT_LOCALE_CODE';
    protected const INCORRECT_CONNECTION_NAME = 'INCORRECT_CONNECTION_NAME';

    /**
     * @var \SprykerTest\Client\RabbitMq\RabbitMqClientTester
     */
    protected $tester;

    /**
     * @return void
     */
    public function testGetChannelsByQueuePoolNameShouldThrowConnectionConfigIsNotDefinedException(): void
    {
        $this->expectException(ConnectionConfigIsNotDefinedException::class);

        $connectionManager = $this->createConnectionManager($this->createDefaultQueueConnectionTransfer(), static::INCORRECT_CONNECTION_NAME);

        $connectionManager->getChannelsByQueuePoolName(static::QUEUE_POOL_NAME, static::LOCALE_CODE);
    }

    /**
     * @param string $storeName
     *
     * @return \Generated\Shared\Transfer\QueueConnectionTransfer
     */
    protected function createDefaultQueueConnectionTransfer(string $storeName = self::STORE_NAME): QueueConnectionTransfer
    {
        return $this->createQueueConnectionTransfer($storeName)
            ->setIsDefaultConnection(true);
    }

    /**
     * @param string $storeName
     *
     * @return \Generated\Shared\Transfer\QueueConnectionTransfer
     */
    protected function createQueueConnectionTransfer(string $storeName = self::STORE_NAME): QueueConnectionTransfer
    {
        return (new QueueConnectionTransfer())
            ->setName(sprintf('%s-name', $storeName))
            ->setHost(sprintf('%s-host', $storeName))
            ->setPort(10)
            ->setUsername(sprintf('%s-username', $storeName))
            ->setPassword(sprintf('%s-password', $storeName))
            ->setVirtualHost(sprintf('\%s-virtual-host', $storeName))
            ->setStoreNames(['DE', 'AT']);
    }

    /**
     * @param \Generated\Shared\Transfer\QueueConnectionTransfer $connectionTransfer
     * @param string $poolConnectionName
     *
     * @return \Spryker\Client\RabbitMq\Model\Connection\ConnectionManager\ConnectionManagerInterface
     */
    protected function createConnectionManager(
        QueueConnectionTransfer $connectionTransfer,
        string $poolConnectionName = self::DEFAULT_POOL_CONNECTION_NAME
    ): ConnectionManagerInterface {
        $storeClient = $this->getStoreClientMock($poolConnectionName);
        $connectionFactory = $this->getConnectionFactoryMock([$connectionTransfer]);

        return new ConnectionManager(
            $storeClient,
            $connectionFactory,
            new ConnectionConfigMapper($connectionFactory),
            new ConnectionConfigFilter($storeClient, $connectionFactory),
            new ConnectionCreator($connectionFactory)
        );
    }

    /**
     * @param string $poolConnectionName
     *
     * @return \Spryker\Client\RabbitMq\Dependency\Client\RabbitMqToStoreClientInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected function getStoreClientMock(string $poolConnectionName)
    {
        $storeClientMock = $this->getMockBuilder(RabbitMqToStoreClientBridge::class)
            ->disableOriginalConstructor()
            ->getMock();

        $storeClientMock
            ->method('getCurrentStore')
            ->willReturn($this->createStoreTransfer($poolConnectionName));

        $storeClientMock
            ->method('getStoreByName')
            ->willReturn($this->createStoreTransfer($poolConnectionName));

        return $storeClientMock;
    }

    /**
     * @param string $poolConnectionName
     *
     * @return \Generated\Shared\Transfer\StoreTransfer
     */
    protected function createStoreTransfer(string $poolConnectionName): StoreTransfer
    {
        return (new StoreTransfer())
            ->setQueuePools([
                static::QUEUE_POOL_NAME => [
                    $poolConnectionName,
                ],
            ])
            ->setAvailableLocaleIsoCodes([
                static::LOCALE_CODE,
            ]);
    }

    /**
     * @param \Generated\Shared\Transfer\QueueConnectionTransfer[] $queueConnectionTransfers
     *
     * @return \Spryker\Client\RabbitMq\Model\Connection\ConnectionFactoryInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected function getConnectionFactoryMock(array $queueConnectionTransfers)
    {
        $connectionFactoryMock = $this->getMockBuilder(ConnectionFactory::class)
            ->getMock();

        $connectionFactoryMock
            ->method('getQueueConnectionConfigs')
            ->willReturn($queueConnectionTransfers);

        $connectionFactoryMock
            ->method('createConnection')
            ->willReturn($this->getConnectionMock());

        return $connectionFactoryMock;
    }

    /**
     * @return \Spryker\Client\RabbitMq\Model\Connection\ConnectionInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected function getConnectionMock()
    {
        $connectionMock = $this->getMockBuilder(Connection::class)
            ->disableOriginalConstructor()
            ->getMock();

        $connectionMock
            ->method('getChannel')
            ->willReturn($this->createAMQPChannelMock());

        return $connectionMock;
    }

    /**
     * @return \PhpAmqpLib\Channel\AMQPChannel|\PHPUnit\Framework\MockObject\MockObject
     */
    protected function createAMQPChannelMock()
    {
        return $this->getMockBuilder(AMQPChannel::class)
            ->disableOriginalConstructor()
            ->getMock();
    }

    /**
     * @return void
     */
    public function testGetDefaultChannelShouldThrowDefaultConnectionNotFoundException(): void
    {
        $this->expectException(DefaultConnectionNotFoundException::class);

        $connectionManager = $this->createConnectionManager($this->createQueueConnectionTransfer());

        $connectionManager->getDefaultChannel();
    }

    /**
     * @return void
     */
    public function testGetChannelsByStoreNameForIncorrectLocale(): void
    {
        $connectionManager = $this->createConnectionManager($this->createDefaultQueueConnectionTransfer());

        $channels = $connectionManager
            ->getChannelsByStoreName(static::STORE_NAME, static::INCORRECT_LOCALE_CODE);

        $this->assertCount(0, $channels);
    }

    /**
     * @return void
     */
    public function testGetChannelsByQueuePoolNameWithoutLocale(): void
    {
        $connectionManager = $this->createConnectionManager($this->createDefaultQueueConnectionTransfer());

        $channels = $connectionManager->getChannelsByQueuePoolName(static::QUEUE_POOL_NAME, null);

        $this->assertCount(1, $channels);
    }

    /**
     * @return void
     */
    public function testGetChannelsByStoreNameForDefinedLocale(): void
    {
        $connectionManager = $this->createConnectionManager($this->createDefaultQueueConnectionTransfer());

        $channels = $connectionManager->getChannelsByStoreName(static::STORE_NAME, static::LOCALE_CODE);

        $this->assertCount(1, $channels);
    }

    /**
     * @return void
     */
    public function testGetChannelsByStoreNameWithoutLocale(): void
    {
        $connectionManager = $this->createConnectionManager($this->createDefaultQueueConnectionTransfer());

        $channels = $connectionManager->getChannelsByStoreName(static::STORE_NAME, null);

        $this->assertCount(1, $channels);
    }

    /**
     * @return void
     */
    public function testGetDefaultChannelShouldGetTheSameConnectionForMultipleCalls(): void
    {
        $connectionManager = $this->createConnectionManager($this->createDefaultQueueConnectionTransfer());

        $firstChannelResult = $connectionManager->getDefaultChannel();
        $secondChannelResult = $connectionManager->getDefaultChannel();

        $this->assertSame($firstChannelResult, $secondChannelResult);
    }

    /**
     * @return void
     */
    public function testGetChannelsByStoreNameShouldGetTheSameConnectionForMultipleCalls(): void
    {
        $connectionManager = $this->createConnectionManager($this->createDefaultQueueConnectionTransfer());

        $firstChannelsResult = $connectionManager->getChannelsByStoreName(static::STORE_NAME, null);
        $secondChannelsResult = $connectionManager->getChannelsByStoreName(static::STORE_NAME, null);

        $this->assertSame($firstChannelsResult, $secondChannelsResult);
    }

    /**
     * @return void
     */
    public function testGetChannelsByQueuePoolNameShouldGetTheSameConnectionForMultipleCalls(): void
    {
        $connectionManager = $this->createConnectionManager($this->createDefaultQueueConnectionTransfer());

        $firstChannelsResult = $connectionManager
            ->getChannelsByQueuePoolName(static::QUEUE_POOL_NAME, static::LOCALE_CODE);
        $secondChannelsResult = $connectionManager
            ->getChannelsByQueuePoolName(static::QUEUE_POOL_NAME, static::LOCALE_CODE);

        $this->assertSame($firstChannelsResult, $secondChannelsResult);
    }
}
