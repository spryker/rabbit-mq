<?php

/**
 * MIT License
 * For full license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Spryker\Client\RabbitMq\Model\Connection;

use PhpAmqpLib\Channel\AMQPChannel;

interface ConnectionManagerInterface
{
    /**
     * @param string $queuePoolName
     * @param string|null $localeCode
     *
     * @return \PhpAmqpLib\Channel\AMQPChannel[]
     */
    public function getChannelsByQueuePoolName(string $queuePoolName, ?string $localeCode): array;

    /**
     * @param string $storeName
     * @param string|null $localeCode
     *
     * @return \PhpAmqpLib\Channel\AMQPChannel[]
     */
    public function getChannelsByStoreName(string $storeName, ?string $localeCode): array;

    /**
     * @return \PhpAmqpLib\Channel\AMQPChannel
     */
    public function getDefaultChannel(): AMQPChannel;

    /**
     * @return void
     */
    public function setupQueuesAndExchanges(): void;
}
