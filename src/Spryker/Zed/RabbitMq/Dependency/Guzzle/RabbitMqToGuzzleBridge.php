<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
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
     * @param array $options
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function get($uri, array $options = [])
    {
        return $this->client->get($uri, $options);
    }

    /**
     * @param string $uri
     * @param array $options
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function put($uri, array $options = [])
    {
        return $this->client->put($uri, $options);
    }
}
