<?php

/**
 * MIT License
 * For full license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Spryker\Client\RabbitMq\Model\Publisher;

use Generated\Shared\Transfer\QueueSendMessageTransfer;
use PhpAmqpLib\Message\AMQPMessage;
use PhpAmqpLib\Wire\AMQPTable;
use Spryker\Client\RabbitMq\Model\Connection\ConnectionManager\ConnectionManagerInterface;
use Spryker\Client\RabbitMq\RabbitMqConfig;

class Publisher implements PublisherInterface
{
    /**
     * @var \Spryker\Client\RabbitMq\Model\Connection\ConnectionManager\ConnectionManagerInterface
     */
    protected $connectionManager;

    /**
     * @var \Spryker\Client\RabbitMq\RabbitMqConfig
     */
    protected $config;

    /**
     * @param \Spryker\Client\RabbitMq\Model\Connection\ConnectionManager\ConnectionManagerInterface $connectionManager
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
            return $this->connectionManager->getChannelsByStoreName(
                $queueSendMessageTransfer->getStoreName(),
                $queueSendMessageTransfer->getLocale()
            );
        }

        if ($queueSendMessageTransfer->getQueuePoolName()) {
            return $this->connectionManager->getChannelsByQueuePoolName(
                $queueSendMessageTransfer->getQueuePoolName(),
                $queueSendMessageTransfer->getLocale()
            );
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
