<?php

/**
 * MIT License
 * For full license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Spryker\Client\RabbitMq\Model\Consumer;

use Generated\Shared\Transfer\QueueReceiveMessageTransfer;
use Generated\Shared\Transfer\QueueSendMessageTransfer;
use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Message\AMQPMessage;
use Throwable;

class Consumer implements ConsumerInterface
{
    public const CONSUMER_TAG = 'consumerTag';
    public const NO_LOCAL = 'noLocal';
    public const NO_ACK = 'noAck';
    public const EXCLUSIVE = 'exclusive';
    public const NOWAIT = 'nowait';

    public const QUEUE_LOG_FILE = 'queue.log';
    public const DEFAULT_CONSUMER_TIMEOUT_SECONDS = 1;
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
     * @param array $options
     *
     * @return \Generated\Shared\Transfer\QueueReceiveMessageTransfer[]
     */
    public function receiveMessages($queueName, $chunkSize = 100, array $options = [])
    {
        $this->channel->callbacks = [];

        /** @var \Generated\Shared\Transfer\RabbitMqConsumerOptionTransfer $rabbitMqOption */
        $rabbitMqOption = $options['rabbitmq'];

        $this->channel->basic_qos(null, $chunkSize, null);
        $this->channel->basic_consume(
            $queueName,
            $rabbitMqOption->getConsumerTag(),
            $rabbitMqOption->getNoLocal(),
            $rabbitMqOption->getNoAck(),
            $rabbitMqOption->getConsumerExclusive(),
            $rabbitMqOption->getNoWait(),
            [$this, 'collectQueueMessages']
        );

        try {
            $finished = false;
            while (count($this->channel->callbacks) && !$finished) {
                $this->channel->wait(null, false, self::DEFAULT_CONSUMER_TIMEOUT_SECONDS);
            }
        } catch (Throwable $e) {
            $finished = true;
        }

        return $this->retrieveCollectedMessages();
    }

    /**
     * @param string $queueName
     * @param array $options
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
        $this->channel->basic_ack($queueReceiveMessageTransfer->getDeliveryTag());
        $this->publishOnRoutingKey($queueReceiveMessageTransfer);
    }

    /**
     * @param \Generated\Shared\Transfer\QueueReceiveMessageTransfer $queueReceiveMessageTransfer
     *
     * @return void
     */
    public function reject(QueueReceiveMessageTransfer $queueReceiveMessageTransfer)
    {
        $this->channel->basic_reject($queueReceiveMessageTransfer->getDeliveryTag(), $queueReceiveMessageTransfer->getRequeue());
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
     * @return \Generated\Shared\Transfer\QueueReceiveMessageTransfer[]
     */
    protected function retrieveCollectedMessages(): array
    {
        $collectedMessages = $this->collectedMessages;
        $this->collectedMessages = [];

        return $collectedMessages;
    }
}
