<?php

namespace Spryker\Zed\RabbitMq\Communication\Plugin\Queue;

use Generated\Shared\Transfer\QueueMetricsRequestTransfer;
use Generated\Shared\Transfer\QueueMetricsResponseTransfer;
use Spryker\Zed\Kernel\Communication\AbstractCommunicationFactory;
use Spryker\Zed\Kernel\Communication\AbstractPlugin;
use Spryker\Zed\Queue\Dependency\Plugin\QueueMetricsExpanderPluginInterface;
use Spryker\Zed\RabbitMq\Business\RabbitMqFacadeInterface;

/**
 * @method RabbitMqFacadeInterface getFacade()
 */
class RabbitMqQueueMetricsExpanderPlugin extends AbstractPlugin implements QueueMetricsExpanderPluginInterface
{
    public function expand(
        QueueMetricsRequestTransfer $queueMetricsRequestTransfer,
        QueueMetricsResponseTransfer $queueMetricsResponseTransfer
    ): QueueMetricsResponseTransfer {
        return $this->getFacade()->getQueueMetrics($queueMetricsRequestTransfer, $queueMetricsResponseTransfer);
    }
}
