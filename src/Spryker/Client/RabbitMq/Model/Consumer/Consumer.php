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
    const CONSUMER_TAG = 'consumerTag';
    const NO_LOCAL = 'noLocal';
    const NO_ACK = 'noAck';
    const EXCLUSIVE = 'exclusive';
    const NOWAIT = 'nowait';

    const QUEUE_LOG_FILE = 'queue.log';
    const DEFAULT_CONSUMER_TIMEOUT_SECONDS = 1;
    const DEFAULT_PREFETCH_COUNT = 100;

    /**
     * @var \PhpAmqpLib\Channel\AMQPChannel
     */
    protected $channel;

    /**
     * @var array
     */
    protected $collectedMessages = [];

    /**
     * @var int
     */
    protected $collectedMessagesCount = 0;

    /**
     * @var array
     */
    protected $processedMessages = [];

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

        return $this->collectedMessages;
    }

    /**
     * @param string $queueName
     * @param callable $callback
     * @param int $chunkSize
     * @param array $options
     *
     * @return \Generated\Shared\Transfer\QueueReceiveMessageTransfer[]
     */
    public function processMessages(string $queueName, callable $callback, int $chunkSize = 100, array $options = []): array
    {
        /** @var \Generated\Shared\Transfer\RabbitMqConsumerOptionTransfer $rabbitMqOption */
        $rabbitMqOption = $options['rabbitmq'];

        $this->channel->basic_qos(null, $chunkSize, null);

        $innerCallback = function ($message) use ($chunkSize, $callback) {
            $this->collectQueueMessages($message);

            ++$this->collectedMessagesCount;

            if ($this->collectedMessagesCount >= $chunkSize) {
                $this->processCallback($callback, $this->collectedMessages);
            }
        };

        $this->channel->basic_consume(
            $queueName,
            $rabbitMqOption->getConsumerTag(),
            $rabbitMqOption->getNoLocal(),
            $rabbitMqOption->getNoAck(),
            $rabbitMqOption->getConsumerExclusive(),
            $rabbitMqOption->getNoWait(),
            $innerCallback
        );

        try {
            $finished = false;
            while (count($this->channel->callbacks) && !$finished) {
                $this->channel->wait(null, false, self::DEFAULT_CONSUMER_TIMEOUT_SECONDS);
            }
        } catch (Throwable $e) {
            $finished = true;
        }

        $haveUnprocessedMessages = !empty($this->collectedMessages) && $this->collectedMessagesCount < $chunkSize;

        if ($haveUnprocessedMessages) {
            $this->processCallback($callback, $this->collectedMessages);
        }

        return $this->processedMessages;
    }

    /**
     * @param callable $callback
     * @param \Generated\Shared\Transfer\QueueReceiveMessageTransfer[] $collectedMessages
     *
     * @return void
     */
    protected function processCallback(callable $callback, array $collectedMessages): void
    {
        $processedMessages = call_user_func($callback, $collectedMessages);

        $this->setProcessedMessages($processedMessages);
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
     * @param \Generated\Shared\Transfer\QueueReceiveMessageTransfer[] $queueReceiveMessageTransfer
     *
     * @return void
     */
    public function setProcessedMessages(array $queueReceiveMessageTransfer): void
    {
        if (empty($queueReceiveMessageTransfer)) {
            return;
        }

        $this->processedMessages = $queueReceiveMessageTransfer;
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
}
