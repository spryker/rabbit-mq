<?php

/**
 * MIT License
 * For full license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Spryker\Client\RabbitMq;

use Spryker\Client\Kernel\AbstractFactory;
use Spryker\Client\RabbitMq\Model\Connection\ConnectionFactory;
use Spryker\Client\RabbitMq\Model\Connection\ConnectionManager;
use Spryker\Client\RabbitMq\Model\Consumer\Consumer;
use Spryker\Client\RabbitMq\Model\Helper\QueueEstablishmentHelper;
use Spryker\Client\RabbitMq\Model\Manager\Manager;
use Spryker\Client\RabbitMq\Model\Publisher\Publisher;
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
    public function createQueueAdapter()
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
    public function getStaticConnectionManager()
    {
        if (static::$connectionManager === null) {
            static::$connectionManager = $this->createConnectionManager();
        }

        return static::$connectionManager;
    }

    /**
     * @return \Spryker\Client\RabbitMq\Model\Connection\ConnectionManagerInterface
     */
    protected function createConnectionManager()
    {
        $connectionManager = new ConnectionManager(
            $this->getStoreClient(),
            $this->createConnectionFactory()
        );

        return $connectionManager;
    }

    /**
     * @return \Spryker\Client\RabbitMq\Dependency\Client\RabbitMqToStoreClientInterface
     */
    protected function getStoreClient()
    {
        return $this->getProvidedDependency(RabbitMqDependencyProvider::CLIENT_STORE);
    }

    /**
     * @return \Spryker\Client\RabbitMq\Model\Connection\ConnectionFactoryInterface
     */
    protected function createConnectionFactory()
    {
        return new ConnectionFactory();
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
            $this->getStaticConnectionManager(),
            $this->getConfig()
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
}
