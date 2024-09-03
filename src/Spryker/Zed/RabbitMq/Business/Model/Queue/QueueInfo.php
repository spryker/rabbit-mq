<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\RabbitMq\Business\Model\Queue;

use Generated\Shared\Transfer\RabbitMqConsumerOptionTransfer;
use Generated\Shared\Transfer\RabbitMqQueueCollectionTransfer;
use Generated\Shared\Transfer\RabbitMqQueueTransfer;
use Spryker\Client\Queue\QueueClientInterface;
use Spryker\Zed\RabbitMq\Dependency\Guzzle\RabbitMqToGuzzleInterface;

class QueueInfo implements QueueInfoInterface
{
    /**
     * @var \Spryker\Zed\RabbitMq\Dependency\Guzzle\RabbitMqToGuzzleInterface
     */
    protected $guzzleClient;

    /**
     * @var \Spryker\Client\Queue\QueueClientInterface
     */
    protected $queueClient;

    /**
     * @var \Spryker\Zed\Queue\QueueConfig
     */
    protected $queueConfig;

    /**
     * @var string
     */
    protected $apiQueuesUrl;

    /**
     * @var string
     */
    protected $username;

    /**
     * @var string
     */
    protected $password;

    /**
     * @param \Spryker\Zed\RabbitMq\Dependency\Guzzle\RabbitMqToGuzzleInterface $guzzleClient
     * @param string $apiQueuesUrl
     * @param string $username
     * @param string $password
     * @param \Spryker\Client\Queue\QueueClientInterface $queueClient
     */
    public function __construct(
        RabbitMqToGuzzleInterface $guzzleClient,
        $apiQueuesUrl,
        $username,
        $password,
        QueueClientInterface $queueClient
    ) {
        $this->guzzleClient = $guzzleClient;
        $this->apiQueuesUrl = $apiQueuesUrl;
        $this->username = $username;
        $this->password = $password;
        $this->queueClient = $queueClient;
    }

    /**
     * @param array $queueNames
     *
     * @return bool
     */
    public function areQueuesEmpty(array $queueNames): bool
    {
        $queueOptionTransfer = new RabbitMqConsumerOptionTransfer();
        $queueOptionTransfer->setRequeueOnReject(true);

        $options = ['rabbitmq' => $queueOptionTransfer];

        foreach ($queueNames as $queue) {
            $message = $this->queueClient->receiveMessage($queue, $options);

            if ($message->getQueueMessage()) {
                return false;
            }
        }

        return true;
    }

    /**
     * @return \Generated\Shared\Transfer\RabbitMqQueueCollectionTransfer
     */
    public function getQueues()
    {
        $response = $this->guzzleClient->get($this->apiQueuesUrl, ['auth' => [$this->username, $this->password]]);

        $rabbitMqQueueCollectionTransfer = new RabbitMqQueueCollectionTransfer();
        if ($response->getStatusCode() === 200) {
            $decodedResponse = json_decode($response->getBody()->getContents(), true);

            return $this->addRabbitMqQueues($rabbitMqQueueCollectionTransfer, $decodedResponse);
        }

        return $rabbitMqQueueCollectionTransfer;
    }

    /**
     * @param \Generated\Shared\Transfer\RabbitMqQueueCollectionTransfer $rabbitMqQueueCollectionTransfer
     * @param array $response
     *
     * @return \Generated\Shared\Transfer\RabbitMqQueueCollectionTransfer
     */
    protected function addRabbitMqQueues(RabbitMqQueueCollectionTransfer $rabbitMqQueueCollectionTransfer, array $response)
    {
        foreach ($response as $queueInfo) {
            $rabbitMqQueueTransfer = new RabbitMqQueueTransfer();
            $rabbitMqQueueTransfer->setName($queueInfo['name']);

            $rabbitMqQueueCollectionTransfer->addRabbitMqQueue($rabbitMqQueueTransfer);
        }

        return $rabbitMqQueueCollectionTransfer;
    }
}
