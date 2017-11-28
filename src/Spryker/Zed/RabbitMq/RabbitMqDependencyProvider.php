<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\RabbitMq;

use GuzzleHttp\Client;
use Spryker\Zed\Kernel\AbstractBundleDependencyProvider;
use Spryker\Zed\Kernel\Container;
use Spryker\Zed\RabbitMq\Dependency\Guzzle\RabbitMqToGuzzleBridge;

class RabbitMqDependencyProvider extends AbstractBundleDependencyProvider
{
    const QUEUE_ADAPTER = 'QUEUE_ADAPTER';
    const GUZZLE_CLIENT = 'GUZZLE_CLIENT';

    /**
     * @param \Spryker\Zed\Kernel\Container $container
     *
     * @return \Spryker\Zed\Kernel\Container
     */
    public function provideBusinessLayerDependencies(Container $container)
    {
        $container = $this->addQueueAdapter($container);
        $container = $this->addGuzzleClient($container);

        return $container;
    }

    /**
     * @param \Spryker\Zed\Kernel\Container $container
     *
     * @return \Spryker\Zed\Kernel\Container
     */
    protected function addQueueAdapter(Container $container)
    {
        $container[static::QUEUE_ADAPTER] = function () use ($container) {
            return $container->getLocator()->rabbitMq()->client()->createQueueAdapter();
        };

        return $container;
    }

    /**
     * @param \Spryker\Zed\Kernel\Container $container
     *
     * @return \Spryker\Zed\Kernel\Container
     */
    protected function addGuzzleClient(Container $container)
    {
        $container[static::GUZZLE_CLIENT] = function () {
            $rabbitMqToGuzzleBridge = new RabbitMqToGuzzleBridge(new Client());

            return $rabbitMqToGuzzleBridge;
        };

        return $container;
    }
}
