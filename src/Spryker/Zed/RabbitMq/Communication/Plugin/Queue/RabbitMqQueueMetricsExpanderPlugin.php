<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\RabbitMq\Communication\Plugin\Queue;

use Generated\Shared\Transfer\QueueMetricsRequestTransfer;
use Generated\Shared\Transfer\QueueMetricsResponseTransfer;
use Spryker\Client\RabbitMq\Model\RabbitMqAdapter;
use Spryker\Zed\Kernel\Communication\AbstractPlugin;
use Spryker\Zed\QueueExtension\Dependency\Plugin\QueueMetricsExpanderPluginInterface;

/**
 * {@inheritDoc}
 *
 * @api
 *
 * @method \Spryker\Zed\RabbitMq\Business\RabbitMqFacadeInterface getFacade()
 * @method \Spryker\Zed\RabbitMq\RabbitMqConfig getConfig()
 */
class RabbitMqQueueMetricsExpanderPlugin extends AbstractPlugin implements QueueMetricsExpanderPluginInterface
{
    /**
     * {@inheritDoc}
     *
     * @api
     *
     * @param \Generated\Shared\Transfer\QueueMetricsRequestTransfer $queueMetricsRequestTransfer
     * @param \Generated\Shared\Transfer\QueueMetricsResponseTransfer $queueMetricsResponseTransfer
     *
     * @return \Generated\Shared\Transfer\QueueMetricsResponseTransfer
     */
    public function expand(
        QueueMetricsRequestTransfer $queueMetricsRequestTransfer,
        QueueMetricsResponseTransfer $queueMetricsResponseTransfer
    ): QueueMetricsResponseTransfer {
        return $this->getFacade()->getQueueMetrics($queueMetricsRequestTransfer, $queueMetricsResponseTransfer);
    }

    /**
     * {@inheritDoc}
     *
     * @api
     *
     * @param string $adapterClassName
     *
     * @return bool
     */
    public function isApplicable(string $adapterClassName): bool
    {
        return $adapterClassName === RabbitMqAdapter::class;
    }
}
