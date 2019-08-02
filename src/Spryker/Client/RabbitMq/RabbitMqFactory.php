<?php

/**
 * MIT License
 * For full license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Spryker\Client\RabbitMq;

use Spryker\Client\Kernel\AbstractFactory;
use Spryker\Client\Queue\Model\Adapter\AdapterInterface;
use Spryker\Client\RabbitMq\Dependency\Client\RabbitMqToStoreClientInterface;
use Spryker\Client\RabbitMq\Model\Connection\ConnectionConfigFilter\ConnectionConfigFilter;
use Spryker\Client\RabbitMq\Model\Connection\ConnectionConfigFilter\ConnectionConfigFilterInterface;
use Spryker\Client\RabbitMq\Model\Connection\ConnectionConfigMapper\ConnectionConfigMapper;
use Spryker\Client\RabbitMq\Model\Connection\ConnectionConfigMapper\ConnectionConfigMapperInterface;
use Spryker\Client\RabbitMq\Model\Connection\ConnectionCreator\ConnectionCreator;
use Spryker\Client\RabbitMq\Model\Connection\ConnectionCreator\ConnectionCreatorInterface;
use Spryker\Client\RabbitMq\Model\Connection\ConnectionFactory;
use Spryker\Client\RabbitMq\Model\Connection\ConnectionFactoryInterface;
use Spryker\Client\RabbitMq\Model\Connection\ConnectionManager;
use Spryker\Client\RabbitMq\Model\Connection\ConnectionManagerInterface;
use Spryker\Client\RabbitMq\Model\Consumer\Consumer;
use Spryker\Client\RabbitMq\Model\Consumer\ConsumerInterface;
use Spryker\Client\RabbitMq\Model\Helper\QueueEstablishmentHelper;
use Spryker\Client\RabbitMq\Model\Helper\QueueEstablishmentHelperInterface;
use Spryker\Client\RabbitMq\Model\Manager\Manager;
use Spryker\Client\RabbitMq\Model\Manager\ManagerInterface;
use Spryker\Client\RabbitMq\Model\Publisher\Publisher;
use Spryker\Client\RabbitMq\Model\Publisher\PublisherInterface;
use Spryker\Client\RabbitMq\Model\RabbitMqAdapter;

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
     * @return \Spryker\Client\Queue\Model\Adapter\AdapterInterface
     */
    public function createQueueAdapter(): AdapterInterface
    {
        return new RabbitMqAdapter(
            $this->createManager(),
            $this->createPublisher(),
            $this->createConsumer()
        );
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
            $this->createConnectionConfigMapper(),
            $this->createConnectionConfigFilter(),
            $this->createConnectionCreator()
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
     * @return \Spryker\Client\RabbitMq\Model\Connection\ConnectionConfigMapper\ConnectionConfigMapperInterface
     */
    public function createConnectionConfigMapper(): ConnectionConfigMapperInterface
    {
        return new ConnectionConfigMapper($this->getConfig());
    }

    /**
     * @return \Spryker\Client\RabbitMq\Model\Connection\ConnectionConfigFilter\ConnectionConfigFilterInterface
     */
    public function createConnectionConfigFilter(): ConnectionConfigFilterInterface
    {
        return new ConnectionConfigFilter($this->getStoreClient());
    }

    /**
     * @return \Spryker\Client\RabbitMq\Model\Connection\ConnectionCreator\ConnectionCreatorInterface
     */
    public function createConnectionCreator(): ConnectionCreatorInterface
    {
        return new ConnectionCreator($this->createQueueEstablishmentHelper());
    }

    /**
     * @return \Spryker\Client\RabbitMq\Model\Manager\ManagerInterface
     */
    public function createManager(): ManagerInterface
    {
        return new Manager(
            $this->getStaticConnectionManager()->getDefaultChannel(),
            $this->createQueueEstablishmentHelper()
        );
    }

    /**
     * @return \Spryker\Client\RabbitMq\Model\Publisher\PublisherInterface
     */
    public function createPublisher(): PublisherInterface
    {
        return new Publisher(
            $this->getStaticConnectionManager(),
            $this->getConfig()
        );
    }

    /**
     * @return \Spryker\Client\RabbitMq\Model\Consumer\ConsumerInterface
     */
    public function createConsumer(): ConsumerInterface
    {
        return new Consumer(
            $this->getStaticConnectionManager()->getDefaultChannel()
        );
    }

    /**
     * @return \Spryker\Client\RabbitMq\Model\Helper\QueueEstablishmentHelperInterface
     */
    public function createQueueEstablishmentHelper(): QueueEstablishmentHelperInterface
    {
        return new QueueEstablishmentHelper();
    }
}
