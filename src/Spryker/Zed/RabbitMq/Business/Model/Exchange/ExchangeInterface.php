<?php

/**
 * MIT License
 * For full license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Spryker\Zed\RabbitMq\Business\Model\Exchange;

use Psr\Log\LoggerInterface;

interface ExchangeInterface
{
    /**
     * @param \Psr\Log\LoggerInterface $logger
     *
     * @return bool
     */
    public function deleteAllExchanges(LoggerInterface $logger);
}
