<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\RabbitMq\Business\Model\Queue;

interface QueueInfoInterface
{
    /**
     * @param array<string> $queueNames
     *
     * @return bool
     */
    public function areQueuesEmpty(array $queueNames): bool;

    /**
     * @return \Generated\Shared\Transfer\RabbitMqQueueCollectionTransfer
     */
    public function getQueues();
}
