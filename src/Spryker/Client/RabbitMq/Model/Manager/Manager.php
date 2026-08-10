<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Client\RabbitMq\Model\Manager;

use PhpAmqpLib\Channel\AMQPChannel;
use Spryker\Client\RabbitMq\Model\Helper\QueueEstablishmentHelperInterface;

class Manager implements ManagerInterface
{
    /**
     * @var \PhpAmqpLib\Channel\AMQPChannel
     */
    protected $channel;

    /**
     * @var \Spryker\Client\RabbitMq\Model\Helper\QueueEstablishmentHelperInterface
     */
    protected $queueEstablishmentHelper;

    /**
     * @param \PhpAmqpLib\Channel\AMQPChannel $channel
     * @param \Spryker\Client\RabbitMq\Model\Helper\QueueEstablishmentHelperInterface $queueEstablishmentHelper
     */
    public function __construct(AMQPChannel $channel, QueueEstablishmentHelperInterface $queueEstablishmentHelper)
    {
        $this->channel = $channel;
        $this->queueEstablishmentHelper = $queueEstablishmentHelper;
    }

    /**
     * @param string $queueName
     * @param array<string, mixed> $options
     *
     * @return array
     */
    public function createQueue($queueName, array $options = [])
    {
        /** @var \Generated\Shared\Transfer\RabbitMqOptionTransfer $rabbitMqOption */
        $rabbitMqOption = $options['rabbitMqConsumerOption'];

        return $this->queueEstablishmentHelper->createQueue($this->channel, $rabbitMqOption);
    }

    /**
     * @param string $queueName
     * @param array<string, mixed> $options
     *
     * @return bool
     */
    public function deleteQueue($queueName, array $options = [])
    {
        return $this->channel->queue_delete($queueName);
    }

    /**
     * @param string $queueName
     * @param array<string, mixed> $options
     *
     * @return bool
     */
    public function purgeQueue($queueName, array $options = [])
    {
        return $this->channel->queue_purge($queueName);
    }

    /**
     * @param string $exchangeName
     *
     * @return bool
     */
    public function deleteExchange($exchangeName)
    {
        return $this->channel->exchange_delete($exchangeName);
    }
}
