<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\RabbitMq\Business\Model\Exchange;

interface ExchangeInfoInterface
{
    /**
     * @return \Generated\Shared\Transfer\RabbitMqExchangeCollectionTransfer
     */
    public function getExchanges();
}
