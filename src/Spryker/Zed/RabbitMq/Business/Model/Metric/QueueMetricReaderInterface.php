<?php

namespace Spryker\Zed\RabbitMq\Business\Model\Metric;

use Generated\Shared\Transfer\QueueMetricsRequestTransfer;
use Generated\Shared\Transfer\QueueMetricsResponseTransfer;

interface QueueMetricReaderInterface
{
    public function getQueueMetrics(
        QueueMetricsRequestTransfer $queueMetricsRequestTransfer,
        QueueMetricsResponseTransfer $queueMetricsResponseTransfer
    ): QueueMetricsResponseTransfer;
}
