<?php

/**
 * MIT License
 * For full license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Spryker\Client\RabbitMq\Model\Connection;

use Generated\Shared\Transfer\QueueConnectionTransfer;

interface ConnectionFactoryInterface
{
    /**
     * @param \Generated\Shared\Transfer\QueueConnectionTransfer $queueConnectionConfig
     *
     * @return \Spryker\Client\RabbitMq\Model\Connection\ConnectionInterface
     */
    public function createConnection(QueueConnectionTransfer $queueConnectionConfig);

    /**
     * @return \Generated\Shared\Transfer\QueueConnectionTransfer[]
     */
    public function getQueueConnectionConfigs();
}
