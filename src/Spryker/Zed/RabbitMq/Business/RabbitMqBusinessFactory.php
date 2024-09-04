<?php

/**
 * MIT License
 * For full license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Spryker\Zed\RabbitMq\Business;

use Spryker\Client\RabbitMq\Model\Connection\ConnectionInterface;
use Spryker\Zed\Kernel\Business\AbstractBusinessFactory;
use Spryker\Zed\RabbitMq\Business\Model\Exchange\Exchange;
use Spryker\Zed\RabbitMq\Business\Model\Exchange\ExchangeInfo;
use Spryker\Zed\RabbitMq\Business\Model\Exchange\Filter\ExchangeFilterByName;
use Spryker\Zed\RabbitMq\Business\Model\Metric\QueueMetricReader;
use Spryker\Zed\RabbitMq\Business\Model\Metric\QueueMetricReaderInterface;
use Spryker\Zed\RabbitMq\Business\Model\Permission\UserPermissionHandler;
use Spryker\Zed\RabbitMq\Business\Model\Queue\Queue;
use Spryker\Zed\RabbitMq\Business\Model\Queue\QueueInfo;
use Spryker\Zed\RabbitMq\RabbitMqDependencyProvider;

/**
 * @method \Spryker\Zed\RabbitMq\RabbitMqConfig getConfig()
 */
class RabbitMqBusinessFactory extends AbstractBusinessFactory
{
    /**
     * @return \Spryker\Zed\RabbitMq\Business\Model\Queue\QueueInterface
     */
    public function createQueue()
    {
        return new Queue(
            $this->createQueueInfo(),
            $this->getQueueAdapter(),
        );
    }

    /**
     * @return \Spryker\Zed\RabbitMq\Business\Model\Queue\QueueInfoInterface
     */
    protected function createQueueInfo()
    {
        return new QueueInfo(
            $this->getGuzzleClient(),
            $this->getConfig()->getApiQueuesUrl(),
            $this->getConfig()->getApiUsername(),
            $this->getConfig()->getApiPassword(),
        );
    }

    /**
     * @return \Spryker\Zed\RabbitMq\Business\Model\Exchange\ExchangeInterface
     */
    public function createExchange()
    {
        return new Exchange(
            $this->createExchangeInfo(),
            $this->getQueueAdapter(),
            $this->createExchangeFilter(),
        );
    }

    /**
     * @return \Spryker\Zed\RabbitMq\Business\Model\Permission\UserPermissionHandlerInterface
     */
    public function createUserPermissionHandler()
    {
        return new UserPermissionHandler(
            $this->getGuzzleClient(),
            $this->getConfig()->getApiUserPermissionsUrl(),
            $this->getConfig()->getApiUsername(),
            $this->getConfig()->getApiPassword(),
        );
    }

    public function createQueueMetricReader(): QueueMetricReaderInterface
    {
        return new QueueMetricReader(
            $this->getQueueAdapter(),
        );
    }

    /**
     * @return \Spryker\Client\RabbitMq\Model\Connection\ConnectionInterface
     */
    public function getConection(): ConnectionInterface
    {
        return $this->getProvidedDependency(RabbitMqDependencyProvider::CONNECTION);
    }

    /**
     * @return \Spryker\Zed\RabbitMq\Business\Model\Exchange\ExchangeInfoInterface
     */
    protected function createExchangeInfo()
    {
        return new ExchangeInfo(
            $this->getGuzzleClient(),
            $this->getConfig()->getApiExchangesUrl(),
            $this->getConfig()->getApiUsername(),
            $this->getConfig()->getApiPassword(),
        );
    }

    /**
     * @return \Spryker\Zed\RabbitMq\Dependency\Guzzle\RabbitMqToGuzzleInterface
     */
    protected function getGuzzleClient()
    {
        return $this->getProvidedDependency(RabbitMqDependencyProvider::GUZZLE_CLIENT);
    }

    /**
     * @return \Spryker\Zed\RabbitMq\Business\Model\Exchange\Filter\ExchangeFilterInterface
     */
    protected function createExchangeFilter()
    {
        return new ExchangeFilterByName($this->getConfig()->getExchangeNameBlacklist());
    }

    /**
     * @return \Spryker\Client\RabbitMq\Model\RabbitMqAdapterInterface
     */
    protected function getQueueAdapter()
    {
        return $this->getProvidedDependency(RabbitMqDependencyProvider::QUEUE_ADAPTER);
    }
}
