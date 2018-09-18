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
     * @var array|null Keys are pool names, values are lists of channels.
     */
    protected $channelMapByPoolName;

    /**
     * @var array|null Keys are store names, values are lists of channels.
     */
    protected $channelMapByStoreName;

    /**
     * @var string|null
     */
    protected $defaultConnectionName;

    /**
     * @var array
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
        if ($this->channelMapByPoolName === null) {
            $this->channelMapByPoolName = $this->mapConnectionsByPoolName($this->storeClient->getCurrentStore()->getQueuePools());
        }

        return $this->channelMapByPoolName;
    }

    /**
     * @return array
     */
    protected function getConnectionMapByStoreName()
    {
        if ($this->channelMapByStoreName === null) {
            $this->channelMapByStoreName = $this->mapConnectionsByStoreName();
        }

        return $this->channelMapByStoreName;
    }

    /**
     * @return array
     */
    protected function mapConnectionsByStoreName()
    {
        $channelMap = [];
        foreach ($this->connectionMap as $connection) {
            foreach ($connection->getStoreNames() as $storeName) {
                $uniqueChannelId = $this->getUniqueChannelId($connection);
                $channelMap[$storeName][$uniqueChannelId] = $connection;
            }
        }

        return $channelMap;
    }

    /**
     * @param array $queuePools Keys are pool names, values are lists of connection names.
     *
     * @return array
     */
    protected function mapConnectionsByPoolName(array $queuePools)
    {
        $channelMap = [];
        foreach ($queuePools as $queuePoolName => $connectionNames) {
            $channelMap[$queuePoolName] = $this->getConnectionByName($connectionNames);
        }

        return $channelMap;
    }

    /**
     * @param string[] $connectionNames
     *
     * @return \PhpAmqpLib\Channel\AMQPChannel[]
     */
    protected function getConnectionByName(array $connectionNames)
    {
        $channels = [];
        foreach ($connectionNames as $connectionName) {
            $uniqueChannelId = $this->getUniqueChannelId($this->getConnectionMap()[$connectionName]);
            $channels[$uniqueChannelId] = $this->getConnectionMap()[$connectionName];
        }

        return $channels;
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
