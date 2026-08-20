<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Client\RabbitMq\Model;

use Generated\Shared\Transfer\QueueMetricsRequestTransfer;
use Generated\Shared\Transfer\QueueMetricsResponseTransfer;
use Spryker\Client\Queue\Model\Adapter\AdapterInterface;
use Spryker\Client\RabbitMq\Model\Manager\ManagerInterface;

interface RabbitMqAdapterInterface extends AdapterInterface, ManagerInterface
{
    /**
     * @param \Generated\Shared\Transfer\QueueMetricsRequestTransfer $queueMetricsRequestTransfer
     *
     * @return \Generated\Shared\Transfer\QueueMetricsResponseTransfer
     */
    public function getQueueMetrics(QueueMetricsRequestTransfer $queueMetricsRequestTransfer): QueueMetricsResponseTransfer;
}
