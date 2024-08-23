<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Client\RabbitMq\Model\Manager;

use Spryker\Client\Queue\Model\Internal\ManagerInterface as SprykerManagerInterface;

interface ManagerInterface extends SprykerManagerInterface
{
    /**
     * @param string $exchangeName
     *
     * @return bool
     */
    public function deleteExchange($exchangeName);
}
