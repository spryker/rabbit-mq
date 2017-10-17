<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Client\RabbitMq\Model\Publisher;

use Generated\Shared\Transfer\QueueSendMessageTransfer;
use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Message\AMQPMessage;

class Publisher implements PublisherInterface
{
    /**
     * @var \PhpAmqpLib\Channel\AMQPChannel
     */
    protected $channel;

    /**
     * Publisher constructor.
     *
     * @param \PhpAmqpLib\Channel\AMQPChannel $channel
     */
    public function __construct(AMQPChannel $channel)
    {
        $this->channel = $channel;
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

        $this->publish($message, $queueName, $queueSendMessageTransfer->getRoutingKey());
    }

    /**
     * @param string $queueName
     * @param \Generated\Shared\Transfer\QueueSendMessageTransfer[] $queueMessageTransfers
     *
     * @return void
     */
    public function sendMessages($queueName, array $queueMessageTransfers)
    {
        foreach ($queueMessageTransfers as $queueMessageTransfer) {
            $msg = new AMQPMessage($queueMessageTransfer->getBody());
            $this->channel->batch_basic_publish($msg, $queueName, $queueMessageTransfer->getRoutingKey());
        }

        $this->channel->publish_batch();
    }

    /**
     * @param \PhpAmqpLib\Message\AMQPMessage $message
     * @param string $exchangeQueue
     * @param string $routingKey
     *
     * @return void
     */
    protected function publish(AMQPMessage $message, $exchangeQueue, $routingKey)
    {
        $this->channel->basic_publish($message, $exchangeQueue, $routingKey);
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
