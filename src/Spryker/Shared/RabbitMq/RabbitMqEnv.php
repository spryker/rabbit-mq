<?php

/**
 * MIT License
 * For full license information, please view the LICENSE file that was distributed with this source code.
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
    public const RABBITMQ_CONNECTIONS = 'RABBITMQ:RABBITMQ_CONNECTIONS';

    /**
     * Specification:
     * - Use this constant to state which RabbitMQ connection is the default connection.
     *
     * @api
     */
    public const RABBITMQ_DEFAULT_CONNECTION = 'RABBITMQ:RABBITMQ_DEFAULT_CONNECTION';

    /**
     * Specification:
     * - Use this constant to set a logical name for a RabbitMQ connection.
     *
     * @api
     */
    public const RABBITMQ_CONNECTION_NAME = 'RABBITMQ:RABBITMQ_CONNECTION_NAME';

    /**
     * Specification:
     * - Use this constant to configure the scheme of a RabbitMQ connection.
     *
     * @api
     */
    public const RABBITMQ_SCHEME = 'RABBITMQ:RABBITMQ_SCHEME';

    /**
     * Specification:
     * - Use this constant to configure the host of a RabbitMQ connection.
     *
     * @api
     */
    public const RABBITMQ_HOST = 'RABBITMQ:RABBITMQ_HOST';

    /**
     * Specification:
     * - Use this constant to configure the port of a RabbitMQ connection.
     *
     * @api
     */
    public const RABBITMQ_PORT = 'RABBITMQ:RABBITMQ_PORT';

    /**
     * Specification:
     * - Use this constant to configure the username of a RabbitMQ connection.
     *
     * @api
     */
    public const RABBITMQ_USERNAME = 'RABBITMQ:RABBITMQ_USERNAME';

    /**
     * Specification:
     * - Use this constant to configure the password of a RabbitMQ connection.
     *
     * @api
     */
    public const RABBITMQ_PASSWORD = 'RABBITMQ:RABBITMQ_PASSWORD';

    /**
     * Specification:
     * - Use this constant to configure the virtual host of a RabbitMQ connection.
     *
     * @api
     */
    public const RABBITMQ_VIRTUAL_HOST = 'RABBITMQ:RABBITMQ_VIRTUAL_HOST';

    /**
     * Specification:
     * - Use this constant to configure the list of Store names associated with a connection.
     */
    public const RABBITMQ_STORE_NAMES = 'RABBITMQ:RABBITMQ_STORE_NAMES';

    /**
     * Specification:
     * - Use this constant to configure the virtual host of the API.
     *
     * @api
     */
    public const RABBITMQ_API_VIRTUAL_HOST = 'RABBITMQ:RABBITMQ_API_VIRTUAL_HOST';

    /**
     * Specification:
     * - Use this constant to configure the host of the API.
     *
     * @api
     */
    public const RABBITMQ_API_HOST = 'RABBITMQ:RABBITMQ_API_HOST';

    /**
     * Specification:
     * - Use this constant to configure the port of the API.
     *
     * @api
     */
    public const RABBITMQ_API_PORT = 'RABBITMQ:RABBITMQ_API_PORT';

    /**
     * Specification:
     * - Use this constant to configure the username of the API access.
     *
     * @api
     */
    public const RABBITMQ_API_USERNAME = 'RABBITMQ:RABBITMQ_API_USERNAME';

    /**
     * Specification:
     * - Use this constant to configure the password of the API access.
     *
     * @api
     */
    public const RABBITMQ_API_PASSWORD = 'RABBITMQ:RABBITMQ_API_PASSWORD';

    /**
     * Specification:
     * - List of exchanges which should not be deleted.
     * - PCRE compatible pattern or full names of exchanges can be used.
     *
     * @api
     */
    public const RABBITMQ_EXCHANGE_BLACKLIST = 'RABBITMQ:RABBITMQ_EXCHANGE_BLACKLIST';

    /**
     * Specification:
     * - List of all queue pools available for stores.
     *
     * @api
     */
    public const RABBIT_MQ_QUEUE_POOLS = 'RABBITMQ:RABBIT_MQ_QUEUE_POOLS';
}
