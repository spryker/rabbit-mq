<?php

/**
 * MIT License
 * For full license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Spryker\Client\RabbitMq;

use Spryker\Client\Kernel\AbstractClient;

/**
 * @method \Spryker\Client\RabbitMq\RabbitMqFactory getFactory()
 */
class RabbitMqClient extends AbstractClient implements RabbitMqClientInterface
{
    /**
     * {@inheritDoc}
     *
     * @api
     *
     * @return \Spryker\Client\Queue\Model\Adapter\AdapterInterface
     */
    public function createQueueAdapter()
    {
        return $this->getFactory()->createQueueAdapter();
    }
}
