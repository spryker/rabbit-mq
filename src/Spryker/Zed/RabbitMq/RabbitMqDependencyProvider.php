<?php

/**
 * MIT License
 * For full license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Spryker\Zed\RabbitMq;

use GuzzleHttp\Client;
use Spryker\Client\RabbitMq\Model\Connection\ConnectionInterface;
use Spryker\Zed\Kernel\AbstractBundleDependencyProvider;
use Spryker\Zed\Kernel\Container;
use Spryker\Zed\RabbitMq\Dependency\Guzzle\RabbitMqToGuzzleBridge;

/**
 * @method \Spryker\Zed\RabbitMq\RabbitMqConfig getConfig()
 */
class RabbitMqDependencyProvider extends AbstractBundleDependencyProvider
{
    public const QUEUE_ADAPTER = 'QUEUE_ADAPTER';
    public const GUZZLE_CLIENT = 'GUZZLE_CLIENT';
    public const CONNECTION = 'CONNECTION';

    /**
     * @param \Spryker\Zed\Kernel\Container $container
     *
     * @return \Spryker\Zed\Kernel\Container
     */
    public function provideBusinessLayerDependencies(Container $container)
    {
        $container = $this->addQueueAdapter($container);
        $container = $this->addGuzzleClient($container);
        $container = $this->addConnection($container);

        return $container;
    }

    /**
     * @param \Spryker\Zed\Kernel\Container $container
     *
     * @return \Spryker\Zed\Kernel\Container
     */
    protected function addQueueAdapter(Container $container)
    {
        $container->set(static::QUEUE_ADAPTER, function () use ($container) {
            return $container->getLocator()->rabbitMq()->client()->createQueueAdapter();
        });

        return $container;
    }

    /**
     * @param \Spryker\Zed\Kernel\Container $container
     *
     * @return \Spryker\Zed\Kernel\Container
     */
    protected function addGuzzleClient(Container $container)
    {
        $container->set(static::GUZZLE_CLIENT, function () {
            $rabbitMqToGuzzleBridge = new RabbitMqToGuzzleBridge(new Client());

            return $rabbitMqToGuzzleBridge;
        });

        return $container;
    }

    /**
     * @param \Spryker\Zed\Kernel\Container $container
     *
     * @return \Spryker\Zed\Kernel\Container
     */
    protected function addConnection(Container $container): Container
    {
        $container->set(static::CONNECTION, static function () use ($container): ConnectionInterface {
            return $container->getLocator()->rabbitMq()->client()->getConnection();
        });

        return $container;
    }
}
