<?php

/**
 * MIT License
 * For full license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Spryker\Client\RabbitMq\Model\Queue;

interface QueueMetricReaderInterface
{
    /**
     * @param string $queue
     * @param string|null $storeCode
     * @param string|null $locale
     *
     * @throws \RuntimeException
     *
     * @return array<string, int>
     */
    public function getQueueMetrics(string $queue, ?string $storeCode = null, ?string $locale = null): array;
}
