<?php

/**
 * MIT License
 * For full license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Spryker\Client\RabbitMq\Model\Connection\ConnectionCreator;

use Generated\Shared\Transfer\QueueConnectionTransfer;
use Spryker\Client\RabbitMq\Model\Connection\ConnectionInterface;

interface ConnectionCreatorInterface
{
    /**
     * @param \Generated\Shared\Transfer\QueueConnectionTransfer[] $connectionsConfig
     *
     * @return \Spryker\Client\RabbitMq\Model\Connection\ConnectionInterface[]
     */
    public function createConnectionsByConfig(array $connectionsConfig): array;

    /**
     * @param \Generated\Shared\Transfer\QueueConnectionTransfer $connectionConfig
     *
     * @return \Spryker\Client\RabbitMq\Model\Connection\ConnectionInterface
     */
    public function createConnectionByConfig(QueueConnectionTransfer $connectionConfig): ConnectionInterface;
}
