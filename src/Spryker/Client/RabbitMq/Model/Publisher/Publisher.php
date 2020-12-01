<?php

/**
 * MIT License
 * For full license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Spryker\Client\RabbitMq\Model\Publisher;

use Generated\Shared\Transfer\QueueSendMessageTransfer;
use PhpAmqpLib\Message\AMQPMessage;
use PhpAmqpLib\Wire\AMQPTable;
use Spryker\Client\RabbitMq\Model\Connection\ConnectionManagerInterface;
use Spryker\Client\RabbitMq\RabbitMqConfig;

class Publisher implements PublisherInterface
{
    protected const DEFAULT_CHANNEL = 'DEFAULT_CHANNEL';
    protected const STORE_NAME_BUFFER_KEY_FORMAT = 'STORE_NAME:%s-%s';
    protected const QUEUE_POOL_NAME_BUFFER_KEY_FORMAT = 'QUEUE_POOL_NAME:%s-%s';

    /**
     * @var \Spryker\Client\RabbitMq\Model\Connection\ConnectionManagerInterface
     */
    protected $connectionManager;

    /**
     * @var \Spryker\Client\RabbitMq\RabbitMqConfig
     */
    protected $config;

    /**
     * @var \PhpAmqpLib\Channel\AMQPChannel[][]
     */
    protected $channelBuffer = [];

    /**
     * @param \Spryker\Client\RabbitMq\Model\Connection\ConnectionManagerInterface $connectionManager
     * @param \Spryker\Client\RabbitMq\RabbitMqConfig $config
     */
    public function __construct(ConnectionManagerInterface $connectionManager, RabbitMqConfig $config)
    {
        $this->connectionManager = $connectionManager;
        $this->config = $config;
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
     * @param \Generated\Shared\Transfer\QueueSendMessageTransfer[] $queueSendMessageTransfers
     *
     * @return void
     */
    public function sendMessages($queueName, array $queueSendMessageTransfers)
    {
        $usedChannels = [];
        foreach ($queueSendMessageTransfers as $queueMessageTransfer) {
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
        $msg = $this->createMessage($queueSendMessageTransfer);
        $channels = $this->getChannels($queueSendMessageTransfer);

        foreach ($channels as $uniqueChannelId => $channel) {
            $usedChannels[$uniqueChannelId] = $channel;
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
            return $this->getChannelByStoreName($queueSendMessageTransfer);
        }

        if ($queueSendMessageTransfer->getQueuePoolName()) {
            return $this->getChannelByQueuePoolName($queueSendMessageTransfer);
        }

        return $this->getDefaultChannel();
    }

    /**
     * @param \Generated\Shared\Transfer\QueueSendMessageTransfer $queueSendMessageTransfer
     *
     * @return \PhpAmqpLib\Channel\AMQPChannel[]
     */
    protected function getChannelByStoreName(QueueSendMessageTransfer $queueSendMessageTransfer): array
    {
        $localeName = $queueSendMessageTransfer->getLocale();
        $storeName = $queueSendMessageTransfer->getStoreName();

        $bufferKey = sprintf(static::STORE_NAME_BUFFER_KEY_FORMAT, $storeName, $localeName);
        if (isset($this->channelBuffer[$bufferKey])) {
            return $this->channelBuffer[$bufferKey];
        }

        $this->channelBuffer[$bufferKey] = $this->connectionManager->getChannelsByStoreName($storeName, $localeName);

        return $this->channelBuffer[$bufferKey];
    }

    /**
     * @param \Generated\Shared\Transfer\QueueSendMessageTransfer $queueSendMessageTransfer
     *
     * @return \PhpAmqpLib\Channel\AMQPChannel[]
     */
    protected function getChannelByQueuePoolName(QueueSendMessageTransfer $queueSendMessageTransfer): array
    {
        $localeName = $queueSendMessageTransfer->getLocale();
        $queuePoolName = $queueSendMessageTransfer->getQueuePoolName();

        $bufferKey = sprintf(static::QUEUE_POOL_NAME_BUFFER_KEY_FORMAT, $queuePoolName, $localeName);
        if (isset($this->channelBuffer[$bufferKey])) {
            return $this->channelBuffer[$bufferKey];
        }

        $this->channelBuffer[$bufferKey] = $this->connectionManager->getChannelsByQueuePoolName($queuePoolName, $localeName);

        return $this->channelBuffer[$bufferKey];
    }

    /**
     * @return \PhpAmqpLib\Channel\AMQPChannel[]
     */
    protected function getDefaultChannel(): array
    {
        if (isset($this->channelBuffer[static::DEFAULT_CHANNEL])) {
            return $this->channelBuffer[static::DEFAULT_CHANNEL];
        }

        $this->channelBuffer[static::DEFAULT_CHANNEL] = [$this->connectionManager->getDefaultChannel()];

        return $this->channelBuffer[static::DEFAULT_CHANNEL];
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
        $message = new AMQPMessage($messageTransfer->getBody(), $this->config->getMessageConfig());
        $headers = $messageTransfer->getHeaders();

        if ($headers !== null) {
            $headersTable = new AMQPTable($headers);
            $message->set('application_headers', $headersTable);
        }

        return $message;
    }

    /**
     * @deprecated use RabbitMqConfig::getMessageConfig() instead of this.
     *
     * @return array
     */
    protected function getMessageConfig()
    {
        return [];
    }
}
