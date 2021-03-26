<?php

/**
 * MIT License
 * For full license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Spryker\Zed\RabbitMq\Business;

use Spryker\Client\RabbitMq\Model\Connection\ConnectionManagerInterface;
use Spryker\Client\RabbitMq\Model\RabbitMqAdapterInterface;
use Spryker\Zed\Kernel\Business\AbstractBusinessFactory;
use Spryker\Zed\RabbitMq\Business\Model\Exchange\Exchange;
use Spryker\Zed\RabbitMq\Business\Model\Exchange\ExchangeInfo;
use Spryker\Zed\RabbitMq\Business\Model\Exchange\ExchangeInterface;
use Spryker\Zed\RabbitMq\Business\Model\Exchange\Filter\ExchangeFilterByName;
use Spryker\Zed\RabbitMq\Business\Model\Exchange\Filter\ExchangeFilterInterface;
use Spryker\Zed\RabbitMq\Business\Model\Permission\UserPermissionHandler;
use Spryker\Zed\RabbitMq\Business\Model\Permission\UserPermissionHandlerInterface;
use Spryker\Zed\RabbitMq\Business\Model\Queue\Queue;
use Spryker\Zed\RabbitMq\Business\Model\Queue\QueueInfo;
use Spryker\Zed\RabbitMq\Business\Model\Queue\QueueInfoInterface;
use Spryker\Zed\RabbitMq\Business\Model\Queue\QueueInterface;
use Spryker\Zed\RabbitMq\Dependency\Guzzle\RabbitMqToGuzzleInterface;
use Spryker\Zed\RabbitMq\RabbitMqDependencyProvider;

/**
 * @method \Spryker\Zed\RabbitMq\RabbitMqConfig getConfig()
 */
class RabbitMqBusinessFactory extends AbstractBusinessFactory
{
    /**
     * @return \Spryker\Zed\RabbitMq\Business\Model\Queue\QueueInterface
     */
    public function createQueue(): QueueInterface
    {
        return new Queue(
            $this->createQueueInfo(),
            $this->getQueueAdapter()
        );
    }

    /**
     * @return \Spryker\Zed\RabbitMq\Business\Model\Queue\QueueInfoInterface
     */
    protected function createQueueInfo(): QueueInfoInterface
    {
        return new QueueInfo(
            $this->getGuzzleClient(),
            $this->getConfig()->getApiQueuesUrl(),
            $this->getConfig()->getApiUsername(),
            $this->getConfig()->getApiPassword()
        );
    }

    /**
     * @return \Spryker\Zed\RabbitMq\Business\Model\Exchange\ExchangeInterface
     */
    public function createExchange(): ExchangeInterface
    {
        return new Exchange(
            $this->createExchangeInfo(),
            $this->getQueueAdapter(),
            $this->createExchangeFilter()
        );
    }

    /**
     * @return \Spryker\Zed\RabbitMq\Business\Model\Permission\UserPermissionHandlerInterface
     */
    public function createUserPermissionHandler(): UserPermissionHandlerInterface
    {
        return new UserPermissionHandler(
            $this->getGuzzleClient(),
            $this->getConfig()->getApiUserPermissionsUrl(),
            $this->getConfig()->getApiUsername(),
            $this->getConfig()->getApiPassword()
        );
    }

    /**
     * @return \Spryker\Client\RabbitMq\Model\Connection\ConnectionManagerInterface
     */
    public function getConectionManager(): ConnectionManagerInterface
    {
        return $this->getProvidedDependency(RabbitMqDependencyProvider::CONNECTION_MANAGER);
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
            $this->getConfig()->getApiPassword()
        );
    }

    /**
     * @return \Spryker\Zed\RabbitMq\Dependency\Guzzle\RabbitMqToGuzzleInterface
     */
    protected function getGuzzleClient(): RabbitMqToGuzzleInterface
    {
        return $this->getProvidedDependency(RabbitMqDependencyProvider::GUZZLE_CLIENT);
    }

    /**
     * @return \Spryker\Zed\RabbitMq\Business\Model\Exchange\Filter\ExchangeFilterInterface
     */
    protected function createExchangeFilter(): ExchangeFilterInterface
    {
        return new ExchangeFilterByName($this->getConfig()->getExchangeNameBlacklist());
    }

    /**
     * @return \Spryker\Client\RabbitMq\Model\RabbitMqAdapterInterface
     */
    protected function getQueueAdapter(): RabbitMqAdapterInterface
    {
        return $this->getProvidedDependency(RabbitMqDependencyProvider::QUEUE_ADAPTER);
    }
}
