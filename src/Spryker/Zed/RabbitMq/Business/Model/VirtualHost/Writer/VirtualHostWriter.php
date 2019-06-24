<?php

/**
 * MIT License
 * For full license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Spryker\Zed\RabbitMq\Business\Model\VirtualHost\Writer;

use Generated\Shared\Transfer\QueueConnectionTransfer;
use Spryker\Zed\RabbitMq\Business\Model\Init\Exception\InitProcessException;
use Spryker\Zed\RabbitMq\Dependency\Guzzle\RabbitMqToGuzzleInterface;
use Symfony\Component\HttpFoundation\Response;

class VirtualHostWriter implements VirtualHostWriterInterface
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
     * @throws \Spryker\Zed\RabbitMq\Business\Model\Init\Exception\InitProcessException
     *
     * @return bool
     */
    public function add(QueueConnectionTransfer $queueConnectionTransfer): bool
    {
        $virtualHost = $queueConnectionTransfer->getVirtualHost();

        $response = $this->client->put(
            $this->endpoint . '/' . urlencode($virtualHost)
        );

        $responseStatusCode = $response->getStatusCode();

        if ($responseStatusCode >= Response::HTTP_OK && $responseStatusCode <= Response::HTTP_MULTIPLE_CHOICES) {
            return true;
        }

        throw new InitProcessException($response->getReasonPhrase());
    }
}
