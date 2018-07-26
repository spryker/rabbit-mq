<?php

/**
 * MIT License
 * For full license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Spryker\Zed\RabbitMq\Business\Model\Queue;

use Psr\Log\LoggerInterface;
use Spryker\Client\Queue\Model\Adapter\AdapterInterface;

class Queue implements QueueInterface
{
    /**
     * @var \Spryker\Zed\RabbitMq\Business\Model\Queue\QueueInfoInterface
     */
    protected $queueInfo;

    /**
     * @var \Spryker\Client\Queue\Model\Adapter\AdapterInterface
     */
    protected $queueAdapter;

    /**
     * @param \Spryker\Zed\RabbitMq\Business\Model\Queue\QueueInfoInterface $queueInfo
     * @param \Spryker\Client\Queue\Model\Adapter\AdapterInterface $queueAdapter
     */
    public function __construct(QueueInfoInterface $queueInfo, AdapterInterface $queueAdapter)
    {
        $this->queueInfo = $queueInfo;
        $this->queueAdapter = $queueAdapter;
    }

    /**
     * @param \Psr\Log\LoggerInterface $logger
     *
     * @return bool
     */
    public function deleteAllQueues(LoggerInterface $logger)
    {
        foreach ($this->queueInfo->getQueues()->getRabbitMqQueues() as $rabbitMqQueueTransfer) {
            $this->queueAdapter->deleteQueue($rabbitMqQueueTransfer->getName());
            $logger->info(sprintf('Delete queue "%s" request send.', $rabbitMqQueueTransfer->getName()));
        }

        return true;
    }

    /**
     * @param \Psr\Log\LoggerInterface $logger
     *
     * @return bool
     */
    public function purgeAllQueues(LoggerInterface $logger)
    {
        foreach ($this->queueInfo->getQueues()->getRabbitMqQueues() as $rabbitMqQueueTransfer) {
            $this->queueAdapter->purgeQueue($rabbitMqQueueTransfer->getName());
            $logger->info(sprintf('Purge queue "%s" request send.', $rabbitMqQueueTransfer->getName()));
        }

        return true;
    }
}
