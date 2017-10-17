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
        return sprintf('http://%s:%s/api/exchanges', 'localhost', 15672);
    }

    /**
     * @return string
     */
    public function getApiQueuesUrl()
    {
        return sprintf('http://%s:%s/api/queues', 'localhost', 15672);
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
}
