<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Client\RabbitMq;

use Spryker\Client\Kernel\AbstractBundleConfig;
use Spryker\Shared\RabbitMq\RabbitMqConstants;

class RabbitMqConfig extends AbstractBundleConfig
{
    /**
     * @return array
     */
    public function getQueueConnectionConfig()
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
