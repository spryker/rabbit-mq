<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Client\RabbitMq;

use Spryker\Client\RabbitMq\Model\Connection\ConnectionInterface;

interface RabbitMqClientInterface
{
    /**
     * Specification:
     *  - Creates an instance of a concrete adapter
     *
     * @api
     *
     * @return \Spryker\Client\Queue\Model\Adapter\AdapterInterface
     */
    public function createQueueAdapter();

    /**
     * Specification:
     *  - Return default connection.
     *
     * @api
     *
     * @return \Spryker\Client\RabbitMq\Model\Connection\ConnectionInterface
     */
    public function getConnection(): ConnectionInterface;
}
