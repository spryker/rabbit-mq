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
     * @param string|null $locale
     *
     * @return \PhpAmqpLib\Channel\AMQPChannel[]
     */
    public function getChannelsByQueuePoolName(string $queuePoolName, ?string $locale): array;

    /**
     * @param string $storeName
     * @param string|null $locale
     *
     * @return \PhpAmqpLib\Channel\AMQPChannel[]
     */
    public function getChannelsByStoreName(string $storeName, ?string $locale): array;

    /**
     * @return \PhpAmqpLib\Channel\AMQPChannel
     */
    public function getDefaultChannel(): AMQPChannel;
}
