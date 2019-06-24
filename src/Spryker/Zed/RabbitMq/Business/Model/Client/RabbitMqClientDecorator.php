<?php

/**
 * MIT License
 * For full license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Spryker\Zed\RabbitMq\Business\Model\Client;

use GuzzleHttp\RequestOptions;
use Psr\Http\Message\ResponseInterface;
use Spryker\Zed\RabbitMq\Dependency\Guzzle\RabbitMqToGuzzleInterface;

class RabbitMqClientDecorator implements RabbitMqToGuzzleInterface
{
    /**
     * @var string
     */
    protected $apiUserName;

    /**
     * @var string
     */
    protected $apiUserPassword;

    /**
     * @var \Spryker\Zed\RabbitMq\Dependency\Guzzle\RabbitMqToGuzzleInterface
     */
    protected $guzzleClient;

    /**
     * @param \Spryker\Zed\RabbitMq\Dependency\Guzzle\RabbitMqToGuzzleInterface $guzzleClient
     * @param string $apiUserName
     * @param string $apiUserPassword
     */
    public function __construct(
        RabbitMqToGuzzleInterface $guzzleClient,
        string $apiUserName,
        string $apiUserPassword
    ) {
        $this->guzzleClient = $guzzleClient;
        $this->apiUserName = $apiUserName;
        $this->apiUserPassword = $apiUserPassword;
    }

    /**
     * @param string $uri
     * @param array $options
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function get($uri, array $options = []): ResponseInterface
    {
        return $this->guzzleClient->get(
            $uri,
            $this->buildOptions($options)
        );
    }

    /**
     * @param string $uri
     * @param array $options
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function put($uri, array $options = []): ResponseInterface
    {
        return $this->guzzleClient->put(
            $uri,
            $this->buildOptions($options)
        );
    }

    /**
     * @param array $options
     *
     * @return array
     */
    protected function buildOptions(array $options): array
    {
        return array_merge(
            $this->getHttpErrorConfigurationOptions(),
            $this->getAuthOptions(),
            [
                RequestOptions::JSON => $options,
            ]
        );
    }

    /**
     * @return string[]
     */
    protected function getAuthOptions(): array
    {
        return [
            'auth' => [
                $this->apiUserName,
                $this->apiUserPassword,
            ],
        ];
    }

    /**
     * @return bool[]
     */
    protected function getHttpErrorConfigurationOptions(): array
    {
        return [
            'http_errors' => false,
        ];
    }
}
