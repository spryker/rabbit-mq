<?php

/**
 * MIT License
 * For full license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Spryker\Client\RabbitMq\Model\Connection\ConnectionCreator;

use Generated\Shared\Transfer\QueueConnectionTransfer;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use Spryker\Client\RabbitMq\Model\Connection\Connection;
use Spryker\Client\RabbitMq\Model\Connection\ConnectionInterface;
use Spryker\Client\RabbitMq\Model\Helper\QueueEstablishmentHelperInterface;

class ConnectionCreator implements ConnectionCreatorInterface
{
    /**
     * @var \Spryker\Client\RabbitMq\Model\Helper\QueueEstablishmentHelperInterface
     */
    protected $queueEstablishmentHelper;

    /**
     * @var \Spryker\Client\RabbitMq\Model\Connection\ConnectionInterface[]
     */
    protected $createdConnectionsMap;

    /**
     * @param \Spryker\Client\RabbitMq\Model\Helper\QueueEstablishmentHelperInterface $queueEstablishmentHelper
     */
    public function __construct(QueueEstablishmentHelperInterface $queueEstablishmentHelper)
    {
        $this->queueEstablishmentHelper = $queueEstablishmentHelper;
    }

    /**
     * @param \Generated\Shared\Transfer\QueueConnectionTransfer $connectionConfig
     *
     * @return \Spryker\Client\RabbitMq\Model\Connection\ConnectionInterface
     */
    public function createConnectionByConfig(QueueConnectionTransfer $connectionConfig): ConnectionInterface
    {
        return $this->createOrGetConnection($connectionConfig);
    }

    /**
     * @param \Generated\Shared\Transfer\QueueConnectionTransfer $connectionConfig
     *
     * @return \Spryker\Client\RabbitMq\Model\Connection\ConnectionInterface
     */
    protected function createOrGetConnection(QueueConnectionTransfer $connectionConfig): ConnectionInterface
    {
        if (isset($this->createdConnectionsMap[$connectionConfig->getName()])) {
            return $this->createdConnectionsMap[$connectionConfig->getName()];
        }

        $connection = $this->createConnection($connectionConfig);
        $this->createdConnectionsMap[$connectionConfig->getName()] = $connection;

        return $connection;
    }

    /**
     * @param \Generated\Shared\Transfer\QueueConnectionTransfer $queueConnectionConfig
     *
     * @return \Spryker\Client\RabbitMq\Model\Connection\ConnectionInterface
     */
    protected function createConnection(QueueConnectionTransfer $queueConnectionConfig): ConnectionInterface
    {
        return new Connection(
            $this->createAmqpStreamConnection($queueConnectionConfig),
            $this->queueEstablishmentHelper,
            $queueConnectionConfig
        );
    }

    /**
     * @param \Generated\Shared\Transfer\QueueConnectionTransfer $queueConnectionTransfer
     *
     * @return \PhpAmqpLib\Connection\AMQPStreamConnection
     */
    protected function createAmqpStreamConnection(QueueConnectionTransfer $queueConnectionTransfer): AMQPStreamConnection
    {
        $streamConnection = new AMQPStreamConnection(
            $queueConnectionTransfer->getHost(),
            $queueConnectionTransfer->getPort(),
            $queueConnectionTransfer->getUsername(),
            $queueConnectionTransfer->getPassword(),
            $queueConnectionTransfer->getVirtualHost(),
            $queueConnectionTransfer->getInsist() ?? false,
            $queueConnectionTransfer->getLoginMethod() ?? 'AMQPLAIN',
            $queueConnectionTransfer->getLoginResponse(),
            $queueConnectionTransfer->getLocale() ?? 'en_US',
            $queueConnectionTransfer->getConnectionTimeout() ?? 3,
            $queueConnectionTransfer->getReadWriteTimeout() ?? 130,
            null,
            $queueConnectionTransfer->getKeepAlive() ?? false,
            $queueConnectionTransfer->getHeartBeat() ?? 0,
            $queueConnectionTransfer->getChannelRpcTimeout() ?? 0,
            $queueConnectionTransfer->getSslProtocol()
        );

        return $streamConnection;
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
            $connection = $this->createOrGetConnection($connectionConfig);
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
