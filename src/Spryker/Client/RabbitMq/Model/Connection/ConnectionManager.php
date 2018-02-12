<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Client\RabbitMq\Model\Connection;

use Generated\Shared\Transfer\StoreTransfer;
use Spryker\Client\RabbitMq\RabbitMqFactory;
use Spryker\Shared\RabbitMq\RabbitMqConfigInterface;

class ConnectionManager
{
    /**
     * @var \Spryker\Client\RabbitMq\Model\Connection\ConnectionInterface[]
     */
    protected $connectionMap = [];

    /**
     * @var string|null
     */
    protected $defaultConnectionName;

    /**
     * @var array|null
     */
    protected $channelMapBuffer = null;

    /**
     * @var \Generated\Shared\Transfer\StoreTransfer
     */
    protected $currentStoreTransfer;

    /**
     * @var \Spryker\Client\RabbitMq\RabbitMqFactory
     */
    protected $factory;

    /**
     * @param \Generated\Shared\Transfer\StoreTransfer $currentStoreTransfer
     * @param \Spryker\Client\RabbitMq\RabbitMqFactory $factory
     */
    public function __construct(StoreTransfer $currentStoreTransfer, RabbitMqFactory $factory)
    {
        $this->currentStoreTransfer = $currentStoreTransfer;
        $this->factory = $factory;
    }

    /**
     * @return array
     */
    protected function getChannelMap()
    {
        if ($this->channelMapBuffer === null) {
            $channelMap = [
                RabbitMqConfigInterface::QUEUE_POOL_NAME_DEFAULT => [$this->getDefaultChannel()],
            ];

            $eventQueueMap = $this->currentStoreTransfer->getQueuePools();
            foreach ($eventQueueMap as $poolName => $connectionNames) {
                $channelMap[$poolName] = [];
                foreach ($connectionNames as $connectionName) {
                    $channelMap[$poolName][] = $this->getConnectionMap()[$connectionName]->getChannel();
                }
            }

            $this->channelMapBuffer = $channelMap;
        }

        return $this->channelMapBuffer;
    }

    /**
     * @param \Spryker\Client\RabbitMq\Model\Connection\ConnectionInterface $connection
     *
     * @return void
     */
    protected function addConnection(ConnectionInterface $connection)
    {
        $this->connectionMap[$connection->getName()] = $connection;
        if ($connection->getIsDefaultConnection()) {
            $this->defaultConnectionName = $connection->getName();
        }
    }

    /**
     * @return \Spryker\Client\RabbitMq\Model\Connection\ConnectionInterface[]
     */
    protected function getConnectionMap()
    {
        if ($this->connectionMap === null) {
            foreach ($this->factory->getQueueConnectionConfigs() as $queueConnectionConfig) {
                $this->addConnection($this->factory->createConnection($queueConnectionConfig));
            }
        }

        return $this->connectionMap;
    }

    /**
     * @param string $poolName
     *
     * @return \PhpAmqpLib\Channel\AMQPChannel[]
     */
    public function getChannelsByQueuePoolName($poolName)
    {
        return $this->getChannelMap()[$poolName];
    }

    /**
     * @return \PhpAmqpLib\Channel\AMQPChannel
     */
    public function getDefaultChannel()
    {
        return $this->getConnectionMap()[$this->defaultConnectionName]->getChannel();
    }
}
