<?php

/**
 * MIT License
 * For full license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Spryker\Client\RabbitMq\Model\Connection;

use Generated\Shared\Transfer\QueueConnectionTransfer;
use Spryker\Client\RabbitMq\Dependency\Client\RabbitMqToStoreClientInterface;
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
     * @var \Spryker\Client\RabbitMq\Model\Connection\ConnectionInterface[]|null Keys are connection names.
     */
    protected $connectionMap;

    /**
     * @var array|null Keys are pool names, values are lists of connections.
     */
    protected $connectionMapByPoolName;

    /**
     * @var array|null Keys are store names, values are lists of connections.
     */
    protected $connectionMapByStoreName;

    /**
     * @var string|null
     */
    protected $defaultConnectionName;

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
     * @return array
     */
    protected function getConnectionMapByPoolName()
    {
        if ($this->connectionMapByPoolName === null) {
            $this->connectionMapByPoolName = $this->mapConnectionsByPoolName($this->storeClient->getCurrentStore()->getQueuePools());
        }

        return $this->connectionMapByPoolName;
    }

    /**
     * @return array
     */
    protected function getConnectionMapByStoreName()
    {
        if ($this->connectionMapByStoreName === null) {
            $this->connectionMapByStoreName = $this->mapConnectionsByStoreName();
        }

        return $this->connectionMapByStoreName;
    }

    /**
     * @return array
     */
    protected function mapConnectionsByStoreName()
    {
        $connectionMap = [];
        foreach ($this->connectionMap as $connection) {
            foreach ($connection->getStoreNames() as $storeName) {
                $uniqueChannelId = $this->getUniqueChannelId($connection);
                $connectionMap[$storeName][$uniqueChannelId] = $connection;
            }
        }

        return $connectionMap;
    }

    /**
     * @param array $queuePools Keys are pool names, values are lists of connection names.
     *
     * @return array
     */
    protected function mapConnectionsByPoolName(array $queuePools)
    {
        $connectionMap = [];
        foreach ($queuePools as $queuePoolName => $connectionNames) {
            $connectionMap[$queuePoolName] = $this->getConnectionByName($connectionNames);
        }

        return $connectionMap;
    }

    /**
     * @param string[] $connectionNames
     *
     * @return \PhpAmqpLib\Channel\AMQPChannel[]
     */
    protected function getConnectionByName(array $connectionNames)
    {
        $connections = [];
        foreach ($connectionNames as $connectionName) {
            $uniqueChannelId = $this->getUniqueChannelId($this->getConnectionMap()[$connectionName]);
            $connections[$uniqueChannelId] = $this->getConnectionMap()[$connectionName];
        }

        return $connections;
    }

    /**
     * @return \Spryker\Client\RabbitMq\Model\Connection\ConnectionInterface[]
     */
    protected function getConnectionMap()
    {
        if ($this->connectionMap === null) {
            $this->addConnections();
        }

        return $this->connectionMap;
    }

    /**
     * @return void
     */
    protected function addConnections()
    {
        foreach ($this->connectionFactory->getQueueConnectionConfigs() as $queueConnectionConfig) {
            $connection = $this->getConnection($queueConnectionConfig);
            $this->connectionMap[$connection->getName()] = $connection;
            if ($connection->getIsDefaultConnection()) {
                $this->defaultConnectionName = $connection->getName();
            }
        }
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
     * @param \Generated\Shared\Transfer\QueueConnectionTransfer $queueConnectionConfig
     *
     * @return \Spryker\Client\RabbitMq\Model\Connection\ConnectionInterface
     */
    protected function getConnection(QueueConnectionTransfer $queueConnectionConfig)
    {
        return $this->connectionFactory->createConnection($queueConnectionConfig);
    }

    /**
     * @param string $storeName
     * @param string|null $locale
     *
     * @return \PhpAmqpLib\Channel\AMQPChannel[]
     */
    public function getChannelsByStoreName(string $storeName, ?string $locale)
    {
        $connections = $this->getConnectionMapByStoreName()[$storeName];

        return $this->getChannelsFilteredByLocale($connections, $locale);
    }

    /**
     * @param string $queuePoolName
     * @param string|null $locale
     *
     * @return \PhpAmqpLib\Channel\AMQPChannel[]
     */
    public function getChannelsByQueuePoolName(string $queuePoolName, ?string $locale)
    {
        $connections = $this->getConnectionMapByPoolName()[$queuePoolName];

        return $this->getChannelsFilteredByLocale($connections, $locale);
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
     * @param \Spryker\Client\RabbitMq\Model\Connection\ConnectionInterface[] $connections
     * @param string|null $locale
     *
     * @return \PhpAmqpLib\Channel\AMQPChannel[]
     */
    protected function getChannelsFilteredByLocale(array $connections, ?string $locale): array
    {
        if ($locale === null) {
            return $this->getChannels($connections);
        }

        if ($this->connectionNameLocaleMap === null) {
            $this->addConnectionNameLocaleMap();
        }

        $channels = [];
        foreach ($connections as $key => $connection) {
            if (!isset($this->connectionNameLocaleMap[$connection->getName()][$locale])) {
                continue;
            }

            $channels[$key] = $connection->getChannel();
        }

        return $channels;
    }

    /**
     * @throws \Spryker\Client\RabbitMq\Model\Exception\DefaultConnectionNotFoundException
     *
     * @return \PhpAmqpLib\Channel\AMQPChannel
     */
    public function getDefaultChannel()
    {
        if (!isset($this->getConnectionMap()[$this->defaultConnectionName])) {
            throw new DefaultConnectionNotFoundException(
                'Default queue connection not found, You can fix this by adding RabbitMqEnv::RABBITMQ_DEFAULT_CONNECTION = true in your queue connection in config_* files'
            );
        }

        return $this->getConnectionMap()[$this->defaultConnectionName]->getChannel();
    }

    /**
     * @param \Spryker\Client\RabbitMq\Model\Connection\ConnectionInterface $connection
     *
     * @return string
     */
    protected function getUniqueChannelId(ConnectionInterface $connection)
    {
        return $connection->getVirtualHost() . '-' . $connection->getChannel()->getChannelId();
    }

    /**
     * @param string $storeName
     *
     * @return array
     */
    protected function getLocalesPerStore(string $storeName)
    {
        return $this->storeClient->getStoreByName($storeName)->getAvailableLocaleIsoCodes();
    }
}
