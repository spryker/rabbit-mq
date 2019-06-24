<?php

/**
 * MIT License
 * For full license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Spryker\Zed\RabbitMq\Business\Model\User\Reader;

use Generated\Shared\Transfer\QueueConnectionTransfer;
use Spryker\Zed\RabbitMq\Dependency\Guzzle\RabbitMqToGuzzleInterface;
use Symfony\Component\HttpFoundation\Response;

class UserReader implements UserReaderInterface
{
    /**
     * @var \Spryker\Zed\RabbitMq\Dependency\Guzzle\RabbitMqToGuzzleInterface
     */
    protected $client;

    /**
     * @var string
     */
    protected $endpoint;

    /**
     * @param string $endpoint
     * @param \Spryker\Zed\RabbitMq\Dependency\Guzzle\RabbitMqToGuzzleInterface $client
     */
    public function __construct(
        string $endpoint,
        RabbitMqToGuzzleInterface $client
    ) {
        $this->endpoint = $endpoint;
        $this->client = $client;
    }

    /**
     * @param \Generated\Shared\Transfer\QueueConnectionTransfer $queueConnectionTransfer
     *
     * @return bool
     */
    public function has(QueueConnectionTransfer $queueConnectionTransfer): bool
    {
        $userName = $queueConnectionTransfer->getUsername();

        $response = $this->client->get(
            $this->endpoint . '/' . urlencode($userName)
        );

        return $response->getStatusCode() !== Response::HTTP_NOT_FOUND;
    }
}
