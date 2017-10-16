<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\RabbitMq;

use Spryker\Shared\RabbitMq\RabbitMqConstants;
use Spryker\Zed\Kernel\AbstractBundleConfig;

class RabbitMqConfig extends AbstractBundleConfig
{
    /**
     * @return string
     */
    public function getHost()
    {
        return $this->get(RabbitMqConstants::RABBITMQ_HOST);
    }

    /**
     * @return string
     */
    public function getWebPort()
    {
        return 15672;
        return $this->get(RabbitMqConstants::RABBITMQ_PORT);
    }

    /**
     * @return string
     */
    public function getUsername()
    {
        return 'admin';
        return $this->get(RabbitMqConstants::RABBITMQ_USERNAME);
    }

    /**
     * @return string
     */
    public function getPassword()
    {
        return $this->get(RabbitMqConstants::RABBITMQ_PASSWORD);
    }

    /**
     * @return array
     */
    public function getExchangeBlacklist()
    {
        return ['/^amq./'];
    }
}
