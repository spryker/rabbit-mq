<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\RabbitMq\Business\Model\Queue;

use Psr\Log\LoggerInterface;

interface QueueInterface
{
    /**
     * @param \Psr\Log\LoggerInterface $logger
     *
     * @return bool
     */
    public function deleteAllQueues(LoggerInterface $logger);

    /**
     * @param \Psr\Log\LoggerInterface $logger
     *
     * @return bool
     */
    public function purgeAllQueues(LoggerInterface $logger);
}
