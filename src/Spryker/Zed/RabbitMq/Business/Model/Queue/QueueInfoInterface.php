<?php

/**
 * MIT License
 * For full license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Spryker\Zed\RabbitMq\Business\Model\Queue;

interface QueueInfoInterface
{
    /**
     * @return bool
     */
    public function areQueuesEmpty(array $queueNames): bool;

    /**
     * @return bool
     */
    public function isApplicableQueue(string $currentQueueName, array $queueNames): bool;
}
