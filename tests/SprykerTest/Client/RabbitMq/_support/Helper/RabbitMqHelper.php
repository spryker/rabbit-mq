<?php

/**
 * MIT License
 * For full license information, please view the LICENSE file that was distributed with this source code.
 */

namespace SprykerTest\Client\RabbitMq\Helper;

use Codeception\Module;
use Codeception\Stub;
use Generated\Shared\Transfer\QueueConnectionTransfer;
use Generated\Shared\Transfer\StoreTransfer;
use PhpAmqpLib\Channel\AMQPChannel;
use Spryker\Client\RabbitMq\Dependency\Client\RabbitMqToStoreClientBridge;
use Spryker\Client\RabbitMq\Model\Connection\Connection;
use Spryker\Client\RabbitMq\Model\Connection\ConnectionBuilder\ConnectionBuilder;
use Spryker\Client\RabbitMq\Model\Connection\ConnectionManager;
use Spryker\Client\RabbitMq\Model\Connection\ConnectionManagerInterface;
use Spryker\Client\RabbitMq\Model\Connection\QueueConnectionTransferFilter\QueueConnectionTransferFilter;
use Spryker\Client\RabbitMq\Model\Connection\QueueConnectionTransferMapper\QueueConnectionTransferMapper;
use Spryker\Client\RabbitMq\RabbitMqConfig;

class RabbitMqHelper extends Module
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

    protected const DEFAULT_POOL_CONNECTION_NAME = self::STORE_NAME . '-name';

    /**
     * @var string
     */
    protected const VIRTUAL_HOST = 'virtual-host';

    /**
     * @var string
     */
    protected const INCORRECT_LOCALE_CODE = 'INCORRECT_LOCALE_CODE';

    /**
     * @return \Spryker\Client\RabbitMq\Model\Connection\ConnectionManagerInterface
     */
    public function createConnectionManagerWithDefaultQueueConnectionAndIncorrectLocaleCode(): ConnectionManagerInterface
    {
        return $this->createConnectionManager(
            $this->createDefaultQueueConnectionTransfer(),
            static::INCORRECT_LOCALE_CODE,
        );
    }

    /**
     * @return \Spryker\Client\RabbitMq\Model\Connection\ConnectionManagerInterface
     */
    public function createConnectionManagerWithDefaultQueueConnection(): ConnectionManagerInterface
    {
        return $this->createConnectionManager($this->createDefaultQueueConnectionTransfer());
    }

    /**
     * @return \Spryker\Client\RabbitMq\Model\Connection\ConnectionManagerInterface
     */
    public function createConnectionManagerWithoutDefaultQueueConnection(): ConnectionManagerInterface
    {
        return $this->createConnectionManager($this->createQueueConnectionTransfer());
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
     * @return \Spryker\Client\RabbitMq\Model\Connection\ConnectionManagerInterface
     */
    protected function createConnectionManager(
        QueueConnectionTransfer $connectionTransfer,
        string $poolConnectionName = self::DEFAULT_POOL_CONNECTION_NAME
    ): ConnectionManagerInterface {
        $storeClient = $this->getStoreClientMock($poolConnectionName);
        $configMock = $this->getConfigMock([$connectionTransfer]);

        return new ConnectionManager(
            $configMock,
            $storeClient,
            new QueueConnectionTransferMapper($configMock),
            new QueueConnectionTransferFilter($storeClient),
            $this->getConnectionBuilderMock(),
        );
    }

    /**
     * @param string $poolConnectionName
     *
     * @return \Spryker\Client\RabbitMq\Dependency\Client\RabbitMqToStoreClientInterface|object
     */
    protected function getStoreClientMock(string $poolConnectionName)
    {
        return Stub::make(RabbitMqToStoreClientBridge::class, [
            'getCurrentStore' => $this->createStoreTransfer($poolConnectionName),
            'getStoreByName' => $this->createStoreTransfer($poolConnectionName),
        ]);
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
     * @param array<\Generated\Shared\Transfer\QueueConnectionTransfer> $queueConnectionTransfers
     *
     * @return \Spryker\Client\RabbitMq\RabbitMqConfig|object
     */
    protected function getConfigMock(array $queueConnectionTransfers)
    {
        return Stub::make(RabbitMqConfig::class, [
            'getQueueConnections' => $queueConnectionTransfers,
        ]);
    }

    /**
     * @return \Spryker\Client\RabbitMq\Model\Connection\ConnectionBuilder\ConnectionBuilderInterface|object
     */
    protected function getConnectionBuilderMock()
    {
        return Stub::make(ConnectionBuilder::class, [
            'createConnection' => $this->getConnectionMock(),
        ]);
    }

    /**
     * @return \Spryker\Client\RabbitMq\Model\Connection\ConnectionInterface|object
     */
    protected function getConnectionMock()
    {
        return Stub::make(Connection::class, [
            'getChannel' => $this->createAMQPChannelMock(),
            'getVirtualHost' => static::VIRTUAL_HOST,
        ]);
    }

    /**
     * @return \PhpAmqpLib\Channel\AMQPChannel|object
     */
    protected function createAMQPChannelMock()
    {
        return Stub::make(AMQPChannel::class);
    }
}
