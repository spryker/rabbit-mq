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
}
