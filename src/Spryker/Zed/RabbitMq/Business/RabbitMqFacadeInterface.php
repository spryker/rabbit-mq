<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\RabbitMq\Business;

use Generated\Shared\Transfer\QueueMetricsRequestTransfer;
use Generated\Shared\Transfer\QueueMetricsResponseTransfer;
use Psr\Log\LoggerInterface;

/**
 * @method \Spryker\Zed\RabbitMq\Business\RabbitMqBusinessFactory getFactory()
 */
interface RabbitMqFacadeInterface
{
    /**
     * Specification:
     * - Purges all existing queues.
     *
     * @api
     *
     * @param \Psr\Log\LoggerInterface $logger
     *
     * @return bool
     */
    public function purgeAllQueues(LoggerInterface $logger);

    /**
     * Specification:
     * - Deletes all existing queues.
     *
     * @api
     *
     * @param \Psr\Log\LoggerInterface $logger
     *
     * @return bool
     */
    public function deleteAllQueues(LoggerInterface $logger);

    /**
     * Specification:
     * - Deletes all exchanges except amq ones.
     *
     * @api
     *
     * @param \Psr\Log\LoggerInterface $logger
     *
     * @return bool
     */
    public function deleteAllExchanges(LoggerInterface $logger);

    /**
     * Specification:
     * - Set up all queues and exchanges for the default connection.
     *
     * @api
     *
     * @return void
     */
    public function setupConnection(): void;

    /**
     * Specification:
     * - Sets the user permissions.
     *
     * @api
     *
     * @param \Psr\Log\LoggerInterface $logger
     *
     * @return bool
     */
    public function setUserPermissions(LoggerInterface $logger);

    /**
     * Specification:
     *  - Checks if any of the applicable queues have messages.
     *
     * @api
     *
     * @param array<string> $queueNames
     *
     * @return bool
     */
    public function areQueuesEmpty(array $queueNames): bool;

    /**
     * Specification:
     * - Returns the number of messages in the queue and the number of consumers.
     * - Fetches the queue metrics from the RabbitMQ for default connection if store code is not provided.
     *
     * @api
     *
     * @param \Generated\Shared\Transfer\QueueMetricsRequestTransfer $queueMetricsRequestTransfer
     * @param \Generated\Shared\Transfer\QueueMetricsResponseTransfer $queueMetricsResponseTransfer
     *
     * @return \Generated\Shared\Transfer\QueueMetricsResponseTransfer
     */
    public function getQueueMetrics(
        QueueMetricsRequestTransfer $queueMetricsRequestTransfer,
        QueueMetricsResponseTransfer $queueMetricsResponseTransfer
    ): QueueMetricsResponseTransfer;
}
