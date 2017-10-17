<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\RabbitMq\Business;

use Spryker\Zed\Kernel\Business\AbstractBusinessFactory;
use Spryker\Zed\RabbitMq\Business\Model\Exchange\Filter\ExchangeFilterByName;
use Spryker\Zed\RabbitMq\Business\Model\Queue\Queue;
use Spryker\Zed\RabbitMq\Business\Model\Queue\QueueInfo;
use Spryker\Zed\RabbitMq\RabbitMqDependencyProvider;
use Spryker\Zed\RabbitMq\Business\Model\Exchange\Exchange;
use Spryker\Zed\RabbitMq\Business\Model\Exchange\ExchangeInfo;

/**
 * @method \Spryker\Zed\RabbitMq\RabbitMqConfig getConfig()
 */
class RabbitMqBusinessFactory extends AbstractBusinessFactory
{
    /**
     * @return \Spryker\Zed\RabbitMq\Business\Model\Queue\QueueInterface
     */
    public function createQueue()
    {
        return new Queue(
            $this->createQueueInfo(),
            $this->getQueueAdapter()
        );
    }

    /**
     * @return \Spryker\Zed\RabbitMq\Business\Model\Queue\QueueInfoInterface
     */
    protected function createQueueInfo()
    {
        return new QueueInfo(
            $this->getConfig()->getApiQueuesUrl(),
            $this->getConfig()->getApiUsername(),
            $this->getConfig()->getApiPassword()
        );
    }

    /**
     * @return \Spryker\Zed\RabbitMq\Business\Model\Exchange\ExchangeInterface
     */
    public function createExchange()
    {
        return new Exchange(
            $this->createExchangeInfo(),
            $this->getQueueAdapter(),
            $this->createExchangeFilter()
        );
    }

    /**
     * @return \Spryker\Zed\RabbitMq\Business\Model\Exchange\ExchangeInfoInterface
     */
    protected function createExchangeInfo()
    {
        return new ExchangeInfo(
            $this->getConfig()->getApiExchangesUrl(),
            $this->getConfig()->getApiUsername(),
            $this->getConfig()->getApiPassword()
        );
    }

    /**
     * @return \Spryker\Zed\RabbitMq\Business\Model\Exchange\Filter\ExchangeFilterInterface
     */
    protected function createExchangeFilter()
    {
        return new ExchangeFilterByName($this->getConfig()->getExchangeNameBlacklist());
    }

    /**
     * @return \Spryker\Client\Queue\Model\Adapter\AdapterInterface
     */
    protected function getQueueAdapter()
    {
        return $this->getProvidedDependency(RabbitMqDependencyProvider::QUEUE_ADAPTER);
    }
}
