<?php

/**
 * MIT License
 * For full license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Spryker\Client\RabbitMq\Model\Connection;

use Generated\Shared\Transfer\QueueConnectionTransfer;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use Spryker\Client\Kernel\AbstractFactory;
use Spryker\Client\RabbitMq\Model\Helper\QueueEstablishmentHelper;

/**
 * @method \Spryker\Client\RabbitMq\RabbitMqConfig getConfig()
 */
class ConnectionFactory extends AbstractFactory implements ConnectionFactoryInterface
{
    /**
     * @param \Generated\Shared\Transfer\QueueConnectionTransfer $queueConnectionConfig
     *
     * @return \Spryker\Client\RabbitMq\Model\Connection\ConnectionInterface
     */
    public function createConnection(QueueConnectionTransfer $queueConnectionConfig)
    {
        return new Connection(
            $this->createAmqpStreamConnection($queueConnectionConfig),
            $this->createQueueEstablishmentHelper(),
            $queueConnectionConfig
        );
    }

    /**
     * @return \Generated\Shared\Transfer\QueueConnectionTransfer[]
     */
    public function getQueueConnectionConfigs()
    {
        return $this->getConfig()->getQueueConnections();
    }

    /**
     * @param \Generated\Shared\Transfer\QueueConnectionTransfer $queueConnectionTransfer
     *
     * @return \PhpAmqpLib\Connection\AMQPStreamConnection
     */
    protected function createAmqpStreamConnection(QueueConnectionTransfer $queueConnectionTransfer)
    {
        $streamConnection = new AMQPStreamConnection(
            $queueConnectionTransfer->getHost(),
            $queueConnectionTransfer->getPort(),
            $queueConnectionTransfer->getUsername(),
            $queueConnectionTransfer->getPassword(),
            $queueConnectionTransfer->getVirtualHost(),
            $queueConnectionTransfer->getInsist() ?? false,
            $queueConnectionTransfer->getLoginMethod() ?? 'AMQPLAIN',
            $queueConnectionTransfer->getLoginResponse(),
            $queueConnectionTransfer->getLocale() ?? 'en_US',
            $queueConnectionTransfer->getConnectionTimeout() ?? 3,
            $queueConnectionTransfer->getReadWriteTimeout() ?? 130,
            null,
            $queueConnectionTransfer->getKeepAlive() ?? false,
            $queueConnectionTransfer->getHeartBeat() ?? 0,
            $queueConnectionTransfer->getChannelRpcTimeout() ?? 0,
            $queueConnectionTransfer->getSslProtocol()
        );

        return $streamConnection;
    }

    /**
     * @return \Spryker\Client\RabbitMq\Model\Helper\QueueEstablishmentHelperInterface
     */
    protected function createQueueEstablishmentHelper()
    {
        return new QueueEstablishmentHelper();
    }
}
