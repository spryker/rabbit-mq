<?php

/**
 * MIT License
 * For full license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Spryker\Client\RabbitMq\Dependency\Client;

use Generated\Shared\Transfer\StoreTransfer;

class RabbitMqToStoreClientBridge implements RabbitMqToStoreClientInterface
{
    /**
     * @var \Spryker\Client\Store\StoreClientInterface
     */
    protected $storeClient;

    /**
     * @param \Spryker\Client\Store\StoreClientInterface $storeClient
     */
    public function __construct($storeClient)
    {
        $this->storeClient = $storeClient;
    }

    /**
     * @deprecated Will be removed after dynamic multi-store is always enabled.
     *
     * @return \Generated\Shared\Transfer\StoreTransfer
     */
    public function getCurrentStore()
    {
        return $this->storeClient->getCurrentStore();
    }

    /**
     * @param string $name
     *
     * @return \Generated\Shared\Transfer\StoreTransfer
     */
    public function getStoreByName(string $name): StoreTransfer
    {
        return $this->storeClient->getStoreByName($name);
    }

    /**
     * @return bool
     */
    public function isDynamicStoreEnabled(): bool
    {
        return $this->storeClient->isDynamicStoreEnabled();
    }
}
