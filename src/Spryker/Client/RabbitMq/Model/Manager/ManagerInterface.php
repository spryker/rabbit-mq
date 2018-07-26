<?php

/**
 * MIT License
 * For full license information, please view the LICENSE file that was distributed with this source code.
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
