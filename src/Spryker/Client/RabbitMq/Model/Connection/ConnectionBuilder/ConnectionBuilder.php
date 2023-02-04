<?php

/**
 * MIT License
 * For full license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Spryker\Client\RabbitMq\Model\Connection\ConnectionBuilder;

use Generated\Shared\Transfer\QueueConnectionTransfer;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use Spryker\Client\RabbitMq\Dependency\Client\RabbitMqToStoreClientInterface;
use Spryker\Client\RabbitMq\Model\Connection\Connection;
use Spryker\Client\RabbitMq\Model\Connection\ConnectionInterface;
use Spryker\Client\RabbitMq\Model\Helper\QueueEstablishmentHelperInterface;
use Spryker\Client\RabbitMq\RabbitMqConfig;

class ConnectionBuilder implements ConnectionBuilderInterface
{
    /**
     * @var \Spryker\Client\RabbitMq\RabbitMqConfig
     */
    protected $config;

    /**
     * @var \Spryker\Client\RabbitMq\Dependency\Client\RabbitMqToStoreClientInterface
     */
    protected $storeClient;

    /**
     * @var \Spryker\Client\RabbitMq\Model\Helper\QueueEstablishmentHelperInterface
     */
    protected $queueEstablishmentHelper;

    /**
     * @var array<\Spryker\Client\RabbitMq\Model\Connection\ConnectionInterface>
     */
    protected $createdConnectionsByConnectionName;

    /**
     * @param \Spryker\Client\RabbitMq\RabbitMqConfig $config
     * @param \Spryker\Client\RabbitMq\Dependency\Client\RabbitMqToStoreClientInterface $storeClient
     * @param \Spryker\Client\RabbitMq\Model\Helper\QueueEstablishmentHelperInterface $queueEstablishmentHelper
     */
    public function __construct(
        RabbitMqConfig $config,
        RabbitMqToStoreClientInterface $storeClient,
        QueueEstablishmentHelperInterface $queueEstablishmentHelper
    ) {
        $this->config = $config;
        $this->storeClient = $storeClient;
        $this->queueEstablishmentHelper = $queueEstablishmentHelper;
    }

    /**
     * @param \Generated\Shared\Transfer\QueueConnectionTransfer $queueConnectionTransfer
     *
     * @return \Spryker\Client\RabbitMq\Model\Connection\ConnectionInterface
     */
    public function createConnectionByQueueConnectionTransfer(QueueConnectionTransfer $queueConnectionTransfer): ConnectionInterface
    {
        return $this->createOrGetConnection($queueConnectionTransfer);
    }

    /**
     * @param \Generated\Shared\Transfer\QueueConnectionTransfer $queueConnectionTransfer
     *
     * @return \Spryker\Client\RabbitMq\Model\Connection\ConnectionInterface
     */
    protected function createOrGetConnection(QueueConnectionTransfer $queueConnectionTransfer): ConnectionInterface
    {
        if (isset($this->createdConnectionsByConnectionName[$queueConnectionTransfer->getName()])) {
            return $this->createdConnectionsByConnectionName[$queueConnectionTransfer->getName()];
        }

        $connection = $this->createConnection($queueConnectionTransfer);
        $this->createdConnectionsByConnectionName[$queueConnectionTransfer->getName()] = $connection;

        return $connection;
    }

    /**
     * @param \Generated\Shared\Transfer\QueueConnectionTransfer $queueConnectionTransfer
     *
     * @return \Spryker\Client\RabbitMq\Model\Connection\ConnectionInterface
     */
    protected function createConnection(QueueConnectionTransfer $queueConnectionTransfer): ConnectionInterface
    {
        return new Connection(
            $this->createAmqpStreamConnection($queueConnectionTransfer),
            $this->queueEstablishmentHelper,
            $queueConnectionTransfer,
            $this->config,
        );
    }

    /**
     * @param \Generated\Shared\Transfer\QueueConnectionTransfer $queueConnectionTransfer
     *
     * @return \PhpAmqpLib\Connection\AMQPStreamConnection
     */
    protected function createAmqpStreamConnection(QueueConnectionTransfer $queueConnectionTransfer): AMQPStreamConnection
    {
        $defaultQueueConnectionTransfer = $this->config->getDefaultQueueConnectionConfig();

        return new AMQPStreamConnection(
            $queueConnectionTransfer->getHost(),
            $queueConnectionTransfer->getPort(),
            $queueConnectionTransfer->getUsername(),
            $queueConnectionTransfer->getPassword(),
            $queueConnectionTransfer->getVirtualHost(),
            $queueConnectionTransfer->getInsist() ?? $defaultQueueConnectionTransfer->getInsist(),
            $queueConnectionTransfer->getLoginMethod() ?? $defaultQueueConnectionTransfer->getLoginMethod(),
            $queueConnectionTransfer->getLoginResponse(),
            $queueConnectionTransfer->getLocale() ?? $this->getDefaultLocale(),
            $queueConnectionTransfer->getConnectionTimeout() ?? $defaultQueueConnectionTransfer->getConnectionTimeout(),
            $queueConnectionTransfer->getReadWriteTimeout() ?? $defaultQueueConnectionTransfer->getReadWriteTimeout(),
            $this->createStreamContext($queueConnectionTransfer),
            $queueConnectionTransfer->getKeepAlive() ?? $defaultQueueConnectionTransfer->getKeepAlive(),
            $queueConnectionTransfer->getHeartBeat() ?? $defaultQueueConnectionTransfer->getHeartBeat(),
            $queueConnectionTransfer->getChannelRpcTimeout() ?? $defaultQueueConnectionTransfer->getChannelRpcTimeout(),
            $queueConnectionTransfer->getSslProtocol(),
        );
    }

    /**
     * @param \Generated\Shared\Transfer\QueueConnectionTransfer $queueConnectionTransfer
     *
     * @return resource|null
     */
    protected function createStreamContext(QueueConnectionTransfer $queueConnectionTransfer)
    {
        $streamContextOptions = $queueConnectionTransfer->getStreamContextOptions();

        if (!$streamContextOptions) {
            return null;
        }

        $sslContext = stream_context_create();
        foreach ($streamContextOptions as $wrapper => $options) {
            if (!is_array($options)) {
                continue;
            }

            foreach ($options as $key => $value) {
                stream_context_set_option($sslContext, $wrapper, $key, $value);
            }
        }

        if (!stream_context_get_options($sslContext)) {
            return null;
        }

        return $sslContext;
    }

    /**
     * @return string
     */
    protected function getDefaultLocale(): string
    {
        if ($this->config->getDefaultLocaleCode() && $this->config->isDynamicStoreEnabled()) {
            return $this->config->getDefaultLocaleCode();
        }

        return $this->getAvailableLocaleIsoCode();
    }

    /**
     * @param array<\Generated\Shared\Transfer\QueueConnectionTransfer> $queueConnectionTransfers
     *
     * @return array<\Spryker\Client\RabbitMq\Model\Connection\ConnectionInterface>
     */
    public function createConnectionsByQueueConnectionTransfers(array $queueConnectionTransfers): array
    {
        $connections = [];

        foreach ($queueConnectionTransfers as $queueConnectionTransfer) {
            $connection = $this->createOrGetConnection($queueConnectionTransfer);
            $uniqueChannelId = $this->getUniqueChannelId($connection);
            if (!isset($connections[$uniqueChannelId])) {
                $connections[$uniqueChannelId] = $connection;
            }
        }

        return $connections;
    }

    /**
     * @param \Spryker\Client\RabbitMq\Model\Connection\ConnectionInterface $connection
     *
     * @return string
     */
    protected function getUniqueChannelId(ConnectionInterface $connection): string
    {
        return sprintf('%s-%s', $connection->getVirtualHost(), $connection->getChannel()->getChannelId());
    }

    /**
     * @deprecated Will be removed after dynamic multi-store is always enabled.
     *
     * @return string
     */
    protected function getAvailableLocaleIsoCode(): string
    {
        return current($this->storeClient->getCurrentStore()->getAvailableLocaleIsoCodes());
    }
}
