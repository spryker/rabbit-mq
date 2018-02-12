<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Client\RabbitMq\Model\Connection;

use Generated\Shared\Transfer\QueueConnectionTransfer;
use Generated\Shared\Transfer\RabbitMqOptionTransfer;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use Spryker\Client\RabbitMq\Model\Helper\QueueEstablishmentHelperInterface;

class Connection implements ConnectionInterface
{
    const RABBIT_MQ_EXCHANGE = 'exchange';

    /**
     * @var \Generated\Shared\Transfer\QueueConnectionTransfer
     */
    protected $queueConnection;

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
     * @var string
     */
    protected $connectionName;

    /**
     * @var bool
     */
    protected $isDefaultConnection;

    /**
     * @param \PhpAmqpLib\Connection\AMQPStreamConnection $streamConnection
     * @param \Spryker\Client\RabbitMq\Model\Helper\QueueEstablishmentHelperInterface $queueEstablishmentHelper
     * @param \Generated\Shared\Transfer\QueueConnectionTransfer $queueConnection
     */
    public function __construct(
        AMQPStreamConnection $streamConnection,
        QueueEstablishmentHelperInterface $queueEstablishmentHelper,
        QueueConnectionTransfer $queueConnection
    ) {

        $this->streamConnection = $streamConnection;
        $this->queueEstablishmentHelper = $queueEstablishmentHelper;
        $this->queueConnection = $queueConnection;
        $this->connectionName = $queueConnection->getName();
        $this->isDefaultConnection = $queueConnection->getIsDefaultConnection();

        $this->setupConnection();
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
        return $this->connectionName;
    }

    /**
     * @return bool
     */
    public function getIsDefaultConnection()
    {
        return $this->isDefaultConnection;
    }

    /**
     * @return void
     */
    protected function setupConnection()
    {
        $this->channel = $this->streamConnection->channel();

        $this->setupQueueAndExchange();
    }

    /**
     * @return void
     */
    protected function setupQueueAndExchange()
    {
        foreach ($this->queueConnection->getQueueOptionCollection() as $queueOption) {
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
     * @param \Generated\Shared\Transfer\RabbitMqOptionTransfer $queueOption
     * @param string $exchangeQueueName
     *
     * @return void
     */
    protected function createQueueAndBind(RabbitMqOptionTransfer $queueOption, $exchangeQueueName)
    {
        $this->queueEstablishmentHelper->createQueue($this->channel, $queueOption);

        // @deprecated Removed with new Transfer module version which has string[] fix.
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
        $this->close();
    }
}
