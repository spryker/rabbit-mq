<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Client\RabbitMq;

use Generated\Shared\Transfer\QueueConnectionTransfer;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use Spryker\Client\Kernel\AbstractFactory;
use Spryker\Client\RabbitMq\Model\Connection\Connection;
use Spryker\Client\RabbitMq\Model\Connection\ConnectionManager;
use Spryker\Client\RabbitMq\Model\Consumer\Consumer;
use Spryker\Client\RabbitMq\Model\Helper\QueueEstablishmentHelper;
use Spryker\Client\RabbitMq\Model\Manager\Manager;
use Spryker\Client\RabbitMq\Model\Publisher\Publisher;
use Spryker\Client\RabbitMq\Model\RabbitMqAdapter;
use Spryker\Zed\Store\Business\StoreFacade;

/**
 * @method \Spryker\Client\RabbitMq\RabbitMqConfig getConfig()
 */
class RabbitMqFactory extends AbstractFactory
{
    /**
     * @var \Spryker\Client\RabbitMq\Model\Connection\ConnectionManager
     */
    protected static $connectionManager;

    /**
     * @return \Spryker\Client\Queue\Model\Adapter\AdapterInterface
     */
    public function createQueueAdapter()
    {
        return new RabbitMqAdapter(
            $this->createManager(),
            $this->createPublisher(),
            $this->createConsumer()
        );
    }

    /**
     * @return \Spryker\Client\RabbitMq\Model\Connection\ConnectionManager
     */
    protected function createConnectionManager()
    {
        $connectionManager = new ConnectionManager(
            (new StoreFacade())->getCurrentStore(),
            $this
        );

        return $connectionManager;
    }

    /**
     * @param \Generated\Shared\Transfer\QueueConnectionTransfer $queueConnectionConfig
     *
     * @return \Spryker\Client\RabbitMq\Model\Connection\ConnectionInterface
     */
    public function createConnection(QueueConnectionTransfer $queueConnectionConfig)
    {
        return new Connection(
            $this->createAMQPStreamConnection($queueConnectionConfig),
            $this->createQueueEstablishmentHelper(),
            $queueConnectionConfig
        );
    }

    /**
     * @return \Spryker\Client\RabbitMq\Model\Connection\ConnectionManager
     */
    public function getStaticConnectionManager()
    {
        if (static::$connectionManager === null) {
            static::$connectionManager = $this->createConnectionManager();
        }

        return static::$connectionManager;
    }

    /**
     * @return \Spryker\Client\RabbitMq\Model\Manager\Manager
     */
    protected function createManager()
    {
        return new Manager(
            $this->getStaticConnectionManager()->getDefaultChannel(),
            $this->createQueueEstablishmentHelper()
        );
    }

    /**
     * @return \Spryker\Client\RabbitMq\Model\Publisher\Publisher
     */
    protected function createPublisher()
    {
        return new Publisher(
            $this->getStaticConnectionManager()
        );
    }

    /**
     * @return \Spryker\Client\RabbitMq\Model\Consumer\Consumer
     */
    protected function createConsumer()
    {
        return new Consumer(
            $this->getStaticConnectionManager()->getDefaultChannel()
        );
    }

    /**
     * @return \Spryker\Client\RabbitMq\Model\Helper\QueueEstablishmentHelperInterface
     */
    protected function createQueueEstablishmentHelper()
    {
        return new QueueEstablishmentHelper();
    }

    /**
     * @return \Generated\Shared\Transfer\QueueConnectionTransfer[]
     */
    public function getQueueConnectionConfigs()
    {
        return $this->getConfig()->getQueueConnections();
    }

    /**
     * @param \Generated\Shared\Transfer\QueueConnectionTransfer $queueConnectionConfig
     *
     * @return \PhpAmqpLib\Connection\AMQPStreamConnection
     */
    protected function createAMQPStreamConnection(QueueConnectionTransfer $queueConnectionConfig)
    {
        $streamConnection = new AMQPStreamConnection(
            $queueConnectionConfig->getHost(),
            $queueConnectionConfig->getPort(),
            $queueConnectionConfig->getUsername(),
            $queueConnectionConfig->getPassword(),
            $queueConnectionConfig->getVirtualHost()
        );

        return $streamConnection;
    }
}
