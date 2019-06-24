<?php

/**
 * MIT License
 * For full license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Spryker\Zed\RabbitMq\Business\Model\User\Reader;

use Generated\Shared\Transfer\QueueConnectionTransfer;

interface UserReaderInterface
{
    /**
     * @param \Generated\Shared\Transfer\QueueConnectionTransfer $queueConnectionTransfer
     *
     * @return bool
     */
    public function has(QueueConnectionTransfer $queueConnectionTransfer): bool;
}
