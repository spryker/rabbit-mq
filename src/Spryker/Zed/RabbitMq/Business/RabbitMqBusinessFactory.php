<?php

/**
 * MIT License
 * For full license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Spryker\Zed\RabbitMq\Business;

use Spryker\Zed\Kernel\Business\AbstractBusinessFactory;
use Spryker\Zed\RabbitMq\Business\Model\Client\RabbitMqClientDecorator;
use Spryker\Zed\RabbitMq\Business\Model\Connection\ConnectionReader;
use Spryker\Zed\RabbitMq\Business\Model\Connection\ConnectionReaderInterface;
use Spryker\Zed\RabbitMq\Business\Model\Connection\Filter\ConnectionFilterByVirtualHost;
use Spryker\Zed\RabbitMq\Business\Model\Exchange\Exchange;
use Spryker\Zed\RabbitMq\Business\Model\Exchange\ExchangeInfo;
use Spryker\Zed\RabbitMq\Business\Model\Exchange\Filter\ExchangeFilterByName;
use Spryker\Zed\RabbitMq\Business\Model\Init\Command\InitCommandInterface;
use Spryker\Zed\RabbitMq\Business\Model\Init\Executor\InitExecutor;
use Spryker\Zed\RabbitMq\Business\Model\Init\Executor\InitExecutorInterface;
use Spryker\Zed\RabbitMq\Business\Model\Permission\UserPermissionHandler;
use Spryker\Zed\RabbitMq\Business\Model\Queue\Queue;
use Spryker\Zed\RabbitMq\Business\Model\Queue\QueueInfo;
use Spryker\Zed\RabbitMq\Business\Model\User\Command\UserInitCommand;
use Spryker\Zed\RabbitMq\Business\Model\User\Reader\UserReader;
use Spryker\Zed\RabbitMq\Business\Model\User\Reader\UserReaderInterface;
use Spryker\Zed\RabbitMq\Business\Model\User\Writer\UserWriter;
use Spryker\Zed\RabbitMq\Business\Model\User\Writer\UserWriterInterface;
use Spryker\Zed\RabbitMq\Business\Model\UserPermission\Command\UserPermissionInitCommand;
use Spryker\Zed\RabbitMq\Business\Model\UserPermission\Writer\UserPermissionWriter;
use Spryker\Zed\RabbitMq\Business\Model\UserPermission\Writer\UserPermissionWriterInterface;
use Spryker\Zed\RabbitMq\Business\Model\VirtualHost\Command\VirtualHostInitCommand;
use Spryker\Zed\RabbitMq\Business\Model\VirtualHost\Reader\VirtualHostReader;
use Spryker\Zed\RabbitMq\Business\Model\VirtualHost\Reader\VirtualHostReaderInterface;
use Spryker\Zed\RabbitMq\Business\Model\VirtualHost\Writer\VirtualHostWriter;
use Spryker\Zed\RabbitMq\Business\Model\VirtualHost\Writer\VirtualHostWriterInterface;
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
    public function createQueue()
    {
        return new Queue(
            $this->createQueueInfo(),
            $this->getQueueAdapter()
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
            $this->getConfig()->getApiPassword()
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
            $this->createExchangeFilter()
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
            $this->getConfig()->getApiPassword()
        );
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
     * @return \Spryker\Client\Queue\Model\Adapter\AdapterInterface
     */
    protected function getQueueAdapter()
    {
        return $this->getProvidedDependency(RabbitMqDependencyProvider::QUEUE_ADAPTER);
    }

    /**
     * @return \Spryker\Zed\RabbitMq\Business\Model\Init\Executor\InitExecutorInterface
     */
    public function createInitExecutor(): InitExecutorInterface
    {
        return new InitExecutor(
            $this->getInitCommandCollection(),
            $this->createConnectionConfigReader()
        );
    }

    /**
     * @return \Spryker\Zed\RabbitMq\Dependency\Guzzle\RabbitMqToGuzzleInterface
     */
    public function createClientDecorator(): RabbitMqToGuzzleInterface
    {
        return new RabbitMqClientDecorator(
            $this->getGuzzleClient(),
            $this->getConfig()->getApiUsername(),
            $this->getConfig()->getApiPassword()
        );
    }

    /**
     * @return \Spryker\Zed\RabbitMq\Business\Model\Connection\ConnectionReaderInterface
     */
    public function createConnectionConfigReader(): ConnectionReaderInterface
    {
        return new ConnectionReader(
            $this->createConnectionConfigFilters(),
            $this->getConfig()->getConnectionConfigs()
        );
    }

    /**
     * @return array
     */
    protected function createConnectionConfigFilters(): array
    {
        return [
            new ConnectionFilterByVirtualHost($this->getConfig()->getApiVirtualHost()),
        ];
    }

    /**
     * @return \Spryker\Zed\RabbitMq\Business\Model\Init\Command\InitCommandInterface
     */
    protected function createVirtualHostInitCommand(): InitCommandInterface
    {
        return new VirtualHostInitCommand(
            $this->createVirtualHostReader(),
            $this->createVirtualHostWriter()
        );
    }

    /**
     * @return \Spryker\Zed\RabbitMq\Business\Model\VirtualHost\Reader\VirtualHostReaderInterface
     */
    protected function createVirtualHostReader(): VirtualHostReaderInterface
    {
        return new VirtualHostReader(
            $this->getConfig()->getApiVirtualHostsUrl(),
            $this->createClientDecorator()
        );
    }

    /**
     * @return \Spryker\Zed\RabbitMq\Business\Model\VirtualHost\Writer\VirtualHostWriterInterface
     */
    protected function createVirtualHostWriter(): VirtualHostWriterInterface
    {
        return new VirtualHostWriter(
            $this->getConfig()->getApiVirtualHostsUrl(),
            $this->createClientDecorator()
        );
    }

    /**
     * @return \Spryker\Zed\RabbitMq\Business\Model\Init\Command\InitCommandInterface
     */
    protected function createUserInitCommand(): InitCommandInterface
    {
        return new UserInitCommand(
            $this->createUserReader(),
            $this->createUserWriter()
        );
    }

    /**
     * @return \Spryker\Zed\RabbitMq\Business\Model\User\Reader\UserReaderInterface
     */
    protected function createUserReader(): UserReaderInterface
    {
        return new UserReader(
            $this->getConfig()->getApiUsersUrl(),
            $this->createClientDecorator()
        );
    }

    /**
     * @return \Spryker\Zed\RabbitMq\Business\Model\User\Writer\UserWriterInterface
     */
    protected function createUserWriter(): UserWriterInterface
    {
        return new UserWriter(
            $this->getConfig()->getApiUsersUrl(),
            $this->createClientDecorator()
        );
    }

    /**
     * @return array
     */
    protected function getInitCommandCollection(): array
    {
        return [
            $this->createVirtualHostInitCommand(),
            $this->createUserInitCommand(),
            $this->createUserPermissionInitCommand(),
        ];
    }

    /**
     * @return \Spryker\Zed\RabbitMq\Business\Model\Init\Command\InitCommandInterface
     */
    protected function createUserPermissionInitCommand(): InitCommandInterface
    {
        return new UserPermissionInitCommand(
            $this->createUserPermissionsWriter()
        );
    }

    /**
     * @return \Spryker\Zed\RabbitMq\Business\Model\UserPermission\Writer\UserPermissionWriterInterface
     */
    protected function createUserPermissionsWriter(): UserPermissionWriterInterface
    {
        return new UserPermissionWriter(
            $this->getConfig()->getApiPermissionsUrl(),
            $this->createClientDecorator()
        );
    }
}
