<?php

/**
 * MIT License
 * For full license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Spryker\Zed\RabbitMq\Business\Model\UserPermission\Writer;

use Generated\Shared\Transfer\QueueConnectionTransfer;
use Spryker\Zed\RabbitMq\Business\Model\Init\Exception\InitProcessException;
use Spryker\Zed\RabbitMq\Dependency\Guzzle\RabbitMqToGuzzleInterface;
use Symfony\Component\HttpFoundation\Response;

class UserPermissionWriter implements UserPermissionWriterInterface
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
        $response = $this->client->put(
            $this->buildUrl($queueConnectionTransfer),
            $this->getUserPermissionOptions()
        );

        $responseStatusCode = $response->getStatusCode();

        if ($responseStatusCode >= Response::HTTP_OK && $responseStatusCode <= Response::HTTP_MULTIPLE_CHOICES) {
            return true;
        }

        throw new InitProcessException($response->getReasonPhrase());
    }

    /**
     * @param \Generated\Shared\Transfer\QueueConnectionTransfer $queueConnectionTransfer
     *
     * @return string
     */
    protected function buildUrl(QueueConnectionTransfer $queueConnectionTransfer): string
    {
        $virtualHost = $queueConnectionTransfer->getVirtualHost();
        $userName = $queueConnectionTransfer->getUsername();

        return $this->endpoint
            . '/'
            . urlencode($virtualHost)
            . '/'
            . urlencode($userName);
    }

    /**
     * @return array
     */
    protected function getUserPermissionOptions(): array
    {
        return [
            'configure' => '.*',
            'read' => '.*',
            'write' => '.*',
        ];
    }
}
