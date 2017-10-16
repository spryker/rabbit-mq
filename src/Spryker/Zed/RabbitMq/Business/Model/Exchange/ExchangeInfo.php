<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\RabbitMq\Business\Model\Exchange;

use GuzzleHttp\Client;

class ExchangeInfo implements ExchangeInfoInterface
{
    /**
     * @var string
     */
    protected $host;

    /**
     * @var string
     */
    protected $webPort;

    /**
     * @var string
     */
    protected $username;

    /**
     * @var string
     */
    protected $password;

    /**
     * @param string $host
     * @param string $webPort
     * @param string $username
     * @param string $password
     */
    public function __construct($host, $webPort, $username, $password)
    {
        $this->host = $host;
        $this->webPort = $webPort;
        $this->username = $username;
        $this->password = $password;
    }

    /**
     * @return array
     */
    public function getAllExchangeNames()
    {
        $client = new Client();
        $url = sprintf('http://%s:%s/api/exchanges', $this->host, $this->webPort);
        $response = $client->get($url, ['auth' => [$this->username, $this->password]]);

        if ($response->getStatusCode() === 200) {
            $decodedResponse = json_decode($response->getBody()->getContents(), true);

            return $this->extractExchangeNames($decodedResponse);
        }

        return [];
    }

    /**
     * @param array $response
     *
     * @return array
     */
    protected function extractExchangeNames(array $response)
    {
        $exchangeNames = [];
        foreach ($response as $exchangeInfo) {
            if (!empty($exchangeInfo['name'])) {
                $exchangeNames[] = $exchangeInfo['name'];
            }
        }

        return $exchangeNames;
    }
}
