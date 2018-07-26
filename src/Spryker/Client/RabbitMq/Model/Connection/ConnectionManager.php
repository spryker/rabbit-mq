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
    protected function getChannelMapByPoolName()
    {
        if ($this->channelMapByPoolName === null) {
            $this->channelMapByPoolName = $this->mapChannelsByPoolName($this->storeClient->getCurrentStore()->getQueuePools());
        }

        return $this->channelMapByPoolName;
    }

    /**
     * @return array
     */
    protected function getChannelMapByStoreName()
    {
        if ($this->channelMapByStoreName === null) {
            $this->channelMapByStoreName = $this->mapChannelsByStoreName();
        }

        return $this->channelMapByStoreName;
    }

    /**
     * @return array
     */
    protected function mapChannelsByStoreName()
    {
        $channelMap = [];
        foreach ($this->connectionMap as $connection) {
            foreach ($connection->getStoreNames() as $storeName) {
                $uniqueChannelId = $this->getUniqueChannelId($connection);
                $channelMap[$storeName][$uniqueChannelId] = $connection->getChannel();
            }
        }

        return $channelMap;
    }

    /**
     * @param array $queuePools Keys are pool names, values are lists of connection names.
     *
     * @return array
     */
    protected function mapChannelsByPoolName(array $queuePools)
    {
        $channelMap = [];
        foreach ($queuePools as $queuePoolName => $connectionNames) {
            $channelMap[$queuePoolName] = $this->getChannelsByConnectionName($connectionNames);
        }

        return $channelMap;
    }

    /**
     * @param string[] $connectionNames
     *
     * @return \PhpAmqpLib\Channel\AMQPChannel[]
     */
    protected function getChannelsByConnectionName(array $connectionNames)
    {
        $channels = [];
        foreach ($connectionNames as $connectionName) {
            $uniqueChannelId = $this->getUniqueChannelId($this->getConnectionMap()[$connectionName]);
            $channels[$uniqueChannelId] = $this->getConnectionMap()[$connectionName]->getChannel();
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
     *
     * @return \PhpAmqpLib\Channel\AMQPChannel[]
     */
    public function getChannelsByStoreName($storeName)
    {
        return $this->getChannelMapByStoreName()[$storeName];
    }

    /**
     * @param string $queuePoolName
     *
     * @return \PhpAmqpLib\Channel\AMQPChannel[]
     */
    public function getChannelsByQueuePoolName($queuePoolName)
    {
        return $this->getChannelMapByPoolName()[$queuePoolName];
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
}
