<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Client\RabbitMq;

use PhpAmqpLib\Connection\AMQPStreamConnection;
use Spryker\Client\Kernel\AbstractFactory;
use Spryker\Client\RabbitMq\Model\Connection\Connection;
use Spryker\Client\RabbitMq\Model\Consumer\Consumer;
use Spryker\Client\RabbitMq\Model\Helper\QueueEstablishmentHelper;
use Spryker\Client\RabbitMq\Model\Manager\Manager;
use Spryker\Client\RabbitMq\Model\Publisher\Publisher;
use Spryker\Client\RabbitMq\Model\RabbitMqAdapter;

/**
 * @method RabbitMqConfig getConfig()
 */
class RabbitMqFactory extends AbstractFactory
{

    /**
     * @var \Spryker\Client\RabbitMq\Model\Connection\ConnectionInterface
     */
    protected static $connection;

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
     * @return \Spryker\Client\RabbitMq\Model\Connection\Connection
     */
    public function createConnection()
    {
        return new Connection(
            $this->createAMQPStreamConnection(),
            $this->createQueueEstablishmentHelper(),
            $this->getQueueConnectionConfig()->getQueueOptionCollection()
        );
    }

    /**
     * @return \Spryker\Client\RabbitMq\Model\Connection\ConnectionInterface
     */
    public function createStaticConnection()
    {
        if (static::$connection === null) {
            static::$connection = $this->createConnection();
        }

        return static::$connection;
    }

    /**
     * @return \Spryker\Client\RabbitMq\Model\Manager\Manager
     */
    protected function createManager()
    {
        return new Manager(
            $this->createStaticConnection()->getChannel(),
            $this->createQueueEstablishmentHelper()
        );
    }

    /**
     * @return \Spryker\Client\RabbitMq\Model\Publisher\Publisher
     */
    protected function createPublisher()
    {
        return new Publisher(
            $this->createStaticConnection()->getChannel()
        );
    }

    /**
     * @return \Spryker\Client\RabbitMq\Model\Consumer\Consumer
     */
    protected function createConsumer()
    {
        return new Consumer(
            $this->createStaticConnection()->getChannel()
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
     * @return \Generated\Shared\Transfer\QueueConnectionTransfer
     */
    protected function getQueueConnectionConfig()
    {
        return $this->getConfig()->getQueueConnection();
    }

    /**
     * @return \PhpAmqpLib\Connection\AMQPStreamConnection
     */
    protected function createAMQPStreamConnection()
    {
        $queueConnectionConfig = $this->getQueueConnectionConfig();

        return new AMQPStreamConnection(
            $queueConnectionConfig->getHost(),
            $queueConnectionConfig->getPort(),
            $queueConnectionConfig->getUsername(),
            $queueConnectionConfig->getPassword(),
            $queueConnectionConfig->getVirtualHost()
        );
    }

}
