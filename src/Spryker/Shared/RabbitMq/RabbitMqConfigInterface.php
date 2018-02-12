<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Shared\RabbitMq;

interface RabbitMqConfigInterface
{
    /**
     * Value is used to indicate that the default pool should be used for the publish of the corresponding message.
     */
    const QUEUE_POOL_NAME_DEFAULT = 'QUEUE_POOL_NAME_DEFAULT';
}
