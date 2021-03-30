<?php

/**
 * MIT License
 * For full license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Spryker\Client\RabbitMq\Model\Connection;

use Generated\Shared\Transfer\QueueConnectionTransfer;
use Generated\Shared\Transfer\RabbitMqOptionTransfer;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Exception\AMQPProtocolChannelException;
use Spryker\Client\RabbitMq\Model\Helper\QueueEstablishmentHelperInterface;
use Spryker\Client\RabbitMq\RabbitMqConfig;

class Connection implements ConnectionInterface
{
    public const RABBIT_MQ_EXCHANGE = 'exchange';

    /**
     * @var \Generated\Shared\Transfer\QueueConnectionTransfer
     */
    protected $queueConnectionConfig;

    /**
     * @var \PhpAmqpLib\Connection\AMQPStreamConnection
     */
    protected $streamConnection;

    /**
     * @var \PhpAmqpLib\Channel\AMQPChannel
     */
    protected $channel;

    /**
     * @var \Spryker\Client\RabbitMq\Model\Helper\QueueEstablishmentHelperInterface
     */
    protected $queueEstablishmentHelper;

    /**
     * @param \PhpAmqpLib\Connection\AMQPStreamConnection $streamConnection
     * @param \Spryker\Client\RabbitMq\Model\Helper\QueueEstablishmentHelperInterface $queueEstablishmentHelper
     * @param \Generated\Shared\Transfer\QueueConnectionTransfer $queueConnection
     * @param \Spryker\Client\RabbitMq\RabbitMqConfig $clientConfig
     */
    public function __construct(
        AMQPStreamConnection $streamConnection,
        QueueEstablishmentHelperInterface $queueEstablishmentHelper,
        QueueConnectionTransfer $queueConnection,
        RabbitMqConfig $clientConfig
    ) {
        $this->streamConnection = $streamConnection;
        $this->queueEstablishmentHelper = $queueEstablishmentHelper;
        $this->queueConnectionConfig = $queueConnection;
        $this->channel = $this->streamConnection->channel();

        if ($clientConfig->isRuntimeSettingUpEnabled()) {
            $this->setupQueuesAndExchanges();
        }
    }

    /**
     * @return \PhpAmqpLib\Channel\AMQPChannel
     */
    public function getChannel()
    {
        return $this->channel;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->queueConnectionConfig->getName();
    }

    /**
     * @return string[]
     */
    public function getStoreNames()
    {
        return $this->queueConnectionConfig->getStoreNames();
    }

    /**
     * @return bool
     */
    public function getIsDefaultConnection()
    {
        return $this->queueConnectionConfig->getIsDefaultConnection();
    }

    /**
     * @return string
     */
    public function getVirtualHost()
    {
        return $this->queueConnectionConfig->getVirtualHost();
    }

    /**
     * @deprecated Use {@link \Spryker\Client\RabbitMq\Model\Connection\Connection::setupQueuesAndExchanges()} instead
     *
     * @return void
     */
    protected function setupConnection()
    {
        $this->channel = $this->streamConnection->channel();

        $this->setupQueuesAndExchanges();
    }

    /**
     * @return void
     */
    public function setupQueuesAndExchanges(): void
    {
        foreach ($this->queueConnectionConfig->getQueueOptionCollection() as $queueOption) {
            if ($queueOption->getDeclarationType() !== self::RABBIT_MQ_EXCHANGE) {
                $this->queueEstablishmentHelper->createQueue($this->channel, $queueOption);

                continue;
            }

            $this->queueEstablishmentHelper->createExchange($this->channel, $queueOption);
            foreach ($queueOption->getBindingQueueCollection() as $bindingQueueItem) {
                $this->createQueueAndBind($bindingQueueItem, $queueOption->getQueueName());
            }
        }
    }

    /**
     * @deprecated Use {@link \Spryker\Client\RabbitMq\Model\Connection\Connection::setupQueuesAndExchanges()} instead
     *
     * @return void
     */
    protected function setupQueueAndExchange()
    {
        $this->setupQueuesAndExchanges();
    }

    /**
     * @param \Generated\Shared\Transfer\RabbitMqOptionTransfer $queueOption
     * @param string $exchangeQueueName
     *
     * @return void
     */
    protected function createQueueAndBind(RabbitMqOptionTransfer $queueOption, $exchangeQueueName)
    {
        $this->queueEstablishmentHelper->createQueue($this->channel, $queueOption);

        if ($queueOption->getRoutingKeys() === null) {
            return;
        }

        foreach ($queueOption->getRoutingKeys() as $routingKey) {
            $this->bindQueues($queueOption->getQueueName(), $exchangeQueueName, $routingKey);
        }
    }

    /**
     * @param string $queueName
     * @param string $exchangeName
     * @param string $routingKey
     *
     * @return void
     */
    protected function bindQueues($queueName, $exchangeName, $routingKey = '')
    {
        $this->channel->queue_bind($queueName, $exchangeName, $routingKey);
    }

    /**
     * @return void
     */
    public function close()
    {
        if ($this->channel === null) {
            return;
        }

        $this->channel->close();
        $this->streamConnection->close();
    }

    public function __destruct()
    {
        try {
            $this->close();
        } catch (AMQPProtocolChannelException $e) {
            // Exchange was likely deleted previously
            return;
        }
    }
}
