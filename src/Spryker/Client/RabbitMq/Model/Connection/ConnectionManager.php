<?php

/**
 * MIT License
 * For full license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Spryker\Client\RabbitMq\Model\Connection;

use Generated\Shared\Transfer\QueueConnectionTransfer;
use PhpAmqpLib\Channel\AMQPChannel;
use Spryker\Client\RabbitMq\Dependency\Client\RabbitMqToStoreClientInterface;
use Spryker\Client\RabbitMq\Model\Exception\ConnectionNotFoundException;
use Spryker\Client\RabbitMq\Model\Exception\DefaultConnectionNotFoundException;

class ConnectionManager implements ConnectionManagerInterface
{
    /**
     * @var \Spryker\Client\RabbitMq\Dependency\Client\RabbitMqToStoreClientInterface
     */
    protected $storeClient;

    /**
     * @var \Spryker\Client\RabbitMq\Model\Connection\ConnectionFactoryInterface
     */
    protected $connectionFactory;

    /**
     * @var \Generated\Shared\Transfer\QueueConnectionTransfer[]|null Keys are connection names.
     */
    protected $connectionsConfigurationMap;

    /**
     * @var array|null Keys are pool names, values are lists of connections.
     */
    protected $connectionsConfigurationMapByPoolName;

    /**
     * @var \Generated\Shared\Transfer\QueueConnectionTransfer|null Keys are store names, values are lists of connections.
     */
    protected $connectionsConfigurationMapByStoreName;

    /**
     * @var array|null
     */
    protected $connectionNameLocaleMap;

    /**
     * @param \Spryker\Client\RabbitMq\Dependency\Client\RabbitMqToStoreClientInterface $storeClient
     * @param \Spryker\Client\RabbitMq\Model\Connection\ConnectionFactoryInterface $connectionFactory
     */
    public function __construct(RabbitMqToStoreClientInterface $storeClient, ConnectionFactoryInterface $connectionFactory)
    {
        $this->storeClient = $storeClient;
        $this->connectionFactory = $connectionFactory;
    }

    /**
     * @return \Generated\Shared\Transfer\QueueConnectionTransfer[]
     */
    protected function getConnectionsConfigurationMapByPoolName(): array
    {
        if ($this->connectionsConfigurationMapByPoolName === null) {
            $this->connectionsConfigurationMapByPoolName = $this->mapConnectionsConfigurationByPoolName($this->storeClient->getCurrentStore()->getQueuePools());
        }

        return $this->connectionsConfigurationMapByPoolName;
    }

    /**
     * @return array
     */
    protected function getConnectionsConfigurationMapByStoreName()
    {
        if ($this->connectionsConfigurationMapByStoreName === null) {
            $this->connectionsConfigurationMapByStoreName = $this->mapConnectionsConfigurationByStoreName();
        }

        return $this->connectionsConfigurationMapByStoreName;
    }

    /**
     * @return \Generated\Shared\Transfer\QueueConnectionTransfer[]
     */
    protected function mapConnectionsConfigurationByStoreName(): array
    {
        $connectionConfigMap = [];
        foreach ($this->getConnectionsConfigurationMap() as $connectionConfig) {
            foreach ($connectionConfig->getStoreNames() as $storeName) {
                $connectionConfigMap[$storeName][] = $connectionConfig;
            }
        }

        return $connectionConfigMap;
    }

    /**
     * @param string[][] $queuePools Keys are pool names, values are lists of connection names.
     *
     * @return \Generated\Shared\Transfer\QueueConnectionTransfer[]
     */
    protected function mapConnectionsConfigurationByPoolName(array $queuePools): array
    {
        $connectionConfigMap = [];
        foreach ($queuePools as $queuePoolName => $connectionNames) {
            $connectionConfigMap[$queuePoolName] = $this->getConnectionsConfigurationByName($connectionNames);
        }

        return $connectionConfigMap;
    }

    /**
     * @param string[] $connectionNames
     *
     * @throws \Spryker\Client\RabbitMq\Model\Exception\ConnectionNotFoundException
     *
     * @return \Generated\Shared\Transfer\QueueConnectionTransfer[]
     */
    protected function getConnectionsConfigurationByName(array $connectionNames): array
    {
        $connectionsConfig = [];
        foreach ($connectionNames as $connectionName) {
            $connectionsConfigurationMap = $this->getConnectionsConfigurationMap();

            if (!isset($connectionsConfigurationMap[$connectionName])) {
                throw new ConnectionNotFoundException(
                    sprintf('Couldn\'t find configuration "%s" to create connection. Please, check RabbitMqEnv::RABBITMQ_CONNECTIONS in config_*.php files, or your stores.php', $connectionName)
                );
            }

            $connectionsConfig[$connectionName] = $connectionsConfigurationMap[$connectionName];

        }

        return $connectionsConfig;
    }

    /**
     * @param \Generated\Shared\Transfer\QueueConnectionTransfer[] $connectionsConfig
     *
     * @return \Spryker\Client\RabbitMq\Model\Connection\ConnectionInterface[]
     */
    protected function createConnectionsByConnectionsConfig(array $connectionsConfig): array
    {
        $connections = [];

        foreach ($connectionsConfig as $connectionConfig) {
            $connection = $this->connectionFactory->createConnection($connectionConfig);
            $uniqueChannelId = $this->getUniqueChannelId($connection);
            if (!isset($connections[$uniqueChannelId])) {
                $connections[$uniqueChannelId] = $connection;
            }
        }

        return $connections;
    }

    /**
     * @return \Generated\Shared\Transfer\QueueConnectionTransfer[]
     */
    protected function getConnectionsConfigurationMap(): array
    {
        if ($this->connectionsConfigurationMap === null) {
            $this->addConnectionsConfiguration();
        }

        return $this->connectionsConfigurationMap;
    }


    /**
     * @return void
     */
    protected function addConnectionsConfiguration(): void
    {
        foreach ($this->connectionFactory->getQueueConnectionConfigs() as $queueConnectionConfig) {
            $this->connectionsConfigurationMap[$queueConnectionConfig->getName()] = $queueConnectionConfig;
        }
    }

    /**
     * @throws \Spryker\Client\RabbitMq\Model\Exception\DefaultConnectionNotFoundException
     *
     * @return \Generated\Shared\Transfer\QueueConnectionTransfer
     */
    protected function getDefaultConnectionConfiguration(): QueueConnectionTransfer
    {
        foreach ($this->connectionFactory->getQueueConnectionConfigs() as $queueConnectionConfig) {
            if ($queueConnectionConfig->getIsDefaultConnection()) {
                return $queueConnectionConfig;
            }
        }

        throw new DefaultConnectionNotFoundException(
            'Default queue connection not found, You can fix this by adding RabbitMqEnv::RABBITMQ_DEFAULT_CONNECTION = true in your queue connection in config_* files'
        );
    }

    /**
     * @return void
     */
    protected function addConnectionNameLocaleMap()
    {
        foreach ($this->connectionFactory->getQueueConnectionConfigs() as $queueConnectionConfig) {
            foreach ($queueConnectionConfig->getStoreNames() as $storeName) {
                foreach ($this->getLocalesPerStore($storeName) as $locale) {
                    $this->connectionNameLocaleMap[$queueConnectionConfig->getName()][$locale] = true;
                }
            }
        }
    }

    /**
     * @param string $storeName
     * @param string|null $locale
     *
     * @return \PhpAmqpLib\Channel\AMQPChannel[]
     */
    public function getChannelsByStoreName(string $storeName, ?string $locale): array
    {
        $connectionsConfigMap = $this->getConnectionsConfigurationMapByStoreName()[$storeName];

        return $this->getChannelsFilteredByLocaleByConnectionsConfig($connectionsConfigMap, $locale);
    }

    /**
     * @param string $queuePoolName
     * @param string|null $locale
     *
     * @return \PhpAmqpLib\Channel\AMQPChannel[]
     */
    public function getChannelsByQueuePoolName(string $queuePoolName, ?string $locale): array
    {
        $connectionsConfigMap = $this->getConnectionsConfigurationMapByPoolName()[$queuePoolName];

        return $this->getChannelsFilteredByLocaleByConnectionsConfig($connectionsConfigMap, $locale);
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
     * @param \Generated\Shared\Transfer\QueueConnectionTransfer[] $connectionsConfig
     * @param string|null $locale
     *
     * @return \PhpAmqpLib\Channel\AMQPChannel[]
     */
    protected function getChannelsFilteredByLocaleByConnectionsConfig(array $connectionsConfig, ?string $locale): array
    {
        if ($locale === null) {
            return $this->getChannels($this->createConnectionsByConnectionsConfig($connectionsConfig));
        }

        if ($this->connectionNameLocaleMap === null) {
            $this->addConnectionNameLocaleMap();
        }

        $channels = [];
        foreach ($connectionsConfig as $key => $connectionConfig) {
            if (!isset($this->connectionNameLocaleMap[$connectionConfig->getName()][$locale])) {
                continue;
            }

            $channels[$key] = $this->getChannels($this->createConnectionsByConnectionsConfig([$connectionConfig]));
        }

        if (count($channels)) {
            $channels = array_merge(...$channels);
        }

        return $channels;
    }

    /**
     * @return \PhpAmqpLib\Channel\AMQPChannel
     */
    public function getDefaultChannel(): AMQPChannel
    {
        $defaultConnection = $this->connectionFactory->createConnection($this->getDefaultConnectionConfiguration());

        return $defaultConnection->getChannel();
    }

    /**
     * @param \Spryker\Client\RabbitMq\Model\Connection\ConnectionInterface $connection
     *
     * @return string
     */
    protected function getUniqueChannelId(ConnectionInterface $connection): string
    {
        return $connection->getVirtualHost() . '-' . $connection->getChannel()->getChannelId();
    }

    /**
     * @param string $storeName
     *
     * @return string[]
     */
    protected function getLocalesPerStore(string $storeName): array
    {
        return $this->storeClient->getStoreByName($storeName)->getAvailableLocaleIsoCodes();
    }
}
