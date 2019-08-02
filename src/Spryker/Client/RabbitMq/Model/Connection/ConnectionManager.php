<?php

/**
 * MIT License
 * For full license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Spryker\Client\RabbitMq\Model\Connection;

use Generated\Shared\Transfer\QueueConnectionTransfer;
use PhpAmqpLib\Channel\AMQPChannel;
use Spryker\Client\RabbitMq\Dependency\Client\RabbitMqToStoreClientInterface;
use Spryker\Client\RabbitMq\Model\Connection\ConnectionConfigFilter\ConnectionConfigFilterInterface;
use Spryker\Client\RabbitMq\Model\Connection\ConnectionConfigMapper\ConnectionConfigMapperInterface;
use Spryker\Client\RabbitMq\Model\Connection\ConnectionCreator\ConnectionCreatorInterface;
use Spryker\Client\RabbitMq\Model\Exception\DefaultConnectionNotFoundException;
use Spryker\Client\RabbitMq\RabbitMqConfig;

class ConnectionManager implements ConnectionManagerInterface
{
    /**
     * @var \Spryker\Client\RabbitMq\RabbitMqConfig
     */
    protected $config;

    /**
     * @var \Spryker\Client\RabbitMq\Dependency\Client\RabbitMqToStoreClientInterface
     */
    protected $storeClient;

    /**
     * @var \Spryker\Client\RabbitMq\Model\Connection\ConnectionConfigMapper\ConnectionConfigMapperInterface
     */
    protected $connectionConfigMapper;

    /**
     * @var \Spryker\Client\RabbitMq\Model\Connection\ConnectionConfigFilter\ConnectionConfigFilterInterface
     */
    protected $connectionConfigFilter;

    /**
     * @var \Spryker\Client\RabbitMq\Model\Connection\ConnectionCreator\ConnectionCreatorInterface
     */
    protected $connectionCreator;

    /**
     * @param \Spryker\Client\RabbitMq\RabbitMqConfig $config
     * @param \Spryker\Client\RabbitMq\Dependency\Client\RabbitMqToStoreClientInterface $storeClient
     * @param \Spryker\Client\RabbitMq\Model\Connection\ConnectionConfigMapper\ConnectionConfigMapperInterface $connectionConfigMapper
     * @param \Spryker\Client\RabbitMq\Model\Connection\ConnectionConfigFilter\ConnectionConfigFilterInterface $connectionConfigFilter
     * @param \Spryker\Client\RabbitMq\Model\Connection\ConnectionCreator\ConnectionCreatorInterface $connectionCreator
     */
    public function __construct(
        RabbitMqConfig $config,
        RabbitMqToStoreClientInterface $storeClient,
        ConnectionConfigMapperInterface $connectionConfigMapper,
        ConnectionConfigFilterInterface $connectionConfigFilter,
        ConnectionCreatorInterface $connectionCreator
    ) {
        $this->config = $config;
        $this->storeClient = $storeClient;
        $this->connectionConfigMapper = $connectionConfigMapper;
        $this->connectionConfigFilter = $connectionConfigFilter;
        $this->connectionCreator = $connectionCreator;
    }

    /**
     * @param string $queuePoolName
     * @param string|null $localeCode
     *
     * @return \PhpAmqpLib\Channel\AMQPChannel[]
     */
    public function getChannelsByQueuePoolName(string $queuePoolName, ?string $localeCode): array
    {
        $connectionsConfigMap = $this->connectionConfigMapper->mapConnectionsConfigByPoolName(
            $this->storeClient->getCurrentStore()->getQueuePools()
        )[$queuePoolName];

        return $this->getChannelsFilteredByLocaleCode($connectionsConfigMap, $localeCode);
    }

    /**
     * @param \Generated\Shared\Transfer\QueueConnectionTransfer[] $connectionsConfig
     * @param string|null $localeCode
     *
     * @return \PhpAmqpLib\Channel\AMQPChannel[]
     */
    protected function getChannelsFilteredByLocaleCode(array $connectionsConfig, ?string $localeCode): array
    {
        $filteredConnectionsConfig = $this->connectionConfigFilter->filterByLocaleCode($connectionsConfig, $localeCode);
        $connections = $this->connectionCreator->createConnectionsByConfig($filteredConnectionsConfig);

        return $this->getChannels($connections);
    }

    /**
     * @param array $connections
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
        $connectionsConfigMap = $this->connectionConfigMapper->mapConnectionsConfigByStoreName()[$storeName];

        return $this->getChannelsFilteredByLocaleCode($connectionsConfigMap, $localeCode);
    }

    /**
     * @return \PhpAmqpLib\Channel\AMQPChannel
     */
    public function getDefaultChannel(): AMQPChannel
    {
        $defaultConnection = $this->connectionCreator->createConnectionByConfig($this->getDefaultConnectionConfig());

        return $defaultConnection->getChannel();
    }

    /**
     * @throws \Spryker\Client\RabbitMq\Model\Exception\DefaultConnectionNotFoundException
     *
     * @return \Generated\Shared\Transfer\QueueConnectionTransfer
     */
    protected function getDefaultConnectionConfig(): QueueConnectionTransfer
    {
        foreach ($this->config->getQueueConnections() as $queueConnectionConfig) {
            if ($queueConnectionConfig->getIsDefaultConnection()) {
                return $queueConnectionConfig;
            }
        }

        throw new DefaultConnectionNotFoundException(
            'Default queue connection not found, You can fix this by adding RabbitMqEnv::RABBITMQ_DEFAULT_CONNECTION = true in your queue connection in config_* files'
        );
    }
}
