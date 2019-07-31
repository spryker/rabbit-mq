<?php

/**
 * MIT License
 * For full license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Spryker\Client\RabbitMq\Model\Connection\ConnectionConfigMapper;

interface ConnectionConfigMapperInterface
{
    /**
     * @return \Generated\Shared\Transfer\QueueConnectionTransfer[][]
     */
    public function mapConnectionsConfigByStoreName(): array;

    /**
     * @param string[][] $queuePools Keys are pool names, values are lists of connection names.
     *
     * @return \Generated\Shared\Transfer\QueueConnectionTransfer[][]
     */
    public function mapConnectionsConfigByPoolName(array $queuePools): array;
}
