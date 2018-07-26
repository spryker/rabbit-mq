<?php

/**
 * MIT License
 * For full license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Spryker\Zed\RabbitMq\Business\Model\Permission;

use Psr\Log\LoggerInterface;

interface UserPermissionHandlerInterface
{
    /**
     * @param \Psr\Log\LoggerInterface $logger
     *
     * @return bool
     */
    public function setPermissions(LoggerInterface $logger);
}
