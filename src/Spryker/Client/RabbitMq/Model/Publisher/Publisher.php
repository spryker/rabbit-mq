<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Client\RabbitMq\Model\Publisher;

use Generated\Shared\Transfer\QueueSendMessageTransfer;
use PhpAmqpLib\Message\AMQPMessage;
use Spryker\Client\RabbitMq\Model\Connection\ConnectionManagerInterface;
use Spryker\Shared\RabbitMq\RabbitMqConfigInterface;

class Publisher implements PublisherInterface
{
    /**
     * @var \Spryker\Client\RabbitMq\Model\Connection\ConnectionManagerInterface
     */
    protected $connectionManager;

    /**
     * @param \Spryker\Client\RabbitMq\Model\Connection\ConnectionManagerInterface $connectionManager
     */
    public function __construct(ConnectionManagerInterface $connectionManager)
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
        $queuePoolName = $this->getQueueSendMessageQueuePoolName($queueSendMessageTransfer);
        $this->publish($message, $queueName, $queueSendMessageTransfer->getRoutingKey(), $queuePoolName);
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
            $usedChannels += $this->addBatchMessage($queueMessageTransfer, $queueName);
        }

        $this->publishBatches($usedChannels);
    }

    /**
     * @param QueueSendMessageTransfer $queueSendMessageTransfer
     *
     * @return string
     */
    protected function getQueueSendMessageQueuePoolName(QueueSendMessageTransfer $queueSendMessageTransfer)
    {
        if ($queueSendMessageTransfer->getQueuePoolName() === null) {
            return RabbitMqConfigInterface::QUEUE_POOL_NAME_DEFAULT;
        }

        return $queueSendMessageTransfer->getQueuePoolName();
    }

    /**
     * @param \Generated\Shared\Transfer\QueueSendMessageTransfer $queueSendMessageTransfer
     * @param string $queueName
     *
     * @return \PhpAmqpLib\Channel\AMQPChannel[]
     */
    protected function addBatchMessage(QueueSendMessageTransfer $queueSendMessageTransfer, $queueName)
    {
        $usedChannels = [];
        $msg = new AMQPMessage($queueSendMessageTransfer->getBody());
        $queuePoolName = $this->getQueueSendMessageQueuePoolName($queueSendMessageTransfer);
        $channels = $this->connectionManager->getChannelsByQueuePoolName($queuePoolName);

        foreach ($channels as $channel) {
            $usedChannels[$channel->getChannelId()] = $channel;
            $channel->batch_basic_publish($msg, $queueName, $queueSendMessageTransfer->getRoutingKey());
        }

        return $usedChannels;
    }

    /**
     * @param \PhpAmqpLib\Channel\AMQPChannel[] $channels
     *
     * @return void
     */
    protected function publishBatches(array $channels)
    {
        foreach ($channels as $channel) {
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
