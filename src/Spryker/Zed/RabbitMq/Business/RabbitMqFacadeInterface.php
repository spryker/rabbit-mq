<?php

/**
 * MIT License
 * For full license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Spryker\Zed\RabbitMq\Business;

use Psr\Log\LoggerInterface;
use Generated\Shared\Transfer\RabbitMqQueueCollectionTransfer;

/**
 * @method \Spryker\Zed\RabbitMq\Business\RabbitMqBusinessFactory getFactory()
 */
interface RabbitMqFacadeInterface
{
    /**
     * Specification:
     * - Purges all existing queues.
     *
     * @api
     *
     * @param \Psr\Log\LoggerInterface $logger
     *
     * @return bool
     */
    public function purgeAllQueues(LoggerInterface $logger);

    /**
     * Specification:
     * - Deletes all existing queues.
     *
     * @api
     *
     * @param \Psr\Log\LoggerInterface $logger
     *
     * @return bool
     */
    public function deleteAllQueues(LoggerInterface $logger);

    /**
     * Specification:
     * - Deletes all exchanges except amq ones.
     *
     * @api
     *
     * @param \Psr\Log\LoggerInterface $logger
     *
     * @return bool
     */
    public function deleteAllExchanges(LoggerInterface $logger);

    /**
     * Specification:
     * - Set up all queues and exchanges for the default connection.
     *
     * @api
     *
     * @return void
     */
    public function setupConnection(): void;

    /**
     * Specification:
     * - Sets the user permissions.
     *
     * @api
     *
     * @param \Psr\Log\LoggerInterface $logger
     *
     * @return bool
     */
    public function setUserPermissions(LoggerInterface $logger);

    /**
     * @api
     *
     * @return \Generated\Shared\Transfer\RabbitMqQueueCollectionTransfer
     */
    public function getQueues(): RabbitMqQueueCollectionTransfer;
}
