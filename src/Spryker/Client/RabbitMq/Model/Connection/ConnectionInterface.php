<?php

/**
 * MIT License
 * For full license information, please view the LICENSE file that was distributed with this source code.
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

    /**
     * @return string[]
     */
    public function getStoreNames();

    /**
     * @return string
     */
    public function getVirtualHost();

    /**
     * @return void
     */
    public function setupQueuesAndExchanges(): void;
}
