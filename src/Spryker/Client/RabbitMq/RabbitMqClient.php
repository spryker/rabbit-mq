<?php

/**
 * MIT License
 * For full license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Spryker\Client\RabbitMq;

use Generated\Shared\Transfer\QueueMetricsRequestTransfer;
use Generated\Shared\Transfer\QueueMetricsResponseTransfer;
use Spryker\Client\Kernel\AbstractClient;
use Spryker\Client\RabbitMq\Model\Connection\ConnectionInterface;

/**
 * @method \Spryker\Client\RabbitMq\RabbitMqFactory getFactory()
 */
class RabbitMqClient extends AbstractClient implements RabbitMqClientInterface
{
    /**
     * {@inheritDoc}
     *
     * @api
     *
     * @return \Spryker\Client\Queue\Model\Adapter\AdapterInterface
     */
    public function createQueueAdapter()
    {
        return $this->getFactory()->createQueueAdapter();
    }

    /**
     * {@inheritDoc}
     *
     * @api
     *
     * @return \Spryker\Client\RabbitMq\Model\Connection\ConnectionInterface
     */
    public function getConnection(): ConnectionInterface
    {
        return $this->getFactory()->getStaticConnectionManager()->getDefaultConnection();
    }

    /**
     * {@inheritDoc}
     *
     * @api
     *
     * @param QueueMetricsRequestTransfer $queueMetricsRequestTransfer
     *
     * @return \Generated\Shared\Transfer\QueueMetricsResponseTransfer
     */
    public function getQueueMetrics(
        QueueMetricsRequestTransfer $queueMetricsRequestTransfer
    ): QueueMetricsResponseTransfer {
        return $this->getFactory()->getQueueMetricReader()->getQueueMetrics($queueMetricsRequestTransfer);
    }
}
