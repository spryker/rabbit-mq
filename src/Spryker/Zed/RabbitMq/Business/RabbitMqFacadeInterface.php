<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\RabbitMq\Business;

use Psr\Log\LoggerInterface;

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
     * {@inheritdoc}
     *
     * @api
     *
     * @param \Psr\Log\LoggerInterface $logger
     *
     * @return bool
     */
    public function setAdminPermissions(LoggerInterface $logger);
}
