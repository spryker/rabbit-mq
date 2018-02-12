<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Client\RabbitMq\Model\Publisher;

use Generated\Shared\Transfer\QueueSendMessageTransfer;
use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Message\AMQPMessage;
use Spryker\Client\RabbitMq\Model\Connection\ConnectionManager;

class Publisher implements PublisherInterface
{
    /**
     * @var ConnectionManager
     */
    protected $connectionManager;

    /**
     * @param ConnectionManager $connectionManager
     */
    public function __construct(ConnectionManager $connectionManager)
    {
        $this->connectionManager = $connectionManager;
    }

    /**
     * @param string $queueName
     * @param \Generated\Shared\Transfer\QueueSendMessageTransfer $queueSendMessageTransfer
     *
     * @return void
     */
    public function sendMessage($queueName, QueueSendMessageTransfer $queueSendMessageTransfer)
    {
        $message = $this->createMessage($queueSendMessageTransfer);

        $this->publish($message, $queueName, $queueSendMessageTransfer->getRoutingKey(), $queueSendMessageTransfer->getQueuePoolName());
    }

    /**
     * @param string $queueName
     * @param \Generated\Shared\Transfer\QueueSendMessageTransfer[] $queueMessageTransfers
     *
     * @return void
     */
    public function sendMessages($queueName, array $queueMessageTransfers)
    {
        $usedChannels = [];
        foreach ($queueMessageTransfers as $queueMessageTransfer) {
            $usedChannels += $this->publishBatchMessage($queueMessageTransfer, $queueName);
        }

        $this->publishChannels($usedChannels);
    }

    /**
     * @param QueueSendMessageTransfer $queueMessageTransfer
     * @param string $queueName
     *
     * @return AMQPChannel[]
     */
    protected function publishBatchMessage(QueueSendMessageTransfer $queueMessageTransfer, $queueName)
    {
        $usedChannels = [];
        $msg = new AMQPMessage($queueMessageTransfer->getBody());
        $channels = $this->connectionManager->getChannelsByQueuePoolName($queueMessageTransfer->getQueuePoolName());

        foreach ($channels as $channel) {
            $usedChannels[$channel->getChannelId()] = $channel;
            $channel->batch_basic_publish($msg, $queueName, $queueMessageTransfer->getRoutingKey());
        }

        return $usedChannels;
    }

    /**
     * @param AMQPChannel[] $channels
     */
    protected function publishChannels(array $channels)
    {
        foreach ($channels as $channel)
        {
            $channel->publish_batch();
        }
    }

    /**
     * @param \PhpAmqpLib\Message\AMQPMessage $message
     * @param string $exchangeQueue
     * @param string $routingKey
     * @param string $queuePoolName
     *
     * @return void
     */
    protected function publish(AMQPMessage $message, $exchangeQueue, $routingKey, $queuePoolName)
    {
        $channels = $this->connectionManager->getChannelsByQueuePoolName($queuePoolName);

        foreach ($channels as $channel) {
            $channel->basic_publish($message, $exchangeQueue, $routingKey);
        }
    }

    /**
     * @param \Generated\Shared\Transfer\QueueSendMessageTransfer $messageTransfer
     *
     * @return \PhpAmqpLib\Message\AMQPMessage
     */
    protected function createMessage(QueueSendMessageTransfer $messageTransfer)
    {
        return new AMQPMessage($messageTransfer->getBody(), $this->getMessageConfig());
    }

    /**
     * @return array
     */
    protected function getMessageConfig()
    {
        return [];
    }
}
