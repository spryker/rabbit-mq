<?php

/**
 * MIT License
 * For full license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Spryker\Client\RabbitMq\Model\Connection;

use Generated\Shared\Transfer\QueueConnectionTransfer;
use PhpAmqpLib\Channel\AMQPChannel;
use Spryker\Client\RabbitMq\Dependency\Client\RabbitMqToStoreClientInterface;
use Spryker\Client\RabbitMq\Exception\QueuePoolsNotConfiguredException;
use Spryker\Client\RabbitMq\Model\Connection\ConnectionBuilder\ConnectionBuilderInterface;
use Spryker\Client\RabbitMq\Model\Connection\QueueConnectionTransferFilter\QueueConnectionTransferFilterInterface;
use Spryker\Client\RabbitMq\Model\Connection\QueueConnectionTransferMapper\QueueConnectionTransferMapperInterface;
use Spryker\Client\RabbitMq\Model\Exception\DefaultConnectionNotFoundException;
use Spryker\Client\RabbitMq\RabbitMqConfig;

class ConnectionManager implements ConnectionManagerInterface
{
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
     * @return \PhpAmqpLib\Channel\AMQPChannel[]
     */
    public function getChannelsByQueuePoolName(string $queuePoolName, ?string $localeCode): array
    {
        $queuePools = $this->config->getQueuePoolsForStore(APPLICATION_STORE);
        $queueConnectionTransfers = $this->connectionConfigMapper->mapQueueConnectionTransfersByPoolName(
            $queuePools
        );

        if (!isset($queueConnectionTransfers[$queuePoolName])) {
            throw new QueuePoolsNotConfiguredException(sprintf('Queue pools are not configured for store %s', APPLICATION_STORE));
        }

        $queueConnectionTransfersByBoolName = $this->connectionConfigMapper->mapQueueConnectionTransfersByPoolName(
            $queuePools
        )[$queuePoolName];

        return $this->getChannelsFilteredByLocaleCode($queueConnectionTransfersByBoolName, $localeCode);
    }

    /**
     * @param \Generated\Shared\Transfer\QueueConnectionTransfer[] $queueConnectionTransfers
     * @param string|null $localeCode
     *
     * @return \PhpAmqpLib\Channel\AMQPChannel[]
     */
    protected function getChannelsFilteredByLocaleCode(array $queueConnectionTransfers, ?string $localeCode): array
    {
        $filteredQueueConnectionTransfers = $this->connectionConfigFilter->filterByLocaleCode(
            $queueConnectionTransfers,
            $localeCode
        );

        $connections = $this->connectionBuilder->createConnectionsByQueueConnectionTransfers(
            $filteredQueueConnectionTransfers
        );

        return $this->getChannels($connections);
    }

    /**
     * @param \Spryker\Client\RabbitMq\Model\Connection\ConnectionInterface[] $connections
     *
     * @return \PhpAmqpLib\Channel\AMQPChannel[]
     */
    protected function getChannels(array $connections): array
    {
        return array_map(function (ConnectionInterface $connection) {
            return $connection->getChannel();
        }, $connections);
    }

    /**
     * @param string $storeName
     * @param string|null $localeCode
     *
     * @return \PhpAmqpLib\Channel\AMQPChannel[]
     */
    public function getChannelsByStoreName(string $storeName, ?string $localeCode): array
    {
        $queueConnectionTransfersByStoreName = $this->connectionConfigMapper
            ->mapQueueConnectionTransfersByStoreName()[$storeName];

        return $this->getChannelsFilteredByLocaleCode($queueConnectionTransfersByStoreName, $localeCode);
    }

    /**
     * @return \PhpAmqpLib\Channel\AMQPChannel
     */
    public function getDefaultChannel(): AMQPChannel
    {
        $defaultConnection = $this->connectionBuilder->createConnectionByQueueConnectionTransfer(
            $this->getDefaultQueueConnectionTransfer()
        );

        return $defaultConnection->getChannel();
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
}
