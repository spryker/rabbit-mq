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
            '%s://%s:%s/api/exchanges/%s',
            $this->getApiScheme(),
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
            '%s://%s:%s/api/queues/%s',
            $this->getApiScheme(),
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
            '%s://%s:%s/api/permissions/%s/%s',
            $this->getApiScheme(),
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

     /**
      * @return string
      */
    protected function getApiScheme()
    {
        return $this->get(RabbitMqEnv::RABBITMQ_SCHEME, 'http');
    }

    /**
     * @return string[]
     */
    public function getConnectionConfigs(): array
    {
        $connections = [];

        foreach ($this->get(RabbitMqEnv::RABBITMQ_CONNECTIONS) as $storeName => $connection) {
            $connections[$storeName] = [
                'name' => $connection[RabbitMqEnv::RABBITMQ_CONNECTION_NAME],
                'host' => $connection[RabbitMqEnv::RABBITMQ_HOST],
                'port' => $connection[RabbitMqEnv::RABBITMQ_PORT],
                'username' => $connection[RabbitMqEnv::RABBITMQ_USERNAME],
                'password' => $connection[RabbitMqEnv::RABBITMQ_PASSWORD],
                'virtualHost' => $connection[RabbitMqEnv::RABBITMQ_VIRTUAL_HOST],
                'storeNames' => $connection[RabbitMqEnv::RABBITMQ_STORE_NAMES],
            ];
        }

        return $connections;
    }

    /**
     * @return string
     */
    public function getApiVirtualHostsUrl(): string
    {
        return sprintf(
            '%s://%s:%s/api/vhosts',
            $this->getApiScheme(),
            $this->getApiHost(),
            $this->getApiPort()
        );
    }

    /**
     * @return string
     */
    public function getApiUsersUrl(): string
    {
        return sprintf(
            '%s://%s:%s/api/users',
            $this->getApiScheme(),
            $this->getApiHost(),
            $this->getApiPort()
        );
    }

    /**
     * @return string
     */
    public function getApiPermissionsUrl(): string
    {
        return sprintf(
            '%s://%s:%s/api/permissions',
            $this->getApiScheme(),
            $this->getApiHost(),
            $this->getApiPort()
        );
    }
}
