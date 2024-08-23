<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
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
