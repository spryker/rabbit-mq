<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Client\RabbitMq\Model\Connection;

use PhpAmqpLib\Channel\AMQPChannel;

interface ConnectionManagerInterface
{
    /**
     * @param string $queuePoolName
     * @param string|null $localeCode
     *
     * @return array<\Spryker\Client\RabbitMq\Model\Connection\ChannelInterface>
     */
    public function getChannelsByQueuePoolName(string $queuePoolName, ?string $localeCode): array;

    /**
     * @param string $storeName
     * @param string|null $localeCode
     *
     * @return array<\Spryker\Client\RabbitMq\Model\Connection\ChannelInterface>
     */
    public function getChannelsByStoreName(string $storeName, ?string $localeCode): array;

    /**
     * @return \PhpAmqpLib\Channel\AMQPChannel
     */
    public function getDefaultChannel(): AMQPChannel;

    /**
     * @return \Spryker\Client\RabbitMq\Model\Connection\ConnectionInterface
     */
    public function getDefaultConnection(): ConnectionInterface;
}
