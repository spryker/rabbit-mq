<?php

/**
 * Copyright © 2017-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\RabbitMq\Business\Model\Exchange;

interface ExchangeInfoInterface
{
    /**
     * @return array
     */
    public function getAllExchangeNames();
}
