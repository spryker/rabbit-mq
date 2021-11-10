<?php

/**
 * MIT License
 * For full license information, please view the LICENSE file that was distributed with this source code.
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
