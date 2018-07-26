<?php

/**
 * MIT License
 * For full license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Spryker\Zed\RabbitMq\Business\Model\Exchange;

use Generated\Shared\Transfer\RabbitMqExchangeCollectionTransfer;
use Generated\Shared\Transfer\RabbitMqExchangeTransfer;
use Spryker\Zed\RabbitMq\Dependency\Guzzle\RabbitMqToGuzzleInterface;

class ExchangeInfo implements ExchangeInfoInterface
{
    const AMQP_DEFAULT_EXCHANGE_NAME = 'AMQP-default';

    /**
     * @var \Spryker\Zed\RabbitMq\Dependency\Guzzle\RabbitMqToGuzzleInterface
     */
    protected $client;

    /**
     * @var string
     */
    protected $apiExchangeUrl;

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
     * @param string $apiExchangeUrl
     * @param string $username
     * @param string $password
     */
    public function __construct(RabbitMqToGuzzleInterface $client, $apiExchangeUrl, $username, $password)
    {
        $this->client = $client;
        $this->apiExchangeUrl = $apiExchangeUrl;
        $this->username = $username;
        $this->password = $password;
    }

    /**
     * @return \Generated\Shared\Transfer\RabbitMqExchangeCollectionTransfer
     */
    public function getExchanges()
    {
        $response = $this->client->get($this->apiExchangeUrl, ['auth' => [$this->username, $this->password]]);

        $rabbitMqExchangeCollectionTransfer = new RabbitMqExchangeCollectionTransfer();
        if ($response->getStatusCode() === 200) {
            $decodedResponse = json_decode($response->getBody()->getContents(), true);

            return $this->addRabbitMqExchanges($rabbitMqExchangeCollectionTransfer, $decodedResponse);
        }

        return $rabbitMqExchangeCollectionTransfer;
    }

    /**
     * @param \Generated\Shared\Transfer\RabbitMqExchangeCollectionTransfer $rabbitMqExchangeCollectionTransfer
     * @param array $response
     *
     * @return \Generated\Shared\Transfer\RabbitMqExchangeCollectionTransfer
     */
    protected function addRabbitMqExchanges(RabbitMqExchangeCollectionTransfer $rabbitMqExchangeCollectionTransfer, array $response)
    {
        foreach ($response as $exchangeInfo) {
            $rabbitMqExchangeTransfer = new RabbitMqExchangeTransfer();
            $rabbitMqExchangeTransfer
                ->setName(!empty($exchangeInfo['name']) ? $exchangeInfo['name'] : static::AMQP_DEFAULT_EXCHANGE_NAME)
                ->setVirtualHost($exchangeInfo['vhost']);

            $rabbitMqExchangeCollectionTransfer->addRabbitMqExchange($rabbitMqExchangeTransfer);
        }

        return $rabbitMqExchangeCollectionTransfer;
    }
}
