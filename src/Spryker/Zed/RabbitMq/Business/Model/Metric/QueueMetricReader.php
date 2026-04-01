<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\RabbitMq\Business\Model\Metric;

use Generated\Shared\Transfer\QueueMetricsRequestTransfer;
use Generated\Shared\Transfer\QueueMetricsResponseTransfer;
use Spryker\Client\RabbitMq\Model\RabbitMqAdapterInterface;

class QueueMetricReader implements QueueMetricReaderInterface
{
    /**
     * @var \Spryker\Client\RabbitMq\Model\RabbitMqAdapterInterface
     */
    protected RabbitMqAdapterInterface $queueAdapter;

    public function __construct(RabbitMqAdapterInterface $queueAdapter)
    {
        $this->queueAdapter = $queueAdapter;
    }

    public function getQueueMetrics(
        QueueMetricsRequestTransfer $queueMetricsRequestTransfer,
    ): QueueMetricsResponseTransfer {
        return $this->queueAdapter->getQueueMetrics($queueMetricsRequestTransfer);
    }
}
