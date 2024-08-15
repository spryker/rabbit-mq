<?php

/**
 * MIT License
 * For full license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Spryker\Zed\RabbitMq\Communication\Plugin;

use Generated\Shared\Transfer\RabbitMqQueueCollectionTransfer;
use Spryker\Zed\Kernel\Communication\AbstractPlugin;
use Spryker\Zed\Queue\Dependency\Plugin\QueueProviderPluginInterface;

/**
 * {@inheritDoc}
 *
 * @api
 *
 * @method \Spryker\Zed\RabbitMq\Business\RabbitMqFacadeInterface getFacade()
 * @method \Spryker\Zed\RabbitMq\RabbitMqConfig getConfig()
 */
class RabbitMqQueueProviderPlugin extends AbstractPlugin implements QueueProviderPluginInterface
{
    public function getQueues(): RabbitMqQueueCollectionTransfer
    {
        return $this->getFacade()->getQueues();
    }
}
