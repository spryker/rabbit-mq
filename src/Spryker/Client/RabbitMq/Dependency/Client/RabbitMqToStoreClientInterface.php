<?php

namespace Spryker\Client\RabbitMq\Dependency\Client;

use Generated\Shared\Transfer\StoreTransfer;

interface RabbitMqToStoreClientInterface
{
    /**
     * @return StoreTransfer
     */
    public function getCurrentStore();
}
