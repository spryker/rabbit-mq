<?php

namespace Spryker\Client\RabbitMq\Dependency\Client;

use Generated\Shared\Transfer\StoreTransfer;
use Spryker\Client\Store\StoreClientInterface;

class RabbitMqToStoreClientBridge implements RabbitMqToStoreClientInterface
{
    /**
     * @var StoreClientInterface
     */
    protected $storeClient;

    /**
     * @param StoreClientInterface $storeClient
     */
    public function __construct($storeClient)
    {
        $this->storeClient = $storeClient;
    }

    /**
     * @return StoreTransfer
     */
    public function getCurrentStore()
    {
        return $this->storeClient->getCurrentStore();
    }
}
