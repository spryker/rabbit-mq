<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Shared\RabbitMq;

use Spryker\Shared\Queue\QueueConstants;

interface RabbitMqConstants extends QueueConstants
{
    const RABBITMQ_HOST = 'RABBITMQ_HOST';
    const RABBITMQ_PORT = 'RABBITMQ_PORT';

    const RABBITMQ_USERNAME = 'RABBITMQ_USERNAME';
    const RABBITMQ_PASSWORD = 'RABBITMQ_PASSWORD';
    const RABBITMQ_VIRTUAL_HOST = 'RABBITMQ_VIRTUAL_HOST';

    /**
     * Specification:
     * - Api host.
     *
     * @api
     */
    const RABBIT_MQ_API_HOST = 'RABBITMQ:RABBIT_MQ_API_HOST';

    /**
     * Specification:
     * - Api port.
     *
     * @api
     */
    const RABBIT_MQ_API_PORT = 'RABBITMQ:RABBIT_MQ_API_PORT';

    /**
     * Specification:
     * - Api username.
     *
     * @api
     */
    const RABBIT_MQ_API_USERNAME = 'RABBITMQ:RABBIT_MQ_API_USERNAME';

    /**
     * Specification:
     * - Api password.
     *
     * @api
     */
    const RABBIT_MQ_API_PASSWORD = 'RABBITMQ:RABBIT_MQ_API_PASSWORD';

    /**
     * Specification:
     * - List of exchanges which shouldn't be deleted.
     * - PCRE compatible pattern or full names of exchanges can be used.
     *
     * @api
     */
    const RABBIT_MQ_EXCHANGE_BLACKLIST = 'RABBITMQ:RABBIT_MQ_EXCHANGE_BLACKLIST';
}
