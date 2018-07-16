<?php

/**
 * MIT License
 * For full license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Spryker\Client\RabbitMq\Model\Connection;

interface ConnectionManagerInterface
{
    /**
     * @param string $queuePoolName
     *
     * @return \PhpAmqpLib\Channel\AMQPChannel[]
     */
    public function getChannelsByQueuePoolName($queuePoolName);

    /**
     * @param string $storeName
     *
     * @return \PhpAmqpLib\Channel\AMQPChannel[]
     */
    public function getChannelsByStoreName($storeName);

    /**
     * @return \PhpAmqpLib\Channel\AMQPChannel
     */
    public function getDefaultChannel();
}
