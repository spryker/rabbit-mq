<?php

/**
 * MIT License
 * For full license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Spryker\Client\RabbitMq;

use Spryker\Client\RabbitMq\Model\Connection\ConnectionInitializerInterface;

interface RabbitMqClientInterface
{
    /**
     * Specification:
     *  - Creates an instance of a concrete adapter
     *
     * @api
     *
     * @return \Spryker\Client\Queue\Model\Adapter\AdapterInterface
     */
    public function createQueueAdapter();

    /**
     * Specification:
     *  - Return connection initializer.
     *
     * @api
     *
     * @return \Spryker\Client\RabbitMq\Model\Connection\ConnectionInitializerInterface
     */
    public function getConnectionInitializer(): ConnectionInitializerInterface;
}
