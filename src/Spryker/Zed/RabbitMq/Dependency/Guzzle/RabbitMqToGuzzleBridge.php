<?php

/**
 * MIT License
 * For full license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Spryker\Zed\RabbitMq\Dependency\Guzzle;

class RabbitMqToGuzzleBridge implements RabbitMqToGuzzleInterface
{
    /**
     * @var \GuzzleHttp\Client
     */
    protected $client;

    /**
     * @param \GuzzleHttp\Client $client
     */
    public function __construct($client)
    {
        $this->client = $client;
    }

    /**
     * @param string $uri
     * @param array<string, mixed> $options
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function get($uri, array $options = [])
    {
        return $this->client->get($uri, $options);
    }

    /**
     * @param string $uri
     * @param array<string, mixed> $options
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function put($uri, array $options = [])
    {
        return $this->client->put($uri, $options);
    }
}
