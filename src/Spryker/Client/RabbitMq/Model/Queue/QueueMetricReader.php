<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Client\RabbitMq\Model\Queue;

use Generated\Shared\Transfer\QueueMetricsRequestTransfer;
use Generated\Shared\Transfer\QueueMetricsResponseTransfer;
use RuntimeException;
use Spryker\Client\RabbitMq\Model\Connection\ConnectionManagerInterface;

class QueueMetricReader implements QueueMetricReaderInterface
{
    /**
     * @var \Spryker\Client\RabbitMq\Model\Connection\ConnectionManagerInterface
     */
    private ConnectionManagerInterface $connectionManager;

    /**
     * @param \Spryker\Client\RabbitMq\Model\Connection\ConnectionManagerInterface $connectionManager
     */
    public function __construct(ConnectionManagerInterface $connectionManager)
    {
        $this->connectionManager = $connectionManager;
    }

    /**
     * @param \Generated\Shared\Transfer\QueueMetricsRequestTransfer $queueMetricsRequestTransfer
     *
     * @throws \RuntimeException
     *
     * @return \Generated\Shared\Transfer\QueueMetricsResponseTransfer
     */
    public function getQueueMetrics(QueueMetricsRequestTransfer $queueMetricsRequestTransfer): QueueMetricsResponseTransfer
    {
        $queueMetricsRequestTransfer->requireQueueName();

        $storeName = $queueMetricsRequestTransfer->getStoreName();
        $channels = $storeName ?
            $this->connectionManager->getChannelsByStoreName($storeName, $queueMetricsRequestTransfer->getLocaleName()) :
            [$this->connectionManager->getDefaultChannel()];

        $channel = reset($channels);
        if (!$channel) {
            throw new RuntimeException(sprintf('Could not find a connection for %s %s', $storeName, $queueMetricsRequestTransfer->getQueueName()));
        }

        [, $messageCount, $consumerCount] = $channel->queue_declare($queueMetricsRequestTransfer->getQueueName(), true);

        return (new QueueMetricsResponseTransfer())
            ->setConsumerCount($consumerCount)
            ->setMessageCount($messageCount);
    }
}
