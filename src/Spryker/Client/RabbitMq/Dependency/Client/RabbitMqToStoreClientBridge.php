<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
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
}
