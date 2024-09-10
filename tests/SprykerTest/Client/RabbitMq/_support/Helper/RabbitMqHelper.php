<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerTest\Client\RabbitMq\Helper;

use Codeception\Module;
use Codeception\Stub;
use Codeception\TestInterface;
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
use SprykerTest\Client\Testify\Helper\ClientHelperTrait;
use SprykerTest\Client\Testify\Helper\ConfigHelperTrait;
use SprykerTest\Shared\Store\Helper\StoreDataHelper;
use SprykerTest\Shared\Store\Helper\StoreDataHelperTrait;
use SprykerTest\Shared\Testify\Helper\LocatorHelperTrait;

class RabbitMqHelper extends Module
{
    use ConfigHelperTrait;
    use LocatorHelperTrait;
    use ClientHelperTrait;
    use StoreDataHelperTrait;

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
     * @param \Codeception\TestInterface $test
     *
     * @return void
     */
    public function _before(TestInterface $test)
    {
        parent::_before($test);

        $key = 'DE';
        if ($this->hasModule('\\' . StoreDataHelper::class) && $this->getStoreDataHelper()->isDynamicStoreEnabled()) {
            $key = 'EU';
        }
        $this->getConfigHelper()->mockConfigMethod(
            'getQueuePools',
            [
                'synchronizationPool' => [sprintf('%s-connection', $key)],
            ],
            'RabbitMq',
            'Client',
        );
        $this->getConfigHelper()->mockConfigMethod(
            'getDefaultLocaleCode',
            'en_US',
            'RabbitMq',
            'Client',
        );

        $client = $this->getClientHelper()->getClient('RabbitMq');
        $this->getLocatorHelper()->addToLocatorCache('rabbitMq-client', $client);
    }

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
