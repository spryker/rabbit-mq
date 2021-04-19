<?php

/**
 * MIT License
 * For full license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Spryker\Client\RabbitMq\Model\Connection\QueueConnectionTransferMapper;

use Spryker\Client\RabbitMq\Dependency\Client\RabbitMqToStoreClientInterface;
use Spryker\Client\RabbitMq\Model\Exception\ConnectionConfigIsNotDefinedException;
use Spryker\Client\RabbitMq\RabbitMqConfig;

class QueueConnectionTransferMapper implements QueueConnectionTransferMapperInterface
{
    protected const CONNECTION_CONFIG_IS_NOT_DEFINED_EXCEPTION_MESSAGE = 'Couldn\'t find configuration "%s" to create connection. Please, check `RabbitMqEnv::RABBITMQ_CONNECTIONS` in `config_*.php` files, or your `stores.php`';

    /**
     * @var \Spryker\Client\RabbitMq\RabbitMqConfig
     */
    protected $config;

    /**
     * @var \Generated\Shared\Transfer\QueueConnectionTransfer[]|null
     */
    protected $queueConnectionTransfersByConnectionName;

    /**
     * @var \Generated\Shared\Transfer\QueueConnectionTransfer[][]|null
     */
    protected $queueConnectionTransfersByStoreName;

    /**
     * @var \Generated\Shared\Transfer\QueueConnectionTransfer[][]|null
     */
    protected $queueConnectionTransfersByPoolName;

    /**
     * @var \Spryker\Client\RabbitMq\Dependency\Client\RabbitMqToStoreClientInterface
     */
    protected $storeClient;

    /**
     * @param \Spryker\Client\RabbitMq\RabbitMqConfig $config
     * @param \Spryker\Client\RabbitMq\Dependency\Client\RabbitMqToStoreClientInterface $storeClient
     */
    public function __construct(RabbitMqConfig $config, RabbitMqToStoreClientInterface $storeClient)
    {
        $this->config = $config;
        $this->storeClient = $storeClient;
    }

    /**
     * @return \Generated\Shared\Transfer\QueueConnectionTransfer[][]
     */
    public function mapQueueConnectionTransfersByStoreName(): array
    {
        if ($this->queueConnectionTransfersByStoreName) {
            return $this->queueConnectionTransfersByStoreName;
        }

        $queueConnectionTransfersByStoreName = [];
        foreach ($this->getQueueConnectionTransfersByConnectionName() as $queueConnectionTransfer) {
            foreach ($queueConnectionTransfer->getStoreNames() as $storeName) {
                $queueConnectionTransfersByStoreName[$storeName][] = $queueConnectionTransfer;
            }
        }

        $this->queueConnectionTransfersByStoreName = $queueConnectionTransfersByStoreName;

        return $this->queueConnectionTransfersByStoreName;
    }

    /**
     * @return \Generated\Shared\Transfer\QueueConnectionTransfer[]
     */
    protected function getQueueConnectionTransfersByConnectionName(): array
    {
        if ($this->queueConnectionTransfersByConnectionName === null) {
            $this->addQueueConnectionTransfersByConnectionName();
        }

        return $this->queueConnectionTransfersByConnectionName;
    }

    /**
     * @return void
     */
    protected function addQueueConnectionTransfersByConnectionName(): void
    {
        $queueConnectionTransfers = $this->config->getQueueConnections(
            $this->config->isQueueStorePrefixEnabled() ? $this->storeClient->getStores() : null
        );

        foreach ($queueConnectionTransfers as $queueConnectionTransfer) {
            $this->queueConnectionTransfersByConnectionName[$queueConnectionTransfer->getName()] = $queueConnectionTransfer;
        }
    }

    /**
     * @param string[][] $queuePools
     *
     * @return \Generated\Shared\Transfer\QueueConnectionTransfer[][]
     */
    public function mapQueueConnectionTransfersByPoolName(array $queuePools): array
    {
        if ($this->queueConnectionTransfersByPoolName) {
            return $this->queueConnectionTransfersByPoolName;
        }

        $queueConnectionTransfersByBoolName = [];
        foreach ($queuePools as $queuePoolName => $connectionNames) {
            $queueConnectionTransfersByBoolName[$queuePoolName] = $this->getQueueConnectionTransferByName($connectionNames);
        }

        $this->queueConnectionTransfersByPoolName = $queueConnectionTransfersByBoolName;

        return $this->queueConnectionTransfersByPoolName;
    }

    /**
     * @param string[] $connectionNames
     *
     * @throws \Spryker\Client\RabbitMq\Model\Exception\ConnectionConfigIsNotDefinedException
     *
     * @return \Generated\Shared\Transfer\QueueConnectionTransfer[]
     */
    protected function getQueueConnectionTransferByName(array $connectionNames): array
    {
        $queueConnectionTransfers = [];
        foreach ($connectionNames as $connectionName) {
            $queueConnectionTransfersByConnectionName = $this->getQueueConnectionTransfersByConnectionName();

            if (!isset($queueConnectionTransfersByConnectionName[$connectionName])) {
                throw new ConnectionConfigIsNotDefinedException(
                    sprintf(static::CONNECTION_CONFIG_IS_NOT_DEFINED_EXCEPTION_MESSAGE, $connectionName)
                );
            }

            $queueConnectionTransfers[$connectionName] = $queueConnectionTransfersByConnectionName[$connectionName];
        }

        return $queueConnectionTransfers;
    }
}
