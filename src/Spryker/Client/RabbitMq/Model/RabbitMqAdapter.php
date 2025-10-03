<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Client\RabbitMq\Model;

use Generated\Shared\Transfer\QueueReceiveMessageTransfer;
use Generated\Shared\Transfer\QueueSendMessageTransfer;
use Spryker\Client\RabbitMq\Model\Consumer\ConsumerInterface;
use Spryker\Client\RabbitMq\Model\Manager\ManagerInterface;
use Spryker\Client\RabbitMq\Model\Publisher\PublisherInterface;

class RabbitMqAdapter implements RabbitMqAdapterInterface
{
    /**
     * @var \Spryker\Client\RabbitMq\Model\Manager\ManagerInterface
     */
    protected $manager;

    /**
     * @var \Spryker\Client\RabbitMq\Model\Publisher\PublisherInterface
     */
    protected $publisher;

    /**
     * @var \Spryker\Client\RabbitMq\Model\Consumer\ConsumerInterface
     */
    protected $consumer;

    /**
     * @param \Spryker\Client\RabbitMq\Model\Manager\ManagerInterface $manager
     * @param \Spryker\Client\RabbitMq\Model\Publisher\PublisherInterface $publisher
     * @param \Spryker\Client\RabbitMq\Model\Consumer\ConsumerInterface $consumer
     */
    public function __construct(
        ManagerInterface $manager,
        PublisherInterface $publisher,
        ConsumerInterface $consumer
    ) {
        $this->manager = $manager;
        $this->publisher = $publisher;
        $this->consumer = $consumer;
    }

    /**
     * @param string $queueName
     * @param array<string, mixed> $options
     *
     * @return array
     */
    public function createQueue($queueName, array $options = [])
    {
        return $this->manager->createQueue($queueName, $options);
    }

    /**
     * @param string $queueName
     * @param array<string, mixed> $options
     *
     * @return bool
     */
    public function purgeQueue($queueName, array $options = [])
    {
        return $this->manager->purgeQueue($queueName, $options);
    }

    /**
     * @param string $queueName
     * @param array<string, mixed> $options
     *
     * @return bool
     */
    public function deleteQueue($queueName, array $options = [])
    {
        return $this->manager->deleteQueue($queueName, $options);
    }

    /**
     * @param string $exchangeName
     *
     * @return bool
     */
    public function deleteExchange($exchangeName)
    {
        return $this->manager->deleteExchange($exchangeName);
    }

    /**
     * @param string $queueName
     *
     * @return bool
     */
    public function isQueueEmpty(string $queueName): bool
    {
        return $this->manager->isQueueEmpty($queueName);
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
        return $this->consumer->receiveMessages($queueName, $chunkSize, $options);
    }

    /**
     * @param string $queueName
     * @param array<string, mixed> $options
     *
     * @return \Generated\Shared\Transfer\QueueReceiveMessageTransfer
     */
    public function receiveMessage($queueName, array $options = [])
    {
        return $this->consumer->receiveMessage($queueName, $options);
    }

    /**
     * @param \Generated\Shared\Transfer\QueueReceiveMessageTransfer $queueReceiveMessageTransfer
     *
     * @return void
     */
    public function acknowledge(QueueReceiveMessageTransfer $queueReceiveMessageTransfer)
    {
        $this->consumer->acknowledge($queueReceiveMessageTransfer);
    }

    /**
     * @param \Generated\Shared\Transfer\QueueReceiveMessageTransfer $queueReceiveMessageTransfer
     *
     * @return void
     */
    public function reject(QueueReceiveMessageTransfer $queueReceiveMessageTransfer)
    {
        $this->consumer->reject($queueReceiveMessageTransfer);
    }

    /**
     * @param \Generated\Shared\Transfer\QueueReceiveMessageTransfer $queueReceiveMessageTransfer
     *
     * @return bool
     */
    public function handleError(QueueReceiveMessageTransfer $queueReceiveMessageTransfer)
    {
        return $this->consumer->handleError($queueReceiveMessageTransfer);
    }

    /**
     * @param string $queueName
     * @param \Generated\Shared\Transfer\QueueSendMessageTransfer $queueSendMessageTransfer
     *
     * @return void
     */
    public function sendMessage($queueName, QueueSendMessageTransfer $queueSendMessageTransfer)
    {
        $this->publisher->sendMessage($queueName, $queueSendMessageTransfer);
    }

    /**
     * @param string $queueName
     * @param array<\Generated\Shared\Transfer\QueueSendMessageTransfer> $queueSendMessageTransfers
     *
     * @return void
     */
    public function sendMessages($queueName, array $queueSendMessageTransfers)
    {
        $this->publisher->sendMessages($queueName, $queueSendMessageTransfers);
    }
}
