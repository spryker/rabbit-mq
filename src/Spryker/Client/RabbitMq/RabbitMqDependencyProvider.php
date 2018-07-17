<?php

/**
 * MIT License
 * For full license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Spryker\Client\RabbitMq;

use Spryker\Client\Kernel\AbstractDependencyProvider;
use Spryker\Client\Kernel\Container;
use Spryker\Client\RabbitMq\Dependency\Client\RabbitMqToStoreClientBridge;

class RabbitMqDependencyProvider extends AbstractDependencyProvider
{
    public const CLIENT_STORE = 'CLIENT_STORE';

    /**
     * @param \Spryker\Client\Kernel\Container $container
     *
     * @return \Spryker\Client\Kernel\Container
     */
    public function provideServiceLayerDependencies(Container $container)
    {
        $container = parent::provideServiceLayerDependencies($container);

        $container = $this->addStoreClient($container);

        return $container;
    }

    /**
     * @param \Spryker\Client\Kernel\Container $container
     *
     * @return \Spryker\Client\Kernel\Container
     */
    protected function addStoreClient(Container $container)
    {
        $container[static::CLIENT_STORE] = function (Container $container) {
            return new RabbitMqToStoreClientBridge($container->getLocator()->store()->client());
        };

        return $container;
    }
}
