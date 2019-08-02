<?php

/**
 * MIT License
 * For full license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Spryker\Client\RabbitMq\Model\Connection\ConnectionConfigFilter;

use Generated\Shared\Transfer\QueueConnectionTransfer;
use Spryker\Client\RabbitMq\Dependency\Client\RabbitMqToStoreClientInterface;

class ConnectionConfigFilter implements ConnectionConfigFilterInterface
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
     * @param \Generated\Shared\Transfer\QueueConnectionTransfer[] $connectionsConfig
     * @param string|null $locale
     *
     * @return \Generated\Shared\Transfer\QueueConnectionTransfer[]
     */
    public function filterByLocale(array $connectionsConfig, ?string $locale): array
    {
        if ($locale === null) {
            return $connectionsConfig;
        }

        $filteredConnectionsConfig = [];
        foreach ($connectionsConfig as $key => $connectionConfig) {
            if ($this->shouldWriteConnectionConfigByLocale($connectionConfig, $locale)) {
                $filteredConnectionsConfig[$key] = $connectionConfig;
            }
        }

        return $filteredConnectionsConfig;
    }

    /**
     * @param \Generated\Shared\Transfer\QueueConnectionTransfer $connectionConfig
     * @param string $locale
     *
     * @return bool
     */
    protected function shouldWriteConnectionConfigByLocale(QueueConnectionTransfer $connectionConfig, string $locale): bool
    {
        foreach ($connectionConfig->getStoreNames() as $storeName) {
            if ($this->isLocaleDefinedForStore($storeName, $locale)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param string $storeName
     * @param string $locale
     *
     * @return bool
     */
    protected function isLocaleDefinedForStore(string $storeName, string $locale): bool
    {
        return in_array($locale, $this->getLocalesPerStore($storeName), true);
    }

    /**
     * @param string $storeName
     *
     * @return string[]
     */
    protected function getLocalesPerStore(string $storeName): array
    {
        return $this->storeClient->getStoreByName($storeName)->getAvailableLocaleIsoCodes();
    }
}
