<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\RabbitMq\Business\Model\Exchange;

use Spryker\Client\Queue\Model\Adapter\AdapterInterface;

class Exchange implements ExchangeInterface
{
    /**
     * @var \Spryker\Zed\RabbitMq\Business\Model\Exchange\ExchangeInfoInterface
     */
    protected $exchangeInfo;

    /**
     * @var \Spryker\Client\Queue\Model\Adapter\AdapterInterface
     */
    protected $queueAdapter;

    /**
     * @var array
     */
    protected $exchangeBlackList;

    /**
     * @param \Spryker\Zed\RabbitMq\Business\Model\Exchange\ExchangeInfoInterface $exchangeInfo
     * @param \Spryker\Client\Queue\Model\Adapter\AdapterInterface $queueAdapter
     * @param array $exchangeBlackList
     */
    public function __construct(ExchangeInfoInterface $exchangeInfo, AdapterInterface $queueAdapter, array $exchangeBlackList)
    {
        $this->exchangeInfo = $exchangeInfo;
        $this->queueAdapter = $queueAdapter;
        $this->exchangeBlackList = $exchangeBlackList;
    }

    /**
     * @return bool
     */
    public function deleteAllExchanges()
    {
        $isSuccess = true;
        foreach ($this->exchangeInfo->getAllExchangeNames() as $exchangeName) {
            if ($this->isBlackListed($exchangeName)) {
                continue;
            }

            if (!$this->queueAdapter->deleteExchange($exchangeName)) {
                $isSuccess = false;
            }
        }

        return $isSuccess;
    }

    /**
     * @param string $exchangeName
     *
     * @return bool
     */
    protected function isBlacklisted($exchangeName)
    {
        if (in_array($exchangeName, $this->exchangeBlackList)) {
            return true;
        }

        foreach ($this->exchangeBlackList as $blacklistedExchangePattern) {
            if ($this->isPattern($blacklistedExchangePattern) && preg_match($blacklistedExchangePattern, $exchangeName)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param string $pattern
     *
     * @return bool
     */
    protected function isPattern($pattern)
    {
        if (@preg_match($pattern, null) === false) {
            return false;
        }

        return true;
    }
}
