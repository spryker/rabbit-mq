<?php

/**
 * MIT License
 * For full license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Spryker\Client\RabbitMq\Model\Connection\QueueConnectionTransferMapper;

use Spryker\Client\RabbitMq\Model\Exception\ConnectionConfigIsNotDefinedException;
use Spryker\Client\RabbitMq\RabbitMqConfig;

class QueueConnectionTransferMapper implements QueueConnectionTransferMapperInterface
{
    /**
     * @var string
     */
    protected const CONNECTION_CONFIG_IS_NOT_DEFINED_EXCEPTION_MESSAGE = 'Couldn\'t find configuration "%s" to create connection. Please, check `RabbitMqEnv::RABBITMQ_CONNECTIONS` in `config_*.php` files, or your `stores.php`';

    /**
     * @var \Spryker\Client\RabbitMq\RabbitMqConfig
     */
    protected $config;

    /**
     * @var array<\Generated\Shared\Transfer\QueueConnectionTransfer>|null
     */
    protected $queueConnectionTransfersByConnectionName;

    /**
     * @var array<array<\Generated\Shared\Transfer\QueueConnectionTransfer>>|null
     */
    protected $queueConnectionTransfersByStoreName;

    /**
     * @var array<array<\Generated\Shared\Transfer\QueueConnectionTransfer>>|null
     */
    protected $queueConnectionTransfersByPoolName;

    /**
     * @param \Spryker\Client\RabbitMq\RabbitMqConfig $config
     */
    public function __construct(RabbitMqConfig $config)
    {
        $this->config = $config;
    }

    /**
     * @return array<array<\Generated\Shared\Transfer\QueueConnectionTransfer>>
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
     * @return array<\Generated\Shared\Transfer\QueueConnectionTransfer>
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
        foreach ($this->config->getQueueConnections() as $queueConnectionTransfer) {
            $this->queueConnectionTransfersByConnectionName[$queueConnectionTransfer->getName()] = $queueConnectionTransfer;
        }
    }

    /**
     * @param array<array<string>> $queuePools
     *
     * @return array<array<\Generated\Shared\Transfer\QueueConnectionTransfer>>
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
     * @param array<string> $connectionNames
     *
     * @throws \Spryker\Client\RabbitMq\Model\Exception\ConnectionConfigIsNotDefinedException
     *
     * @return array<\Generated\Shared\Transfer\QueueConnectionTransfer>
     */
    protected function getQueueConnectionTransferByName(array $connectionNames): array
    {
        $queueConnectionTransfers = [];
        foreach ($connectionNames as $connectionName) {
            $queueConnectionTransfersByConnectionName = $this->getQueueConnectionTransfersByConnectionName();

            if (!isset($queueConnectionTransfersByConnectionName[$connectionName])) {
                throw new ConnectionConfigIsNotDefinedException(
                    sprintf(static::CONNECTION_CONFIG_IS_NOT_DEFINED_EXCEPTION_MESSAGE, $connectionName),
                );
            }

            $queueConnectionTransfers[$connectionName] = $queueConnectionTransfersByConnectionName[$connectionName];
        }

        return $queueConnectionTransfers;
    }
}
