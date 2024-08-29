<?php

/**
 * MIT License
 * For full license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Spryker\Client\RabbitMq;

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
     * @param string $queue
     * @param string|null $storeCode
     * @param string|null $locale
     *
     * @return array<string, int>
     *@api
     *
     */
    public function getQueueMetrics(
        string $queue,
        ?string $storeCode = null,
        ?string $locale = null
    ): array
    {
        return $this->getFactory()->createQueueMetricReader()->getQueueMetrics($queue, $storeCode, $locale);
    }
}
