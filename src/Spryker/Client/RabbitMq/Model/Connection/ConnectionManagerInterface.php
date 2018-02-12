<?php
/**
 * Created by PhpStorm.
 * User: karolygerner
 * Date: 12.February.2018
 * Time: 12:38
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
     * @return \PhpAmqpLib\Channel\AMQPChannel
     */
    public function getDefaultChannel();
}
