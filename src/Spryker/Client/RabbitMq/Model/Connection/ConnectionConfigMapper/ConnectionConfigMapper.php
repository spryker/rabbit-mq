<?php

/**
 * MIT License
 * For full license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Spryker\Client\RabbitMq\Model\Connection\ConnectionConfigMapper;

use Spryker\Client\RabbitMq\Model\Connection\ConnectionFactoryInterface;
use Spryker\Client\RabbitMq\Model\Exception\ConnectionConfigIsNotDefinedException;

class ConnectionConfigMapper implements ConnectionConfigMapperInterface
{
    /**
     * @var \Spryker\Client\RabbitMq\Model\Connection\ConnectionFactoryInterface
     */
    protected $connectionFactory;

    /**
     * @var \Generated\Shared\Transfer\QueueConnectionTransfer[]|null Keys are connection names.
     */
    protected $connectionsConfigMap;

    /**
     * @var \Generated\Shared\Transfer\QueueConnectionTransfer[]|null Keys are store names, values are lists of connections.
     */
    protected $connectionsConfigMapByStoreName;

    /**
     * @var \Generated\Shared\Transfer\QueueConnectionTransfer[]|null Keys are pool names, values are lists of connections.
     */
    protected $connectionsConfigMapByPoolName;

    /**
     * @param \Spryker\Client\RabbitMq\Model\Connection\ConnectionFactoryInterface $connectionFactory
     */
    public function __construct(ConnectionFactoryInterface $connectionFactory)
    {
        $this->connectionFactory = $connectionFactory;
    }

    /**
     * @return \Generated\Shared\Transfer\QueueConnectionTransfer[][]
     */
    public function mapConnectionsConfigByStoreName(): array
    {
        if ($this->connectionsConfigMapByStoreName) {
            return $this->connectionsConfigMapByStoreName;
        }

        $connectionConfigMap = [];
        foreach ($this->getConnectionsConfigMap() as $connectionConfig) {
            foreach ($connectionConfig->getStoreNames() as $storeName) {
                $connectionConfigMap[$storeName][] = $connectionConfig;
            }
        }
        $this->connectionsConfigMapByStoreName = $connectionConfigMap;

        return $this->connectionsConfigMapByStoreName;
    }

    /**
     * @return \Generated\Shared\Transfer\QueueConnectionTransfer[]
     */
    protected function getConnectionsConfigMap(): array
    {
        if ($this->connectionsConfigMap === null) {
            $this->addConnectionsConfig();
        }

        return $this->connectionsConfigMap;
    }

    /**
     * @return void
     */
    protected function addConnectionsConfig(): void
    {
        foreach ($this->connectionFactory->getQueueConnectionConfigs() as $queueConnectionConfig) {
            $this->connectionsConfigMap[$queueConnectionConfig->getName()] = $queueConnectionConfig;
        }
    }

    /**
     * @param string[][] $queuePools Keys are pool names, values are lists of connection names.
     *
     * @return \Generated\Shared\Transfer\QueueConnectionTransfer[][]
     */
    public function mapConnectionsConfigByPoolName(array $queuePools): array
    {
        if ($this->connectionsConfigMapByPoolName) {
            return $this->connectionsConfigMapByPoolName;
        }

        $connectionConfigMap = [];
        foreach ($queuePools as $queuePoolName => $connectionNames) {
            $connectionConfigMap[$queuePoolName] = $this->getConnectionsConfigByName($connectionNames);
        }

        $this->connectionsConfigMapByPoolName = $connectionConfigMap;

        return $this->connectionsConfigMapByPoolName;
    }

    /**
     * @param string[] $connectionNames
     *
     * @throws \Spryker\Client\RabbitMq\Model\Exception\ConnectionConfigIsNotDefinedException
     *
     * @return \Generated\Shared\Transfer\QueueConnectionTransfer[]
     */
    protected function getConnectionsConfigByName(array $connectionNames): array
    {
        $connectionsConfig = [];
        foreach ($connectionNames as $connectionName) {
            $connectionsConfigMap = $this->getConnectionsConfigMap();

            if (!isset($connectionsConfigMap[$connectionName])) {
                throw new ConnectionConfigIsNotDefinedException(
                    sprintf('Couldn\'t find configuration "%s" to create connection. Please, check RabbitMqEnv::RABBITMQ_CONNECTIONS in config_*.php files, or your stores.php', $connectionName)
                );
            }

            $connectionsConfig[$connectionName] = $connectionsConfigMap[$connectionName];
        }

        return $connectionsConfig;
    }
}
