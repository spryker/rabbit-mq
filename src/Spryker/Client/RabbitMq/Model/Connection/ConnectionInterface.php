<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Client\RabbitMq\Model\Connection;

interface ConnectionInterface
{
    /**
     * @return \PhpAmqpLib\Channel\AMQPChannel
     */
    public function getChannel();

    /**
     * @return string
     */
    public function getName();

    /**
     * @return bool
     */
    public function getIsDefaultConnection();
}
