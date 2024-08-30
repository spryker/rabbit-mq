<?php

/**
 * MIT License
 * For full license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Spryker\Client\RabbitMq\Model\Queue;

use Generated\Shared\Transfer\QueueMetricsRequestTransfer;
use Generated\Shared\Transfer\QueueMetricsResponseTransfer;

interface QueueMetricReaderInterface
{
    /**
     * @param string $queue
     * @param string|null $storeCode
     * @param string|null $locale
     *
     * @throws \RuntimeException
     *
     * @return \Generated\Shared\Transfer\QueueMetricsResponseTransfer
     */
    public function getQueueMetrics(QueueMetricsRequestTransfer $queueMetricsRequestTransfer): QueueMetricsResponseTransfer;
}
