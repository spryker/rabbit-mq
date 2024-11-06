<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Client\RabbitMq\Model\Connection;

use Generated\Shared\Transfer\QueueConnectionTransfer;
use PhpAmqpLib\Channel\AMQPChannel;
use Spryker\Client\RabbitMq\Dependency\Client\RabbitMqToStoreClientInterface;
use Spryker\Client\RabbitMq\Model\Connection\ConnectionBuilder\ConnectionBuilderInterface;
use Spryker\Client\RabbitMq\Model\Connection\QueueConnectionTransferFilter\QueueConnectionTransferFilterInterface;
use Spryker\Client\RabbitMq\Model\Connection\QueueConnectionTransferMapper\QueueConnectionTransferMapperInterface;
use Spryker\Client\RabbitMq\Model\Exception\DefaultConnectionNotFoundException;
use Spryker\Client\RabbitMq\RabbitMqConfig;

class ConnectionManager implements ConnectionManagerInterface
{
    /**
     * @var string
     */
    protected const EXCEPTION_MESSAGE_DEFAULT_CONNECTION_NOT_FOUND = 'Default queue connection not found. You can fix this by adding `RabbitMqEnv::RABBITMQ_DEFAULT_CONNECTION = true` in your queue connection in `config_*.php` files';

    /**
     * @var \Spryker\Client\RabbitMq\RabbitMqConfig
     */
    protected $config;

    /**
     * @var \Spryker\Client\RabbitMq\Dependency\Client\RabbitMqToStoreClientInterface
     */
    protected $storeClient;

    /**
     * @var \Spryker\Client\RabbitMq\Model\Connection\QueueConnectionTransferMapper\QueueConnectionTransferMapperInterface
     */
    protected $connectionConfigMapper;

    /**
     * @var \Spryker\Client\RabbitMq\Model\Connection\QueueConnectionTransferFilter\QueueConnectionTransferFilterInterface
     */
    protected $connectionConfigFilter;

    /**
     * @var \Spryker\Client\RabbitMq\Model\Connection\ConnectionBuilder\ConnectionBuilderInterface
     */
    protected $connectionBuilder;

    /**
     * @param \Spryker\Client\RabbitMq\RabbitMqConfig $config
     * @param \Spryker\Client\RabbitMq\Dependency\Client\RabbitMqToStoreClientInterface $storeClient
     * @param \Spryker\Client\RabbitMq\Model\Connection\QueueConnectionTransferMapper\QueueConnectionTransferMapperInterface $connectionConfigMapper
     * @param \Spryker\Client\RabbitMq\Model\Connection\QueueConnectionTransferFilter\QueueConnectionTransferFilterInterface $connectionConfigFilter
     * @param \Spryker\Client\RabbitMq\Model\Connection\ConnectionBuilder\ConnectionBuilderInterface $connectionBuilder
     */
    public function __construct(
        RabbitMqConfig $config,
        RabbitMqToStoreClientInterface $storeClient,
        QueueConnectionTransferMapperInterface $connectionConfigMapper,
        QueueConnectionTransferFilterInterface $connectionConfigFilter,
        ConnectionBuilderInterface $connectionBuilder
    ) {
        $this->config = $config;
        $this->storeClient = $storeClient;
        $this->connectionConfigMapper = $connectionConfigMapper;
        $this->connectionConfigFilter = $connectionConfigFilter;
        $this->connectionBuilder = $connectionBuilder;
    }

    /**
     * @param string $queuePoolName
     * @param string|null $localeCode
     *
     * @return array<\Spryker\Client\RabbitMq\Model\Connection\ChannelInterface>
     */
    public function getChannelsByQueuePoolName(string $queuePoolName, ?string $localeCode): array
    {
        $queueConnectionTransfersByBoolName = $this->connectionConfigMapper->mapQueueConnectionTransfersByPoolName(
            $this->getQueuePools(),
        )[$queuePoolName];

        return $this->getChannelsFilteredByLocaleCode($queueConnectionTransfersByBoolName, $localeCode);
    }

    /**
     * @param array<\Generated\Shared\Transfer\QueueConnectionTransfer> $queueConnectionTransfers
     * @param string|null $localeCode
     *
     * @return array<\Spryker\Client\RabbitMq\Model\Connection\ChannelInterface>
     */
    protected function getChannelsFilteredByLocaleCode(array $queueConnectionTransfers, ?string $localeCode): array
    {
        $filteredQueueConnectionTransfers = $this->connectionConfigFilter->filterByLocaleCode(
            $queueConnectionTransfers,
            $localeCode,
        );

        $connections = $this->connectionBuilder->createConnectionsByQueueConnectionTransfers(
            $filteredQueueConnectionTransfers,
        );

        return $this->getChannels($connections);
    }

    /**
     * @param array<\Spryker\Client\RabbitMq\Model\Connection\ConnectionInterface> $connections
     *
     * @return array<\Spryker\Client\RabbitMq\Model\Connection\ChannelInterface>
     */
    protected function getChannels(array $connections): array
    {
        return array_map(function (ConnectionInterface $connection) {
            return (new Channel())->setChannel($connection->getChannel())->setStores($connection->getStoreNames());
        }, $connections);
    }

    /**
     * @param string $storeName
     * @param string|null $localeCode
     *
     * @return array<\Spryker\Client\RabbitMq\Model\Connection\ChannelInterface>
     */
    public function getChannelsByStoreName(string $storeName, ?string $localeCode): array
    {
        $queueConnectionTransfersByStoreName = $this->connectionConfigMapper
            ->mapQueueConnectionTransfersByStoreName();

        if (!isset($queueConnectionTransfersByStoreName[$storeName])) {
            return [];
        }

        return $this->getChannelsFilteredByLocaleCode($queueConnectionTransfersByStoreName[$storeName], $localeCode);
    }

    /**
     * @return \PhpAmqpLib\Channel\AMQPChannel
     */
    public function getDefaultChannel(): AMQPChannel
    {
        return $this->getDefaultConnection()->getChannel();
    }

    /**
     * @throws \Spryker\Client\RabbitMq\Model\Exception\DefaultConnectionNotFoundException
     *
     * @return \Generated\Shared\Transfer\QueueConnectionTransfer
     */
    protected function getDefaultQueueConnectionTransfer(): QueueConnectionTransfer
    {
        foreach ($this->config->getQueueConnections() as $queueConnectionTransfer) {
            if ($queueConnectionTransfer->getIsDefaultConnection()) {
                return $queueConnectionTransfer;
            }
        }

        throw new DefaultConnectionNotFoundException(static::EXCEPTION_MESSAGE_DEFAULT_CONNECTION_NOT_FOUND);
    }

    /**
     * @return \Spryker\Client\RabbitMq\Model\Connection\ConnectionInterface
     */
    public function getDefaultConnection(): ConnectionInterface
    {
        return $this->connectionBuilder->createConnectionByQueueConnectionTransfer(
            $this->getDefaultQueueConnectionTransfer(),
        );
    }

    /**
     * @return array
     */
    protected function getQueuePools(): array
    {
        if ($this->config->getQueuePools() && $this->config->isDynamicStoreEnabled()) {
            return $this->config->getQueuePools();
        }

        return $this->storeClient->getCurrentStore()->getQueuePools();
    }
}
