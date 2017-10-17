<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\RabbitMq\Business\Model\Exchange;

use Generated\Shared\Transfer\RabbitMqExchangeCollectionTransfer;
use Generated\Shared\Transfer\RabbitMqExchangeTransfer;
use GuzzleHttp\Client;

class ExchangeInfo implements ExchangeInfoInterface
{
    const AMQP_DEFAULT_EXCHANGE_NAME = 'AMQP-default';

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
     * @param string $apiExchangeUrl
     * @param string $username
     * @param string $password
     */
    public function __construct($apiExchangeUrl, $username, $password)
    {
        $this->apiExchangeUrl = $apiExchangeUrl;
        $this->username = $username;
        $this->password = $password;
    }

    /**
     * @return \Generated\Shared\Transfer\RabbitMqExchangeCollectionTransfer
     */
    public function getExchanges()
    {
        $client = new Client();
        $response = $client->get($this->apiExchangeUrl, ['auth' => [$this->username, $this->password]]);

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
