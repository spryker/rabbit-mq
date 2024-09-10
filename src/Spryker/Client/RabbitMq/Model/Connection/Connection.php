<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
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
    /**
     * @var string
     */
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
     * @var \Spryker\Client\RabbitMq\RabbitMqConfig
     */
    protected $clientConfig;

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
        $this->clientConfig = $clientConfig;

        if ($this->clientConfig->isRuntimeSettingUpEnabled()) {
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
     * @return array<string>
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
     * @return void
     */
    public function setupQueuesAndExchanges(): void
    {
        foreach ($this->queueConnectionConfig->getQueueOptionCollection() as $queueOption) {
            if ($queueOption->getDeclarationType() !== static::RABBIT_MQ_EXCHANGE) {
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
     * @param \Generated\Shared\Transfer\RabbitMqOptionTransfer $queueOption
     * @param string $exchangeQueueName
     *
     * @return void
     */
    protected function createQueueAndBind(RabbitMqOptionTransfer $queueOption, $exchangeQueueName)
    {
        $this->queueEstablishmentHelper->createQueue($this->channel, $queueOption);

        /** @var array<string>|null $routingKeys */
        $routingKeys = $queueOption->getRoutingKeys();
        if ($routingKeys === null) {
            return;
        }

        foreach ($routingKeys as $routingKey) {
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
