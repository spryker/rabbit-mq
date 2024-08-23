<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Client\RabbitMq\Model\Connection\QueueConnectionTransferFilter;

use Generated\Shared\Transfer\QueueConnectionTransfer;
use Spryker\Client\RabbitMq\Dependency\Client\RabbitMqToStoreClientInterface;

class QueueConnectionTransferFilter implements QueueConnectionTransferFilterInterface
{
    /**
     * @var \Spryker\Client\RabbitMq\Dependency\Client\RabbitMqToStoreClientInterface
     */
    protected $storeClient;

    /**
     * @param \Spryker\Client\RabbitMq\Dependency\Client\RabbitMqToStoreClientInterface $storeClient
     */
    public function __construct(RabbitMqToStoreClientInterface $storeClient)
    {
        $this->storeClient = $storeClient;
    }

    /**
     * @param array<\Generated\Shared\Transfer\QueueConnectionTransfer> $queueConnectionTransfers
     * @param string|null $localeCode
     *
     * @return array<\Generated\Shared\Transfer\QueueConnectionTransfer>
     */
    public function filterByLocaleCode(array $queueConnectionTransfers, ?string $localeCode): array
    {
        if ($localeCode === null) {
            return $queueConnectionTransfers;
        }

        $filteredQueueConnectionTransfers = [];
        foreach ($queueConnectionTransfers as $key => $queueConnectionTransfer) {
            if ($this->isLocaleCodeDefinedInQueueConnectionTransfer($queueConnectionTransfer, $localeCode)) {
                $filteredQueueConnectionTransfers[$key] = $queueConnectionTransfer;
            }
        }

        return $filteredQueueConnectionTransfers;
    }

    /**
     * @param \Generated\Shared\Transfer\QueueConnectionTransfer $queueConnectionTransfer
     * @param string $localeCode
     *
     * @return bool
     */
    protected function isLocaleCodeDefinedInQueueConnectionTransfer(
        QueueConnectionTransfer $queueConnectionTransfer,
        string $localeCode
    ): bool {
        foreach ($queueConnectionTransfer->getStoreNames() as $storeName) {
            if ($this->isLocaleCodeDefinedForStore($storeName, $localeCode)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param string $storeName
     * @param string $localeCode
     *
     * @return bool
     */
    protected function isLocaleCodeDefinedForStore(string $storeName, string $localeCode): bool
    {
        return in_array($localeCode, $this->getLocaleCodesPerStore($storeName), true);
    }

    /**
     * @param string $storeName
     *
     * @return array<string>
     */
    protected function getLocaleCodesPerStore(string $storeName): array
    {
        return $this->storeClient->getStoreByName($storeName)->getAvailableLocaleIsoCodes();
    }
}
