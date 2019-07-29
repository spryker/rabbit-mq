<?php

/**
 * MIT License
 * For full license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Spryker\Client\RabbitMq\Model\Connection\ConnectionManager\ConnectionConfigFilter;

use Spryker\Client\RabbitMq\Dependency\Client\RabbitMqToStoreClientInterface;
use Spryker\Client\RabbitMq\Model\Connection\ConnectionFactoryInterface;

class ConnectionConfigFilter implements ConnectionConfigFilterInterface
{
    /**
     * @var \Spryker\Client\RabbitMq\Dependency\Client\RabbitMqToStoreClientInterface
     */
    protected $storeClient;

    /**
     * @var \Spryker\Client\RabbitMq\Model\Connection\ConnectionFactoryInterface
     */
    protected $connectionFactory;

    /**
     * @var bool[]|null
     */
    protected $connectionNameLocaleMap;

    /**
     * @param \Spryker\Client\RabbitMq\Dependency\Client\RabbitMqToStoreClientInterface $storeClient
     * @param \Spryker\Client\RabbitMq\Model\Connection\ConnectionFactoryInterface $connectionFactory
     */
    public function __construct(RabbitMqToStoreClientInterface $storeClient, ConnectionFactoryInterface $connectionFactory)
    {
        $this->storeClient = $storeClient;
        $this->connectionFactory = $connectionFactory;
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

        if ($this->connectionNameLocaleMap === null) {
            $this->addConnectionNameLocaleMap();
        }

        $filteredConnectionsConfig = [];
        foreach ($connectionsConfig as $key => $connectionConfig) {
            if (!isset($this->connectionNameLocaleMap[$connectionConfig->getName()][$locale])) {
                continue;
            }

            $filteredConnectionsConfig[$key] = $connectionConfig;
        }

        return $filteredConnectionsConfig;
    }

    /**
     * @return void
     */
    protected function addConnectionNameLocaleMap(): void
    {
        foreach ($this->connectionFactory->getQueueConnectionConfigs() as $queueConnectionConfig) {
            foreach ($queueConnectionConfig->getStoreNames() as $storeName) {
                foreach ($this->getLocalesPerStore($storeName) as $locale) {
                    $this->connectionNameLocaleMap[$queueConnectionConfig->getName()][$locale] = true;
                }
            }
        }
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
