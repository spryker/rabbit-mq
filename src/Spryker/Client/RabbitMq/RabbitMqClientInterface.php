<?php

/**
 * MIT License
 * For full license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Spryker\Client\RabbitMq;

use Generated\Shared\Transfer\QueueMetricsRequestTransfer;
use Generated\Shared\Transfer\QueueMetricsResponseTransfer;
use Spryker\Client\RabbitMq\Model\Connection\ConnectionInterface;

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
     *  - Return default connection.
     *
     * @api
     *
     * @return \Spryker\Client\RabbitMq\Model\Connection\ConnectionInterface
     */
    public function getConnection(): ConnectionInterface;

    /**
     * Specification:
     * - Gets queue metrics for the given queue per store.
     *
     * @api
     *
     * @param string $queue
     * @param string|null $storeCode
     * @param string|null $locale
     *
     * @return \Generated\Shared\Transfer\QueueMetricsResponseTransfer
     */
    public function getQueueMetrics(
        QueueMetricsRequestTransfer $queueMetricsRequestTransfer
    ): QueueMetricsResponseTransfer;
}
