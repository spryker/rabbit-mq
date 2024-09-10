<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\RabbitMq\Business\Model\Exchange;

use Psr\Log\LoggerInterface;
use Spryker\Client\RabbitMq\Model\RabbitMqAdapterInterface;
use Spryker\Zed\RabbitMq\Business\Model\Exchange\Filter\ExchangeFilterInterface;

class Exchange implements ExchangeInterface
{
    /**
     * @var \Spryker\Zed\RabbitMq\Business\Model\Exchange\ExchangeInfoInterface
     */
    protected $exchangeInfo;

    /**
     * @var \Spryker\Client\RabbitMq\Model\RabbitMqAdapterInterface
     */
    protected $queueAdapter;

    /**
     * @var \Spryker\Zed\RabbitMq\Business\Model\Exchange\Filter\ExchangeFilterInterface
     */
    protected $exchangeFilter;

    /**
     * @param \Spryker\Zed\RabbitMq\Business\Model\Exchange\ExchangeInfoInterface $exchangeInfo
     * @param \Spryker\Client\RabbitMq\Model\RabbitMqAdapterInterface $queueAdapter
     * @param \Spryker\Zed\RabbitMq\Business\Model\Exchange\Filter\ExchangeFilterInterface $exchangeFilter
     */
    public function __construct(ExchangeInfoInterface $exchangeInfo, RabbitMqAdapterInterface $queueAdapter, ExchangeFilterInterface $exchangeFilter)
    {
        $this->exchangeInfo = $exchangeInfo;
        $this->queueAdapter = $queueAdapter;
        $this->exchangeFilter = $exchangeFilter;
    }

    /**
     * @param \Psr\Log\LoggerInterface $logger
     *
     * @return bool
     */
    public function deleteAllExchanges(LoggerInterface $logger)
    {
        $rabbitMyExchangeCollectionTransfer = $this->exchangeFilter->filter(
            $this->exchangeInfo->getExchanges(),
        );

        foreach ($rabbitMyExchangeCollectionTransfer->getRabbitMqExchanges() as $rabbitMqExchangeTransfer) {
            $this->queueAdapter->deleteExchange($rabbitMqExchangeTransfer->getName());
            $logger->info(sprintf('Delete exchange "%s" request send.', $rabbitMqExchangeTransfer->getName()));
        }

        return true;
    }
}
