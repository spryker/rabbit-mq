<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Shared\RabbitMq;

use Spryker\Shared\Queue\QueueConstants;

interface RabbitMqEnv extends QueueConstants
{
    /**
     * Specification:
     * - Use this constant to define the available RabbitMQ connections.
     *
     * @api
     */
    const RABBITMQ_CONNECTIONS = 'RABBITMQ:RABBITMQ_CONNECTIONS';

    /**
     * Specification:
     * - Use this constant to state which RabbitMQ connection is the default connection.
     *
     * @api
     */
    const RABBITMQ_DEFAULT_CONNECTION = 'RABBITMQ:RABBITMQ_DEFAULT_CONNECTION';

    /**
     * Specification:
     * - Use this constant to set a logical name for a RabbitMQ connection.
     *
     * @api
     */
    const RABBITMQ_CONNECTION_NAME = 'RABBITMQ:RABBITMQ_CONNECTION_NAME';

    /**
     * Specification:
     * - Use this constant to configure the host of a RabbitMQ connection.
     *
     * @api
     */
    const RABBITMQ_HOST = 'RABBITMQ:RABBITMQ_HOST';

    /**
     * Specification:
     * - Use this constant to configure the port of a RabbitMQ connection.
     *
     * @api
     */
    const RABBITMQ_PORT = 'RABBITMQ:RABBITMQ_PORT';

    /**
     * Specification:
     * - Use this constant to configure the username of a RabbitMQ connection.
     *
     * @api
     */
    const RABBITMQ_USERNAME = 'RABBITMQ:RABBITMQ_USERNAME';

    /**
     * Specification:
     * - Use this constant to configure the password of a RabbitMQ connection.
     *
     * @api
     */
    const RABBITMQ_PASSWORD = 'RABBITMQ:RABBITMQ_PASSWORD';

    /**
     * Specification:
     * - Use this constant to configure the virtual host of a RabbitMQ connection.
     *
     * @api
     */
    const RABBITMQ_VIRTUAL_HOST = 'RABBITMQ:RABBITMQ_VIRTUAL_HOST';

    /**
     * Specification:
     * - Use this constant to configure the list of Store names associated with a connection.
     */
    const RABBITMQ_STORE_NAMES = 'RABBITMQ:RABBITMQ_STORE_NAMES';

    /**
     * Specification:
     * - Use this constant to configure the host of the API.
     *
     * @api
     */
    const RABBITMQ_API_HOST = 'RABBITMQ:RABBITMQ_API_HOST';

    /**
     * Specification:
     * - Use this constant to configure the port of the API.
     *
     * @api
     */
    const RABBITMQ_API_PORT = 'RABBITMQ:RABBITMQ_API_PORT';

    /**
     * Specification:
     * - Use this constant to configure the username of the API access.
     *
     * @api
     */
    const RABBITMQ_API_USERNAME = 'RABBITMQ:RABBITMQ_API_USERNAME';

    /**
     * Specification:
     * - Use this constant to configure the password of the API access.
     *
     * @api
     */
    const RABBITMQ_API_PASSWORD = 'RABBITMQ:RABBITMQ_API_PASSWORD';

    /**
     * Specification:
     * - List of exchanges which should not be deleted.
     * - PCRE compatible pattern or full names of exchanges can be used.
     *
     * @api
     */
    const RABBITMQ_EXCHANGE_BLACKLIST = 'RABBITMQ:RABBITMQ_EXCHANGE_BLACKLIST';
}
