<?php

/**
 * MIT License
 * For full license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Spryker\Zed\RabbitMq\Business\Model\Queue;

use Generated\Shared\Transfer\RabbitMqQueueCollectionTransfer;
use Generated\Shared\Transfer\RabbitMqQueueTransfer;
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

    /**
     * @return \Generated\Shared\Transfer\RabbitMqQueueCollectionTransfer
     */
    public function getQueues()
    {
        $response = $this->client->get($this->apiQueuesUrl, ['auth' => [$this->username, $this->password]]);

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
