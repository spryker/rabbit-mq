<?php
/**
 * Created by PhpStorm.
 * User: karolygerner
 * Date: 08.February.2018
 * Time: 12:42
 */

namespace Spryker\Client\RabbitMq\Model\Connection;


use Generated\Shared\Transfer\StoreTransfer;
use Spryker\Zed\Store\Business\StoreFacadeInterface;

class ConnectionManager
{
    /**
     * @var ConnectionInterface[]
     */
    protected $connectionMap;

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
     * @param StoreTransfer $currentStoreTransfer
     */
    public function __construct(StoreTransfer $currentStoreTransfer)
    {
        $this->currentStoreTransfer = $currentStoreTransfer;
    }

    /**
     * @return array
     */
    protected function getChannelMap()
    {
        if ($this->channelMapBuffer === null) {
            $channelMap = [
                null => [$this->getDefaultChannel()]
            ];

            $eventQueueMap = $this->currentStoreTransfer->getQueuePools();
            foreach ($eventQueueMap as $poolName => $connectionNames) {
                $channelMap[$poolName] = [];
                foreach ($connectionNames as $connectionName) {
                    $channelMap[$poolName][] = $this->connectionMap[$connectionName]->getChannel();
                }
            }

            $this->channelMapBuffer = $channelMap;
        }

        return $this->channelMapBuffer;
    }

    /**
     * @param ConnectionInterface $connection
     */
    public function addConnection(ConnectionInterface $connection)
    {
        $this->connectionMap[$connection->getName()] = $connection;
        if ($this->defaultConnectionName === null) {
            $this->defaultConnectionName = $connection->getName();
        }
    }

    /**
     * @param string|null $poolName
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
        return $this->connectionMap[$this->defaultConnectionName]->getChannel();
    }
}
