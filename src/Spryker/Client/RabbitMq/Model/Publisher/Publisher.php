<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Client\RabbitMq\Model\Publisher;

use Generated\Shared\Transfer\QueueSendMessageTransfer;
use PhpAmqpLib\Message\AMQPMessage;
use Spryker\Client\RabbitMq\Model\Connection\ConnectionManagerInterface;

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
        $publishChannels = $this->getChannels($queueSendMessageTransfer);
        $routingKey = $queueSendMessageTransfer->getRoutingKey();

        $this->publish($message, $queueName, $routingKey, $publishChannels);
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
     * @param \Generated\Shared\Transfer\QueueSendMessageTransfer $queueSendMessageTransfer
     * @param string $queueName
     *
     * @return \PhpAmqpLib\Channel\AMQPChannel[]
     */
    protected function addBatchMessage(QueueSendMessageTransfer $queueSendMessageTransfer, $queueName)
    {
        $usedChannels = [];
        $msg = new AMQPMessage($queueSendMessageTransfer->getBody());
        $channels = $this->getChannels($queueSendMessageTransfer);

        foreach ($channels as $channel) {
            $usedChannels[$channel->getChannelId()] = $channel;
            $channel->batch_basic_publish($msg, $queueName, $queueSendMessageTransfer->getRoutingKey());
        }

        return $usedChannels;
    }

    /**
     * @param \Generated\Shared\Transfer\QueueSendMessageTransfer $queueSendMessageTransfer
     *
     * @return \PhpAmqpLib\Channel\AMQPChannel[]
     */
    protected function getChannels(QueueSendMessageTransfer $queueSendMessageTransfer)
    {
        if ($queueSendMessageTransfer->getStoreName()) {
            return $this->connectionManager->getChannelsByStoreName($queueSendMessageTransfer->getStoreName());
        }

        if ($queueSendMessageTransfer->getQueuePoolName()) {
            return $this->connectionManager->getChannelsByQueuePoolName($queueSendMessageTransfer->getQueuePoolName());
        }

        return [$this->connectionManager->getDefaultChannel()];
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
     * @param \PhpAmqpLib\Channel\AMQPChannel[] $publishChannels
     *
     * @return void
     */
    protected function publish(AMQPMessage $message, $exchangeQueue, $routingKey, $publishChannels)
    {
        foreach ($publishChannels as $channel) {
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
