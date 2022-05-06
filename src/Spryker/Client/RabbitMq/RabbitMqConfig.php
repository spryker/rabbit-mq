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
use Spryker\Client\RabbitMq\Model\Connection\Connection;
use Spryker\Shared\RabbitMq\RabbitMqEnv;

class RabbitMqConfig extends AbstractBundleConfig
{
    /**
     * @var bool
     */
    protected const AMQP_STREAM_CONNECTION_INSIST = false;

    /**
     * @var string
     */
    protected const AMQP_STREAM_CONNECTION_LOGIN_METHOD = 'AMQPLAIN';

    /**
     * @var int
     */
    protected const AMQP_STREAM_CONNECTION_CONNECTION_TIMEOUT = 3;

    /**
     * @var int
     */
    protected const AMQP_STREAM_CONNECTION_READ_WRITE_TIMEOUT = 130;

    /**
     * @var bool
     */
    protected const AMQP_STREAM_CONNECTION_KEEP_ALIVE = false;

    /**
     * @var int
     */
    protected const AMQP_STREAM_CONNECTION_HEART_BEAT = 0;

    /**
     * @var int
     */
    protected const AMQP_STREAM_CONNECTION_CHANNEL_RPC_TIMEOUT = 0;

    /**
     * @var \ArrayObject<int|string, \Generated\Shared\Transfer\RabbitMqOptionTransfer>|null
     */
    protected $queueOptionCollection;

    /**
     * @api
     *
     * @return array<\Generated\Shared\Transfer\QueueConnectionTransfer>
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
     * @api
     *
     * @return array
     */
    public function getMessageConfig(): array
    {
        return [
            'delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT,
        ];
    }

    /**
     * @api
     *
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
     * Specification:
     * - Allow creation queues and exchanges in runtime.
     *
     * @api
     *
     * @return bool
     */
    public function isRuntimeSettingUpEnabled(): bool
    {
        return (bool)$this->get(RabbitMqEnv::RABBITMQ_ENABLE_RUNTIME_SETTING_UP, true);
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

            $streamContextOptions = isset($connection[RabbitMqEnv::RABBITMQ_STREAM_CONTEXT_OPTIONS]) ?
                (array)$connection[RabbitMqEnv::RABBITMQ_STREAM_CONTEXT_OPTIONS] :
                null;

            $connections[] = [
                'name' => $connection[RabbitMqEnv::RABBITMQ_CONNECTION_NAME],
                'host' => $connection[RabbitMqEnv::RABBITMQ_HOST],
                'port' => $connection[RabbitMqEnv::RABBITMQ_PORT],
                'username' => $connection[RabbitMqEnv::RABBITMQ_USERNAME],
                'password' => $connection[RabbitMqEnv::RABBITMQ_PASSWORD],
                'virtualHost' => $connection[RabbitMqEnv::RABBITMQ_VIRTUAL_HOST],
                'storeNames' => $connection[RabbitMqEnv::RABBITMQ_STORE_NAMES],
                'isDefaultConnection' => $isDefaultConnection,
                'streamContextOptions' => $streamContextOptions,
            ];
        }

        return $connections;
    }

    /**
     * @return \ArrayObject<int|string, \Generated\Shared\Transfer\RabbitMqOptionTransfer>
     */
    protected function getQueueOptions()
    {
        if ($this->queueOptionCollection !== null) {
            return $this->queueOptionCollection;
        }

        $queueConfigurations = $this->getQueueConfiguration();
        $this->queueOptionCollection = new ArrayObject();

        foreach ($queueConfigurations as $queueNameKey => $queueConfiguration) {
            if (!is_array($queueConfiguration)) {
                $defaultBoundQueueNamePrefix = $this->getDefaultBoundQueueNamePrefix();
                $boundQueueName = $defaultBoundQueueNamePrefix === '' ? $queueConfiguration : sprintf('%s.%s', $queueConfiguration, $defaultBoundQueueNamePrefix);

                $this->queueOptionCollection->append(
                    $this->createExchangeOptionTransfer($queueConfiguration, $boundQueueName, $defaultBoundQueueNamePrefix),
                );

                continue;
            }

            foreach ($queueConfiguration as $routingKey => $queueName) {
                $this->queueOptionCollection->append(
                    $this->createExchangeOptionTransfer($queueNameKey, $queueName, $routingKey),
                );
            }
        }

        return $this->queueOptionCollection;
    }

    /**
     * @return array
     */
    protected function getQueueConfiguration(): array
    {
        return [];
    }

    /**
     * @return string
     */
    protected function getDefaultBoundQueueNamePrefix(): string
    {
        return '';
    }

    /**
     * @param string $queueName
     * @param string $boundQueueName
     * @param string $routingKey
     *
     * @return \Generated\Shared\Transfer\RabbitMqOptionTransfer
     */
    protected function createExchangeOptionTransfer($queueName, $boundQueueName, $routingKey = '')
    {
        $queueOptionTransfer = new RabbitMqOptionTransfer();
        $queueOptionTransfer
            ->setQueueName($queueName)
            ->setDurable(true)
            ->setType('direct')
            ->setDeclarationType(Connection::RABBIT_MQ_EXCHANGE)
            ->addBindingQueueItem($this->createQueueOptionTransfer($queueName))
            ->addBindingQueueItem($this->createQueueOptionTransfer($boundQueueName, $routingKey));

        return $queueOptionTransfer;
    }

    /**
     * @param string $queueName
     * @param string $routingKey
     *
     * @return \Generated\Shared\Transfer\RabbitMqOptionTransfer
     */
    protected function createQueueOptionTransfer($queueName, $routingKey = '')
    {
        $queueOptionTransfer = new RabbitMqOptionTransfer();
        $queueOptionTransfer
            ->setQueueName($queueName)
            ->setDurable(true)
            ->setNoWait(false)
            ->addRoutingKey($routingKey);

        return $queueOptionTransfer;
    }

    /**
     * @api
     *
     * @return string|null
     */
    public function getDefaultLocaleCode(): ?string
    {
        return null;
    }

    /**
     * @api
     *
     * @return string[][]
     */
    public function getQueuePools(): array
    {
        return [];
    }
}
