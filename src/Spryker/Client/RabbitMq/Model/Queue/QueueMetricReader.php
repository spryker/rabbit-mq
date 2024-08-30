<?php

/**
 * MIT License
 * For full license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Spryker\Client\RabbitMq\Model\Queue;

use Generated\Shared\Transfer\QueueMetricsTransfer;
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
     * @return \Generated\Shared\Transfer\QueueMetricsTransfer
     */
    public function getQueueMetrics(string $queue, ?string $storeCode = null, ?string $locale = null): QueueMetricsTransfer
    {
        $channels = $storeCode ?
            $this->connectionManager->getChannelsByStoreName($storeCode, $locale) :
            [$this->connectionManager->getDefaultChannel()];

        $channel = reset($channels);
        if (!$channel) {
            throw new RuntimeException(sprintf('Could not find a connection for %s %s', $storeCode, $queue));
        }

        [, $messageCount, $consumerCount] = $channel->queue_declare($queue, true);

        return (new QueueMetricsTransfer())
            ->setConsumerCount($consumerCount)
            ->setMessageCount($messageCount);
    }
}
