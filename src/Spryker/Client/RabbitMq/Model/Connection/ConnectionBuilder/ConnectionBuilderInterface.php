<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Client\RabbitMq\Model\Connection\ConnectionBuilder;

use Generated\Shared\Transfer\QueueConnectionTransfer;
use Spryker\Client\RabbitMq\Model\Connection\ConnectionInterface;

interface ConnectionBuilderInterface
{
    /**
     * @param array<\Generated\Shared\Transfer\QueueConnectionTransfer> $queueConnectionTransfers
     *
     * @return array<\Spryker\Client\RabbitMq\Model\Connection\ConnectionInterface>
     */
    public function createConnectionsByQueueConnectionTransfers(array $queueConnectionTransfers): array;

    public function createConnectionByQueueConnectionTransfer(QueueConnectionTransfer $queueConnectionTransfer): ConnectionInterface;
}
