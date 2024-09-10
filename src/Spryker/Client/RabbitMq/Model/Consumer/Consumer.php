<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Client\RabbitMq\Model\Consumer;

use Generated\Shared\Transfer\QueueReceiveMessageTransfer;
use Generated\Shared\Transfer\QueueSendMessageTransfer;
use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Message\AMQPMessage;
use Throwable;

class Consumer implements ConsumerInterface
{
    /**
     * @var string
     */
    public const CONSUMER_TAG = 'consumerTag';

    /**
     * @var string
     */
    public const NO_LOCAL = 'noLocal';

    /**
     * @var string
     */
    public const NO_ACK = 'noAck';

    /**
     * @var string
     */
    public const EXCLUSIVE = 'exclusive';

    /**
     * @var string
     */
    public const NOWAIT = 'nowait';

    /**
     * @var string
     */
    public const QUEUE_LOG_FILE = 'queue.log';

    /**
     * @var int
     */
    public const DEFAULT_CONSUMER_TIMEOUT_SECONDS = 1;

    /**
     * @var int
     */
    public const DEFAULT_PREFETCH_COUNT = 100;

    /**
     * @var \PhpAmqpLib\Channel\AMQPChannel
     */
    protected $channel;

    /**
     * @var array
     */
    protected $collectedMessages = [];

    /**
     * @param \PhpAmqpLib\Channel\AMQPChannel $channel
     */
    public function __construct(AMQPChannel $channel)
    {
        $this->channel = $channel;
    }

    /**
     * @param string $queueName
     * @param int $chunkSize
     * @param array<string, mixed> $options
     *
     * @return array<\Generated\Shared\Transfer\QueueReceiveMessageTransfer>
     */
    public function receiveMessages($queueName, $chunkSize = 100, array $options = [])
    {
        $this->channel->callbacks = [];

        /** @var \Generated\Shared\Transfer\RabbitMqConsumerOptionTransfer $rabbitMqOption */
        $rabbitMqOption = $options['rabbitmq'];

        /** @var string $consumerTag */
        $consumerTag = $rabbitMqOption->getConsumerTag();

        $this->channel->basic_qos(0, $chunkSize, false);
        $consumerTag = $this->channel->basic_consume(
            $queueName,
            $consumerTag,
            $rabbitMqOption->getNoLocal(),
            $rabbitMqOption->getNoAck(),
            $rabbitMqOption->getConsumerExclusive(),
            $rabbitMqOption->getNoWait(),
            [$this, 'collectQueueMessages'],
        );

        try {
            while (count($this->channel->callbacks)) {
                $this->channel->wait(null, false, static::DEFAULT_CONSUMER_TIMEOUT_SECONDS);
            }
        } catch (Throwable $e) {
            $this->channel->basic_cancel($consumerTag);
        }

        return $this->retrieveCollectedMessages();
    }

    /**
     * @param string $queueName
     * @param array<string, mixed> $options
     *
     * @return \Generated\Shared\Transfer\QueueReceiveMessageTransfer
     */
    public function receiveMessage($queueName, array $options = [])
    {
        /** @var \Generated\Shared\Transfer\RabbitMqConsumerOptionTransfer $rabbitMqOption */
        $rabbitMqOption = $options['rabbitmq'];

        $queueReceiveMessageTransfer = new QueueReceiveMessageTransfer();
        $message = $this->channel->basic_get($queueName, $rabbitMqOption->getNoAck());

        if ($message === null) {
            return $queueReceiveMessageTransfer;
        }

        $queueSendMessageTransfer = new QueueSendMessageTransfer();
        $queueSendMessageTransfer->setBody($message->getBody());
        $queueSendMessageTransfer = $this->addApplicationHeaders($message, $queueSendMessageTransfer);

        $queueReceiveMessageTransfer->setQueueMessage($queueSendMessageTransfer);
        $queueReceiveMessageTransfer->setQueueName($queueName);
        $queueReceiveMessageTransfer->setDeliveryTag($message->delivery_info['delivery_tag']);
        $queueReceiveMessageTransfer->setRequeue($rabbitMqOption->getRequeueOnReject());

        return $queueReceiveMessageTransfer;
    }

