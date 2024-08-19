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
     * @return bool
     */
    public function areQueuesEmpty(array $queueNames): bool
    {
        $response = $this->client->get($this->apiQueuesUrl, ['auth' => [$this->username, $this->password]]);

        $rabbitMqQueueCollectionTransfer = new RabbitMqQueueCollectionTransfer();
        if ($response->getStatusCode() === 200) {
            $decodedResponse = json_decode($response->getBody()->getContents(), true);

            foreach ($decodedResponse as $queueInfo) {
                if ($queueInfo['messages'] > 0 && $this->isApplicableQueue($queueInfo['name'], $queueNames)) {
                    return false;
                }
            }
        }

        return true;
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

            foreach ($decodedResponse as $queueInfo) {
                $rabbitMqQueueTransfer = (new RabbitMqQueueTransfer())->fromArray($queueInfo, true);
                $rabbitMqQueueTransfer->setMessageCount($queueInfo['messages']);

                $rabbitMqQueueCollectionTransfer->addRabbitMqQueue($rabbitMqQueueTransfer);
            }
        }

        return $rabbitMqQueueCollectionTransfer;
    }

    public function isApplicableQueue(string $currentQueueName, array $queueNames): bool
    {
        return in_array($currentQueueName, $queueNames);
    }
}
