<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Client\RabbitMq\Model\Connection\QueueConnectionTransferMapper;

interface QueueConnectionTransferMapperInterface
{
    /**
     * @return array<array<\Generated\Shared\Transfer\QueueConnectionTransfer>>
     */
    public function mapQueueConnectionTransfersByStoreName(): array;

    /**
     * @param array<array<string>> $queuePools Keys are pool names, values are lists of connection names.
     *
     * @return array<array<\Generated\Shared\Transfer\QueueConnectionTransfer>>
     */
    public function mapQueueConnectionTransfersByPoolName(array $queuePools): array;
}
