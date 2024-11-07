<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Client\RabbitMq;

use Spryker\Client\Kernel\AbstractFactory;
use Spryker\Client\Queue\Model\Adapter\AdapterInterface;
use Spryker\Client\RabbitMq\Dependency\Client\RabbitMqToStoreClientInterface;
use Spryker\Client\RabbitMq\Model\Connection\ConnectionBuilder\ConnectionBuilder;
use Spryker\Client\RabbitMq\Model\Connection\ConnectionBuilder\ConnectionBuilderInterface;
use Spryker\Client\RabbitMq\Model\Connection\ConnectionManager;
use Spryker\Client\RabbitMq\Model\Connection\ConnectionManagerInterface;
use Spryker\Client\RabbitMq\Model\Connection\QueueConnectionTransferFilter\QueueConnectionTransferFilter;
use Spryker\Client\RabbitMq\Model\Connection\QueueConnectionTransferFilter\QueueConnectionTransferFilterInterface;
use Spryker\Client\RabbitMq\Model\Connection\QueueConnectionTransferMapper\QueueConnectionTransferMapper;
use Spryker\Client\RabbitMq\Model\Connection\QueueConnectionTransferMapper\QueueConnectionTransferMapperInterface;
use Spryker\Client\RabbitMq\Model\Consumer\Consumer;
use Spryker\Client\RabbitMq\Model\Consumer\ConsumerInterface;
use Spryker\Client\RabbitMq\Model\Helper\QueueEstablishmentHelper;
use Spryker\Client\RabbitMq\Model\Helper\QueueEstablishmentHelperInterface;
use Spryker\Client\RabbitMq\Model\Manager\Manager;
use Spryker\Client\RabbitMq\Model\Manager\ManagerInterface;
use Spryker\Client\RabbitMq\Model\Publisher\Publisher;
use Spryker\Client\RabbitMq\Model\Publisher\PublisherInterface;
use Spryker\Client\RabbitMq\Model\Queue\QueueMetricReader;
use Spryker\Client\RabbitMq\Model\Queue\QueueMetricReaderInterface;
use Spryker\Client\RabbitMq\Model\RabbitMqAdapter;
use Spryker\Client\RabbitMq\Model\RabbitMqAdapterInterface;

/**
 * @method \Spryker\Client\RabbitMq\RabbitMqConfig getConfig()
 */
class RabbitMqFactory extends AbstractFactory
{
    /**
     * @var \Spryker\Client\RabbitMq\Model\Connection\ConnectionManagerInterface
     */
    protected static $connectionManager;

    /**
     * @var \Spryker\Client\RabbitMq\Model\RabbitMqAdapterInterface|null
     */
    protected static RabbitMqAdapterInterface|null $rabbitMqAdapter = null;

    /**
     * @return \Spryker\Client\Queue\Model\Adapter\AdapterInterface
     */
    public function createQueueAdapter(): AdapterInterface
    {
        if (static::$rabbitMqAdapter === null) {
            static::$rabbitMqAdapter = new RabbitMqAdapter(
                $this->createManager(),
                $this->createPublisher(),
                $this->createConsumer(),
                $this->createQueueMetricReader(),
            );
        }

        return static::$rabbitMqAdapter;
    }

    /**
     * @return \Spryker\Client\RabbitMq\Model\Connection\ConnectionManagerInterface
     */
    public function getStaticConnectionManager(): ConnectionManagerInterface
    {
        if (static::$connectionManager === null) {
            static::$connectionManager = $this->createConnectionManager();
        }

        return static::$connectionManager;
    }

    /**
     * @return \Spryker\Client\RabbitMq\Model\Connection\ConnectionManagerInterface
     */
    public function createConnectionManager(): ConnectionManagerInterface
    {
        return new ConnectionManager(
            $this->getConfig(),
            $this->getStoreClient(),
            $this->createQueueConnectionTransferMapper(),
            $this->createQueueConnectionTransferFilter(),
            $this->createConnectionBuilder(),
        );
    }

    /**
     * @return \Spryker\Client\RabbitMq\Dependency\Client\RabbitMqToStoreClientInterface
     */
    public function getStoreClient(): RabbitMqToStoreClientInterface
    {
        return $this->getProvidedDependency(RabbitMqDependencyProvider::CLIENT_STORE);
    }

    /**
     * @return \Spryker\Client\RabbitMq\Model\Connection\QueueConnectionTransferMapper\QueueConnectionTransferMapperInterface
     */
    public function createQueueConnectionTransferMapper(): QueueConnectionTransferMapperInterface
    {
        return new QueueConnectionTransferMapper($this->getConfig());
    }

    /**
     * @return \Spryker\Client\RabbitMq\Model\Connection\QueueConnectionTransferFilter\QueueConnectionTransferFilterInterface
     */
    public function createQueueConnectionTransferFilter(): QueueConnectionTransferFilterInterface
    {
        return new QueueConnectionTransferFilter($this->getStoreClient());
    }

    /**
     * @return \Spryker\Client\RabbitMq\Model\Connection\ConnectionBuilder\ConnectionBuilderInterface
     */
    public function createConnectionBuilder(): ConnectionBuilderInterface
    {
        return new ConnectionBuilder(
            $this->getConfig(),
            $this->getStoreClient(),
            $this->createQueueEstablishmentHelper(),
        );
    }

    /**
     * @return \Spryker\Client\RabbitMq\Model\Manager\ManagerInterface
     */
    public function createManager(): ManagerInterface
    {
        return new Manager(
            $this->getStaticConnectionManager()->getDefaultChannel(),
            $this->createQueueEstablishmentHelper(),
        );
    }

    /**
     * @return \Spryker\Client\RabbitMq\Model\Publisher\PublisherInterface
     */
    public function createPublisher(): PublisherInterface
    {
        return new Publisher(
            $this->getStaticConnectionManager(),
            $this->getConfig(),
        );
    }

    /**
     * @return \Spryker\Client\RabbitMq\Model\Consumer\ConsumerInterface
     */
    public function createConsumer(): ConsumerInterface
    {
        return new Consumer(
            $this->getStaticConnectionManager()->getDefaultChannel(),
        );
    }

    /**
     * @return \Spryker\Client\RabbitMq\Model\Helper\QueueEstablishmentHelperInterface
     */
    public function createQueueEstablishmentHelper(): QueueEstablishmentHelperInterface
    {
        return new QueueEstablishmentHelper();
    }

    /**
     * @return \Spryker\Client\RabbitMq\Model\Queue\QueueMetricReaderInterface
     */
    public function createQueueMetricReader(): QueueMetricReaderInterface
    {
        return new QueueMetricReader($this->getStaticConnectionManager());
    }
}
