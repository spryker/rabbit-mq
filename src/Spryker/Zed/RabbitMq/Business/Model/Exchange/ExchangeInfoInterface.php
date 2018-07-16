<?php

/**
 * MIT License
 * For full license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Spryker\Zed\RabbitMq\Business\Model\Exchange;

interface ExchangeInfoInterface
{
    /**
     * @return \Generated\Shared\Transfer\RabbitMqExchangeCollectionTransfer
     */
    public function getExchanges();
}
