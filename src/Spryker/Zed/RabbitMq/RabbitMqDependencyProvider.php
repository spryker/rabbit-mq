<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\RabbitMq;

use Spryker\Client\RabbitMq\RabbitMqClient;
use Spryker\Zed\Kernel\AbstractBundleDependencyProvider;
use Spryker\Zed\Kernel\Container;

class RabbitMqDependencyProvider extends AbstractBundleDependencyProvider
{
    const QUEUE_ADAPTER = 'QUEUE_ADAPTER';

    /**
     * @param \Spryker\Zed\Kernel\Container $container
     *
     * @return \Spryker\Zed\Kernel\Container
     */
    public function provideBusinessLayerDependencies(Container $container)
    {
        $container = $this->addQueueAdapter($container);

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
            return (new RabbitMqClient())->createQueueAdapter();
        };
        return $container;
    }
}
