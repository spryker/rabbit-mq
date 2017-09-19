<?php
/**
 * Copyright © 2017-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Client\RabbitMq;

use Generated\Shared\Transfer\QueueConnectionTransfer;
use Generated\Shared\Transfer\RabbitMqOptionTransfer;
use Spryker\Client\Kernel\AbstractBundleConfig;
use Spryker\Shared\RabbitMq\RabbitMqConstants;

class RabbitMqConfig extends AbstractBundleConfig
{

    /**
     * @return \Generated\Shared\Transfer\QueueConnectionTransfer
     */
    public function getQueueConnection()
    {
        $queueConfig = $this->getQueueConnectionConfig();

        $connectionTransfer = new QueueConnectionTransfer();
        $connectionTransfer->setHost($queueConfig['host']);
        $connectionTransfer->setPort($queueConfig['port']);
        $connectionTransfer->setUsername($queueConfig['username']);
        $connectionTransfer->setPassword($queueConfig['password']);
        $connectionTransfer->setVirtualHost($queueConfig['virtualHost']);

        $connectionTransfer->setQueueOptionCollection($this->getQueueOptions());

        return $connectionTransfer;
    }

    /**
     * @return \ArrayObject
     */
    protected function getQueueOptions()
    {
        $queueOptionCollection = new \ArrayObject();
        $queueOptionCollection->append(new RabbitMqOptionTransfer());

        return $queueOptionCollection;
    }

    /**
     * @return array
     */
    protected function getQueueConnectionConfig()
    {
        return [
            'host' => $this->get(RabbitMqConstants::RABBITMQ_HOST),
            'port' => $this->get(RabbitMqConstants::RABBITMQ_PORT),
            'username' => $this->get(RabbitMqConstants::RABBITMQ_USERNAME),
            'password' => $this->get(RabbitMqConstants::RABBITMQ_PASSWORD),
            'virtualHost' => $this->get(RabbitMqConstants::RABBITMQ_VIRTUAL_HOST),
        ];
    }
}
