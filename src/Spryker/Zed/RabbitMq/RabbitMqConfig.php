<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\RabbitMq;

use Spryker\Shared\RabbitMq\RabbitMqConstants;
use Spryker\Zed\Kernel\AbstractBundleConfig;
use Spryker\Zed\RabbitMq\Business\Model\Exchange\ExchangeInfo;

class RabbitMqConfig extends AbstractBundleConfig
{
    /**
     * @return string
     */
    public function getApiExchangesUrl()
    {
        return sprintf(
            'http://%s:%s/api/exchanges/%s',
            $this->getApiHost(),
            $this->getApiPort(),
            urlencode($this->getVirtualHost())
        );
    }

    /**
     * @return string
     */
    public function getApiQueuesUrl()
    {
        return sprintf(
            'http://%s:%s/api/queues/%s',
            $this->getApiHost(),
            $this->getApiPort(),
            urlencode($this->getVirtualHost())
        );
    }

    /**
     * @return string
     */
    public function getApiUsername()
    {
        return $this->get(RabbitMqConstants::RABBITMQ_API_USERNAME);
    }

    /**
     * @return string
     */
    public function getApiPassword()
    {
        return $this->get(RabbitMqConstants::RABBITMQ_API_PASSWORD);
    }

    /**
     * @return array
     */
    public function getExchangeNameBlacklist()
    {
        return ['/^amq./', ExchangeInfo::AMQP_DEFAULT_EXCHANGE_NAME];
    }

    /**
     * @return string
     */
    public function getVirtualHost()
    {
        return $this->get(RabbitMqConstants::RABBITMQ_VIRTUAL_HOST);
    }

    /**
     * @return string
     */
    protected function getApiHost()
    {
        return $this->get(RabbitMqConstants::RABBITMQ_API_HOST);
    }

    /**
     * @return int
     */
    protected function getApiPort()
    {
        return $this->get(RabbitMqConstants::RABBITMQ_API_PORT);
    }

    /**
     * @return string
     */
    public function getApiUserPermissionsUrl()
    {
        return sprintf(
            'http://%s:%s/api/permissions/%s/admin',
            $this->getApiHost(),
            $this->getApiPort(),
            urlencode($this->getVirtualHost())
        );
    }
}
