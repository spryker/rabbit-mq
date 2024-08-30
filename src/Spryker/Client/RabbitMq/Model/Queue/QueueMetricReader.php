<?php

/**
 * MIT License
 * For full license information, please view the LICENSE file that was distributed with this source code.
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
     * @param string $queue
     * @param string|null $storeCode
     * @param string|null $locale
     *
     * @throws \RuntimeException
     *
     * @return \Generated\Shared\Transfer\QueueMetricsResponseTransfer
     */
    public function getQueueMetrics(QueueMetricsRequestTransfer $queueMetricsRequestTransfer): QueueMetricsResponseTransfer
    {
        $queueMetricsRequestTransfer->requireQueueName();

        $storeCode = $queueMetricsRequestTransfer->getStoreCode();
        $channels = $storeCode ?
            $this->connectionManager->getChannelsByStoreName($storeCode, $queueMetricsRequestTransfer->getLocaleName()) :
            [$this->connectionManager->getDefaultChannel()];

        $channel = reset($channels);
        if (!$channel) {
            throw new RuntimeException(sprintf('Could not find a connection for %s %s', $storeCode, $queueMetricsRequestTransfer->getQueueName()));
        }

        [, $messageCount, $consumerCount] = $channel->queue_declare($queueMetricsRequestTransfer->getQueueName(), true);

        return (new QueueMetricsResponseTransfer())
            ->setConsumerCount($consumerCount)
            ->setMessageCount($messageCount);
    }
}
