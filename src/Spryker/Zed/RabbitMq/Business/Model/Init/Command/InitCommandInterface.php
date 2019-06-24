<?php

/**
 * MIT License
 * For full license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Spryker\Zed\RabbitMq\Business\Model\Init\Command;

use Generated\Shared\Transfer\QueueConnectionTransfer;

interface InitCommandInterface
{
    /**
     * @param \Generated\Shared\Transfer\QueueConnectionTransfer $queueConnectionTransfer
     *
     * @return bool
     */
    public function exec(QueueConnectionTransfer $queueConnectionTransfer): bool;
}
