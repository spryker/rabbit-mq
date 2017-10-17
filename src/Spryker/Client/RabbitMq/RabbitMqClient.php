<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Client\RabbitMq;

use Spryker\Client\Kernel\AbstractClient;

/**
 * @method \Spryker\Client\RabbitMq\RabbitMqFactory getFactory()
 */
class RabbitMqClient extends AbstractClient implements RabbitMqClientInterface
{
    /**
     * @api
     *
     * @return \Spryker\Client\Queue\Model\Adapter\AdapterInterface
     */
    public function createQueueAdapter()
    {
        return $this->getFactory()->createQueueAdapter();
    }
}
