<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Client\RabbitMq;

use ArrayObject;
use Generated\Shared\Transfer\QueueConnectionTransfer;
use Generated\Shared\Transfer\RabbitMqOptionTransfer;
use Spryker\Client\Kernel\AbstractBundleConfig;
use Spryker\Shared\RabbitMq\RabbitMqConstants;

class RabbitMqConfig extends AbstractBundleConfig
{
    /**
     * @return \Generated\Shared\Transfer\QueueConnectionTransfer[]
     */
    public function getQueueConnections()
    {
        $queueConnectionConfigs = $this->getQueueConnectionConfigs();

        $connectionTransferCollection = [];
        foreach ($queueConnectionConfigs as $queueConnectionConfig) {
            $connectionTransfer = new QueueConnectionTransfer();
            $connectionTransfer->setName($queueConnectionConfig['name']);
            $connectionTransfer->setHost($queueConnectionConfig['host']);
            $connectionTransfer->setPort($queueConnectionConfig['port']);
            $connectionTransfer->setUsername($queueConnectionConfig['username']);
            $connectionTransfer->setPassword($queueConnectionConfig['password']);
            $connectionTransfer->setVirtualHost($queueConnectionConfig['virtualHost']);

            $connectionTransfer->setQueueOptionCollection($this->getQueueOptions());

            $connectionTransferCollection[] = $connectionTransfer;
        }

        return $connectionTransferCollection;
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
    protected function getQueueConnectionConfigs()
    {
        $connections = [];

        foreach ($this->get(RabbitMqConstants::RABBITMQ_CONNECTIONS) as $connection) {
            $connections[] = [
                'name' => $connection[RabbitMqConstants::RABBITMQ_CONNECTION_NAME],
                'host' => $connection[RabbitMqConstants::RABBITMQ_HOST],
                'port' => $connection[RabbitMqConstants::RABBITMQ_PORT],
                'username' => $connection[RabbitMqConstants::RABBITMQ_USERNAME],
                'password' => $connection[RabbitMqConstants::RABBITMQ_PASSWORD],
                'virtualHost' => $connection[RabbitMqConstants::RABBITMQ_VIRTUAL_HOST],
            ];
        }

        return $connections;
    }
}
