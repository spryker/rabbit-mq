<?php

namespace Spryker\Zed\RabbitMq\Communication\Plugin;

use Spryker\Zed\Kernel\Communication\AbstractPlugin;
use Spryker\Zed\Queue\Dependency\Plugin\QueueProviderPluginInterface;
use Generated\Shared\Transfer\QueueCollectionTransfer;

/**
 * @method \Spryker\Zed\[module]\Business\[module]Facade getFacade()
 */
class RabbitMqQueueProviderPlugin extends AbstractPlugin implements QueueProviderPluginInterface
{
    public function getQueues(): QueueCollectionTransfer
    {
        return $this->getFacade()->getQueues();
    }
}
