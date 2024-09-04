<?php

namespace Spryker\Zed\RabbitMq\Business\Model\Metric;

use Generated\Shared\Transfer\QueueMetricsRequestTransfer;
use Generated\Shared\Transfer\QueueMetricsResponseTransfer;
use Spryker\Client\RabbitMq\Model\RabbitMqAdapterInterface;
use Spryker\Zed\RabbitMq\Business\Model\Exchange\ExchangeInfoInterface;
use Spryker\Zed\RabbitMq\Business\Model\Exchange\Filter\ExchangeFilterInterface;

class QueueMetricReader implements QueueMetricReaderInterface
{
    /**
     * @var \Spryker\Client\RabbitMq\Model\RabbitMqAdapterInterface
     */
    protected $queueAdapter;

    /**
     * @param \Spryker\Zed\RabbitMq\Business\Model\Exchange\ExchangeInfoInterface $exchangeInfo
     * @param \Spryker\Client\RabbitMq\Model\RabbitMqAdapterInterface $queueAdapter
     * @param \Spryker\Zed\RabbitMq\Business\Model\Exchange\Filter\ExchangeFilterInterface $exchangeFilter
     */
    public function __construct(RabbitMqAdapterInterface $queueAdapter)
    {
        $this->queueAdapter = $queueAdapter;
    }

    public function getQueueMetrics(
        QueueMetricsRequestTransfer $queueMetricsRequestTransfer,
        QueueMetricsResponseTransfer $queueMetricsResponseTransfer
    ): QueueMetricsResponseTransfer {
        return $this->queueAdapter->getQueueMetrics($queueMetricsRequestTransfer, $queueMetricsResponseTransfer);
    }
}
