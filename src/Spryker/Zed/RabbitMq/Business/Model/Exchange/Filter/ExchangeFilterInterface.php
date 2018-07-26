<?php

/**
 * MIT License
 * For full license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Spryker\Zed\RabbitMq\Business\Model\Exchange\Filter;

use Generated\Shared\Transfer\RabbitMqExchangeCollectionTransfer;

interface ExchangeFilterInterface
{
    /**
     * @param \Generated\Shared\Transfer\RabbitMqExchangeCollectionTransfer $rabbitMqExchangeCollectionTransfer
     *
     * @return \Generated\Shared\Transfer\RabbitMqExchangeCollectionTransfer
     */
    public function filter(RabbitMqExchangeCollectionTransfer $rabbitMqExchangeCollectionTransfer);
}