    /**
     * @param \PhpAmqpLib\Message\AMQPMessage $message
     *
     * @return void
     */
    public function collectQueueMessages(AMQPMessage $message)
    {
        $queueSendMessageTransfer = new QueueSendMessageTransfer();
        $queueSendMessageTransfer->setBody($message->getBody());
        $queueSendMessageTransfer = $this->addApplicationHeaders($message, $queueSendMessageTransfer);

        $queueReceiveMessageTransfer = new QueueReceiveMessageTransfer();
        $queueReceiveMessageTransfer->setQueueMessage($queueSendMessageTransfer);

        $queueName = $message->delivery_info['exchange'];
        if (!$queueName) {
            $queueName = $message->delivery_info['routing_key'];
        }

        $queueReceiveMessageTransfer->setQueueName($queueName);
        $queueReceiveMessageTransfer->setDeliveryTag($message->delivery_info['delivery_tag']);

        $this->collectedMessages[] = $queueReceiveMessageTransfer;
    }

    /**
     * @param \PhpAmqpLib\Message\AMQPMessage $message $message
     * @param \Generated\Shared\Transfer\QueueSendMessageTransfer $queueSendMessageTransfer
     *
     * @return \Generated\Shared\Transfer\QueueSendMessageTransfer
     */
    protected function addApplicationHeaders(AMQPMessage $message, QueueSendMessageTransfer $queueSendMessageTransfer): QueueSendMessageTransfer
    {
        $messageProperties = $message->get_properties();

        if (isset($messageProperties['application_headers'])) {
            $headers = $messageProperties['application_headers'];
            $queueSendMessageTransfer->setHeaders($headers->getNativeData());
        }

        return $queueSendMessageTransfer;
    }

    /**
     * @param \Generated\Shared\Transfer\QueueReceiveMessageTransfer $queueReceiveMessageTransfer
     *
     * @return void
     */
    public function acknowledge(QueueReceiveMessageTransfer $queueReceiveMessageTransfer)
    {
        /** @var int $deliveryTag */
        $deliveryTag = $queueReceiveMessageTransfer->getDeliveryTag();
        $this->channel->basic_ack($deliveryTag);
        $this->publishOnRoutingKey($queueReceiveMessageTransfer);
    }

    /**
     * @param \Generated\Shared\Transfer\QueueReceiveMessageTransfer $queueReceiveMessageTransfer
     *
     * @return void
     */
    public function reject(QueueReceiveMessageTransfer $queueReceiveMessageTransfer)
    {
        /** @var int $deliveryTag */
        $deliveryTag = $queueReceiveMessageTransfer->getDeliveryTag();
        $this->channel->basic_reject($deliveryTag, $queueReceiveMessageTransfer->getRequeue());
    }

    /**
     * @param \Generated\Shared\Transfer\QueueReceiveMessageTransfer $queueReceiveMessageTransfer
     *
     * @return bool
     */
    public function handleError(QueueReceiveMessageTransfer $queueReceiveMessageTransfer)
    {
        $this->publishOnRoutingKey($queueReceiveMessageTransfer);

        return true;
    }

    /**
     * @param \Generated\Shared\Transfer\QueueReceiveMessageTransfer $queueReceiveMessageTransfer
     *
     * @return void
     */
    protected function publishOnRoutingKey(QueueReceiveMessageTransfer $queueReceiveMessageTransfer): void
    {
        if ($queueReceiveMessageTransfer->getRoutingKey()) {
            $message = new AMQPMessage($queueReceiveMessageTransfer->getQueueMessage()->getBody());
            $this->channel->basic_publish($message, $queueReceiveMessageTransfer->getQueueName(), $queueReceiveMessageTransfer->getRoutingKey());
        }
    }

    /**
     * @return array<\Generated\Shared\Transfer\QueueReceiveMessageTransfer>
     */
    protected function retrieveCollectedMessages(): array
    {
        $collectedMessages = $this->collectedMessages;
        $this->collectedMessages = [];

        return $collectedMessages;
    }
}
