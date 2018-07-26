<?php

/**
 * MIT License
 * For full license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Spryker\Zed\RabbitMq;

use Spryker\Shared\RabbitMq\RabbitMqEnv;
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
            urlencode($this->getApiVirtualHost())
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
            urlencode($this->getApiVirtualHost())
        );
    }

    /**
     * @return string
     */
    public function getApiUserPermissionsUrl()
    {
        return sprintf(
            'http://%s:%s/api/permissions/%s/%s',
            $this->getApiHost(),
            $this->getApiPort(),
            urlencode($this->getApiVirtualHost()),
            $this->getApiUsername()
        );
    }

    /**
     * @return string
     */
    public function getApiUsername()
    {
        return $this->get(RabbitMqEnv::RABBITMQ_API_USERNAME);
    }

    /**
     * @return string
     */
    public function getApiPassword()
    {
        return $this->get(RabbitMqEnv::RABBITMQ_API_PASSWORD);
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
    public function getApiVirtualHost()
    {
        return $this->get(RabbitMqEnv::RABBITMQ_API_VIRTUAL_HOST);
    }

    /**
     * @return string
     */
    protected function getApiHost()
    {
        return $this->get(RabbitMqEnv::RABBITMQ_API_HOST);
    }

    /**
     * @return int
     */
    protected function getApiPort()
    {
        return $this->get(RabbitMqEnv::RABBITMQ_API_PORT);
    }
}
