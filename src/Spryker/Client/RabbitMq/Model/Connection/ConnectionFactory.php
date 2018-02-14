<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
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
            $this->createAMQPStreamConnection($queueConnectionConfig),
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
     * @param \Generated\Shared\Transfer\QueueConnectionTransfer $queueConnectionConfig
     *
     * @return \PhpAmqpLib\Connection\AMQPStreamConnection
     */
    protected function createAMQPStreamConnection(QueueConnectionTransfer $queueConnectionConfig)
    {
        $streamConnection = new AMQPStreamConnection(
            $queueConnectionConfig->getHost(),
            $queueConnectionConfig->getPort(),
            $queueConnectionConfig->getUsername(),
            $queueConnectionConfig->getPassword(),
            $queueConnectionConfig->getVirtualHost()
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
