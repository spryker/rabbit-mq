<?php

/**
 * MIT License
 * For full license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Spryker\Zed\RabbitMq\Business\Model\Exchange\Filter;

use Generated\Shared\Transfer\RabbitMqExchangeCollectionTransfer;
use Generated\Shared\Transfer\RabbitMqExchangeTransfer;
use Throwable;

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
        } catch (Throwable $exception) {
            return false;
        }
    }
}
