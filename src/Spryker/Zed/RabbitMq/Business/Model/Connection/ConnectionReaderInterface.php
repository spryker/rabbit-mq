<?php

/**
 * MIT License
 * For full license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Spryker\Zed\RabbitMq\Business\Model\Connection;

interface ConnectionReaderInterface
{
    /**
     * @return \Generated\Shared\Transfer\QueueConnectionTransfer[]
     */
    public function getConnectionTransferCollection(): array;

    /**
     * @return \Generated\Shared\Transfer\QueueConnectionTransfer[]
     */
    public function getFilteredConnectionTransferCollection(): array;
}
