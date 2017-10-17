<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\RabbitMq\Business\Model\Queue;

use Generated\Shared\Transfer\RabbitMqQueueCollectionTransfer;
use Generated\Shared\Transfer\RabbitMqQueueTransfer;
use GuzzleHttp\Client;

class QueueInfo implements QueueInfoInterface
{
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
     * @param string $apiQueuesUrl
     * @param string $username
     * @param string $password
     */
    public function __construct($apiQueuesUrl, $username, $password)
    {
        $this->apiQueuesUrl = $apiQueuesUrl;
        $this->username = $username;
        $this->password = $password;
    }

    /**
     * @return \Generated\Shared\Transfer\RabbitMqQueueCollectionTransfer
     */
    public function getQueues()
    {
        $client = new Client();
        $response = $client->get($this->apiQueuesUrl, ['auth' => [$this->username, $this->password]]);

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
