<?php

/**
 * MIT License
 * For full license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Spryker\Zed\RabbitMq\Communication\Plugin;

use Spryker\Client\RabbitMq\Model\RabbitMqAdapter;
use Spryker\Zed\Kernel\Communication\AbstractPlugin;
use Spryker\Zed\QueueExtension\Dependency\Plugin\QueueMessageCheckerPluginInterface;

/**
 * {@inheritDoc}
 *
 * @api
 *
 * @method \Spryker\Zed\RabbitMq\Business\RabbitMqFacadeInterface getFacade()
 * @method \Spryker\Zed\RabbitMq\RabbitMqConfig getConfig()
 */
class RabbitMqQueueMessageCheckerPlugin extends AbstractPlugin implements QueueMessageCheckerPluginInterface
{
    /**
     * {@inheritDoc}
     *
     * - Checks if any of the queues provided has messages in it.
     *
     * @api
     *
     * @param array<string> $queueNames
     *
     * @return bool
     */
    public function areQueuesEmpty(array $queueNames): bool
    {
        return $this->getFacade()->areQueuesEmpty($queueNames);
    }

    /**
     * {@inheritDoc}
     *
     * - Checks if the current queue adapter is RabbitMq.
     *
     * @api
     *
     * @param string $adapterName
     *
     * @return bool
     */
    public function isApplicable(string $adapterName): bool
    {
        return $adapterName === RabbitMqAdapter::class;
    }
}
