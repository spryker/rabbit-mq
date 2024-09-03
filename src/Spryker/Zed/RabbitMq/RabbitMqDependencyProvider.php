<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
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
    /**
     * @var string
     */
    public const CLIENT_QUEUE = 'CLIENT_QUEUE';

    /**
     * @var string
     */
    public const QUEUE_ADAPTER = 'QUEUE_ADAPTER';

    /**
     * @var string
     */
    public const GUZZLE_CLIENT = 'GUZZLE_CLIENT';

    /**
     * @var string
     */
    public const CONNECTION = 'CONNECTION';

    /**
     * @param \Spryker\Zed\Kernel\Container $container
     *
     * @return \Spryker\Zed\Kernel\Container
     */
    public function provideBusinessLayerDependencies(Container $container)
    {
        $container = $this->addQueueAdapter($container);
        $container = $this->addQueueClient($container);
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
    protected function addQueueClient(Container $container)
    {
        $container->set(static::CLIENT_QUEUE, function () use ($container) {
            return $container->getLocator()->queue()->client();
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
        $container->set(static::CONNECTION, function (Container $container): ConnectionInterface {
            return $container->getLocator()->rabbitMq()->client()->getConnection();
        });

        return $container;
    }
}
