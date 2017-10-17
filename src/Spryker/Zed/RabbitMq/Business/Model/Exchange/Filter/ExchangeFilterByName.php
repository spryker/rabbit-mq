<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\RabbitMq\Business\Model\Exchange\Filter;

use Generated\Shared\Transfer\RabbitMqExchangeCollectionTransfer;
use Generated\Shared\Transfer\RabbitMqExchangeTransfer;
use Exception;

class ExchangeFilterByName implements ExchangeFilterInterface
{
    /**
     * @var array
     */
    protected $exchangeNameBlacklist;

    /**
     * @param array $exchangeNameBlacklist
     */
    public function __construct(array $exchangeNameBlacklist)
    {
        $this->exchangeNameBlacklist = $exchangeNameBlacklist;
    }

    /**
     * @param \Generated\Shared\Transfer\RabbitMqExchangeCollectionTransfer $rabbitMqExchangeCollectionTransfer
     *
     * @return \Generated\Shared\Transfer\RabbitMqExchangeCollectionTransfer
     */
    public function filter(RabbitMqExchangeCollectionTransfer $rabbitMqExchangeCollectionTransfer)
    {
        $filteredRabbitMqExchangeCollectionTransfer = new RabbitMqExchangeCollectionTransfer();

        foreach ($rabbitMqExchangeCollectionTransfer->getRabbitMqExchanges() as $rabbitMqExchangeTransfer) {
            if (!$this->isBlacklisted($rabbitMqExchangeTransfer)) {
                $filteredRabbitMqExchangeCollectionTransfer->addRabbitMqExchange($rabbitMqExchangeTransfer);
            }
        }

        return $filteredRabbitMqExchangeCollectionTransfer;
    }

    /**
     * @param \Generated\Shared\Transfer\RabbitMqExchangeTransfer $rabbitMqExchangeTransfer
     *
     * @return bool
     */
    protected function isBlacklisted(RabbitMqExchangeTransfer $rabbitMqExchangeTransfer)
    {
        if (in_array($rabbitMqExchangeTransfer->getName(), $this->exchangeNameBlacklist)) {
            return true;
        }

        foreach ($this->exchangeNameBlacklist as $blacklistedExchangePattern) {
            if ($this->isPattern($blacklistedExchangePattern) && preg_match($blacklistedExchangePattern, $rabbitMqExchangeTransfer->getName())) {
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
        try {
            return (preg_match($pattern, null) !== false);
        } catch (Exception $exception) {
            return false;
        }
    }
}
