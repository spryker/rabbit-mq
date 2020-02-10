<?php

/**
 * MIT License
 * For full license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Spryker\Client\RabbitMq;

use ArrayObject;
use Generated\Shared\Transfer\QueueConnectionTransfer;
use Generated\Shared\Transfer\RabbitMqOptionTransfer;
use PhpAmqpLib\Message\AMQPMessage;
use Spryker\Client\Kernel\AbstractBundleConfig;
use Spryker\Shared\Kernel\Store;
use Spryker\Shared\RabbitMq\RabbitMqEnv;

class RabbitMqConfig extends AbstractBundleConfig
{
    protected const AMQP_STREAM_CONNECTION_INSIST = false;
    protected const AMQP_STREAM_CONNECTION_LOGIN_METHOD = 'AMQPLAIN';
    protected const AMQP_STREAM_CONNECTION_CONNECTION_TIMEOUT = 3;
    protected const AMQP_STREAM_CONNECTION_READ_WRITE_TIMEOUT = 130;
    protected const AMQP_STREAM_CONNECTION_KEEP_ALIVE = false;
    protected const AMQP_STREAM_CONNECTION_HEART_BEAT = 0;
    protected const AMQP_STREAM_CONNECTION_CHANNEL_RPC_TIMEOUT = 0;

    /**
     * @return \Generated\Shared\Transfer\QueueConnectionTransfer[]
     */
    public function getQueueConnections(): array
    {
        $queueConnectionConfigs = $this->getQueueConnectionConfigs();

        $connectionTransferCollection = [];
        foreach ($queueConnectionConfigs as $queueConnectionConfig) {
            $connectionTransfer = (new QueueConnectionTransfer())
                ->fromArray($queueConnectionConfig, true)
                ->setQueueOptionCollection($this->getQueueOptions());

            $connectionTransferCollection[] = $connectionTransfer;
        }

        return $connectionTransferCollection;
    }

    /**
     * @return array
     */
    protected function getQueueConnectionConfigs(): array
    {
        $connections = [];

        foreach ($this->get(RabbitMqEnv::RABBITMQ_CONNECTIONS) as $connection) {
            $isDefaultConnection = isset($connection[RabbitMqEnv::RABBITMQ_DEFAULT_CONNECTION]) ?
                (bool)$connection[RabbitMqEnv::RABBITMQ_DEFAULT_CONNECTION] :
                false;

            $connections[] = [
                'name' => $connection[RabbitMqEnv::RABBITMQ_CONNECTION_NAME],
                'host' => $connection[RabbitMqEnv::RABBITMQ_HOST],
                'port' => $connection[RabbitMqEnv::RABBITMQ_PORT],
                'username' => $connection[RabbitMqEnv::RABBITMQ_USERNAME],
                'password' => $connection[RabbitMqEnv::RABBITMQ_PASSWORD],
                'virtualHost' => $connection[RabbitMqEnv::RABBITMQ_VIRTUAL_HOST],
                'storeNames' => $connection[RabbitMqEnv::RABBITMQ_STORE_NAMES],
                'isDefaultConnection' => $isDefaultConnection,
            ];
        }

        return $connections;
    }

    /**
     * @return \ArrayObject
     */
    protected function getQueueOptions()
    {
        $queueOptionCollection = new ArrayObject();
        $queueOptionCollection->append(new RabbitMqOptionTransfer());

        return $queueOptionCollection;
    }

    /**
     * @return array
     */
    public function getMessageConfig(): array
    {
        return [
            'delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT,
        ];
    }

    /**
     * @return \Generated\Shared\Transfer\QueueConnectionTransfer
     */
    public function getDefaultQueueConnectionConfig(): QueueConnectionTransfer
    {
        return (new QueueConnectionTransfer())
            ->setInsist(static::AMQP_STREAM_CONNECTION_INSIST)
            ->setLoginMethod(static::AMQP_STREAM_CONNECTION_LOGIN_METHOD)
            ->setConnectionTimeout(static::AMQP_STREAM_CONNECTION_CONNECTION_TIMEOUT)
            ->setReadWriteTimeout(static::AMQP_STREAM_CONNECTION_READ_WRITE_TIMEOUT)
            ->setKeepAlive(static::AMQP_STREAM_CONNECTION_KEEP_ALIVE)
            ->setHeartBeat(static::AMQP_STREAM_CONNECTION_HEART_BEAT)
            ->setChannelRpcTimeout(static::AMQP_STREAM_CONNECTION_CHANNEL_RPC_TIMEOUT);
    }

    /**
     * @param string $storeName
     *
     * @return array
     */
    public function getQueuePoolsForStore(string $storeName): array
    {
        $queuePoolsByStore = $this->getQueuePoolsByStore();

        if (isset($queuePoolsByStore[$storeName])) {
            return $queuePoolsByStore[$storeName];
        }

        return $this->getDefaultQueuePools();
    }

    /**
     * @return array
     */
    public function getQueuePoolsByStore(): array
    {
        return [];
    }

    /**
     * @return array
     */
    public function getDefaultQueuePools(): array
    {
        return Store::getInstance()->getQueuePools();
    }
}
