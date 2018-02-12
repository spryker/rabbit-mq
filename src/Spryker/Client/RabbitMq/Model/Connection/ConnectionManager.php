<?php
/**
 * Created by PhpStorm.
 * User: karolygerner
 * Date: 08.February.2018
 * Time: 12:42
 */

namespace Spryker\Client\RabbitMq\Model\Connection;


use Generated\Shared\Transfer\StoreTransfer;
use Spryker\Shared\Event\EventConstants;
use Spryker\Shared\RabbitMq\RabbitMqConfigInterface;

class ConnectionManager
{
    /**
     * @var ConnectionInterface[]
     */
    protected $connectionMap = [];

    /**
     * @var string|null
     */
    protected $defaultConnectionName;

    /**
     * @var array|null
     */
    protected $channelMapBuffer = null;

    /**
     * @var StoreTransfer
     */
    protected $currentStoreTransfer;

    /**
     * @var \Spryker\Client\RabbitMq\RabbitMqFactory
     */
    protected $factory;

    /**
     * @param StoreTransfer $currentStoreTransfer
     * @param \Spryker\Client\RabbitMq\RabbitMqFactory $factory
     */
    public function __construct(StoreTransfer $currentStoreTransfer, \Spryker\Client\RabbitMq\RabbitMqFactory $factory)
    {
        $this->currentStoreTransfer = $currentStoreTransfer;
        $this->factory = $factory;
    }

    /**
     * @return array
     */
    protected function getChannelMap()
    {
        if ($this->channelMapBuffer === null) {
            $channelMap = [
                RabbitMqConfigInterface::QUEUE_POOL_NAME_DEFAULT => [$this->getDefaultChannel()],
            ];

            $eventQueueMap = $this->currentStoreTransfer->getQueuePools();
            foreach ($eventQueueMap as $poolName => $connectionNames) {
                $channelMap[$poolName] = [];
                foreach ($connectionNames as $connectionName) {
                    $channelMap[$poolName][] = $this->getConnectionMap()[$connectionName]->getChannel();
                }
            }

            $this->channelMapBuffer = $channelMap;
        }

        return $this->channelMapBuffer;
    }

    /**
     * @param ConnectionInterface $connection
     */
    protected function addConnection(ConnectionInterface $connection)
    {
        $this->connectionMap[$connection->getName()] = $connection;
        if ($connection->getIsDefaultConnection()) {
            $this->defaultConnectionName = $connection->getName();
        }
    }

    /**
     * @return ConnectionInterface[]
     */
    protected function getConnectionMap()
    {
        if ($this->connectionMap === null) {
            foreach ($this->factory->getQueueConnectionConfigs() as $queueConnectionConfig) {
                $this->addConnection($this->factory->createConnection($queueConnectionConfig));
            }
        }

        return $this->connectionMap;
    }

    /**
     * @param string $poolName
     *
     * @return \PhpAmqpLib\Channel\AMQPChannel[]
     */
    public function getChannelsByQueuePoolName($poolName)
    {
        return $this->getChannelMap()[$poolName];
    }

    /**
     * @return \PhpAmqpLib\Channel\AMQPChannel
     */
    public function getDefaultChannel()
    {
        return $this->getConnectionMap()[$this->defaultConnectionName]->getChannel();
    }
}
