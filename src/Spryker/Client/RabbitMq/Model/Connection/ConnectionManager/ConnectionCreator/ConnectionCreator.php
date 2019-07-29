<?php

/**
 * MIT License
 * For full license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Spryker\Client\RabbitMq\Model\Connection\ConnectionManager\ConnectionCreator;

use Generated\Shared\Transfer\QueueConnectionTransfer;
use Spryker\Client\RabbitMq\Model\Connection\ConnectionFactoryInterface;
use Spryker\Client\RabbitMq\Model\Connection\ConnectionInterface;

class ConnectionCreator implements ConnectionCreatorInterface
{
    /**
     * @var \Spryker\Client\RabbitMq\Model\Connection\ConnectionFactoryInterface
     */
    protected $connectionFactory;

    /**
     * @var \Spryker\Client\RabbitMq\Model\Connection\ConnectionInterface[]
     */
    protected $createdConnectionsMap;

    /**
     * @param \Spryker\Client\RabbitMq\Model\Connection\ConnectionFactoryInterface $connectionFactory
     */
    public function __construct(ConnectionFactoryInterface $connectionFactory)
    {
        $this->connectionFactory = $connectionFactory;
    }

    /**
     * @param \Generated\Shared\Transfer\QueueConnectionTransfer $connectionConfig
     *
     * @return \Spryker\Client\RabbitMq\Model\Connection\ConnectionInterface
     */
    public function createConnectionByConfig(QueueConnectionTransfer $connectionConfig): ConnectionInterface
    {
        return $this->createConnection($connectionConfig);
    }

    /**
     * @param \Generated\Shared\Transfer\QueueConnectionTransfer $connectionConfig
     *
     * @return \Spryker\Client\RabbitMq\Model\Connection\ConnectionInterface
     */
    protected function createConnection(QueueConnectionTransfer $connectionConfig): ConnectionInterface
    {
        if (isset($this->createdConnectionsMap[$connectionConfig->getName()])) {
            return $this->createdConnectionsMap[$connectionConfig->getName()];
        }

        $connection = $this->connectionFactory->createConnection($connectionConfig);
        $this->createdConnectionsMap[$connectionConfig->getName()] = $connection;

        return $connection;
    }

    /**
     * @param \Generated\Shared\Transfer\QueueConnectionTransfer[] $connectionsConfig
     *
     * @return \Spryker\Client\RabbitMq\Model\Connection\ConnectionInterface[]
     */
    public function createConnectionsByConfig(array $connectionsConfig): array
    {
        $connections = [];

        foreach ($connectionsConfig as $connectionConfig) {
            $connection = $this->createConnection($connectionConfig);
            $uniqueChannelId = $this->getUniqueChannelId($connection);
            if (!isset($connections[$uniqueChannelId])) {
                $connections[$uniqueChannelId] = $connection;
            }
        }

        return $connections;
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
}
