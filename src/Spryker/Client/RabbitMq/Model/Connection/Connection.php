<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Client\RabbitMq\Model\Connection;

use ArrayObject;
use Generated\Shared\Transfer\RabbitMqOptionTransfer;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use Spryker\Client\RabbitMq\Model\Helper\QueueEstablishmentHelperInterface;

class Connection implements ConnectionInterface
{

    const RABBIT_MQ_EXCHANGE = 'exchange';

    /**
     * @var \Generated\Shared\Transfer\RabbitMqOptionTransfer[]
     */
    protected $queueOptionCollection;

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
     * @param \ArrayObject $queueOptionCollection
     */
    public function __construct(
        AMQPStreamConnection $streamConnection,
        QueueEstablishmentHelperInterface $queueEstablishmentHelper,
        ArrayObject $queueOptionCollection
    ) {

        $this->streamConnection = $streamConnection;
        $this->queueEstablishmentHelper = $queueEstablishmentHelper;
        $this->queueOptionCollection = $queueOptionCollection;

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
        //TODO Check performance, it will create queues again and again!
        foreach ($this->queueOptionCollection as $queueOption) {
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
     * @param string $exchangeQueueName
     * @param \Generated\Shared\Transfer\RabbitMqOptionTransfer $queueOption
     *
     * @return void
     */
    protected function createQueueAndBind(RabbitMqOptionTransfer $queueOption, $exchangeQueueName)
    {
        $this->queueEstablishmentHelper->createQueue($this->channel, $queueOption);

        $this->bindQueues($queueOption->getQueueName(), $exchangeQueueName, $queueOption->getRoutingKey());
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
