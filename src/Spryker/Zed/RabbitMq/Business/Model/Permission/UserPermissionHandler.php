<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\RabbitMq\Business\Model\Permission;

use Psr\Log\LoggerInterface;
use Spryker\Zed\RabbitMq\Dependency\Guzzle\RabbitMqToGuzzleInterface;

class UserPermissionHandler implements UserPermissionHandlerInterface
{
    /**
     * @var \Spryker\Zed\RabbitMq\Dependency\Guzzle\RabbitMqToGuzzleInterface
     */
    protected $client;

    /**
     * @var string
     */
    protected $apiUserPermissionUrl;

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
     * @param string $apiUserPermissionUrl
     * @param string $username
     * @param string $password
     */
    public function __construct(RabbitMqToGuzzleInterface $client, $apiUserPermissionUrl, $username, $password)
    {
        $this->client = $client;
        $this->apiUserPermissionUrl = $apiUserPermissionUrl;
        $this->username = $username;
        $this->password = $password;
    }

    /**
     * @param \Psr\Log\LoggerInterface $logger
     *
     * @return bool
     */
    public function setPermissions(LoggerInterface $logger)
    {
        $response = $this->client->put($this->apiUserPermissionUrl, [
            'auth' => [$this->username, $this->password],
            'json' => [
                'configure' => '.*',
                'read' => '.*',
                'write' => '.*',
            ],
        ]);

        if ($response->getStatusCode() > 200 && $response->getStatusCode() < 300) {
            $logger->info(sprintf('Permissions successfully set for %s.', $this->username));

            return true;
        }

        $logger->error(sprintf('Permission set failed: %s', print_r($response, true)));

        return false;
    }
}
