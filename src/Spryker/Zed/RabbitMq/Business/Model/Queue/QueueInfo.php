<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\RabbitMq\Business\Model\Queue;

use Generated\Shared\Transfer\QueueInformationCollectionTransfer;
use Generated\Shared\Transfer\QueueInformationTransfer;
use Spryker\Zed\RabbitMq\Dependency\Guzzle\RabbitMqToGuzzleInterface;

class QueueInfo implements QueueInfoInterface
{
    /**
     * @var \Spryker\Zed\RabbitMq\Dependency\Guzzle\RabbitMqToGuzzleInterface
     */
    protected $client;

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
     * @param \Spryker\Zed\RabbitMq\Dependency\Guzzle\RabbitMqToGuzzleInterface $client
     * @param string $apiQueuesUrl
     * @param string $username
     * @param string $password
     */
    public function __construct(RabbitMqToGuzzleInterface $client, $apiQueuesUrl, $username, $password)
    {
        $this->client = $client;
        $this->apiQueuesUrl = $apiQueuesUrl;
        $this->username = $username;
        $this->password = $password;
    }

    public function areQueuesEmpty(array $queueNames): bool
    {
        $response = $this->client->get($this->apiQueuesUrl, ['auth' => [$this->username, $this->password]]);

        if ($response->getStatusCode() !== 200) {
            return true;
        }

        $decodedResponse = json_decode($response->getBody()->getContents(), true);

        foreach ($decodedResponse as $queueInfo) {
            if ($queueInfo['messages'] > 0 && in_array($queueInfo['name'], $queueNames)) {
                return false;
            }
        }

        return true;
    }

    public function getQueues(): QueueInformationCollectionTransfer
    {
        $response = $this->client->get($this->apiQueuesUrl, ['auth' => [$this->username, $this->password]]);

        $queueInformationCollectionTransfer = new QueueInformationCollectionTransfer();
        if ($response->getStatusCode() === 200) {
            $decodedResponse = json_decode($response->getBody()->getContents(), true);

            return $this->addRabbitMqQueues($queueInformationCollectionTransfer, $decodedResponse);
        }

        return $queueInformationCollectionTransfer;
    }

    /**
     * @param \Generated\Shared\Transfer\QueueInformationCollectionTransfer $queueInformationCollectionTransfer
     * @param array $response
     *
     * @return \Generated\Shared\Transfer\QueueInformationCollectionTransfer
     */
    protected function addRabbitMqQueues(QueueInformationCollectionTransfer $queueInformationCollectionTransfer, array $response)
    {
        foreach ($response as $queueInfo) {
            $queueInformationTransfer = new QueueInformationTransfer();
            $queueInformationTransfer->setName($queueInfo['name']);
            $queueInformationTransfer->setReadyCount($queueInfo['messages_ready']);

            $queueInformationCollectionTransfer->addQueue($queueInformationTransfer);
        }

        return $queueInformationCollectionTransfer;
    }
}
